<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceTimeLog;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WorkspaceTimeLogController extends Controller
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

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * List time logs for this workspace.
     *
     * - Admin / workspace_admin / manager: see all logs.
     * - Talent / assigned_user: see own logs only.
     * - Client roles: see approved logs with visibility=client_summary only.
     * - Observer: no access.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ($role === 'observer') {
            abort(403, 'Observers cannot view time logs.');
        }

        $query = $workspace->timeLogs()->with(['user', 'task']);

        if (WorkspaceTimeLog::canViewAll($role)) {
            // Managers and admins see everything
        } elseif (WorkspaceTimeLog::isClientRole($role)) {
            // Clients see only approved + client_summary logs
            $query->where('status', 'approved')
                  ->where('visibility', 'client_summary');
        } else {
            // Talent: own logs only
            $query->where('user_id', $user->id);
        }

        $timeLogs   = $query->orderByDesc('log_date')->orderByDesc('created_at')->paginate(25);
        $canCreate  = WorkspaceTimeLog::canCreate($role);
        $canReview  = WorkspaceTimeLog::canReview($role);
        $isClient   = WorkspaceTimeLog::isClientRole($role);
        $userActiveTimer = WorkspaceTimeLog::activeTimerFor($user);
        $activeTimer = $userActiveTimer && (int) $userActiveTimer->workspace_id === (int) $workspace->id
            ? $userActiveTimer
            : null;

        $runningTimers = $canReview
            ? $workspace->timeLogs()->with(['user', 'task'])->running()->orderByDesc('started_at')->get()
            : collect();

        $startableTasks = collect();
        if ($canCreate && ! $userActiveTimer) {
            $startableTasks = $workspace->tasks()
                ->where(function ($q) use ($request, $role) {
                    if (WorkspaceTimeLog::canViewAll($role)) {
                        // Managers see all tasks.
                    } else {
                        $q->where('assigned_to_user_id', $request->user()->id);
                    }
                })
                ->whereIn('status', ['pending', 'in_progress', 'blocked', 'revision_requested'])
                ->orderBy('title')
                ->get();
        }

        return view('workspace.time-logs.index', compact(
            'workspace', 'timeLogs', 'role', 'canCreate', 'canReview', 'isClient',
            'activeTimer', 'userActiveTimer', 'runningTimers', 'startableTasks'
        ));
    }

    /**
     * Show the create time log form.
     */
    public function create(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceTimeLog::canCreate($role)) {
            abort(403, 'You cannot log time in this workspace.');
        }

        // Load tasks relevant to this user in this workspace
        $tasks = $workspace->tasks()
            ->where(function ($q) use ($request, $role) {
                if (WorkspaceTimeLog::canViewAll($role)) {
                    // Managers see all tasks
                } else {
                    $q->where('assigned_to_user_id', $request->user()->id);
                }
            })
            ->orderBy('title')
            ->get();

        return view('workspace.time-logs.create', compact('workspace', 'role', 'tasks'));
    }

    /**
     * Store a new time log.
     */
    public function store(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! WorkspaceTimeLog::canCreate($role)) {
            abort(403, 'You cannot log time in this workspace.');
        }

        $validated = $request->validate([
            'log_date'              => 'required|date',
            'work_summary'          => 'required|string|max:1000',
            'work_details'          => 'nullable|string|max:5000',
            'started_at'            => 'nullable|date_format:H:i',
            'ended_at'              => 'nullable|date_format:H:i|after:started_at',
            'duration_minutes'      => 'nullable|integer|min:1|max:1440',
            'workspace_task_id'     => 'nullable|exists:workspace_tasks,id',
            'status'                => 'nullable|in:draft,submitted',
        ]);

        // Build started_at / ended_at datetime from date + time inputs
        $startedAt = null;
        $endedAt   = null;
        if (! empty($validated['started_at'])) {
            $startedAt = $validated['log_date'] . ' ' . $validated['started_at'] . ':00';
        }
        if (! empty($validated['ended_at'])) {
            $endedAt = $validated['log_date'] . ' ' . $validated['ended_at'] . ':00';
        }

        // Verify task belongs to this workspace if provided
        if (! empty($validated['workspace_task_id'])) {
            $taskCheck = $workspace->tasks()->where('id', $validated['workspace_task_id'])->first();
            if (! $taskCheck) {
                abort(422, 'Selected task does not belong to this workspace.');
            }
        }

        $timeLog = WorkspaceTimeLog::create([
            'workspace_id'      => $workspace->id,
            'user_id'           => $request->user()->id,
            'workspace_task_id' => $validated['workspace_task_id'] ?? null,
            'log_date'          => $validated['log_date'],
            'started_at'        => $startedAt,
            'ended_at'          => $endedAt,
            'duration_minutes'  => $validated['duration_minutes'] ?? null,
            'work_summary'      => $validated['work_summary'],
            'work_details'      => $validated['work_details'] ?? null,
            'status'            => $validated['status'] ?? 'draft',
            'visibility'        => 'internal',
        ]);

        AuditLogger::timeLogCreated($timeLog, [
            'workspace_code' => $workspace->workspace_code,
        ]);

        if ($timeLog->status === 'submitted') {
            app(NotificationService::class)->notifyTimeLogSubmitted($timeLog, $request->user());
        }

        return redirect()
            ->route('workspace.time-logs.show', [$workspace, $timeLog])
            ->with('success', 'Time log saved.');
    }

    /**
     * Show a single time log.
     */
    public function show(Request $request, Workspace $workspace, WorkspaceTimeLog $timeLog)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $timeLog->workspace_id !== (int) $workspace->id) {
            abort(404, 'Time log not found in this workspace.');
        }

        if ($role === 'observer') {
            abort(403, 'Observers cannot view time logs.');
        }

        // Clients: only approved + client_summary
        if (WorkspaceTimeLog::isClientRole($role)) {
            if (! $timeLog->isClientVisible()) {
                abort(403, 'This log is not available.');
            }
        }

        // Talent: only own logs
        if (! WorkspaceTimeLog::canViewAll($role) && ! WorkspaceTimeLog::isClientRole($role)) {
            if ((int) $timeLog->user_id !== (int) $user->id) {
                abort(403, 'You can only view your own time logs.');
            }
        }

        $timeLog->load(['user', 'task', 'reviewedBy']);
        $canReview = WorkspaceTimeLog::canReview($role);
        $canEdit   = WorkspaceTimeLog::canCreate($role)
                     && in_array($timeLog->status, ['draft', 'rejected'], true)
                     && (int) $timeLog->user_id === (int) $user->id;

        return view('workspace.time-logs.show', compact(
            'workspace', 'timeLog', 'role', 'canReview', 'canEdit'
        ));
    }

    /**
     * Show the edit form for a time log.
     */
    public function edit(Request $request, Workspace $workspace, WorkspaceTimeLog $timeLog)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $timeLog->workspace_id !== (int) $workspace->id) {
            abort(404, 'Time log not found in this workspace.');
        }

        if ($timeLog->isRunning()) {
            abort(403, 'Stop the running timer before editing this time log.');
        }

        // Only log author can edit; only if draft or rejected
        if ((int) $timeLog->user_id !== (int) $user->id) {
            if (! WorkspaceTimeLog::canReview($role)) {
                abort(403, 'You cannot edit this time log.');
            }
        }

        if (! in_array($timeLog->status, ['draft', 'rejected'], true)) {
            if (! WorkspaceTimeLog::canReview($role)) {
                abort(403, 'This log cannot be edited once submitted.');
            }
        }

        $tasks = $workspace->tasks()
            ->where(function ($q) use ($request, $role) {
                if (WorkspaceTimeLog::canViewAll($role)) {
                    // show all tasks to managers
                } else {
                    $q->where('assigned_to_user_id', $request->user()->id);
                }
            })
            ->orderBy('title')
            ->get();

        return view('workspace.time-logs.edit', compact('workspace', 'timeLog', 'role', 'tasks'));
    }

    /**
     * Update a time log.
     */
    public function update(Request $request, Workspace $workspace, WorkspaceTimeLog $timeLog)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $timeLog->workspace_id !== (int) $workspace->id) {
            abort(404, 'Time log not found in this workspace.');
        }

        if ($timeLog->isRunning()) {
            abort(403, 'Stop the running timer before editing this time log.');
        }

        $isOwner = (int) $timeLog->user_id === (int) $user->id;
        $canManage = WorkspaceTimeLog::canReview($role);

        if (! $isOwner && ! $canManage) {
            abort(403, 'You cannot edit this time log.');
        }

        if (! in_array($timeLog->status, ['draft', 'rejected'], true) && ! $canManage) {
            abort(403, 'This log cannot be edited once submitted.');
        }

        $validated = $request->validate([
            'log_date'              => 'required|date',
            'work_summary'          => 'required|string|max:1000',
            'work_details'          => 'nullable|string|max:5000',
            'started_at'            => 'nullable|date_format:H:i',
            'ended_at'              => 'nullable|date_format:H:i|after:started_at',
            'duration_minutes'      => 'nullable|integer|min:1|max:1440',
            'workspace_task_id'     => 'nullable|exists:workspace_tasks,id',
            'status'                => 'nullable|in:draft,submitted',
            // Manager-only editable fields
            'visibility'            => 'nullable|in:internal,client_summary',
            'client_visible_summary'=> 'nullable|string|max:2000',
        ]);

        $startedAt = null;
        $endedAt   = null;
        if (! empty($validated['started_at'])) {
            $startedAt = $validated['log_date'] . ' ' . $validated['started_at'] . ':00';
        }
        if (! empty($validated['ended_at'])) {
            $endedAt = $validated['log_date'] . ' ' . $validated['ended_at'] . ':00';
        }

        $update = [
            'workspace_task_id' => $validated['workspace_task_id'] ?? null,
            'log_date'          => $validated['log_date'],
            'started_at'        => $startedAt,
            'ended_at'          => $endedAt,
            'duration_minutes'  => $validated['duration_minutes'] ?? null,
            'work_summary'      => $validated['work_summary'],
            'work_details'      => $validated['work_details'] ?? null,
            'status'            => $validated['status'] ?? $timeLog->status,
        ];

        // Only managers/admins can change visibility and client-facing summary
        if ($canManage) {
            $update['visibility']             = $validated['visibility'] ?? $timeLog->visibility;
            $update['client_visible_summary'] = $validated['client_visible_summary'] ?? $timeLog->client_visible_summary;
        }

        $oldStatus = $timeLog->status;
        $timeLog->update($update);

        AuditLogger::timeLogUpdated($timeLog, [
            'workspace_code' => $workspace->workspace_code,
            'updated_by'     => $user->id,
        ]);

        if ($oldStatus !== 'submitted' && $timeLog->status === 'submitted') {
            app(NotificationService::class)->notifyTimeLogSubmitted($timeLog, $user);
        }

        return redirect()
            ->route('workspace.time-logs.show', [$workspace, $timeLog])
            ->with('success', 'Time log updated.');
    }

    /**
     * Manager/admin review: approve, reject, or mark reviewed.
     */
    public function review(Request $request, Workspace $workspace, WorkspaceTimeLog $timeLog)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $timeLog->workspace_id !== (int) $workspace->id) {
            abort(404, 'Time log not found in this workspace.');
        }

        if ($timeLog->isRunning()) {
            abort(403, 'Stop the running timer before reviewing this time log.');
        }

        if (! WorkspaceTimeLog::canReview($role)) {
            abort(403, 'You do not have permission to review time logs.');
        }

        $validated = $request->validate([
            'action'                => 'required|in:reviewed,approved,rejected',
            'manager_notes'         => 'nullable|string|max:2000',
            'visibility'            => 'nullable|in:internal,client_summary',
            'client_visible_summary'=> 'nullable|string|max:2000',
        ]);

        $timeLog->update([
            'status'                 => $validated['action'],
            'reviewed_by_user_id'    => $user->id,
            'reviewed_at'            => now(),
            'manager_notes'          => $validated['manager_notes'] ?? $timeLog->manager_notes,
            'visibility'             => $validated['visibility'] ?? $timeLog->visibility,
            'client_visible_summary' => $validated['client_visible_summary'] ?? $timeLog->client_visible_summary,
        ]);

        AuditLogger::timeLogReviewed($timeLog, [
            'workspace_code' => $workspace->workspace_code,
            'action'         => $validated['action'],
            'reviewed_by'    => $user->id,
        ]);

        return redirect()
            ->route('workspace.time-logs.show', [$workspace, $timeLog])
            ->with('success', 'Time log marked as ' . $validated['action'] . '.');
    }

    /**
     * Soft-delete a time log (admin/manager or log owner in draft state).
     */
    public function destroy(Request $request, Workspace $workspace, WorkspaceTimeLog $timeLog)
    {
        $role = $this->requireAccess($request, $workspace);
        $user = $request->user();

        if ((int) $timeLog->workspace_id !== (int) $workspace->id) {
            abort(404, 'Time log not found in this workspace.');
        }

        $isOwner   = (int) $timeLog->user_id === (int) $user->id;
        $canManage = WorkspaceTimeLog::canReview($role);

        if ($timeLog->isRunning()) {
            abort(403, 'Stop the running timer before deleting this time log.');
        }

        if (! $canManage && ! ($isOwner && $timeLog->status === 'draft')) {
            abort(403, 'You cannot delete this time log.');
        }

        $timeLog->delete();

        AuditLogger::timeLogDeleted($timeLog, [
            'workspace_code' => $workspace->workspace_code,
            'deleted_by'     => $user->id,
        ]);

        return redirect()
            ->route('workspace.time-logs.index', $workspace)
            ->with('success', 'Time log deleted.');
    }
}
