<x-layouts.gvos title="Manager Dashboard">
{{-- Stitch reference: manager_command_center_gvos/code.html --}}
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

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ $greeting }}, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            @if ($myWorkspaces > 0)
                Here's your command overview for {{ $myWorkspaces }} supervised {{ Str::plural('workspace', $myWorkspaces) }}.
            @else
                You have no active workspaces yet. Contact your administrator.
            @endif
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('workspace.index') }}"
           class="flex items-center gap-2 px-4 py-2 bg-white border border-border-subtle rounded-lg font-label-md text-label-md text-primary hover:bg-surface-container-low transition-all">
            <span class="material-symbols-outlined" style="font-size:18px;">workspaces</span>
            Workspaces
        </a>
        @if ($myWorkspaces > 0)
            <a href="{{ route('workspace.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:18px;">add_task</span>
                Tasks
            </a>
        @endif
    </div>
</section>

{{-- ── Bento status grid (4 columns) ──────────────────────────────────── --}}
{{-- Stitch: Talents Online, Late/Absent, Active Workspaces, Trial Conversion --}}
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[160px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Active Workspaces</span>
            <span class="w-3 h-3 bg-status-active rounded-full animate-pulse"></span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg text-on-surface">{{ $workspacesActive }}</span>
            @if ($workspacesPending > 0)
                <span class="font-label-md text-label-md text-status-payment-due">{{ $workspacesPending }} pending</span>
            @endif
        </div>
        <p class="font-label-md text-label-md text-on-surface-variant">of {{ $myWorkspaces }} total</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[160px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Tasks Awaiting</span>
            <span class="material-symbols-outlined text-status-payment-due" style="font-size:18px;">pending_actions</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg {{ $managerTasksSubmitted > 0 ? 'text-status-payment-due' : 'text-on-surface' }}">
                {{ $managerTasksSubmitted }}
            </span>
            <span class="font-label-md text-label-md text-on-surface-variant">
                {{ $managerTasksSubmitted > 0 ? 'Needs review' : 'All clear' }}
            </span>
        </div>
        <div class="w-full bg-surface-container-low h-1.5 rounded-full">
            @php $pct = $managerTasksOpen > 0 ? min(100, round($managerTasksSubmitted / ($managerTasksOpen + $managerTasksSubmitted + 1) * 100)) : 0; @endphp
            <div class="bg-secondary h-full rounded-full" style="width:{{ $pct }}%"></div>
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[160px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Blocked Tasks</span>
            <span class="material-symbols-outlined {{ $managerTasksBlocked > 0 ? 'text-status-blocked' : 'text-outline' }}"
                  style="font-size:18px;">block</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg {{ $managerTasksBlocked > 0 ? 'text-status-blocked' : 'text-on-surface' }}">
                {{ $managerTasksBlocked }}
            </span>
            <span class="font-label-md text-label-md text-on-surface-variant">
                {{ $managerTasksBlocked > 0 ? 'Requires action' : 'No blockers' }}
            </span>
        </div>
        <p class="font-label-md text-label-md text-on-surface-variant">{{ $managerTasksOpen }} open total</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[160px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Pending Review</span>
            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">schedule</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg text-on-surface">{{ $timeLogsPending }}</span>
            <span class="font-label-md text-label-md text-on-surface-variant">time logs</span>
        </div>
        @if ($reportsPending > 0)
            <a href="{{ route('workspace.index') }}"
               class="font-label-md text-label-md font-semibold hover:underline"
               style="color:#D97706;">
                {{ $reportsPending }} report {{ Str::plural('draft', $reportsPending) }} awaiting review
            </a>
        @else
            <p class="font-label-md text-label-md text-on-surface-variant">Awaiting your review</p>
        @endif
    </div>

</section>

{{-- ── Main content: Workspace list + Action queue ─────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Workspace list --}}
    <div class="lg:col-span-8">
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-card-padding py-4 border-b border-border-subtle flex items-center justify-between bg-surface-container-lowest">
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold">My Workspaces</h3>
                <a href="{{ route('workspace.index') }}"
                   class="text-secondary font-label-md text-label-md hover:underline">View all</a>
            </div>

            @if ($managedWorkspaceIds->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3"
                         style="background:rgba(0,88,190,0.06)">
                        <span class="material-symbols-outlined text-secondary" style="font-size:20px;">workspaces</span>
                    </div>
                    <p class="font-label-md text-label-md text-on-surface font-semibold mb-1">No workspaces yet</p>
                    <p class="font-label-md text-label-md text-outline max-w-xs mx-auto">
                        Workspaces will appear here once your administrator assigns them to you.
                    </p>
                </div>
            @else
                @php
                    $wsList = \App\Models\Workspace::whereIn('id', $managedWorkspaceIds)
                        ->with(['primaryTalent'])
                        ->orderBy('status')
                        ->limit(6)
                        ->get();
                @endphp
                <div class="divide-y divide-border-subtle">
                    @foreach ($wsList as $ws)
                        @php
                            $wsTasks     = \App\Models\WorkspaceTask::where('workspace_id', $ws->id);
                            $wsSubmitted = (clone $wsTasks)->where('status', 'submitted')->count();
                            $wsBlocked   = (clone $wsTasks)->where('status', 'blocked')->count();
                            $statusColor = match($ws->status) {
                                'active'   => '#10B981',
                                'pending'  => '#F59E0B',
                                'paused'   => '#64748B',
                                default    => '#94A3B8',
                            };
                        @endphp
                        <a href="{{ route('workspace.show', $ws) }}"
                           class="px-card-padding py-4 flex items-center justify-between hover:bg-surface-container-low transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background-color:#0058be;">
                                    {{ strtoupper(substr($ws->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-label-md text-label-md text-primary font-semibold group-hover:text-secondary transition-colors">
                                        {{ $ws->name }}
                                    </p>
                                    <p class="font-label-md text-[10px] text-outline">
                                        {{ $ws->workspace_code }}
                                        @if ($ws->primaryTalent)
                                            &middot; {{ $ws->primaryTalent->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($wsSubmitted > 0)
                                    <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          style="background:rgba(245,158,11,0.1);color:#F59E0B;">
                                        {{ $wsSubmitted }} pending
                                    </span>
                                @endif
                                @if ($wsBlocked > 0)
                                    <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          style="background:rgba(239,68,68,0.1);color:#EF4444;">
                                        {{ $wsBlocked }} blocked
                                    </span>
                                @endif
                                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                      style="color:{{ $statusColor }};background:{{ $statusColor }}18;">
                                    {{ ucfirst($ws->status) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Action queue sidebar --}}
    <div class="lg:col-span-4 space-y-4">

        {{-- Manager profile --}}
        @if ($managerProfile)
        <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:rgba(0,88,190,0.06)">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">manage_accounts</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface font-semibold">Manager Profile</p>
                    @if ($managerProfile->manager_code)
                        <p class="font-mono-sm text-mono-sm text-outline">{{ $managerProfile->manager_code }}</p>
                    @endif
                </div>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-outline font-label-md text-label-md">Capacity</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ $managerProfile->current_load }} / {{ $managerProfile->capacity_limit }} clients
                </span>
            </div>
        </div>
        @endif

        {{-- Quick links --}}
        <div class="bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
            <h4 class="font-label-md text-label-md text-outline uppercase tracking-wider mb-4">Quick Links</h4>
            <div class="space-y-1">
                @foreach ([
                    ['label' => 'Workspaces',        'icon' => 'workspaces',       'route' => route('workspace.index')],
                    ['label' => 'Notifications',      'icon' => 'notifications',    'route' => route('notifications.index')],
                    ['label' => 'My Profile',         'icon' => 'manage_accounts',  'route' => route('profile.show')],
                ] as $link)
                <a href="{{ $link['route'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-on-surface-variant
                          hover:text-secondary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined" style="font-size:18px;">{{ $link['icon'] }}</span>
                    <span class="font-label-md text-label-md">{{ $link['label'] }}</span>
                    <span class="material-symbols-outlined ml-auto" style="font-size:14px;color:#CBD5E1;">chevron_right</span>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

</x-layouts.gvos>
