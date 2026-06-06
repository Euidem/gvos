<x-layouts.gvos title="Business Account">
{{-- Stitch reference: business_admin_dashboard_gvos/code.html --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
    $company = $clientProfile?->company;

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

    // Phase 8 billing
    $outstandingBalance = \App\Models\Invoice::whereIn('workspace_id', $clientWorkspaceIds)
        ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
        ->sum('balance_due');
    $billingWorkspaceId = $clientWorkspaceIds->first();

    $companyName = $company?->name ?? 'Your Company';
    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<section class="flex flex-col md:flex-row justify-between items-start gap-6 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ $companyName }} Overview</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Welcome back, {{ $name }}. Managing {{ $myWorkspaces }} active {{ Str::plural('workspace', $myWorkspaces) }}.
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('workspace.index') }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
            <span class="material-symbols-outlined" style="font-size:18px;">workspaces</span>
            My Workspaces
        </a>
    </div>
</section>

{{-- ── Bento grid — account card + metrics ────────────────────────────── --}}
{{-- Stitch: dark account card left + metric cards right --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">

    {{-- Account/plan card (dark, spans 4 cols) --}}
    <div class="md:col-span-4 p-card-padding rounded-xl flex flex-col justify-between shadow-lg relative overflow-hidden"
         style="background-color:#131b2e;min-height:200px;">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20 blur-2xl"
             style="background:#0058be;"></div>
        <div class="relative z-10">
            @if ($company)
                <span class="inline-block px-3 py-1 rounded-full font-label-md text-[10px] mb-4"
                      style="background:#2170e4;color:#fefcff;">
                    BUSINESS ACCOUNT
                </span>
                <h3 class="font-headline-md text-headline-md text-white mb-1">{{ $company->name }}</h3>
                @if ($company->domain)
                    <p class="font-label-md text-label-md text-on-primary-container">{{ $company->domain }}</p>
                @endif
            @else
                <span class="inline-block px-3 py-1 rounded-full font-label-md text-[10px] mb-4"
                      style="background:#2170e4;color:#fefcff;">
                    CLIENT ACCOUNT
                </span>
                <h3 class="font-headline-md text-headline-md text-white mb-2">Business Portal</h3>
                <p class="font-body-sm text-body-sm text-on-primary-container">
                    Welcome, {{ $name }}
                </p>
            @endif
        </div>
        <div class="mt-6 pt-4 border-t relative z-10" style="border-color:rgba(255,255,255,0.1);">
            <div class="flex justify-between items-center">
                <p class="font-label-md text-label-md text-on-primary-container">Active workspaces</p>
                <span class="font-bold text-white">{{ $myWorkspaces }}</span>
            </div>
        </div>
    </div>

    {{-- Metric cards (span 8 cols, 2x2 grid) --}}
    <div class="md:col-span-8 grid grid-cols-2 gap-5">

        <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-lg" style="background:rgba(0,88,190,0.06)">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">task_alt</span>
                </div>
                <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Open Tasks</p>
            </div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $clientOpenTasks }}</p>
            <p class="font-body-sm text-body-sm text-outline mt-1">Across all workspaces</p>
        </div>

        <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-lg" style="background:rgba(245,158,11,0.08)">
                    <span class="material-symbols-outlined text-status-payment-due" style="font-size:18px;">pending_actions</span>
                </div>
                <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Awaiting Approval</p>
            </div>
            <p class="font-headline-lg text-headline-lg {{ $clientSubmittedTasks > 0 ? 'text-status-payment-due' : 'text-primary' }} font-bold">
                {{ $clientSubmittedTasks }}
            </p>
            <p class="font-body-sm text-body-sm text-outline mt-1">Submitted tasks</p>
        </div>

        <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-lg" style="background:rgba(5,150,105,0.08)">
                    <span class="material-symbols-outlined text-status-completed" style="font-size:18px;">summarize</span>
                </div>
                <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Published Reports</p>
            </div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $publishedReports }}</p>
            <p class="font-body-sm text-body-sm text-outline mt-1">Available to view</p>
        </div>

        <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 rounded-lg" style="background:rgba(16,185,129,0.06)">
                    <span class="material-symbols-outlined text-status-active" style="font-size:18px;">workspaces</span>
                </div>
                <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Workspaces</p>
            </div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $myWorkspaces }}</p>
            <p class="font-body-sm text-body-sm text-outline mt-1">Active &amp; pending</p>
        </div>

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
            <p class="font-body-sm text-body-sm text-outline">No active workspaces yet.</p>
        </div>
    @else
        @php
            $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
                ->whereIn('status', ['pending', 'active'])
                ->with(['primaryTalent', 'primaryManager'])
                ->limit(4)
                ->get();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 divide-border-subtle divide-y sm:divide-y-0 sm:divide-x">
            @foreach ($workspaceList as $ws)
                @php
                    $wsSubmitted = \App\Models\WorkspaceTask::where('workspace_id', $ws->id)->where('status', 'submitted')->count();
                    $statusColor = match($ws->status) { 'active' => '#10B981', 'pending' => '#F59E0B', default => '#94A3B8' };
                @endphp
                <a href="{{ route('workspace.show', $ws) }}"
                   class="p-card-padding hover:bg-surface-container-low transition-all group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background-color:#0058be;">
                            {{ strtoupper(substr($ws->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">
                                {{ $ws->name }}
                            </p>
                            <p class="font-label-md text-[10px] text-outline">{{ $ws->workspace_code }}</p>
                        </div>
                        <span class="ml-auto font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                              style="color:{{ $statusColor }};background:{{ $statusColor }}18;">
                            {{ ucfirst($ws->status) }}
                        </span>
                    </div>
                    @if ($ws->primaryTalent)
                        <p class="font-body-sm text-body-sm text-outline">
                            Talent: <span class="text-on-surface font-medium">{{ $ws->primaryTalent->name }}</span>
                        </p>
                    @endif
                    @if ($wsSubmitted > 0)
                        <p class="font-label-md text-[10px] text-status-payment-due font-bold mt-1">
                            {{ $wsSubmitted }} task{{ $wsSubmitted > 1 ? 's' : '' }} awaiting your approval
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Quick links ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
    @php
        $billingWorkspace = $billingWorkspaceId ? \App\Models\Workspace::find($billingWorkspaceId) : null;
    @endphp
    @foreach ([
        ['label' => 'Chat',       'icon' => 'forum',        'color' => '#8B5CF6', 'route' => route('workspace.index')],
        ['label' => 'Files',      'icon' => 'folder_open',  'color' => '#0058be', 'route' => route('workspace.index')],
        ['label' => 'Reports',    'icon' => 'summarize',    'color' => '#10B981', 'route' => route('workspace.index')],
        ['label' => 'Billing',    'icon' => 'receipt_long', 'color' => $outstandingBalance > 0 ? '#F59E0B' : '#0058be',
                                  'route' => $billingWorkspace ? route('workspace.billing.index', $billingWorkspace) : route('workspace.index')],
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
