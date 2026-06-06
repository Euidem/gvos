<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceTask;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkspaceFileController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve workspace role and abort 403 if the user has no access.
     */
    private function requireAccess(Request $request, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($request->user());

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    /**
     * True if the role can view/upload internal files (admin / workspace_admin / manager).
     */
    private function canViewInternal(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * True if the role can upload files (any role except observer and none).
     */
    private function canUpload(string $role): bool
    {
        return ! in_array($role, ['observer', 'none'], true);
    }

    /**
     * True if the user may delete a file.
     * Allowed if: uploader of the file, OR admin/workspace_admin/manager.
     */
    private function canDelete(string $role, WorkspaceFile $file, int $userId): bool
    {
        if ($this->canViewInternal($role)) {
            return true;
        }

        return (int) $file->uploaded_by_user_id === $userId;
    }

    // ── Shared upload logic ────────────────────────────────────────────────

    /**
     * Validate and persist an uploaded file to local storage.
     * Returns the created WorkspaceFile model.
     */
    private function handleUpload(
        Request      $request,
        Workspace    $workspace,
        string       $role,
        ?WorkspaceTask $task = null
    ): WorkspaceFile {
        if (! $this->canUpload($role)) {
            abort(403, 'Observers cannot upload files.');
        }

        $validated = $request->validate([
            'file'        => [
                'required',
                'file',
                'max:10240',   // 10 MB in KB
                'mimes:' . implode(',', WorkspaceFile::allowedMimes()),
            ],
            'title'       => 'nullable|string|max:255',
            'category'    => 'nullable|in:' . implode(',', array_keys(WorkspaceFile::categoryLabels())),
            'description' => 'nullable|string|max:2000',
            'visibility'  => 'nullable|in:public,internal',
        ]);

        // Non-internal roles cannot upload internal files
        if (! $this->canViewInternal($role)) {
            $validated['visibility'] = 'public';
        }

        $uploadedFile = $request->file('file');
        $extension    = strtolower($uploadedFile->getClientOriginalExtension());
        $storedName   = Str::uuid() . '.' . $extension;
        $storagePath  = 'workspaces/' . $workspace->id . '/' . $storedName;

        // Ensure directory exists, then store
        Storage::disk('local')->makeDirectory('workspaces/' . $workspace->id);
        Storage::disk('local')->put($storagePath, file_get_contents($uploadedFile->getRealPath()));

        $file = WorkspaceFile::create([
            'workspace_id'        => $workspace->id,
            'uploaded_by_user_id' => $request->user()->id,
            'workspace_task_id'   => $task?->id,
            'title'               => $validated['title'] ?? null,
            'original_filename'   => $uploadedFile->getClientOriginalName(),
            'stored_filename'     => $storedName,
            'storage_path'        => $storagePath,
            'mime_type'           => $uploadedFile->getMimeType(),
            'file_size'           => $uploadedFile->getSize(),
            'visibility'          => $validated['visibility'] ?? 'public',
            'category'            => $validated['category'] ?? ($task ? 'task_attachment' : 'general'),
            'description'         => $validated['description'] ?? null,
            'downloads_count'     => 0,
        ]);

        AuditLogger::workspaceFileUploaded($file, [
            'workspace_code' => $workspace->workspace_code,
        ]);

        app(NotificationService::class)->notifyFileUploaded($file, $request->user());

        return $file;
    }

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * Workspace file library index.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $role        = $this->requireAccess($request, $workspace);
        $canUpload   = $this->canUpload($role);
        $seeInternal = $this->canViewInternal($role);

        $filesQuery = $workspace->files()->with(['uploadedBy', 'task']);

        if (! $seeInternal) {
            $filesQuery->where('visibility', 'public');
        }

        $files = $filesQuery->paginate(20)->withQueryString();

        $categoryLabels = WorkspaceFile::categoryLabels();
        $allowedMimes   = WorkspaceFile::allowedMimes();

        return view('workspace.files.index', compact(
            'workspace', 'files', 'role', 'canUpload', 'seeInternal',
            'categoryLabels', 'allowedMimes'
        ));
    }

    /**
     * Upload a file to the workspace file library.
     */
    public function store(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        $this->handleUpload($request, $workspace, $role);

        return redirect()
            ->route('workspace.files.index', $workspace)
            ->with('success', 'File uploaded successfully.');
    }

    /**
     * Upload a file attached to a specific task.
     * Called from the task detail page.
     */
    public function storeForTask(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $role = $this->requireAccess($request, $workspace);

        // Verify the task belongs to this workspace
        if ((int) $task->workspace_id !== (int) $workspace->id) {
            abort(404, 'Task not found in this workspace.');
        }

        $this->handleUpload($request, $workspace, $role, $task);

        return redirect()
            ->route('workspace.tasks.show', [$workspace, $task])
            ->with('success', 'File attached to task.');
    }

    /**
     * Download a file — verifies workspace access and visibility before serving.
     */
    public function download(Request $request, Workspace $workspace, WorkspaceFile $file)
    {
        $role = $this->requireAccess($request, $workspace);

        // Verify the file belongs to this workspace
        if ((int) $file->workspace_id !== (int) $workspace->id) {
            abort(404, 'File not found in this workspace.');
        }

        // Internal files: manager/admin only
        if ($file->isInternal() && ! $this->canViewInternal($role)) {
            abort(403, 'You do not have permission to download this file.');
        }

        // Verify the file exists on disk
        if (! Storage::disk('local')->exists($file->storage_path)) {
            abort(404, 'File not found on storage. Please contact the GVOS team.');
        }

        // Increment download count and log
        $file->increment('downloads_count');

        AuditLogger::workspaceFileDownloaded($file, [
            'workspace_code' => $workspace->workspace_code,
        ]);

        return Storage::disk('local')->download($file->storage_path, $file->original_filename);
    }

    /**
     * Soft-delete a file.
     *
     * Allowed if: the user uploaded the file, OR has admin/workspace_admin/manager role.
     * Note: the physical file on disk is NOT removed here; it is preserved in case
     * a restore is ever needed. A scheduled cleanup command can prune orphaned files.
     */
    public function destroy(Request $request, Workspace $workspace, WorkspaceFile $file)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        // Verify the file belongs to this workspace
        if ((int) $file->workspace_id !== (int) $workspace->id) {
            abort(404, 'File not found in this workspace.');
        }

        if (! $this->canDelete($role, $file, (int) $user->id)) {
            abort(403, 'You cannot delete this file.');
        }

        $file->delete();

        AuditLogger::workspaceFileDeleted($file, [
            'workspace_code' => $workspace->workspace_code,
            'deleted_by'     => $user->id,
        ]);

        // Return to where the user came from (task detail or file index)
        $referer = $request->headers->get('referer', '');

        if (str_contains($referer, '/tasks/')) {
            return back()->with('success', 'File removed.');
        }

        return redirect()
            ->route('workspace.files.index', $workspace)
            ->with('success', 'File deleted.');
    }
}
