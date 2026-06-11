<x-layouts.gvos title="Manager Dashboard">
{{-- Stitch reference: manager_command_center_gvos/code.html — Phase 26 Batch 3 --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $managerProfile = $user->managerProfile;

    $managedWorkspaceIds = \App\Models\Workspace::where(function ($q) use ($user) {
            $q->where('primary_manager_id', $user->id)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('role', 'manager')->where('status', 'active'));
        })->pluck('id');

    $myWorkspaces        = $managedWorkspaceIds->count();
    $workspacesActive    = \App\Models\Workspace::whereIn('id', $managedWorkspaceIds)->where('status', 'active')->count();
    $workspacesPending   = \App\Models\Workspace::whereIn('id', $managedWorkspaceIds)->where('status', 'pending')->count();

    $managerTasksOpen      = \App\Models\WorkspaceTask::whereIn('workspace_id', $managedWorkspaceIds)
        ->whereIn('status', ['pending', 'in_progress', 'revision_requested'])->count();
    $managerTasksSubmitted = \App\Models\WorkspaceTask::whereIn('workspace_id', $managedWorkspaceIds)
        ->where('status', 'submitted')->count();
    $managerTasksBlocked   = \App\Models\WorkspaceTask::whereIn('workspace_id', $managedWorkspaceIds)
        ->where('status', 'blocked')->count();

    $timeLogsPending = \App\Models\WorkspaceTimeLog::whereIn('workspace_id', $managedWorkspaceIds)
        ->where('status', 'submitted')->count();
    $reportsPending  = \App\Models\WorkspaceWeeklyReport::whereIn('workspace_id', $managedWorkspaceIds)
        ->whereIn('status', ['draft', 'submitted'])->count();

    $name = $profile?->first_name ?? $user->name ?? 'there';
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
@endphp

{{-- Phase 16: onboarding banner --}}
@php $__obUser = $user; @endphp
@include('partials.onboarding-banner')

{{-- ── Hero panel ───────────────────────────────────────────────────────── --}}
<div class="rounded-2xl border border-border-subtle shadow-sm overflow-hidden mb-8"
     style="background:linear-gradient(135deg,rgba(0,88,190,0.04) 0%,rgba(255,255,255,0) 55%),#fff;">
    <div class="p-6 lg:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1 min-w-0">
            <p class="font-label-md text-label-md text-secondary uppercase tracking-widest mb-2">Manager Command Center</p>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ $greeting }}, {{ $name }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                @if ($myWorkspaces === 0)
                    No workspaces assigned yet. Contact your administrator to get started.
                @elseif ($managerTasksSubmitted > 0 || $timeLogsPending > 0 || $reportsPending > 0)
                    @php $actionCount = $managerTasksSubmitted + $timeLogsPending + $reportsPending; @endphp
                    {{ $actionCount }} {{ Str::plural('item', $actionCount) }} require your review across {{ $myWorkspaces }} {{ Str::plural('workspace', $myWorkspaces) }}.
                @elseif ($managerTasksBlocked > 0)
                    {{ $managerTasksBlocked }} blocked {{ Str::plural('task', $managerTasksBlocked) }} need attention across your workspaces.
                @else
                    All clear. {{ $myWorkspaces }} {{ Str::plural('workspace', $myWorkspaces) }} running smoothly.
                @endif
            </p>
            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('workspace.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                    My Workspaces
                </a>
                @if ($timeLogsPending > 0)
                    <a href="{{ route('workspace.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                        Review Time Logs
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold"
                              style="background:#D97706;">{{ $timeLogsPending }}</span>
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">notifications</span>
                    Notifications
                </a>
            </div>
        </div>
        {{-- Manager profile card --}}
        @if ($managerProfile)
        <div class="flex-shrink-0 w-full lg:w-64 rounded-xl border border-border-subtle p-5"
             style="background:rgba(0,88,190,0.03);">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(0,88,190,0.08);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:20px;">manage_accounts</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface font-semibold">Manager Profile</p>
                    @if ($managerProfile->manager_code)
                        <p class="font-mono-sm text-mono-sm text-outline">{{ $managerProfile->manager_code }}</p>
                    @endif
                </div>
            </div>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center border-t border-border-subtle pt-2.5">
                    <span class="font-label-md text-[11px] text-outline">Client capacity</span>
                    <span class="font-label-md text-label-md font-semibold text-on-surface">
                        {{ $managerProfile->current_load }} / {{ $managerProfile->capacity_limit }}
                    </span>
                </div>
                <div class="flex justify-between items-center border-t border-border-subtle pt-2.5">
                    <span class="font-label-md text-[11px] text-outline">Active workspaces</span>
                    <span class="font-label-md text-label-md font-semibold text-on-surface">{{ $workspacesActive }}</span>
                </div>
                @php $fillPct = $managerProfile->capacity_limit > 0 ? min(100, round($managerProfile->current_load / $managerProfile->capacity_limit * 100)) : 0; @endphp
                <div class="border-t border-border-subtle pt-2.5">
                    <div class="flex justify-between text-[10px] text-outline mb-1.5">
                        <span>Load</span>
                        <span>{{ $fillPct }}%</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full" style="background:rgba(0,88,190,0.10);">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ $fillPct }}%;background:{{ $fillPct >= 90 ? '#EF4444' : ($fillPct >= 70 ? '#F59E0B' : '#0058be') }};"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Stat cards (4-up) ────────────────────────────────────────────────── --}}
<section class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-portal.stat-card
        label="Active Workspaces"
        :value="$workspacesActive"
        icon="workspaces"
        accent="secondary"
        :href="route('workspace.index')"
        :hint="$workspacesPending > 0 ? $workspacesPending . ' pending' : 'of ' . $myWorkspaces . ' total'" />

    @php
        $reviewPct = ($managerTasksOpen + $managerTasksSubmitted) > 0
            ? min(100, round($managerTasksSubmitted / ($managerTasksOpen + $managerTasksSubmitted) * 100))
            : 0;
    @endphp
    <x-portal.stat-card
        label="Tasks for Review"
        :value="$managerTasksSubmitted"
        icon="pending_actions"
        :value-class="$managerTasksSubmitted > 0 ? 'text-status-payment-due' : 'text-primary'"
        :hint="$managerTasksSubmitted > 0 ? 'Needs review' : 'All clear'"
        :progress="$reviewPct"
        progress-color="#D97706" />

    <x-portal.stat-card
        label="Blocked Tasks"
        :value="$managerTasksBlocked"
        icon="block"
        :value-class="$managerTasksBlocked > 0 ? 'text-status-blocked' : 'text-primary'"
        :hint="$managerTasksBlocked > 0 ? 'Requires action' : $managerTasksOpen . ' open, no blockers'" />

    <x-portal.stat-card
        label="Pending Review"
        :value="$timeLogsPending + $reportsPending"
        icon="rate_review"
        accent="secondary"
        :hint="$timeLogsPending . ' time ' . Str::plural('log', $timeLogsPending) . ($reportsPending > 0 ? ' · ' . $reportsPending . ' ' . Str::plural('report', $reportsPending) : '')"
        :hint-class="$reportsPending > 0 ? 'text-status-payment-due' : 'text-outline'" />

</section>

{{-- ── Main 12-col grid: Workspace list + Sidebar ──────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Workspace list (8 cols) --}}
    <div class="lg:col-span-8">
        <x-portal.section-card flush title="Supervised Workspaces">
            <x-slot:actions>
                @if ($myWorkspaces > 0)
                    <a href="{{ route('workspace.index') }}"
                       class="text-secondary font-label-md text-label-md hover:underline flex items-center gap-1">
                        View all
                        <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                    </a>
                @endif
            </x-slot:actions>

            @if ($managedWorkspaceIds->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No workspaces yet"
                    message="Workspaces will appear here once your administrator assigns them to you." />
            @else
                @php
                    $wsList = \App\Models\Workspace::whereIn('id', $managedWorkspaceIds)
                        ->with(['primaryTalent'])
                        ->orderBy('status')
                        ->limit(8)
                        ->get();
                @endphp
                <div class="divide-y divide-border-subtle">
                    @foreach ($wsList as $ws)
                        @php
                            $wsTasksQ    = \App\Models\WorkspaceTask::where('workspace_id', $ws->id);
                            $wsSubmitted = (clone $wsTasksQ)->where('status', 'submitted')->count();
                            $wsBlocked   = (clone $wsTasksQ)->where('status', 'blocked')->count();
                            $wsTimeLogs  = \App\Models\WorkspaceTimeLog::where('workspace_id', $ws->id)->where('status', 'submitted')->count();
                            $statusColor = match($ws->status) {
                                'active'    => '#10B981',
                                'pending'   => '#F59E0B',
                                'paused'    => '#64748B',
                                'completed' => '#7C3AED',
                                default     => '#94A3B8',
                            };
                        @endphp
                        <div class="px-card-padding py-4">
                            {{-- Workspace identity row --}}
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background-color:#0058be;">
                                    {{ strtoupper(substr($ws->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('workspace.show', $ws) }}"
                                       class="font-label-md text-label-md text-on-surface font-semibold hover:text-secondary transition-colors block truncate">
                                        {{ $ws->name }}
                                    </a>
                                    <p class="font-label-md text-[10px] text-outline">
                                        {{ $ws->workspace_code }}
                                        @if ($ws->primaryTalent)
                                            &middot; {{ $ws->primaryTalent->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0"
                                      style="color:{{ $statusColor }};background:{{ $statusColor }}18;">
                                    {{ ucfirst($ws->status) }}
                                </span>
                            </div>
                            {{-- Alert badges + quick-link chips --}}
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($wsSubmitted > 0)
                                    <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          style="background:rgba(245,158,11,0.1);color:#D97706;">
                                        {{ $wsSubmitted }} task {{ Str::plural('review', $wsSubmitted) }}
                                    </span>
                                @endif
                                @if ($wsBlocked > 0)
                                    <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          style="background:rgba(239,68,68,0.1);color:#EF4444;">
                                        {{ $wsBlocked }} blocked
                                    </span>
                                @endif
                                @if ($wsTimeLogs > 0)
                                    <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          style="background:rgba(0,88,190,0.1);color:#0058be;">
                                        {{ $wsTimeLogs }} log {{ Str::plural('review', $wsTimeLogs) }}
                                    </span>
                                @endif
                                <div class="flex flex-wrap gap-1.5 ml-auto">
                                    <a href="{{ route('workspace.tasks.index', $ws) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                       style="background:rgba(0,88,190,0.03);">
                                        <span class="material-symbols-outlined" style="font-size:12px;">task_alt</span>
                                        Tasks
                                    </a>
                                    <a href="{{ route('workspace.time-logs.index', $ws) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                       style="background:rgba(0,88,190,0.03);">
                                        <span class="material-symbols-outlined" style="font-size:12px;">schedule</span>
                                        Logs
                                    </a>
                                    <a href="{{ route('workspace.reports.index', $ws) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                       style="background:rgba(0,88,190,0.03);">
                                        <span class="material-symbols-outlined" style="font-size:12px;">summarize</span>
                                        Reports
                                    </a>
                                    <a href="{{ route('workspace.show', $ws) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                                       style="background:rgba(0,88,190,0.03);">
                                        <span class="material-symbols-outlined" style="font-size:12px;">open_in_new</span>
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-portal.section-card>
    </div>

    {{-- Sidebar (4 cols) --}}
    <div class="lg:col-span-4 space-y-4">

        {{-- Action queue --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-card-padding py-3.5 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                <h4 class="font-label-md text-label-md text-outline uppercase tracking-wider">Action Queue</h4>
            </div>
            <div class="divide-y divide-border-subtle">
                <div class="px-card-padding py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined" style="font-size:18px;color:{{ $timeLogsPending > 0 ? '#D97706' : '#76777d' }};">schedule</span>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Time Log Reviews</p>
                            <p class="font-label-md text-[11px] text-outline">
                                {{ $timeLogsPending > 0 ? $timeLogsPending . ' submitted' : 'No pending reviews' }}
                            </p>
                        </div>
                    </div>
                    @if ($timeLogsPending > 0)
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white text-[10px] font-bold"
                              style="background:#D97706;">{{ $timeLogsPending }}</span>
                    @else
                        <span class="material-symbols-outlined text-outline" style="font-size:16px;">check_circle</span>
                    @endif
                </div>
                <div class="px-card-padding py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined" style="font-size:18px;color:{{ $managerTasksSubmitted > 0 ? '#7C3AED' : '#76777d' }};">pending_actions</span>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Tasks for Review</p>
                            <p class="font-label-md text-[11px] text-outline">
                                {{ $managerTasksSubmitted > 0 ? $managerTasksSubmitted . ' submitted' : 'No pending tasks' }}
                            </p>
                        </div>
                    </div>
                    @if ($managerTasksSubmitted > 0)
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white text-[10px] font-bold"
                              style="background:#7C3AED;">{{ $managerTasksSubmitted }}</span>
                    @else
                        <span class="material-symbols-outlined text-outline" style="font-size:16px;">check_circle</span>
                    @endif
                </div>
                <div class="px-card-padding py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined" style="font-size:18px;color:{{ $reportsPending > 0 ? '#0058be' : '#76777d' }};">summarize</span>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Report Drafts</p>
                            <p class="font-label-md text-[11px] text-outline">
                                {{ $reportsPending > 0 ? $reportsPending . ' awaiting publish' : 'No drafts pending' }}
                            </p>
                        </div>
                    </div>
                    @if ($reportsPending > 0)
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white text-[10px] font-bold"
                              style="background:#0058be;">{{ $reportsPending }}</span>
                    @else
                        <span class="material-symbols-outlined text-outline" style="font-size:16px;">check_circle</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-card-padding py-3.5 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                <h4 class="font-label-md text-label-md text-outline uppercase tracking-wider">Quick Links</h4>
            </div>
            <div class="divide-y divide-border-subtle">
                @foreach ([
                    ['label' => 'All Workspaces',  'icon' => 'workspaces',       'route' => route('workspace.index')],
                    ['label' => 'Notifications',    'icon' => 'notifications',    'route' => route('notifications.index')],
                    ['label' => 'My Profile',        'icon' => 'manage_accounts',  'route' => route('profile.show')],
                ] as $link)
                <a href="{{ $link['route'] }}"
                   class="flex items-center gap-3 px-card-padding py-3 text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined" style="font-size:18px;">{{ $link['icon'] }}</span>
                    <span class="font-label-md text-label-md flex-1">{{ $link['label'] }}</span>
                    <span class="material-symbols-outlined" style="font-size:14px;color:#CBD5E1;">chevron_right</span>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

</x-layouts.gvos>
