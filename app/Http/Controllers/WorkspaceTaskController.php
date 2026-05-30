<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskComment;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class WorkspaceTaskController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve the authenticated user's effective role within a workspace.
     *
     * Returns: 'admin' | 'manager' | 'talent' | 'client' | 'observer' | 'none'
     */
    private function getUserWorkspaceRole(\App\Models\User $user, Workspace $workspace): string
    {
        if ($user->hasAnyRole(['super_admin', 'operations_admin'])) {
            return 'admin';
        }

        $member = $workspace->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($member) {
            return $member->role; // client | talent | manager | observer
        }

        if ($workspace->primary_manager_id === $user->id) {
            return 'manager';
        }

        if ($workspace->primary_talent_id === $user->id) {
            return 'talent';
        }

        return 'none';
    }

    /**
     * Resolve role or abort 403 if the user has no access to the workspace.
     */
    private function requireWorkspaceAccess(\App\Models\User $user, Workspace $workspace): string
    {
        $role = $this->getUserWorkspaceRole($user, $workspace);

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    /**
     * Ensure the task belongs to the given workspace or abort 404.
     */
    private function authorizeTaskBelongsToWorkspace(Workspace $workspace, WorkspaceTask $task): void
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }
    }

    private function isAdminOrManager(string $role): bool
    {
        return in_array($role, ['admin', 'manager']);
    }

    // ── Controller actions ────────────────────────────────────────────────

    /**
     * Task board — tasks grouped by status.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);

        $tasks = $workspace->tasks()
            ->with(['createdBy', 'assignedTo'])
            ->withCount('comments')
            ->get();

        $tasksByStatus = $tasks->groupBy('status');

        $canCreate = ! in_array($role, ['observer']);

        return view('workspace.tasks.index', compact(
            'workspace', 'tasks', 'tasksByStatus', 'role', 'canCreate'
        ));
    }

    /**
     * Task create form.
     */
    public function create(Request $request, Workspace $workspace)
    {
        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);

        if ($role === 'observer') {
            abort(403, 'You cannot create tasks in this workspace.');
        }

        $members = $workspace->activeMembers()->with('user')->get();
        $isAdminOrManager = $this->isAdminOrManager($role);

        return view('workspace.tasks.create', compact(
            'workspace', 'members', 'role', 'isAdminOrManager'
        ));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request, Workspace $workspace)
    {
        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);

        if ($role === 'observer') {
            abort(403);
        }

        $isAdminOrManager = $this->isAdminOrManager($role);

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:10000',
            'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'priority'            => 'required|in:low,normal,high,urgent',
            'due_date'            => 'nullable|date|after_or_equal:today',
            'internal_notes'      => 'nullable|string|max:5000',
        ]);

        if (! $isAdminOrManager) {
            unset($validated['internal_notes']);
        }

        $task = WorkspaceTask::create(array_merge($validated, [
            'workspace_id'       => $workspace->id,
            'created_by_user_id' => $user->id,
            'status'             => 'pending',
            'task_code'          => WorkspaceTask::generateCode(),
        ]));

        AuditLogger::workspaceTaskCreated($task, [
            'workspace_id' => $workspace->id,
            'title'        => $task->title,
        ]);

        return redirect()
            ->route('workspace.tasks.show', [$workspace, $task])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Task detail page.
     */
    public function show(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task);

        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);
        $isAdminOrManager = $this->isAdminOrManager($role);

        $task->load(['createdBy', 'assignedTo', 'workspace']);

        // Clients and talent only see public comments; admins and managers see all.
        $comments = $isAdminOrManager
            ? $task->comments()->with('user')->get()
            : $task->comments()->where('visibility', 'public')->with('user')->get();

        $allowedTransitions = WorkspaceTask::allowedTransitions($task->status, $role);

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id === $user->id && $task->status === 'pending');

        $members = $workspace->activeMembers()->with('user')->get();

        return view('workspace.tasks.show', compact(
            'workspace', 'task', 'comments', 'role', 'isAdminOrManager',
            'allowedTransitions', 'canEdit', 'members'
        ));
    }

    /**
     * Task edit form.
     */
    public function edit(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task);

        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);
        $isAdminOrManager = $this->isAdminOrManager($role);

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id === $user->id && $task->status === 'pending');

        if (! $canEdit) {
            abort(403, 'You cannot edit this task.');
        }

        $members = $workspace->activeMembers()->with('user')->get();

        return view('workspace.tasks.edit', compact(
            'workspace', 'task', 'members', 'role', 'isAdminOrManager'
        ));
    }

    /**
     * Update task fields.
     */
    public function update(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task);

        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);
        $isAdminOrManager = $this->isAdminOrManager($role);

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id === $user->id && $task->status === 'pending');

        if (! $canEdit) {
            abort(403);
        }

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:10000',
            'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'priority'            => 'required|in:low,normal,high,urgent',
            'due_date'            => 'nullable|date',
            'internal_notes'      => 'nullable|string|max:5000',
        ]);

        if (! $isAdminOrManager) {
            unset($validated['internal_notes']);
        }

        $oldAssignee = $task->assigned_to_user_id;
        $task->update($validated);

        AuditLogger::workspaceTaskUpdated($task, [
            'workspace_id' => $workspace->id,
            'changes'      => array_keys($validated),
        ]);

        if ($oldAssignee !== $task->fresh()->assigned_to_user_id) {
            AuditLogger::workspaceTaskAssigned($task, [
                'workspace_id'    => $workspace->id,
                'old_assignee_id' => $oldAssignee,
                'new_assignee_id' => $task->fresh()->assigned_to_user_id,
            ]);
        }

        return redirect()
            ->route('workspace.tasks.show', [$workspace, $task])
            ->with('success', 'Task updated.');
    }

    /**
     * Add a comment to a task.
     */
    public function storeComment(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task);

        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);
        $isAdminOrManager = $this->isAdminOrManager($role);

        $validated = $request->validate([
            'comment'    => 'required|string|max:5000',
            'visibility' => 'nullable|in:public,internal',
        ]);

        // Non-admin/manager cannot post internal comments.
        if (! $isAdminOrManager) {
            $validated['visibility'] = 'public';
        }

        $visibility = $validated['visibility'] ?? 'public';

        WorkspaceTaskComment::create([
            'workspace_task_id' => $task->id,
            'user_id'           => $user->id,
            'comment'           => $validated['comment'],
            'visibility'        => $visibility,
        ]);

        if ($visibility === 'internal') {
            AuditLogger::workspaceTaskInternalCommentAdded($task, [
                'workspace_id' => $workspace->id,
            ]);
        } else {
            AuditLogger::workspaceTaskCommentAdded($task, [
                'workspace_id' => $workspace->id,
            ]);
        }

        return back()->with('success', 'Comment added.');
    }

    /**
     * Advance or change task status.
     */
    public function updateStatus(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task);

        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,blocked,submitted,revision_requested,approved,closed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $allowed   = WorkspaceTask::allowedTransitions($task->status, $role);

        if (! in_array($newStatus, $allowed)) {
            return back()->withErrors(['status' => 'This status transition is not allowed.']);
        }

        $oldStatus = $task->status;

        $timestamps = [];
        if ($newStatus === 'in_progress' && ! $task->started_at) {
            $timestamps['started_at'] = now();
        }
        if ($newStatus === 'submitted') {
            $timestamps['submitted_at'] = now();
        }
        if ($newStatus === 'approved') {
            $timestamps['approved_at'] = now();
        }
        if ($newStatus === 'closed') {
            $timestamps['closed_at'] = now();
        }

        $task->update(array_merge(['status' => $newStatus], $timestamps));

        AuditLogger::workspaceTaskStatusChanged($task, $oldStatus, $newStatus, [
            'workspace_id' => $workspace->id,
        ]);

        $label = WorkspaceTask::statusLabels()[$newStatus] ?? $newStatus;

        return back()->with('success', "Task status updated to \"{$label}\".");
    }
}
