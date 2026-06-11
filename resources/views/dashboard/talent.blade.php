<x-layouts.gvos title="My Dashboard">
{{-- Stitch reference: talent_dashboard_gvos_1/code.html --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $talentProfile = $user->talentProfile;

    $workspaceList = \App\Models\Workspace::where(function ($q) use ($user) {
            $q->where('primary_talent_id', $user->id)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'));
        })
        ->whereIn('status', ['pending', 'active'])
        ->with(['primaryManager'])
        ->latest()
        ->get();

    $myWorkspaces = $workspaceList->count();
    $activeTimer = \App\Models\WorkspaceTimeLog::activeTimerFor($user);
    $defaultTimerWorkspace = $workspaceList->first();
    $timerTasks = $workspaceList->isNotEmpty()
        ? \App\Models\WorkspaceTask::whereIn('workspace_id', $workspaceList->pluck('id'))
            ->where(function ($q) use ($user) {
                $q->where('assigned_to_user_id', $user->id)
                  ->orWhereNull('assigned_to_user_id');
            })
            ->whereIn('status', ['pending', 'in_progress', 'blocked', 'revision_requested'])
            ->orderBy('title')
            ->get()
        : collect();

    $myAssignedTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->whereIn('status', ['pending', 'in_progress', 'blocked', 'revision_requested'])
        ->count();
    $myDueSoonTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->whereIn('status', ['pending', 'in_progress'])
        ->whereNotNull('due_date')
        ->whereDate('due_date', '<=', now()->addDays(3))
        ->count();
    $myBlockedTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->where('status', 'blocked')
        ->count();

    $weekStart    = now()->startOfWeek();
    $timeThisWeek = \App\Models\WorkspaceTimeLog::where('user_id', $user->id)
        ->where('status', 'approved')
        ->whereBetween('log_date', [$weekStart->format('Y-m-d'), now()->format('Y-m-d')])
        ->sum('duration_minutes');
    $timeThisWeekH = intdiv($timeThisWeek, 60);

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- Phase 16: onboarding banner --}}
@php $__obUser = $user; @endphp
@include('partials.onboarding-banner')

{{-- ── Hero panel (welcome left + timer right) ──────────────────────────── --}}
<div class="rounded-2xl border border-border-subtle shadow-sm overflow-hidden mb-8"
     style="background:linear-gradient(135deg,rgba(0,88,190,0.04) 0%,rgba(255,255,255,0) 55%),#fff;">
    <div class="flex flex-col lg:flex-row lg:items-stretch">

        {{-- Welcome / status panel --}}
        <div class="flex-1 p-6 lg:p-8 border-b border-border-subtle lg:border-b-0 lg:border-r flex flex-col justify-center gap-4">
            <div>
                <p class="font-label-md text-label-md text-secondary uppercase tracking-widest mb-2">Talent Workspace</p>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Welcome back, {{ $name }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                    @if ($activeTimer)
                        You have an active work session running now.
                    @elseif ($myAssignedTasks > 0)
                        You have {{ $myAssignedTasks }} active {{ Str::plural('task', $myAssignedTasks) }}
                        across {{ $myWorkspaces }} {{ Str::plural('workspace', $myWorkspaces) }}.
                    @elseif ($myWorkspaces > 0)
                        All caught up. {{ $myWorkspaces === 1 ? 'Your workspace is' : 'All your workspaces are' }} active.
                    @else
                        No workspaces assigned yet. You'll be notified when one is ready.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($activeTimer && $activeTimer->workspace)
                    <a href="{{ route('workspace.show', $activeTimer->workspace) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                        View Active Workspace
                    </a>
                @elseif ($defaultTimerWorkspace)
                    <a href="{{ route('workspace.show', $defaultTimerWorkspace) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                        Open Workspace
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">notifications</span>
                    Notifications
                </a>
                @if ($defaultTimerWorkspace)
                    <a href="{{ route('workspace.time-logs.index', $defaultTimerWorkspace) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                        Time Logs
                    </a>
                @endif
            </div>
        </div>

        {{-- Clock-In / Out timer widget --}}
        <div class="lg:w-[420px] p-6 lg:p-8 flex flex-col justify-center">
            @if ($activeTimer)
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse"
                         style="background:rgba(16,185,129,0.1);color:#10B981;">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">timer</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-label-md text-[10px] text-outline uppercase tracking-widest">Active session</p>
                        <p class="font-mono-sm text-lg font-bold text-primary tracking-tight js-running-timer"
                           data-started-at="{{ $activeTimer->started_at?->toIso8601String() }}">
                            {{ $activeTimer->durationForHumans() }}
                        </p>
                        <p class="text-xs text-on-surface-variant mt-1">
                            {{ $activeTimer->workspace?->name ?? 'Workspace' }}
                            @if ($activeTimer->task)
                                &middot; {{ Str::limit($activeTimer->task->title, 42) }}
                            @endif
                        </p>
                        <p class="text-[10px] text-outline mt-0.5">
                            Started {{ $activeTimer->started_at?->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('workspace.time-tracker.stop', $activeTimer->workspace) }}">
                        @csrf
                        <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                        <input type="hidden" name="status" value="draft">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border transition-all"
                                style="border-color:#F59E0B;color:#92400E;background:rgba(245,158,11,0.08);">
                            <span class="material-symbols-outlined" style="font-size:16px;">stop_circle</span>
                            Clock Out
                        </button>
                    </form>
                    <form method="POST" action="{{ route('workspace.time-tracker.complete', $activeTimer->workspace) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                        <input type="text" name="work_summary" required maxlength="1000"
                               placeholder="Work summary for review"
                               class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary px-4 py-2 rounded-lg text-sm font-semibold hover:brightness-110 transition-all">
                            <span class="material-symbols-outlined" style="font-size:16px;">task_alt</span>
                            Complete Work Session
                        </button>
                    </form>
                </div>
            @elseif ($workspaceList->isNotEmpty() && $defaultTimerWorkspace)
                <form id="dashboard-start-timer-form"
                      method="POST"
                      action="{{ route('workspace.time-tracker.start', $defaultTimerWorkspace) }}"
                      class="space-y-3">
                    @csrf
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background:rgba(0,88,190,0.08);color:#0058be;">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">timer</span>
                        </div>
                        <div>
                            <p class="font-label-md text-[10px] text-outline uppercase tracking-widest">Session</p>
                            <p class="font-mono-sm text-lg font-bold text-primary tracking-tight">Ready to start</p>
                        </div>
                    </div>
                    <select id="dashboard-timer-workspace"
                            class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                        @foreach ($workspaceList as $timerWorkspace)
                            <option value="{{ $timerWorkspace->id }}"
                                    data-start-url="{{ route('workspace.time-tracker.start', $timerWorkspace) }}">
                                {{ $timerWorkspace->name }}
                            </option>
                        @endforeach
                    </select>
                    <select id="dashboard-timer-task"
                            name="workspace_task_id"
                            class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                        <option value="">No specific task</option>
                        @foreach ($timerTasks as $timerTask)
                            <option value="{{ $timerTask->id }}" data-workspace-id="{{ $timerTask->workspace_id }}">
                                {{ $timerTask->task_code }} - {{ Str::limit($timerTask->title, 48) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-secondary text-on-secondary px-6 py-3 rounded-xl font-bold font-label-md text-label-md hover:brightness-110 transition-all active:scale-95"
                            style="box-shadow:0 4px 12px rgba(0,88,190,0.2);">
                        Clock In
                    </button>
                </form>
            @else
                <div class="flex items-center gap-4 p-4 rounded-xl border border-border-subtle"
                     style="background:rgba(148,163,184,0.04);">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                         style="background:rgba(148,163,184,0.12);color:#64748B;">
                        <span class="material-symbols-outlined text-2xl">timer_off</span>
                    </div>
                    <div>
                        <p class="font-label-md text-[10px] text-outline uppercase tracking-widest">Session</p>
                        <p class="font-body-sm font-semibold text-on-surface-variant mt-0.5">No workspace assigned yet</p>
                        <p class="text-xs text-outline mt-0.5">Your timer will appear once a workspace is activated.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Flash alerts ─────────────────────────────────────────────────────── --}}
@if (session('success'))
    <x-portal.alert type="success" class="mb-6">
        {{ session('success') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error" class="mb-6">
        {{ session('error') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@endif

{{-- ── Work status strip ────────────────────────────────────────────────── --}}
<section class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-portal.stat-card
        label="Active Tasks"
        :value="$myAssignedTasks"
        icon="task_alt"
        accent="secondary"
        :hint="$myDueSoonTasks > 0 ? $myDueSoonTasks . ' due soon' : ($myAssignedTasks === 0 ? 'No active tasks' : null)"
        :hint-class="$myDueSoonTasks > 0 ? 'text-status-payment-due' : 'text-outline'" />

    <x-portal.stat-card
        label="Due Soon"
        :value="$myDueSoonTasks"
        icon="schedule"
        accent="status-urgent"
        :value-class="$myDueSoonTasks > 0 ? 'text-status-urgent' : 'text-primary'"
        :hint="$myDueSoonTasks > 0 ? 'within 3 days' : 'No urgent items'" />

    <x-portal.stat-card
        label="Blocked"
        :value="$myBlockedTasks"
        icon="block"
        accent="status-blocked"
        :value-class="$myBlockedTasks > 0 ? 'text-status-blocked' : 'text-primary'"
        :hint="$myBlockedTasks > 0 ? 'Awaiting feedback' : 'No blockers'" />

    @php
        $goalPct = min(100, $timeThisWeek > 0 ? round($timeThisWeek / (40 * 60) * 100) : 0);
    @endphp
    <x-portal.stat-card
        label="Weekly Time"
        :value="$timeThisWeekH . 'h'"
        icon="timer"
        accent="secondary"
        :hint="$goalPct >= 100 ? 'Weekly goal reached' : $timeThisWeekH . 'h / 40h goal'"
        :progress="$goalPct" />
</section>

{{-- ── Main content: workspaces + sidebar ─────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- My Workspaces (with per-workspace quick links) --}}
    <div class="lg:col-span-2">
        <x-portal.section-card flush title="My Workspaces">
            <x-slot:actions>
                <a href="{{ route('workspace.index') }}"
                   class="text-secondary font-label-md text-label-md hover:underline font-bold flex items-center gap-1">
                    View All
                    <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                </a>
            </x-slot:actions>

            @if ($workspaceList->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No active workspaces yet"
                    message="You have not been added to a workspace yet. Once your workspace is ready, it will appear here." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaceList->take(5) as $ws)
                        <div class="px-card-padding pt-4 pb-3">
                            {{-- Workspace identity row --}}
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0"
                                         style="background-color:#0058be;">
                                        {{ strtoupper(substr($ws->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('workspace.show', $ws) }}"
                                           class="font-body-sm font-semibold text-on-surface hover:text-secondary transition-colors leading-snug block truncate">
                                            {{ $ws->name }}
                                        </a>
                                        <p class="font-label-md text-[10px] text-outline">
                                            {{ $ws->workspace_code }}
                                            @if ($ws->primaryManager)
                                                &middot; {{ $ws->primaryManager->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <x-portal.status-badge :status="$ws->status" />
                            </div>
                            {{-- Quick-link chips --}}
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('workspace.tasks.index', $ws) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                   style="background:rgba(0,88,190,0.03);">
                                    <span class="material-symbols-outlined" style="font-size:13px;">task_alt</span>
                                    Tasks
                                </a>
                                <a href="{{ route('workspace.time-logs.index', $ws) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                   style="background:rgba(0,88,190,0.03);">
                                    <span class="material-symbols-outlined" style="font-size:13px;">schedule</span>
                                    Time Logs
                                </a>
                                <a href="{{ route('workspace.files.index', $ws) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                   style="background:rgba(0,88,190,0.03);">
                                    <span class="material-symbols-outlined" style="font-size:13px;">folder_open</span>
                                    Files
                                </a>
                                <a href="{{ route('workspace.chat.index', $ws) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                   style="background:rgba(0,88,190,0.03);">
                                    <span class="material-symbols-outlined" style="font-size:13px;">forum</span>
                                    Chat
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-portal.section-card>
    </div>

    {{-- Sidebar: talent profile + quick actions --}}
    <div class="space-y-5">

        {{-- Talent profile status --}}
        @if ($talentProfile)
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-card-padding">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:rgba(0,88,190,0.06)">
                        <span class="material-symbols-outlined text-secondary" style="font-size:20px;">badge</span>
                    </div>
                    <div>
                        <p class="font-body-sm font-semibold text-on-surface">Talent Profile</p>
                        @if ($talentProfile->talent_code)
                            <p class="font-mono-sm text-mono-sm text-outline">{{ $talentProfile->talent_code }}</p>
                        @endif
                    </div>
                </div>
                <div class="space-y-0">
                    <div class="flex items-center justify-between py-2.5 border-b border-border-subtle">
                        <span class="font-body-sm text-on-surface-variant">Training</span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold">
                            {{ ucwords(str_replace('_', ' ', $talentProfile->training_status)) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2.5">
                        <span class="font-body-sm text-on-surface-variant">Equipment</span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold">
                            {{ ucwords(str_replace('_', ' ', $talentProfile->equipment_status)) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick actions --}}
        <div>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-3 px-1">Quick Actions</p>
            <div class="space-y-3">
                <x-portal.action-card
                    :href="route('workspace.index')"
                    icon="workspaces"
                    title="All Workspaces"
                    description="View every workspace you are assigned to." />
                <x-portal.action-card
                    :href="$defaultTimerWorkspace ? route('workspace.time-logs.index', $defaultTimerWorkspace) : route('workspace.index')"
                    icon="schedule"
                    title="Time Logs"
                    description="Review and submit your tracked hours." />
                <x-portal.action-card
                    :href="route('notifications.index')"
                    icon="notifications"
                    title="Notifications"
                    description="Catch up on workspace activity and updates." />
                <x-portal.action-card
                    :href="route('profile.show')"
                    icon="person"
                    title="My Profile"
                    description="Update your details and preferences." />
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function formatElapsed(startedAt) {
            var started = new Date(startedAt);
            var totalSeconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
            var hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            var minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            var seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
            return hours + ':' + minutes + ':' + seconds;
        }

        document.querySelectorAll('.js-running-timer[data-started-at]').forEach(function (timer) {
            var tick = function () {
                timer.textContent = formatElapsed(timer.dataset.startedAt);
            };
            tick();
            window.setInterval(tick, 1000);
        });

        var workspaceSelect = document.getElementById('dashboard-timer-workspace');
        var taskSelect = document.getElementById('dashboard-timer-task');
        var startForm = document.getElementById('dashboard-start-timer-form');

        if (workspaceSelect && taskSelect && startForm) {
            var syncTimerForm = function () {
                var selected = workspaceSelect.options[workspaceSelect.selectedIndex];
                var workspaceId = workspaceSelect.value;
                startForm.action = selected.dataset.startUrl;

                taskSelect.querySelectorAll('option[data-workspace-id]').forEach(function (option) {
                    var visible = option.dataset.workspaceId === workspaceId;
                    option.hidden = ! visible;
                    if (! visible && option.selected) {
                        taskSelect.value = '';
                    }
                });
            };

            workspaceSelect.addEventListener('change', syncTimerForm);
            syncTimerForm();
        }
    });
</script>

</x-layouts.gvos>
