<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceMessage;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WorkspaceMessageController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve workspace role and abort 403 if the user has no access at all.
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
     * True if the role can see internal messages (admin / workspace_admin / manager).
     */
    private function canViewInternal(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * True if the role can post messages (any role except observer and none).
     */
    private function canPost(string $role): bool
    {
        return ! in_array($role, ['observer', 'none'], true);
    }

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * Show the workspace chat page.
     *
     * Loads the 100 most recent messages (oldest first so the conversation
     * reads top-to-bottom). Internal messages are filtered out for roles
     * that cannot see them.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $role    = $this->requireAccess($request, $workspace);
        $canPost = $this->canPost($role);
        $seeInternal = $this->canViewInternal($role);

        $messagesQuery = $workspace->messages()->with('user');

        if (! $seeInternal) {
            $messagesQuery->where('visibility', 'public');
        }

        // Last 100 messages, oldest first for reading order
        $messages = $messagesQuery
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->sortBy('created_at')
            ->values();

        return view('workspace.chat.index', compact(
            'workspace', 'messages', 'role', 'canPost', 'seeInternal'
        ));
    }

    /**
     * Post a new message to the workspace chat.
     */
    public function store(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canPost($role)) {
            abort(403, 'Observers cannot post messages.');
        }

        $validated = $request->validate([
            'message'    => 'required|string|max:5000',
            'visibility' => 'nullable|in:public,internal',
        ]);

        // Non-internal roles cannot post internal messages
        if (! $this->canViewInternal($role)) {
            $validated['visibility'] = 'public';
        }

        $message = WorkspaceMessage::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $request->user()->id,
            'message'      => $validated['message'],
            'visibility'   => $validated['visibility'] ?? 'public',
            'message_type' => 'text',
        ]);

        AuditLogger::workspaceMessageCreated($message, [
            'workspace_code' => $workspace->workspace_code,
        ]);

        app(NotificationService::class)->notifyWorkspaceMessage($message, $request->user());

        return redirect()
            ->route('workspace.chat.index', $workspace)
            ->with('success', 'Message sent.');
    }

    /**
     * Soft-delete a message.
     *
     * Allowed if: the user is the author, OR has admin/workspace_admin/manager role.
     */
    public function destroy(Request $request, Workspace $workspace, WorkspaceMessage $message)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        // Verify the message belongs to this workspace
        if ((int) $message->workspace_id !== (int) $workspace->id) {
            abort(404, 'Message not found in this workspace.');
        }

        $isAuthor = (int) $message->user_id === (int) $user->id;

        if (! $isAuthor && ! $this->canViewInternal($role)) {
            abort(403, 'You cannot delete this message.');
        }

        $message->delete();

        AuditLogger::workspaceMessageDeleted($message, [
            'workspace_code' => $workspace->workspace_code,
            'deleted_by'     => $user->id,
        ]);

        return redirect()
            ->route('workspace.chat.index', $workspace)
            ->with('success', 'Message deleted.');
    }
}
