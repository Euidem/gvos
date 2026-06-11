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

    $companyName     = $company?->name ?? 'Your Company';
    $name            = $profile?->first_name ?? $user->name ?? 'there';

    // Phase 18: billing banner workspace
    $primaryWorkspace  = \App\Models\Workspace::find($clientWorkspaceIds->first());
    $__billingBannerWs = $primaryWorkspace;
    $billingWorkspace  = $primaryWorkspace; // used for billing links
    $reportLinkWs      = $primaryWorkspace; // used for report links
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
<x-portal.page-header
    :title="$companyName . ' Overview'"
    :subtitle="'Welcome back, ' . $name . '. Managing ' . $myWorkspaces . ' active ' . Str::plural('workspace', $myWorkspaces) . '.'">
    <x-slot:actions>
        <a href="{{ route('workspace.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
            <span class="material-symbols-outlined" style="font-size:18px;">workspaces</span>
            My Workspaces
        </a>
    </x-slot:actions>
</x-portal.page-header>

{{-- ── Dark account card + metric cards ─────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">

    {{-- Dark account / plan card --}}
    <div class="md:col-span-4 p-card-padding rounded-xl flex flex-col justify-between shadow-lg relative overflow-hidden"
         style="background-color:#131b2e;min-height:220px;">
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
                <p class="font-body-sm text-body-sm text-on-primary-container">Welcome, {{ $name }}</p>
            @endif
        </div>
        <div class="mt-6 pt-4 border-t relative z-10" style="border-color:rgba(255,255,255,0.1);">
            <div class="flex justify-between items-center mb-2">
                <p class="font-label-md text-label-md text-on-primary-container">Active workspaces</p>
                <span class="font-bold text-white">{{ $myWorkspaces }}</span>
            </div>
            @if ($outstandingBalance > 0)
                <div class="flex justify-between items-center">
                    <p class="font-label-md text-label-md" style="color:rgba(245,158,11,0.85);">Outstanding</p>
                    <span class="font-bold" style="color:#F59E0B;">{{ number_format((float)$outstandingBalance, 2) }}</span>
                </div>
            @else
                <div class="flex justify-between items-center">
                    <p class="font-label-md text-label-md text-on-primary-container">Billing</p>
                    <span class="font-semibold" style="color:#10B981;">Up to date</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Metric cards (2 × 2 grid) --}}
    <div class="md:col-span-8 grid grid-cols-2 gap-5">

        <x-portal.stat-card
            label="Open Tasks"
            :value="$clientOpenTasks"
            icon="task_alt"
            accent="secondary"
            hint="Across all workspaces" />

        <x-portal.stat-card
            label="Awaiting Approval"
            :value="$clientSubmittedTasks"
            icon="pending_actions"
            accent="status-payment-due"
            :value-class="$clientSubmittedTasks > 0 ? 'text-status-payment-due' : 'text-primary'"
            hint="Submitted tasks" />

        @if ($reportLinkWs && $publishedReports > 0)
            <x-portal.stat-card
                label="Published Reports"
                :value="$publishedReports"
                icon="summarize"
                accent="status-active"
                :href="route('workspace.reports.index', $reportLinkWs)"
                hint="View reports →"
                hint-class="text-status-active" />
        @else
            <x-portal.stat-card
                label="Published Reports"
                :value="$publishedReports"
                icon="summarize"
                accent="status-active"
                :hint="$publishedReports > 0 ? 'Ready to view' : 'Published when ready'" />
        @endif

        <x-portal.stat-card
            label="Workspaces"
            :value="$myWorkspaces"
            icon="workspaces"
            accent="secondary"
            :href="route('workspace.index')"
            hint="Active &amp; pending" />

    </div>
</div>

{{-- ── Workspace portfolio ──────────────────────────────────────────────── --}}
<x-portal.section-card title="Workspace Portfolio" class="mb-6">
    <x-slot:actions>
        @if ($myWorkspaces > 0)
            <a href="{{ route('workspace.index') }}"
               class="text-secondary font-label-md text-label-md hover:underline flex items-center gap-1">
                View all
                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            </a>
        @endif
    </x-slot:actions>

    @if ($myWorkspaces === 0)
        <x-portal.empty-state
            icon="workspaces"
            title="No workspaces yet"
            message="You have not been added to a workspace yet. Your GVOS team will set up your workspace and notify you when it is ready." />
    @else
        @php
            $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
                ->whereIn('status', ['pending', 'active'])
                ->with(['primaryTalent', 'primaryManager'])
                ->limit(4)
                ->get();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($workspaceList as $ws)
                @php
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
                            <div class="flex-1 min-w-0">
                                <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors truncate">
                                    {{ $ws->name }}
                                </p>
                                <p class="font-label-md text-[10px] text-outline">{{ $ws->workspace_code }}</p>
                            </div>
                            <x-portal.status-badge :status="$ws->status" />
                        </div>
                        <div class="space-y-1 text-xs">
                            @if ($ws->primaryTalent)
                                <div class="flex justify-between">
                                    <span class="text-outline">Talent</span>
                                    <span class="font-semibold text-on-surface">{{ Str::limit($ws->primaryTalent->name, 18) }}</span>
                                </div>
                            @endif
                            @if ($wsSubmitted > 0)
                                <div class="flex justify-between">
                                    <span class="text-outline">Awaiting approval</span>
                                    <span class="font-semibold text-status-payment-due">{{ $wsSubmitted }}</span>
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
                        <a href="{{ route('workspace.billing.index', $ws) }}"
                           class="flex-1 flex items-center justify-center gap-1 py-2.5 text-xs font-semibold text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size:13px;">receipt_long</span>
                            Billing
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

{{-- ── Billing health + quick actions ──────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Billing health --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-card p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:{{ $outstandingBalance > 0 ? 'rgba(245,158,11,0.08)' : 'rgba(16,185,129,0.08)' }}">
                <span class="material-symbols-outlined" style="font-size:20px;color:{{ $outstandingBalance > 0 ? '#F59E0B' : '#10B981' }};">
                    {{ $outstandingBalance > 0 ? 'warning' : 'verified' }}
                </span>
            </div>
            <h3 class="font-body-md font-semibold text-on-surface">Billing Health</h3>
        </div>
        @if ($outstandingBalance > 0)
            <p class="font-label-md text-label-md font-semibold mb-2" style="color:#92400E;">
                Outstanding balance: {{ number_format((float)$outstandingBalance, 2) }}
            </p>
            <p class="font-body-sm text-on-surface-variant mb-4">
                Please review and settle your outstanding invoice to keep your workspaces active.
            </p>
            @if ($billingWorkspace)
                <a href="{{ route('workspace.billing.index', $billingWorkspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size:15px;">receipt_long</span>
                    View Billing
                </a>
            @endif
        @else
            <p class="font-body-sm text-on-surface-variant mb-4">
                Your account is in good standing. No outstanding invoices.
            </p>
            @if ($billingWorkspace)
                <a href="{{ route('workspace.billing.index', $billingWorkspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:border-secondary/30 hover:text-secondary transition-all">
                    <span class="material-symbols-outlined" style="font-size:15px;">receipt_long</span>
                    View Billing
                </a>
            @endif
        @endif
    </div>

    {{-- Quick actions --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-card p-card-padding">
        <h3 class="font-body-md font-semibold text-on-surface mb-4">Quick Actions</h3>
        <div class="space-y-2">
            @foreach ([
                ['label' => 'Workspace Messages', 'icon' => 'forum',         'route' => route('workspace.index')],
                ['label' => 'Shared Files',        'icon' => 'folder_open',   'route' => route('workspace.index')],
                ['label' => 'Progress Reports',    'icon' => 'summarize',     'route' => route('workspace.index')],
                ['label' => 'Notifications',       'icon' => 'notifications', 'route' => route('notifications.index')],
            ] as $action)
                <a href="{{ $action['route'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-transparent hover:bg-surface-container-low hover:border-border-subtle transition-all group">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">{{ $action['icon'] }}</span>
                    <span class="font-body-sm font-semibold text-on-surface group-hover:text-secondary transition-colors flex-1">{{ $action['label'] }}</span>
                    <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors" style="font-size:16px;">chevron_right</span>
                </a>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.gvos>
