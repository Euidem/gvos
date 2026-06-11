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

    $name = $profile?->first_name ?? $user->name ?? 'there';

    // Phase 18: billing banner workspace
    $primaryWorkspace    = \App\Models\Workspace::find($clientWorkspaceIds->first());
    $__billingBannerWs   = $primaryWorkspace;
    $billingWorkspace    = $primaryWorkspace; // used for billing links
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

{{-- ── Hero panel ───────────────────────────────────────────────────────── --}}
<div class="rounded-2xl border border-border-subtle shadow-sm overflow-hidden mb-8"
     style="background:linear-gradient(135deg,rgba(0,88,190,0.03) 0%,rgba(255,255,255,0) 55%),#fff;">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-6 lg:p-8">
        <div class="min-w-0">
            <p class="font-label-md text-label-md text-secondary uppercase tracking-widest mb-2">Client Workspace</p>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Welcome back, {{ $name }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                @if ($myWorkspaces === 0)
                    Your workspace is being set up. You will be notified when it is ready.
                @elseif ($outstandingBalance > 0)
                    You have an outstanding balance. Please contact your account manager.
                @elseif ($clientSubmittedTasks > 0)
                    {{ $clientSubmittedTasks }} {{ Str::plural('task', $clientSubmittedTasks) }}
                    {{ $clientSubmittedTasks === 1 ? 'is' : 'are' }} awaiting your review.
                @else
                    Everything is up to date. No pending actions needed.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-3 flex-shrink-0">
            @if ($primaryWorkspace)
                <a href="{{ route('workspace.show', $primaryWorkspace) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                    Open Workspace
                </a>
            @endif
            @if ($primaryWorkspace && $publishedReports > 0)
                <a href="{{ route('workspace.reports.index', $primaryWorkspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">summarize</span>
                    View Reports
                </a>
            @endif
        </div>
    </div>

    {{-- Outstanding balance notice bar --}}
    @if ($outstandingBalance > 0)
        <div class="px-6 lg:px-8 pb-5">
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg"
                 style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:16px;">warning</span>
                <p class="font-label-md text-label-md text-status-payment-due font-semibold">
                    Outstanding balance: {{ number_format((float)$outstandingBalance, 2) }}
                    @if ($billingWorkspace)
                        &mdash; <a href="{{ route('workspace.billing.index', $billingWorkspace) }}" class="underline">View billing</a>
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>

{{-- ── Engagement stats ─────────────────────────────────────────────────── --}}
<section class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-portal.stat-card
        label="Active Workspaces"
        :value="$myWorkspaces"
        icon="workspaces"
        accent="secondary"
        :href="route('workspace.index')"
        :hint="$myWorkspaces === 0 ? 'Being prepared' : null" />

    <x-portal.stat-card
        label="Open Tasks"
        :value="$clientOpenTasks"
        icon="task_alt"
        accent="secondary"
        :hint="$clientOpenTasks === 0 ? 'All clear' : 'In progress'" />

    <x-portal.stat-card
        label="For Your Review"
        :value="$clientSubmittedTasks"
        icon="pending_actions"
        accent="status-payment-due"
        :value-class="$clientSubmittedTasks > 0 ? 'text-status-payment-due' : 'text-primary'"
        :hint="$clientSubmittedTasks > 0 ? 'Awaiting your approval' : 'Nothing pending'" />

    <x-portal.stat-card
        label="Progress Reports"
        :value="$publishedReports"
        icon="summarize"
        accent="status-active"
        :href="($primaryWorkspace && $publishedReports > 0) ? route('workspace.reports.index', $primaryWorkspace) : null"
        :hint="$publishedReports === 0 ? 'First report pending' : 'Ready to view'" />

</section>

{{-- ── Workspace progress cards ─────────────────────────────────────────── --}}
@php
    $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'active'])
        ->with(['primaryTalent', 'primaryManager'])
        ->limit(3)
        ->get();
@endphp

<x-portal.section-card title="Your Workspaces" class="mb-8">
    <x-slot:actions>
        @if ($myWorkspaces > 0)
            <a href="{{ route('workspace.index') }}"
               class="text-secondary font-label-md text-label-md hover:underline font-bold flex items-center gap-1">
                View All
                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            </a>
        @endif
    </x-slot:actions>

    @if ($workspaceList->isEmpty())
        <x-portal.empty-state
            icon="workspaces"
            title="No workspaces yet"
            message="You have not been added to a workspace yet. Once your workspace is ready, it will appear here." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($workspaceList as $ws)
                @php
                    $wsOpenTasks = \App\Models\WorkspaceTask::where('workspace_id', $ws->id)
                        ->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
                    $wsSubmitted = \App\Models\WorkspaceTask::where('workspace_id', $ws->id)
                        ->where('status', 'submitted')->count();
                @endphp
                <div class="border border-border-subtle rounded-xl overflow-hidden hover:border-secondary/30 hover:shadow-md transition-all group">
                    {{-- Card header --}}
                    <a href="{{ route('workspace.show', $ws) }}"
                       class="block p-4 border-b border-border-subtle hover:bg-surface-container-low transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                 style="background-color:#0058be;">
                                {{ strtoupper(substr($ws->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors truncate">
                                    {{ Str::limit($ws->name, 24) }}
                                </p>
                                <p class="font-label-md text-[10px] text-outline">{{ $ws->workspace_code }}</p>
                            </div>
                            <x-portal.status-badge :status="$ws->status" />
                        </div>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-outline">Open tasks</span>
                                <span class="font-semibold text-on-surface">{{ $wsOpenTasks }}</span>
                            </div>
                            @if ($wsSubmitted > 0)
                                <div class="flex justify-between">
                                    <span class="text-outline">Awaiting your review</span>
                                    <span class="font-semibold text-status-payment-due">{{ $wsSubmitted }}</span>
                                </div>
                            @endif
                            @if ($ws->primaryTalent)
                                <div class="flex justify-between">
                                    <span class="text-outline">Your specialist</span>
                                    <span class="font-semibold text-on-surface">{{ Str::limit($ws->primaryTalent->name, 16) }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                    {{-- Quick links bar --}}
                    <div class="flex divide-x divide-border-subtle" style="background:rgba(247,249,251,1);">
                        <a href="{{ route('workspace.reports.index', $ws) }}"
                           class="flex-1 flex items-center justify-center gap-1 py-2.5 text-xs font-semibold text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size:13px;">summarize</span>
                            Reports
                        </a>
                        <a href="{{ route('workspace.files.index', $ws) }}"
                           class="flex-1 flex items-center justify-center gap-1 py-2.5 text-xs font-semibold text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size:13px;">folder_open</span>
                            Files
                        </a>
                        <a href="{{ route('workspace.tasks.index', $ws) }}"
                           class="flex-1 flex items-center justify-center gap-1 py-2.5 text-xs font-semibold text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size:13px;">task_alt</span>
                            Tasks
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-portal.section-card>

{{-- ── Bottom row: latest report + billing & documents ──────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Latest progress report --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-card p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(5,150,105,0.08)">
                <span class="material-symbols-outlined text-status-completed" style="font-size:20px;">summarize</span>
            </div>
            <h3 class="font-body-md font-semibold text-on-surface">Latest Progress Report</h3>
        </div>
        @if ($publishedReports > 0 && $primaryWorkspace)
            <p class="font-body-sm text-on-surface-variant mb-4">
                Your team has published {{ $publishedReports }} {{ Str::plural('progress report', $publishedReports) }}.
                View the latest update to see what has been completed.
            </p>
            <a href="{{ route('workspace.reports.index', $primaryWorkspace) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:15px;">open_in_new</span>
                View Reports
            </a>
        @else
            <p class="font-body-sm text-on-surface-variant">
                Your first progress update will appear here once your GVOS team publishes a report.
            </p>
        @endif
    </div>

    {{-- Billing & documents --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-card p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:{{ $outstandingBalance > 0 ? 'rgba(245,158,11,0.08)' : 'rgba(0,88,190,0.06)' }}">
                <span class="material-symbols-outlined" style="font-size:20px;color:{{ $outstandingBalance > 0 ? '#F59E0B' : '#0058be' }};">receipt_long</span>
            </div>
            <h3 class="font-body-md font-semibold text-on-surface">Billing &amp; Documents</h3>
        </div>
        @if ($outstandingBalance > 0)
            <p class="font-label-md text-label-md font-semibold mb-1" style="color:#92400E;">
                Outstanding balance: {{ number_format((float)$outstandingBalance, 2) }}
            </p>
            <p class="font-body-sm text-on-surface-variant mb-4">
                Please review your billing and contact your account manager to settle the balance.
            </p>
        @else
            <p class="font-body-sm text-on-surface-variant mb-4">
                Your billing is up to date. No outstanding invoices.
            </p>
        @endif
        <div class="flex flex-wrap gap-3">
            @if ($billingWorkspace)
                <a href="{{ route('workspace.billing.index', $billingWorkspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:border-secondary/30 hover:text-secondary transition-all">
                    <span class="material-symbols-outlined" style="font-size:15px;">receipt_long</span>
                    View Billing
                </a>
            @endif
            @if ($primaryWorkspace)
                <a href="{{ route('workspace.files.index', $primaryWorkspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:border-secondary/30 hover:text-secondary transition-all">
                    <span class="material-symbols-outlined" style="font-size:15px;">folder_open</span>
                    View Files
                </a>
            @endif
        </div>
    </div>
</div>

</x-layouts.gvos>
