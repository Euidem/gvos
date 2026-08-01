<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceWeeklyReport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * Phase 28 — dashboard data is now resolved here instead of inside Blade.
 *
 * Previously every dashboard view ran 6–10 ad-hoc queries in an @php block with
 * no eager loading and no limits. Moving them here lets us:
 *   – eager load the relations the views actually render (no N+1 in loops);
 *   – bound every list with take()/limit();
 *   – reuse one workspace lookup across all the derived figures.
 *
 * No new routes, models, or migrations. No authorization logic changed — each
 * query is still scoped to the authenticated user exactly as before.
 */
class DashboardController extends Controller
{
    /** Statuses that count as "open" work. */
    private const OPEN_TASK_STATUSES = ['pending', 'in_progress', 'blocked', 'revision_requested'];

    // ── Admin (Filament handles /admin; these remain for completeness) ───────

    public function superAdmin(): View
    {
        return view('dashboard.super-admin');
    }

    public function operationsAdmin(): View
    {
        return view('dashboard.operations-admin');
    }

    // ── Talent ───────────────────────────────────────────────────────────────

    public function talent(): View
    {
        $user       = auth()->user();
        $workspaces = $this->workspacesFor($user);
        $wsIds      = $workspaces->pluck('id');

        $activeTimer = WorkspaceTimeLog::activeTimerFor($user);

        // Open tasks assigned to me, ordered so the most urgent surfaces first.
        $myTasks = WorkspaceTask::query()
            ->where('assigned_to_user_id', $user->id)
            ->whereIn('status', self::OPEN_TASK_STATUSES)
            ->with('workspace:id,name,workspace_code')
            ->orderByRaw("FIELD(status,'blocked','revision_requested','in_progress','pending')")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit(12)
            ->get();

        // Time logs that need the talent to do something.
        $logsNeedingAction = WorkspaceTimeLog::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['rejected', 'draft'])
            ->with('workspace:id,name')
            ->orderByDesc('log_date')
            ->limit(5)
            ->get();

        $weekMinutes = WorkspaceTimeLog::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('log_date', [now()->startOfWeek()->toDateString(), now()->toDateString()])
            ->sum('duration_minutes');

        // Tasks selectable when starting a timer (bounded).
        $timerTasks = $wsIds->isEmpty() ? collect() : WorkspaceTask::query()
            ->whereIn('workspace_id', $wsIds)
            ->where(fn ($q) => $q->where('assigned_to_user_id', $user->id)->orWhereNull('assigned_to_user_id'))
            ->whereIn('status', self::OPEN_TASK_STATUSES)
            ->orderBy('title')
            ->limit(50)
            ->get(['id', 'workspace_id', 'task_code', 'title']);

        return view('dashboard.talent', [
            'workspaces'        => $workspaces,
            'activeTimer'       => $activeTimer,
            'myTasks'           => $myTasks,
            'focusTask'         => $myTasks->first(),
            'blockedCount'      => $myTasks->where('status', 'blocked')->count(),
            'dueSoonCount'      => $myTasks->filter(fn ($t) => $t->isDueSoon())->count(),
            'overdueCount'      => $myTasks->filter(fn ($t) => $t->isOverdue())->count(),
            'logsNeedingAction' => $logsNeedingAction,
            'weekMinutes'       => (int) $weekMinutes,
            'timerTasks'        => $timerTasks,
            'latestMessage'     => $this->latestMessageFor($wsIds, $user),
        ]);
    }

    // ── Line manager ─────────────────────────────────────────────────────────

    public function lineManager(): View
    {
        $user       = auth()->user();
        $workspaces = $this->workspacesFor($user);
        $wsIds      = $workspaces->pluck('id');

        $emptyQueue = ['logs' => collect(), 'tasks' => collect(), 'reports' => collect()];

        if ($wsIds->isEmpty()) {
            return view('dashboard.line-manager', [
                'workspaces'    => $workspaces,
                'queue'         => $emptyQueue,
                'queueTotal'    => 0,
                'runningTimers' => collect(),
                'blockedTasks'  => collect(),
                'perWorkspace'  => collect(),
            ]);
        }

        // The review queue lists real items, each deep-linked.
        $logs = WorkspaceTimeLog::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'submitted')
            ->with(['workspace:id,name', 'user:id,name'])
            ->orderBy('log_date')
            ->limit(10)
            ->get();

        $tasks = WorkspaceTask::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'submitted')
            ->with(['workspace:id,name', 'assignedTo:id,name'])
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();

        $reports = WorkspaceWeeklyReport::query()
            ->whereIn('workspace_id', $wsIds)
            ->whereIn('status', ['draft', 'submitted', 'approved'])
            ->with('workspace:id,name')
            ->orderByDesc('week_start_date')
            ->limit(10)
            ->get();

        $blockedTasks = WorkspaceTask::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'blocked')
            ->with(['workspace:id,name', 'assignedTo:id,name'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $runningTimers = WorkspaceTimeLog::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'running')
            ->whereNull('ended_at')
            ->with(['workspace:id,name', 'user:id,name'])
            ->limit(10)
            ->get();

        // Per-workspace attention counts, computed from the collections above
        // so no extra query runs inside the view loop.
        $perWorkspace = $workspaces->mapWithKeys(fn ($w) => [$w->id => [
            'logs'    => $logs->where('workspace_id', $w->id)->count(),
            'tasks'   => $tasks->where('workspace_id', $w->id)->count(),
            'blocked' => $blockedTasks->where('workspace_id', $w->id)->count(),
            'reports' => $reports->where('workspace_id', $w->id)->where('status', '!=', 'published')->count(),
        ]]);

        return view('dashboard.line-manager', [
            'workspaces'    => $workspaces,
            'queue'         => ['logs' => $logs, 'tasks' => $tasks, 'reports' => $reports],
            'queueTotal'    => $logs->count() + $tasks->count() + $reports->count(),
            'runningTimers' => $runningTimers,
            'blockedTasks'  => $blockedTasks,
            'perWorkspace'  => $perWorkspace,
        ]);
    }

    // ── Client roles ─────────────────────────────────────────────────────────

    public function client(): View
    {
        $user       = auth()->user();
        $role       = $user->getGvosRoleName();
        $workspaces = $this->workspacesFor($user);
        $wsIds      = $workspaces->pluck('id');

        $awaitingApproval = $wsIds->isEmpty() ? collect() : WorkspaceTask::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'submitted')
            ->with(['workspace:id,name', 'assignedTo:id,name'])
            ->orderBy('submitted_at')
            ->limit(8)
            ->get();

        $inProgress = $wsIds->isEmpty() ? collect() : WorkspaceTask::query()
            ->whereIn('workspace_id', $wsIds)
            ->whereIn('status', ['in_progress', 'pending'])
            ->with('workspace:id,name')
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit(6)
            ->get();

        $latestReport = $wsIds->isEmpty() ? null : WorkspaceWeeklyReport::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('status', 'published')
            ->with('workspace:id,name')
            ->orderByDesc('published_at')
            ->first();

        $billing = $this->billingSummary($wsIds);

        $data = [
            'workspaces'       => $workspaces,
            'awaitingApproval' => $awaitingApproval,
            'inProgress'       => $inProgress,
            'latestReport'     => $latestReport,
            'billing'          => $billing,
            'latestMessage'    => $this->latestMessageFor($wsIds, $user),
        ];

        return match ($role) {
            'business_client_admin' => view('dashboard.business-client-admin', $data + [
                'company'     => $user->clientProfile?->company,
                'teamCount'   => $this->companyTeamCount($user),
            ]),
            'business_client_staff' => view('dashboard.business-client-staff', $data + [
                'isObserver' => $this->isObserverEverywhere($workspaces, $user),
            ]),
            default => view('dashboard.individual-client', $data),
        };
    }

    // ── Active lead ──────────────────────────────────────────────────────────

    public function lead(): View
    {
        $user = auth()->user();

        $trial = $user->activeLeadTrials()
            ->with(['leadRequest', 'priceEstimate', 'assignedTalent:id,name', 'assignedManager:id,name', 'workspace'])
            ->latest()
            ->first();

        $leadRequest = $trial?->leadRequest;

        if (! $leadRequest) {
            $leadRequest = \App\Models\LeadRequest::query()
                ->where('email', $user->email)
                ->latest()
                ->first();
        }

        return view('dashboard.active-lead', [
            'trial'       => $trial,
            'leadRequest' => $leadRequest,
            'estimate'    => $trial?->priceEstimate ?? $leadRequest?->latestAcceptedEstimate() ?? $leadRequest?->latestEstimate(),
            'workspace'   => $trial?->workspace ?? $user->primaryWorkspace(),
        ]);
    }

    // ── Shared helpers ───────────────────────────────────────────────────────

    /**
     * Every active workspace the user can reach, eager-loaded for card display.
     * One query + its eager loads, reused across all derived figures.
     */
    private function workspacesFor(User $user): Collection
    {
        return Workspace::query()
            ->where(function ($q) use ($user) {
                $q->where('primary_manager_id', $user->id)
                  ->orWhere('primary_talent_id', $user->id)
                  ->orWhereHas('members', fn ($m) => $m
                      ->where('user_id', $user->id)
                      ->where('status', 'active'));
            })
            ->whereIn('status', ['pending', 'active', 'paused'])
            ->with([
                'primaryManager:id,name',
                'primaryTalent:id,name',
                'activeSubscription',
            ])
            ->orderBy('name')
            ->limit(25)
            ->get();
    }

    /** Most recent public message across the user's workspaces, if any. */
    private function latestMessageFor(Collection $wsIds, User $user): ?WorkspaceMessage
    {
        if ($wsIds->isEmpty()) {
            return null;
        }

        return WorkspaceMessage::query()
            ->whereIn('workspace_id', $wsIds)
            ->where('visibility', 'public')
            ->where('user_id', '!=', $user->id)
            ->with(['user:id,name', 'workspace:id,name'])
            ->latest()
            ->first();
    }

    /**
     * Outstanding balance and the invoice that needs attention, if any.
     * Returns currency alongside the amount so views never print a bare number.
     */
    private function billingSummary(Collection $wsIds): array
    {
        if ($wsIds->isEmpty()) {
            return ['outstanding' => 0.0, 'currency' => 'USD', 'invoice' => null, 'overdue' => false];
        }

        $invoices = Invoice::query()
            ->whereIn('workspace_id', $wsIds)
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $first = $invoices->first();

        return [
            'outstanding' => (float) $invoices->sum('balance_due'),
            'currency'    => $first->currency ?? 'USD',
            'invoice'     => $first,
            'overdue'     => (bool) $invoices->first(fn ($i) => $i->isOverdue()),
        ];
    }

    /** Number of colleagues on the same company account. */
    private function companyTeamCount(User $user): int
    {
        $companyId = $user->clientProfile?->company_id;

        if (! $companyId) {
            return 0;
        }

        return \App\Models\ClientProfile::where('company_id', $companyId)->count();
    }

    /**
     * True when the user is an `observer` in every workspace they belong to.
     * Resolved with one query rather than calling resolveUserWorkspaceRole()
     * per workspace (which would issue a query inside a loop).
     */
    private function isObserverEverywhere(Collection $workspaces, User $user): bool
    {
        if ($workspaces->isEmpty()) {
            return false;
        }

        $roles = \App\Models\WorkspaceMember::query()
            ->whereIn('workspace_id', $workspaces->pluck('id'))
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('role');

        return $roles->isNotEmpty() && $roles->every(fn ($r) => $r === 'observer');
    }
}
