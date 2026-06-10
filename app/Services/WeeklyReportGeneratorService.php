<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Phase 17 — Weekly Report Generator
 *
 * Deterministically builds a weekly report draft from workspace activity data.
 * No AI, no LLMs — output is constructed from structured database records only.
 *
 * Privacy rules enforced here:
 * - internal time log work_details are excluded from client-visible fields
 * - manager_notes are excluded entirely
 * - task internal_notes are excluded
 * - Only approved time logs contribute to the public hours summary
 * - Submitted logs count toward manager draft only, not client-facing fields
 *
 * Usage:
 *   $data = app(WeeklyReportGeneratorService::class)->generate($workspace, '2026-06-02', '2026-06-08', $user);
 *   // Returns array ready to merge into WorkspaceWeeklyReport::create([...])
 */
class WeeklyReportGeneratorService
{
    /**
     * Generate a draft report from workspace activity between two dates.
     *
     * @param  Workspace  $workspace
     * @param  string     $startDate  Y-m-d
     * @param  string     $endDate    Y-m-d
     * @param  User       $actor      the user triggering generation
     * @return array      Fields ready for WorkspaceWeeklyReport::create()
     */
    public function generate(
        Workspace $workspace,
        string    $startDate,
        string    $endDate,
        User      $actor,
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        // ── Time logs ──────────────────────────────────────────────────────
        // Approved logs: used for total_minutes and client-facing hours summary
        $approvedLogs = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'approved')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('log_date')
            ->get();

        // Submitted logs: included in manager draft context only
        $submittedLogs = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'submitted')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('log_date')
            ->get();

        $allManagerLogs = $approvedLogs->merge($submittedLogs)->sortBy('log_date');

        $totalApprovedMinutes  = $approvedLogs->sum('duration_minutes');
        $totalSubmittedMinutes = $submittedLogs->sum('duration_minutes');
        $totalManagerMinutes   = $totalApprovedMinutes + $totalSubmittedMinutes;

        // ── Tasks ──────────────────────────────────────────────────────────
        // Completed = approved or closed; use approved_at / closed_at in range OR
        // fall back to updated_at in range for tasks that were closed this week
        $completedTasks = WorkspaceTask::where('workspace_id', $workspace->id)
            ->whereIn('status', ['approved', 'closed'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('approved_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->orWhereBetween('closed_at',   [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->orWhereBetween('updated_at',  [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->with('assignedTo')
            ->orderBy('title')
            ->get();

        // Blocked tasks currently open in the workspace
        $blockedTasks = WorkspaceTask::where('workspace_id', $workspace->id)
            ->where('status', 'blocked')
            ->with('assignedTo')
            ->orderBy('title')
            ->get();

        // Pending + in_progress tasks (next priorities)
        $activeTasks = WorkspaceTask::where('workspace_id', $workspace->id)
            ->whereIn('status', ['pending', 'in_progress', 'revision_requested'])
            ->with('assignedTo')
            ->orderByDesc('priority')
            ->limit(10)
            ->get();

        // ── Build field content ────────────────────────────────────────────

        $weekLabel   = Carbon::parse($startDate)->format('j M') . ' – ' . Carbon::parse($endDate)->format('j M Y');
        $approvedH   = intdiv($totalApprovedMinutes, 60);
        $approvedM   = $totalApprovedMinutes % 60;
        $totalHLabel = $totalApprovedMinutes > 0
            ? ($approvedH > 0 ? "{$approvedH}h " : '') . ($approvedM > 0 ? "{$approvedM}m" : '')
            : '0h';

        // summary — professional overview (manager-editable, shown to client after publish)
        $summary = $this->buildSummary(
            $weekLabel,
            $workspace->name,
            $approvedLogs,
            $totalApprovedMinutes,
            $totalHLabel,
            $completedTasks,
        );

        // achievements — completed task list
        $achievements = $this->buildAchievements($completedTasks);

        // blockers — blocked tasks (internal field, not shown to clients in the view)
        $blockers = $this->buildBlockers($blockedTasks);

        // next_steps — active / pending tasks (internal field)
        $nextSteps = $this->buildNextSteps($activeTasks);

        // client_notes — clean public-facing note (placeholder for manager to edit)
        $clientNotes = $this->buildClientNotes(
            $weekLabel,
            $workspace->name,
            $totalApprovedMinutes,
            $totalHLabel,
            $completedTasks,
        );

        return [
            'total_minutes'         => $totalManagerMinutes, // manager sees submitted+approved; clients see only approved hrs via summary text
            'summary'               => $summary,
            'achievements'          => $achievements,
            'blockers'              => $blockers,
            'next_steps'            => $nextSteps,
            'client_notes'          => $clientNotes,
            'generated_at'          => now(),
            'generated_by_user_id'  => $actor->id,
            // Metadata for the generation preview (not stored, returned for UI)
            '_meta' => [
                'approved_log_count'   => $approvedLogs->count(),
                'submitted_log_count'  => $submittedLogs->count(),
                'completed_task_count' => $completedTasks->count(),
                'blocked_task_count'   => $blockedTasks->count(),
                'active_task_count'    => $activeTasks->count(),
                'total_approved_min'   => $totalApprovedMinutes,
                'total_manager_min'    => $totalManagerMinutes,
            ],
        ];
    }

    /**
     * Lightweight preview: just counts, no text generation.
     * Used by the generate form to show what would be included.
     */
    public function preview(Workspace $workspace, string $startDate, string $endDate): array
    {
        $approvedMin = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'approved')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->sum('duration_minutes') ?? 0;

        $submittedMin = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'submitted')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->sum('duration_minutes') ?? 0;

        $approvedLogCount = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'approved')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->count();

        $submittedLogCount = WorkspaceTimeLog::where('workspace_id', $workspace->id)
            ->where('status', 'submitted')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->count();

        $completedTaskCount = WorkspaceTask::where('workspace_id', $workspace->id)
            ->whereIn('status', ['approved', 'closed'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('approved_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->orWhereBetween('closed_at',   [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->orWhereBetween('updated_at',  [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->count();

        $blockedTaskCount = WorkspaceTask::where('workspace_id', $workspace->id)
            ->where('status', 'blocked')
            ->count();

        return [
            'approved_log_count'    => $approvedLogCount,
            'submitted_log_count'   => $submittedLogCount,
            'completed_task_count'  => $completedTaskCount,
            'blocked_task_count'    => $blockedTaskCount,
            'total_approved_min'    => $approvedMin,
            'total_submitted_min'   => $submittedMin,
            'total_manager_min'     => $approvedMin + $submittedMin,
        ];
    }

    // ── Private builders ───────────────────────────────────────────────────

    private function buildSummary(
        string $weekLabel,
        string $workspaceName,
        Collection $approvedLogs,
        int $totalMinutes,
        string $totalHLabel,
        Collection $completedTasks,
    ): string {
        $lines = [];

        $lines[] = "Work report for the week of {$weekLabel} — {$workspaceName}.";

        if ($totalMinutes > 0) {
            $sessionCount = $approvedLogs->count();
            $lines[] = "{$sessionCount} approved work " . ($sessionCount === 1 ? 'session' : 'sessions') . " totalling {$totalHLabel} were logged and approved this week.";
        } else {
            $lines[] = "No approved time logs were recorded for this week.";
        }

        if ($completedTasks->count() > 0) {
            $lines[] = $completedTasks->count() . " " . ($completedTasks->count() === 1 ? 'task was' : 'tasks were') . " completed.";
        }

        $lines[] = "";
        $lines[] = "See the Achievements and Next Steps sections below for a full breakdown.";

        return implode("\n", $lines);
    }

    private function buildAchievements(Collection $completedTasks): string
    {
        if ($completedTasks->isEmpty()) {
            return "No tasks were marked as completed during this period.";
        }

        $lines = ["Tasks completed this week:", ""];
        foreach ($completedTasks as $task) {
            $assignee = $task->assignedTo ? $task->assignedTo->name : 'Unassigned';
            $lines[]  = "- {$task->title} ({$assignee})";
        }

        return implode("\n", $lines);
    }

    private function buildBlockers(Collection $blockedTasks): string
    {
        if ($blockedTasks->isEmpty()) {
            return "No blocked tasks at this time.";
        }

        $lines = ["Tasks currently blocked:", ""];
        foreach ($blockedTasks as $task) {
            $assignee = $task->assignedTo ? $task->assignedTo->name : 'Unassigned';
            $lines[]  = "- {$task->title} ({$assignee})";
        }

        $lines[] = "";
        $lines[] = "Action required: review blockers and update task status as appropriate.";

        return implode("\n", $lines);
    }

    private function buildNextSteps(Collection $activeTasks): string
    {
        if ($activeTasks->isEmpty()) {
            return "No active tasks found for the upcoming period.";
        }

        $lines = ["Tasks in progress / upcoming:", ""];
        foreach ($activeTasks as $task) {
            $assignee = $task->assignedTo ? $task->assignedTo->name : 'Unassigned';
            $status   = WorkspaceTask::statusLabels()[$task->status] ?? ucfirst($task->status);
            $lines[]  = "- {$task->title} [{$status}] ({$assignee})";
        }

        return implode("\n", $lines);
    }

    private function buildClientNotes(
        string $weekLabel,
        string $workspaceName,
        int $totalMinutes,
        string $totalHLabel,
        Collection $completedTasks,
    ): string {
        $lines = [];

        if ($totalMinutes > 0) {
            $lines[] = "This week we logged {$totalHLabel} of approved work on {$workspaceName}.";
        } else {
            $lines[] = "Work continued on {$workspaceName} this week.";
        }

        if ($completedTasks->count() > 0) {
            $noun    = $completedTasks->count() === 1 ? 'deliverable' : 'deliverables';
            $lines[] = $completedTasks->count() . " {$noun} " . ($completedTasks->count() === 1 ? 'was' : 'were') . " completed and approved.";
        }

        $lines[] = "";
        $lines[] = "[Edit this section before publishing — add context relevant to the client.]";

        return implode("\n", $lines);
    }
}
