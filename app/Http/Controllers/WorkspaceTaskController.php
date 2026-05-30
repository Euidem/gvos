<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskComment;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WorkspaceTaskController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve the user's effective role and abort 403 if they have no access.
     *
     * Returns the raw role string from Workspace::resolveUserWorkspaceRole().
     * Callers that need a role for transition checks should also call
     * transitionRole() to normalise 'assigned_user' → 'talent'.
     */
    private function requireWorkspaceAccess(User $user, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($user);

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    /**
     * Map the 'assigned_user' tier to 'talent' for status-transition checks.
     * All other roles pass through unchanged.
     *
     * 'assigned_user' is returned by resolveUserWorkspaceRole() when the user is
     * assigned to a task but has no workspace member row. For transition purposes
     * they have the same permissions as a talent.
     */
    private function transitionRole(string $role): string
    {
        return $role === 'assigned_user' ? 'talent' : $role;
    }

    /**
     * Ensure the task belongs to the given workspace, or abort with clear messages.
     *
     * Uses explicit (int) casts in addition to the model casts defined on
     * WorkspaceTask to guard against any environment where PDO returns integers
     * as strings (the default with ATTR_EMULATE_PREPARES = true).
     *
     * Returns a JSON error for expectsJson() requests so the Kanban board
     * can surface the real reason rather than a generic fallback.
     */
    private function authorizeTaskBelongsToWorkspace(
        Workspace     $workspace,
        WorkspaceTask $task,
        Request       $request = null
    ): void {
        if ((int) $task->workspace_id !== (int) $workspace->id) {
            if ($request && $request->expectsJson()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'This task does not belong to this workspace.',
                ], 404));
            }
            abort(404, 'Task not found in this workspace.');
        }
    }

    private function isAdminOrManager(string $role): bool
    {
        return in_array($role, ['admin', 'manager'], true);
    }

    // ── Controller actions ────────────────────────────────────────────────

    /**
     * Task board (Kanban) — tasks grouped by status.
     *
     * Accessible to any user with workspace access.
     * Drag permissions and drag-handle visibility are controlled by $role
     * passed to the view. The view also receives $currentUserId so that
     * talent users only see drag handles on their own assigned tasks.
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
        $canCreate     = $workspace->userCanCreateTasks($user);
        $currentUserId = (int) $user->id;

        return view('workspace.tasks.index', compact(
            'workspace', 'tasks', 'tasksByStatus', 'role', 'canCreate', 'currentUserId'
        ));
    }

    /**
     * Task create form.
     */
    public function create(Request $request, Workspace $workspace)
    {
        $user = $request->user();
        $role = $this->requireWorkspaceAccess($user, $workspace);

        if (! $workspace->userCanCreateTasks($user)) {
            abort(403, 'You cannot create tasks in this workspace.');
        }

        $members          = $workspace->activeMembers()->with('user')->get();
        $isAdminOrManager = $this->isAdminOrManager($this->transitionRole($role));

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

        if (! $workspace->userCanCreateTasks($user)) {
            abort(403);
        }

        $effectiveRole    = $this->transitionRole($role);
        $isAdminOrManager = $this->isAdminOrManager($effectiveRole);

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
     *
     * Special case: a user with role 'none' at the workspace level may still
     * view a specific task if they are the assigned user for that task.
     * In that case they receive 'talent' access for display purposes.
     */
    public function show(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        $this->authorizeTaskBelongsToWorkspace($workspace, $task, $request);

        $user = $request->user();
        $role = $workspace->resolveUserWorkspaceRole($user);

        // Task-assigned fallback: no workspace access but assigned to this task
        if ($role === 'none') {
            if ($task->assigned_to_user_id !== null && (int) $task->assigned_to_user_id === (int) $user->id) {
                $role = 'talent'; // Effective role for this specific task page
            } else {
                abort(403, 'You do not have access to this task.');
            }
        }

        $effectiveRole    = $this->transitionRole($role);
        $isAdminOrManager = $this->isAdminOrManager($effectiveRole);

        $task->load(['createdBy', 'assignedTo', 'workspace']);

        // Clients and talent only see public comments; admins and managers see all.
        $comments = $isAdminOrManager
            ? $task->comments()->with('user')->get()
            : $task->comments()->where('visibility', 'public')->with('user')->get();

        $allowedTransitions = WorkspaceTask::allowedTransitions($task->status, $effectiveRole);

        // For talent: further restrict allowed transitions to only their own assigned tasks.
        // If this task is assigned to someone else, talent cannot change its status.
        if ($effectiveRole === 'talent' && $task->assigned_to_user_id !== null
            && (int) $task->assigned_to_user_id !== (int) $user->id) {
            $allowedTransitions = [];
        }

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id !== null
                && (int) $task->created_by_user_id === (int) $user->id
                && $task->status === 'pending');

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
        $this->authorizeTaskBelongsToWorkspace($workspace, $task, $request);

        $user             = $request->user();
        $role             = $this->requireWorkspaceAccess($user, $workspace);
        $effectiveRole    = $this->transitionRole($role);
        $isAdminOrManager = $this->isAdminOrManager($effectiveRole);

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id !== null
                && (int) $task->created_by_user_id === (int) $user->id
                && $task->status === 'pending');

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
        $this->authorizeTaskBelongsToWorkspace($workspace, $task, $request);

        $user             = $request->user();
        $role             = $this->requireWorkspaceAccess($user, $workspace);
        $effectiveRole    = $this->transitionRole($role);
        $isAdminOrManager = $this->isAdminOrManager($effectiveRole);

        $canEdit = $isAdminOrManager
            || ($task->created_by_user_id !== null
                && (int) $task->created_by_user_id === (int) $user->id
                && $task->status === 'pending');

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
        $this->authorizeTaskBelongsToWorkspace($workspace, $task, $request);

        $user             = $request->user();
        $role             = $this->requireWorkspaceAccess($user, $workspace);
        $effectiveRole    = $this->transitionRole($role);
        $isAdminOrManager = $this->isAdminOrManager($effectiveRole);

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
     *
     * Supports both standard HTML form submissions (redirect) and AJAX JSON
     * requests from the Kanban board (returns JSON).
     *
     * JSON success: 200 { success: true, status, message }
     * JSON permission denied: 403 { success: false, message }
     * JSON invalid transition: 422 { success: false, message }
     * JSON wrong workspace: 404 { success: false, message }
     *
     * All JSON error messages are human-readable so the Kanban toast can
     * display them directly without a generic fallback.
     *
     * Role determination uses a multi-signal approach rather than relying
     * solely on resolveUserWorkspaceRole(), so that edge cases such as a
     * primary talent with no member row, or an assigned user whose member
     * row has an unexpected role, are all handled correctly.
     */
    public function updateStatus(Request $request, Workspace $workspace, WorkspaceTask $task)
    {
        // ── Step 1: Verify task belongs to this workspace ──────────────────
        $this->authorizeTaskBelongsToWorkspace($workspace, $task, $request);

        $user = $request->user();

        // ── Step 2: Gather role-resolution signals (PART I / PART B) ──────
        $workspaceRole   = $workspace->resolveUserWorkspaceRole($user);
        $isTaskAssignee  = $task->assigned_to_user_id !== null
                           && (int) $task->assigned_to_user_id === (int) $user->id;
        $isPrimaryTalent = $workspace->primary_talent_id !== null
                           && (int) $workspace->primary_talent_id === (int) $user->id;
        $isPrimaryManager = $workspace->primary_manager_id !== null
                            && (int) $workspace->primary_manager_id === (int) $user->id;

        // ── Step 3: Determine effective role (priority: admin > manager > talent > client) ──
        if ($workspaceRole === 'admin') {
            $effectiveRole = 'admin';
        } elseif ($isPrimaryManager || $workspaceRole === 'manager') {
            $effectiveRole = 'manager';
        } elseif ($isTaskAssignee || $isPrimaryTalent || in_array($workspaceRole, ['talent', 'assigned_user'], true)) {
            $effectiveRole = 'talent';
        } elseif ($workspaceRole === 'client') {
            $effectiveRole = 'client';
        } else {
            // No access path — log and block
            Log::info('workspace_task.status_update_denied', [
                'reason'             => 'no_workspace_access',
                'user_id'            => $user->id,
                'user_email'         => $user->email,
                'user_roles'         => $user->getRoleNames()->toArray(),
                'workspace_id'       => $workspace->id,
                'task_id'            => $task->id,
                'task_assigned_to'   => $task->assigned_to_user_id,
                'workspace_role'     => $workspaceRole,
                'is_task_assignee'   => $isTaskAssignee,
                'is_primary_talent'  => $isPrimaryTalent,
                'is_primary_manager' => $isPrimaryManager,
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update tasks in this workspace.',
                ], 403);
            }
            abort(403, 'You do not have access to this workspace.');
        }

        // ── Step 4: Validate the requested new status ──────────────────────
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,blocked,submitted,revision_requested,approved,closed,cancelled',
        ]);

        $newStatus  = $validated['status'];
        $fromStatus = $task->status;

        // ── Step 5: Log full context for all attempts (PART A) ─────────────
        Log::info('workspace_task.status_update_attempt', [
            'user_id'             => $user->id,
            'user_email'          => $user->email,
            'user_roles'          => $user->getRoleNames()->toArray(),
            'workspace_id'        => $workspace->id,
            'task_id'             => $task->id,
            'task_code'           => $task->task_code,
            'task_assigned_to'    => $task->assigned_to_user_id,
            'from_status'         => $fromStatus,
            'requested_status'    => $newStatus,
            'workspace_role'      => $workspaceRole,
            'effective_role'      => $effectiveRole,
            'is_task_assignee'    => $isTaskAssignee,
            'is_primary_talent'   => $isPrimaryTalent,
            'is_primary_manager'  => $isPrimaryManager,
            'allowed_transitions' => WorkspaceTask::allowedTransitions($fromStatus, $effectiveRole),
        ]);

        // ── Step 6: Talent assignee restriction ────────────────────────────
        // Talent may move:
        //   (a) any task that is explicitly assigned to themselves, OR
        //   (b) an unassigned task if they are the primary talent.
        // All other cases are blocked to prevent cross-task interference.
        if ($effectiveRole === 'talent') {
            $taskIsUnassigned = $task->assigned_to_user_id === null;
            $canMove          = $isTaskAssignee || ($taskIsUnassigned && $isPrimaryTalent);

            if (! $canMove) {
                $assigneeName = $taskIsUnassigned
                    ? 'no one (only the primary talent can move unassigned tasks)'
                    : (optional($task->assignedTo)->name ?? 'another user');

                Log::info('workspace_task.status_update_denied', [
                    'reason'             => 'talent_not_assignee',
                    'user_id'            => $user->id,
                    'user_email'         => $user->email,
                    'workspace_id'       => $workspace->id,
                    'task_id'            => $task->id,
                    'task_code'          => $task->task_code,
                    'task_assigned_to'   => $task->assigned_to_user_id,
                    'task_is_unassigned' => $taskIsUnassigned,
                    'is_task_assignee'   => $isTaskAssignee,
                    'is_primary_talent'  => $isPrimaryTalent,
                    'from_status'        => $fromStatus,
                    'requested_status'   => $newStatus,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $taskIsUnassigned
                            ? 'Only the primary talent can move unassigned tasks.'
                            : "You can only move tasks assigned to you. This task is assigned to {$assigneeName}.",
                    ], 403);
                }
                return back()->withErrors(['status' => 'You can only update tasks assigned to you.']);
            }
        }

        // ── Step 7: Transition permission check ────────────────────────────
        $allowed = WorkspaceTask::allowedTransitions($fromStatus, $effectiveRole);

        if (! in_array($newStatus, $allowed, true)) {
            $fromLabel = WorkspaceTask::statusLabels()[$fromStatus] ?? $fromStatus;
            $toLabel   = WorkspaceTask::statusLabels()[$newStatus]  ?? $newStatus;

            Log::info('workspace_task.status_update_denied', [
                'reason'           => 'transition_not_allowed',
                'user_id'          => $user->id,
                'user_email'       => $user->email,
                'workspace_id'     => $workspace->id,
                'task_id'          => $task->id,
                'task_code'        => $task->task_code,
                'effective_role'   => $effectiveRole,
                'from_status'      => $fromStatus,
                'requested_status' => $newStatus,
                'allowed'          => $allowed,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "This task cannot be moved from \"{$fromLabel}\" to \"{$toLabel}\". "
                               . (empty($allowed)
                                    ? 'No further moves are available from this status.'
                                    : 'Allowed next statuses: ' . implode(', ', array_map(
                                        fn ($s) => WorkspaceTask::statusLabels()[$s] ?? $s,
                                        $allowed
                                      )) . '.'),
                ], 422);
            }
            return back()->withErrors(['status' => "Cannot move from \"{$fromLabel}\" to \"{$toLabel}\"."]);
        }

        // ── Step 8: Apply the transition ───────────────────────────────────
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

        AuditLogger::workspaceTaskStatusChanged($task, $fromStatus, $newStatus, [
            'workspace_id' => $workspace->id,
        ]);

        $label = WorkspaceTask::statusLabels()[$newStatus] ?? $newStatus;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
                'message' => "Task moved to \"{$label}\".",
            ], 200);
        }

        return back()->with('success', "Task status updated to \"{$label}\".");
    }
}
