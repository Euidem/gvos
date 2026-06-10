<x-layouts.gvos title="My Dashboard">
{{-- Stitch reference: client_dashboard_gvos/code.html --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;

    $clientWorkspaceIds = \App\Models\WorkspaceMember::where('user_id', $user->id)
        ->where('status', 'active')->pluck('workspace_id');

    $myWorkspaces = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'active'])->count();

    $clientOpenTasks      = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
    $clientSubmittedTasks = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'submitted')->count();

    $publishedReports = \App\Models\WorkspaceWeeklyReport::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'published')->count();

    // Phase 8 billing
    $outstandingBalance = \App\Models\Invoice::whereIn('workspace_id', $clientWorkspaceIds)
        ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
        ->sum('balance_due');
    $billingWorkspaceId = $clientWorkspaceIds->first(); // for billing link

    $name = $profile?->first_name ?? $user->name ?? 'there';

    // Phase 18: billing banner workspace
    $reportLinkWorkspace = \App\Models\Workspace::find($clientWorkspaceIds->first());
    $__billingBannerWs   = $reportLinkWorkspace;
@endphp

{{-- Phase 16: onboarding banner --}}
@php $__obUser = $user; @endphp
@include('partials.onboarding-banner')

{{-- Phase 18: billing warning banner --}}
@if ($__billingBannerWs)
    @php
        $__billingWorkspace = $__billingBannerWs;
        $__billingForClient = true;
    @endphp
    @include('partials.billing-banner')
@endif

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary">Welcome back, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Your operations are running smoothly.
        </p>
    </div>
    <div class="flex gap-3">
        <div class="bg-white p-3 rounded-xl border border-border-subtle flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-full flex items-center justify-center"
                 style="background:rgba(16,185,129,0.1);color:#10B981;">
                <span class="material-symbols-outlined" style="font-size:18px;">bolt</span>
            </div>
            <div>
                <p class="font-label-md text-[10px] text-outline uppercase">Status</p>
                <p class="font-label-md text-label-md text-status-active">Operational</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Bento grid ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">

    {{-- Assigned team card (spans 8 cols on lg) --}}
    <div class="md:col-span-12 lg:col-span-8 bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-headline-md text-headline-md text-primary font-bold">Your Workspace Overview</h3>
            <a href="{{ route('workspace.index') }}"
               class="text-secondary font-label-md text-label-md flex items-center hover:underline">
                View <span class="material-symbols-outlined ml-1" style="font-size:16px;">chevron_right</span>
            </a>
        </div>

        @if ($myWorkspaces === 0)
            <div class="text-center py-8">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background:rgba(0,88,190,0.06)">
                    <span class="material-symbols-outlined text-secondary" style="font-size:24px;">workspaces</span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">No active workspaces yet.</p>
                <p class="font-body-sm text-body-sm text-outline mt-1">Your team will set up your workspace shortly.</p>
            </div>
        @else
            @php
                $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
                    ->whereIn('status', ['pending', 'active'])
                    ->with(['primaryTalent', 'primaryManager'])
                    ->limit(3)
                    ->get();
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-{{ min(3, $workspaceList->count()) }} gap-4">
                @foreach ($workspaceList as $ws)
                    @php
                        $wsOpenTasks = \App\Models\WorkspaceTask::where('workspace_id', $ws->id)
                            ->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
                        $wsSubmitted = \App\Models\WorkspaceTask::where('workspace_id', $ws->id)
                            ->where('status', 'submitted')->count();
                    @endphp
                    <a href="{{ route('workspace.show', $ws) }}"
                       class="p-4 rounded-xl border border-border-subtle hover:border-secondary/30 hover:shadow-md transition-all group">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background-color:#0058be;">
                                {{ strtoupper(substr($ws->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">
                                    {{ Str::limit($ws->name, 20) }}
                                </p>
                                <p class="font-label-md text-[10px] text-outline">{{ $ws->workspace_code }}</p>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-outline">Open tasks</span>
                                <span class="font-semibold text-on-surface">{{ $wsOpenTasks }}</span>
                            </div>
                            @if ($wsSubmitted > 0)
                            <div class="flex justify-between">
                                <span class="text-outline">Awaiting approval</span>
                                <span class="font-semibold text-status-payment-due">{{ $wsSubmitted }}</span>
                            </div>
                            @endif
                            @if ($ws->primaryTalent)
                            <div class="flex justify-between">
                                <span class="text-outline">Your talent</span>
                                <span class="font-semibold text-on-surface">{{ Str::limit($ws->primaryTalent->name, 14) }}</span>
                            </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Status sidebar (spans 4 cols) --}}
    <div class="md:col-span-12 lg:col-span-4 space-y-4">

        {{-- Tasks awaiting approval --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(245,158,11,0.08)">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:20px;">pending_actions</span>
            </div>
            <div class="flex-1">
                <p class="font-label-md text-label-md text-outline">Tasks Awaiting Approval</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $clientSubmittedTasks }}</h4>
            </div>
        </div>

        {{-- Published reports --}}
        @php
            $reportLinkWorkspace = $clientWorkspaceIds->first()
                ? \App\Models\Workspace::find($clientWorkspaceIds->first())
                : null;
        @endphp
        @if ($reportLinkWorkspace && $publishedReports > 0)
            <a href="{{ route('workspace.reports.index', $reportLinkWorkspace) }}"
               class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4 hover:border-secondary/30 hover:shadow-md transition-all group">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(5,150,105,0.08)">
                    <span class="material-symbols-outlined text-status-completed" style="font-size:20px;">summarize</span>
                </div>
                <div class="flex-1">
                    <p class="font-label-md text-label-md text-outline">Published Reports</p>
                    <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $publishedReports }}</h4>
                </div>
                <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors" style="font-size:16px;">chevron_right</span>
            </a>
        @else
            <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(5,150,105,0.08)">
                    <span class="material-symbols-outlined text-status-completed" style="font-size:20px;">summarize</span>
                </div>
                <div class="flex-1">
                    <p class="font-label-md text-label-md text-outline">Published Reports</p>
                    <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $publishedReports }}</h4>
                </div>
            </div>
        @endif

        {{-- Open tasks --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:20px;">task_alt</span>
            </div>
            <div class="flex-1">
                <p class="font-label-md text-label-md text-outline">Open Tasks</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $clientOpenTasks }}</h4>
            </div>
        </div>

    </div>
</div>

{{-- ── Quick access cards ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
    @php
        $billingWorkspace = $billingWorkspaceId
            ? \App\Models\Workspace::find($billingWorkspaceId)
            : null;
    @endphp
    @foreach ([
        ['label' => 'Workspace', 'sub' => 'View tasks and activity', 'icon' => 'workspaces',   'color' => '#0058be', 'route' => route('workspace.index')],
        ['label' => 'Chat',      'sub' => 'Workspace messages',      'icon' => 'forum',         'color' => '#8B5CF6', 'route' => route('workspace.index')],
        ['label' => 'Files',     'sub' => 'Shared documents',        'icon' => 'folder_open',   'color' => '#10B981', 'route' => route('workspace.index')],
        ['label' => 'Billing',   'sub' => $outstandingBalance > 0 ? 'Balance: ' . number_format((float)$outstandingBalance, 2) : 'View invoices',
                                          'icon' => 'receipt_long',   'color' => $outstandingBalance > 0 ? '#F59E0B' : '#0058be',
                                          'route' => $billingWorkspace ? route('workspace.billing.index', $billingWorkspace) : route('workspace.index')],
    ] as $card)
    <a href="{{ $card['route'] }}"
       class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm hover:border-secondary/30 hover:shadow-md transition-all group">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3"
             style="background:{{ $card['color'] }}12;">
            <span class="material-symbols-outlined" style="font-size:20px;color:{{ $card['color'] }};">{{ $card['icon'] }}</span>
        </div>
        <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">{{ $card['label'] }}</p>
        <p class="font-label-md text-[10px] text-outline mt-0.5">{{ $card['sub'] }}</p>
    </a>
    @endforeach
</div>

</x-layouts.gvos>
