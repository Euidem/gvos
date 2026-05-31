<x-layouts.gvos title="My Dashboard">
{{-- Stitch reference: client_dashboard_gvos/code.html (staff variant) --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;

    $clientWorkspaceIds = \App\Models\WorkspaceMember::where('user_id', $user->id)
        ->where('status', 'active')->pluck('workspace_id');

    $myWorkspaces         = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'active'])->count();
    $clientOpenTasks      = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
    $clientSubmittedTasks = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'submitted')->count();
    $publishedReports     = \App\Models\WorkspaceWeeklyReport::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'published')->count();

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary">Welcome back, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Your workspace access and current task status.
        </p>
    </div>
</section>

{{-- ── Metric cards ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-8">

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="p-1.5 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:16px;">workspaces</span>
            </div>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $myWorkspaces }}</p>
        <p class="font-label-md text-label-md text-outline mt-1">Workspaces</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="p-1.5 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:16px;">task_alt</span>
            </div>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $clientOpenTasks }}</p>
        <p class="font-label-md text-label-md text-outline mt-1">Open Tasks</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="p-1.5 rounded-lg" style="background:rgba(245,158,11,0.08)">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:16px;">pending_actions</span>
            </div>
        </div>
        <p class="font-headline-lg text-headline-lg {{ $clientSubmittedTasks > 0 ? 'text-status-payment-due' : 'text-primary' }} font-bold">
            {{ $clientSubmittedTasks }}
        </p>
        <p class="font-label-md text-label-md text-outline mt-1">For Approval</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="p-1.5 rounded-lg" style="background:rgba(5,150,105,0.08)">
                <span class="material-symbols-outlined text-status-completed" style="font-size:16px;">summarize</span>
            </div>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $publishedReports }}</p>
        <p class="font-label-md text-label-md text-outline mt-1">Reports</p>
    </div>

</div>

{{-- ── Workspace list ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-6">
    <div class="px-card-padding py-4 border-b border-border-subtle flex items-center justify-between">
        <h3 class="font-headline-md text-headline-md text-primary font-bold">Your Workspaces</h3>
        <a href="{{ route('workspace.index') }}"
           class="text-secondary font-label-md text-label-md hover:underline">View all</a>
    </div>

    @if ($myWorkspaces === 0)
        <div class="p-8 text-center">
            <p class="font-body-sm text-body-sm text-outline">No active workspaces assigned.</p>
            <p class="font-label-md text-label-md text-on-surface-variant mt-1">
                Contact your business admin to get access.
            </p>
        </div>
    @else
        @php
            $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
                ->whereIn('status', ['pending', 'active'])
                ->with(['primaryTalent'])
                ->limit(5)
                ->get();
        @endphp
        <div class="divide-y divide-border-subtle">
            @foreach ($workspaceList as $ws)
                @php
                    $statusColor = match($ws->status) { 'active' => '#10B981', 'pending' => '#F59E0B', default => '#94A3B8' };
                @endphp
                <a href="{{ route('workspace.show', $ws) }}"
                   class="flex items-center justify-between px-card-padding py-4 hover:bg-surface-container-low transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background-color:#0058be;">
                            {{ strtoupper(substr($ws->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">
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
                    <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                          style="color:{{ $statusColor }};background:{{ $statusColor }}18;">
                        {{ ucfirst($ws->status) }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Quick links ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    @foreach ([
        ['label' => 'Workspace',  'icon' => 'workspaces',  'color' => '#0058be', 'route' => route('workspace.index')],
        ['label' => 'Chat',       'icon' => 'forum',        'color' => '#8B5CF6', 'route' => route('workspace.index')],
        ['label' => 'Files',      'icon' => 'folder_open',  'color' => '#10B981', 'route' => route('workspace.index')],
        ['label' => 'Profile',    'icon' => 'person',       'color' => '#F59E0B', 'route' => route('profile.show')],
    ] as $card)
    <a href="{{ $card['route'] }}"
       class="bg-white p-4 rounded-xl border border-border-subtle shadow-sm hover:border-secondary/30 hover:shadow-md transition-all group">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-2"
             style="background:{{ $card['color'] }}12;">
            <span class="material-symbols-outlined" style="font-size:18px;color:{{ $card['color'] }};">{{ $card['icon'] }}</span>
        </div>
        <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">{{ $card['label'] }}</p>
    </a>
    @endforeach
</div>

</x-layouts.gvos>
