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

    $myTimeLogs = \App\Models\WorkspaceTimeLog::where('user_id', $user->id);
    $weekStart  = now()->startOfWeek();
    $timeThisWeek = (clone $myTimeLogs)
        ->where('status', 'approved')
        ->whereBetween('log_date', [$weekStart->format('Y-m-d'), now()->format('Y-m-d')])
        ->sum('duration_minutes');
    $timeThisWeekH = intdiv($timeThisWeek, 60);

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- Phase 16: onboarding banner --}}
@php $__obUser = $user; @endphp
@include('partials.onboarding-banner')

{{-- ── Page header + Clock-In widget ──────────────────────────────────── --}}
{{-- Stitch: flex row with welcome left + Clock-In/Out widget right --}}
<section class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary">Welcome back, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            @if ($myAssignedTasks > 0)
                You have {{ $myAssignedTasks }} active {{ Str::plural('task', $myAssignedTasks) }}
                across {{ $myWorkspaces }} {{ Str::plural('workspace', $myWorkspaces) }}.
            @elseif ($myWorkspaces > 0)
                All caught up. {{ $myWorkspaces === 1 ? 'Your workspace is' : 'All your workspaces are' }} active.
            @else
                No workspaces assigned yet. You'll be notified when one is ready.
            @endif
        </p>
    </div>

    {{-- Clock-In/Out timer widget --}}
    {{-- Stitch: bg-white p-2 pl-4 rounded-2xl border-2 border-border-subtle shadow-lg --}}
    <div class="bg-white p-4 rounded-xl border-2 border-border-subtle shadow-lg min-w-full lg:min-w-[420px]">
        @if ($activeTimer)
            <div class="flex items-start gap-4">
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

            <div class="mt-4 grid grid-cols-1 gap-3">
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
                <div class="flex items-center gap-3">
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
                        class="w-full bg-secondary text-on-secondary px-6 py-3 rounded-xl font-bold font-label-md text-label-md hover:brightness-110 transition-all active:scale-95 shadow-md"
                        style="box-shadow:0 4px 12px rgba(0,88,190,0.2);">
                    Clock In
                </button>
            </form>
        @else
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                     style="background:rgba(148,163,184,0.12);color:#64748B;">
                    <span class="material-symbols-outlined text-2xl">timer_off</span>
                </div>
                <div>
                    <p class="font-label-md text-[10px] text-outline uppercase tracking-widest">Session</p>
                    <p class="font-mono-sm text-lg font-bold text-primary tracking-tight">No workspace</p>
                    <p class="text-xs text-on-surface-variant mt-1">Your timer will appear when a workspace is assigned.</p>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- ── Metrics bento grid (4 columns) ─────────────────────────────────── --}}
{{-- Stitch: 4-col grid — Today's Tasks, Overdue, Blocked, Weekly Goal --}}
@if (session('success'))
    <x-portal.alert type="success">
        {{ session('success') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">
        {{ session('error') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@endif

<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-portal.stat-card
        label="Active Tasks"
        :value="$myAssignedTasks"
        icon="task_alt"
        accent="secondary"
        :hint="$myDueSoonTasks > 0 ? $myDueSoonTasks . ' due soon' : null"
        hint-class="text-status-payment-due" />

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

    {{-- Weekly goal card with progress bar --}}
    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-center mb-2">
            <span class="font-label-md text-label-md text-outline uppercase tracking-wider">Weekly Time</span>
            <span class="font-bold text-sm text-primary">{{ $timeThisWeekH }}h</span>
        </div>
        <div class="w-full bg-surface-container-high h-2.5 rounded-full overflow-hidden">
            @php $goalPct = min(100, $timeThisWeek > 0 ? round($timeThisWeek / (40 * 60) * 100) : 0); @endphp
            <div class="bg-secondary h-full rounded-full transition-all" style="width: {{ $goalPct }}%"></div>
        </div>
        <p class="font-label-md text-[10px] text-outline mt-3 flex items-center justify-between">
            <span>{{ $timeThisWeekH }}h / 40h goal</span>
            <span class="text-secondary font-bold">{{ 40 - $timeThisWeekH > 0 ? (40 - $timeThisWeekH) . 'h left' : 'Goal met!' }}</span>
        </p>
    </div>
</section>

{{-- ── Main content: My workspaces + Quick links ──────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- My Workspaces --}}
    <div class="lg:col-span-2">
        <x-portal.section-card flush title="My Workspaces">
            <x-slot:actions>
                <a href="{{ route('workspace.index') }}"
                   class="text-secondary font-label-md text-label-md hover:underline font-bold">
                    View All
                </a>
            </x-slot:actions>

            @if ($workspaceList->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No active workspaces yet"
                    message="Your workspace will appear here once it's activated." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaceList->take(5) as $ws)
                        <a href="{{ route('workspace.show', $ws) }}"
                           class="flex items-center justify-between px-card-padding py-4 hover:bg-surface-container-low transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs text-white flex-shrink-0"
                                     style="background-color:#0058be;">
                                    {{ strtoupper(substr($ws->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-body-sm font-semibold text-on-surface group-hover:text-secondary transition-colors">
                                        {{ $ws->name }}
                                    </p>
                                    <p class="font-label-md text-[10px] text-outline">
                                        {{ $ws->workspace_code }}
                                        @if ($ws->primaryManager)
                                            &middot; Mgr: {{ $ws->primaryManager->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-portal.status-badge :status="$ws->status" />
                                <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors"
                                      style="font-size:16px;">chevron_right</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-portal.section-card>
    </div>

    {{-- Quick links sidebar --}}
    <div class="space-y-4">

        {{-- Talent profile status --}}
        @if ($talentProfile)
        <div class="bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                     style="background:rgba(0,88,190,0.06)">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">badge</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface font-semibold">Talent Profile</p>
                    @if ($talentProfile->talent_code)
                        <p class="font-mono-sm text-mono-sm text-outline">{{ $talentProfile->talent_code }}</p>
                    @endif
                </div>
            </div>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-outline font-label-md text-label-md">Training</span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">
                        {{ ucwords(str_replace('_', ' ', $talentProfile->training_status)) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-outline font-label-md text-label-md">Equipment</span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">
                        {{ ucwords(str_replace('_', ' ', $talentProfile->equipment_status)) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick navigation --}}
        <div>
            <h4 class="font-label-md text-label-md text-outline uppercase tracking-wider mb-3 px-1">Quick Links</h4>
            <div class="space-y-3">
                <x-portal.action-card
                    :href="route('workspace.index')"
                    icon="workspaces"
                    title="My Workspaces"
                    description="View every workspace you're assigned to." />
                <x-portal.action-card
                    :href="$defaultTimerWorkspace ? route('workspace.time-logs.index', $defaultTimerWorkspace) : route('workspace.index')"
                    icon="schedule"
                    title="Time Logs"
                    description="Review and submit your tracked hours." />
                <x-portal.action-card
                    :href="route('notifications.index')"
                    icon="notifications"
                    title="Notifications"
                    description="Catch up on workspace activity and alerts." />
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
