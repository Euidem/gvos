<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceTimeLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceTimeTrackerController extends Controller
{
    private function requireAccess(Request $request, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($request->user());

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    private function canUseTimer(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager', 'talent', 'assigned_user'], true);
    }

    private function validateTaskBelongsToWorkspace(Workspace $workspace, ?int $taskId): void
    {
        if (! $taskId) {
            return;
        }

        if (! $workspace->tasks()->whereKey($taskId)->exists()) {
            abort(422, 'Selected task does not belong to this workspace.');
        }
    }

    private function runningLogForWorkspace(Request $request, Workspace $workspace): ?WorkspaceTimeLog
    {
        $query = $workspace->timeLogs()
            ->with(['workspace', 'task', 'user'])
            ->running();

        if ($request->filled('time_log_id')) {
            return $query->whereKey((int) $request->input('time_log_id'))->first();
        }

        return $query->where('user_id', $request->user()->id)->first();
    }

    private function durationMinutes(WorkspaceTimeLog $timeLog, $endedAt): int
    {
        if (! $timeLog->started_at) {
            return 0;
        }

        return max(0, (int) $timeLog->started_at->diffInMinutes($endedAt));
    }

    private function redirectWithTimerError(Request $request, string $message, ?WorkspaceTimeLog $activeTimer = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'active_timer' => $activeTimer ? [
                    'id' => $activeTimer->id,
                    'workspace_id' => $activeTimer->workspace_id,
                    'workspace_name' => $activeTimer->workspace?->name,
                    'started_at' => $activeTimer->started_at?->toIso8601String(),
                ] : null,
            ], 422);
        }

        $redirect = redirect()->back()->with('error', $message);

        if ($activeTimer?->workspace) {
            $redirect->with('active_timer_url', route('workspace.time-logs.show', [$activeTimer->workspace, $activeTimer]));
        }

        return $redirect;
    }

    public function current(Request $request)
    {
        $activeTimer = WorkspaceTimeLog::activeTimerFor($request->user());

        return response()->json([
            'active' => (bool) $activeTimer,
            'timer' => $activeTimer ? [
                'id' => $activeTimer->id,
                'workspace_id' => $activeTimer->workspace_id,
                'workspace_name' => $activeTimer->workspace?->name,
                'workspace_task_id' => $activeTimer->workspace_task_id,
                'task_title' => $activeTimer->task?->title,
                'started_at' => $activeTimer->started_at?->toIso8601String(),
                'duration_minutes' => $activeTimer->runningDurationMinutes(),
                'show_url' => $activeTimer->workspace
                    ? route('workspace.time-logs.show', [$activeTimer->workspace, $activeTimer])
                    : null,
            ] : null,
        ]);
    }

    public function start(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canUseTimer($role)) {
            abort(403, 'Your role cannot start timers.');
        }

        $validated = $request->validate([
            'workspace_task_id' => 'nullable|integer',
        ]);

        $taskId = isset($validated['workspace_task_id']) ? (int) $validated['workspace_task_id'] : null;
        $this->validateTaskBelongsToWorkspace($workspace, $taskId);

        $timeLog = DB::transaction(function () use ($request, $workspace, $taskId) {
            $activeTimer = WorkspaceTimeLog::query()
                ->with(['workspace', 'task'])
                ->where('user_id', $request->user()->id)
                ->running()
                ->lockForUpdate()
                ->first();

            if ($activeTimer) {
                return $activeTimer;
            }

            return WorkspaceTimeLog::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'workspace_task_id' => $taskId,
                'log_date' => now()->toDateString(),
                'started_at' => now(),
                'ended_at' => null,
                'duration_minutes' => null,
                'work_summary' => 'Work session in progress',
                'work_details' => null,
                'status' => 'running',
                'visibility' => 'internal',
            ]);
        });

        if (! $timeLog->wasRecentlyCreated) {
            return $this->redirectWithTimerError(
                $request,
                'You already have a running timer. Stop or complete it before starting another session.',
                $timeLog
            );
        }

        AuditLogger::timeTrackerStarted($timeLog);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Timer started.',
                'timer_id' => $timeLog->id,
                'started_at' => $timeLog->started_at?->toIso8601String(),
            ], 201);
        }

        return redirect()->back()->with('success', 'Timer started.');
    }

    public function stop(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canUseTimer($role)) {
            abort(403, 'Your role cannot stop timers.');
        }

        $validated = $request->validate([
            'time_log_id' => 'nullable|integer',
            'status' => 'nullable|in:draft,submitted',
            'work_summary' => 'required_if:status,submitted|nullable|string|max:1000',
            'work_details' => 'nullable|string|max:5000',
            'client_visible_summary' => 'nullable|string|max:2000',
        ]);

        $timeLog = $this->runningLogForWorkspace($request, $workspace);

        if (! $timeLog) {
            return $this->redirectWithTimerError($request, 'No running timer was found for this workspace.');
        }

        if (! $timeLog->canBeStoppedBy($request->user())) {
            abort(403, 'You cannot stop this timer.');
        }

        $endedAt = now();
        $status = $validated['status'] ?? 'draft';

        $timeLog->update([
            'ended_at' => $endedAt,
            'duration_minutes' => $this->durationMinutes($timeLog, $endedAt),
            'status' => $status,
            'work_summary' => trim($validated['work_summary'] ?? '') !== ''
                ? $validated['work_summary']
                : $timeLog->work_summary,
            'work_details' => $validated['work_details'] ?? $timeLog->work_details,
            'client_visible_summary' => $validated['client_visible_summary'] ?? $timeLog->client_visible_summary,
        ]);

        AuditLogger::timeTrackerStopped($timeLog);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Timer stopped.',
                'timer_id' => $timeLog->id,
                'duration_minutes' => $timeLog->duration_minutes,
                'status' => $timeLog->status,
            ]);
        }

        return redirect()->back()->with('success', 'Timer stopped.');
    }

    public function complete(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canUseTimer($role)) {
            abort(403, 'Your role cannot complete timers.');
        }

        $validated = $request->validate([
            'time_log_id' => 'nullable|integer',
            'work_summary' => 'required|string|max:1000',
            'work_details' => 'nullable|string|max:5000',
            'client_visible_summary' => 'nullable|string|max:2000',
        ]);

        $timeLog = $this->runningLogForWorkspace($request, $workspace);

        if (! $timeLog) {
            return $this->redirectWithTimerError($request, 'No running timer was found for this workspace.');
        }

        if (! $timeLog->canBeStoppedBy($request->user()) || ! $timeLog->canBeSubmittedBy($request->user())) {
            abort(403, 'You cannot complete this timer.');
        }

        $endedAt = now();

        $timeLog->update([
            'ended_at' => $endedAt,
            'duration_minutes' => $this->durationMinutes($timeLog, $endedAt),
            'status' => 'submitted',
            'work_summary' => $validated['work_summary'],
            'work_details' => $validated['work_details'] ?? $timeLog->work_details,
            'client_visible_summary' => $validated['client_visible_summary'] ?? $timeLog->client_visible_summary,
        ]);

        AuditLogger::timeTrackerCompleted($timeLog);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Work session completed.',
                'timer_id' => $timeLog->id,
                'duration_minutes' => $timeLog->duration_minutes,
                'status' => $timeLog->status,
            ]);
        }

        return redirect()->back()->with('success', 'Work session completed and submitted for review.');
    }
}
