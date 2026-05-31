<x-layouts.gvos title="Ops Console">
{{-- Stitch reference: admin_overview_gvos/code.html --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $companyCount      = \App\Models\Company::count();
    $talentCount       = \App\Models\TalentProfile::count();
    $managerCount      = \App\Models\ManagerProfile::count();
    $clientCount       = \App\Models\ClientProfile::count();
    $workspaceCount    = \App\Models\Workspace::count();
    $workspaceActive   = \App\Models\Workspace::where('status', 'active')->count();

    $leadTotal          = \App\Models\LeadRequest::count();
    $leadNew            = \App\Models\LeadRequest::where('status', 'new')->count();
    $leadUnderReview    = \App\Models\LeadRequest::where('status', 'under_review')->count();
    $leadTrialApproved  = \App\Models\LeadRequest::where('status', 'trial_approved')->count();
    $leadTrialActive    = \App\Models\LeadRequest::where('status', 'trial_active')->count();
    $leadPaymentPending = \App\Models\LeadRequest::where('status', 'payment_pending')->count();

    $taskTotal     = \App\Models\WorkspaceTask::count();
    $taskOpen      = \App\Models\WorkspaceTask::whereIn('status', ['pending', 'in_progress', 'revision_requested'])->count();
    $taskBlocked   = \App\Models\WorkspaceTask::where('status', 'blocked')->count();
    $taskSubmitted = \App\Models\WorkspaceTask::where('status', 'submitted')->count();

    $messageTotal = \App\Models\WorkspaceMessage::count();
    $fileTotal    = \App\Models\WorkspaceFile::count();

    $timeLogTotal   = \App\Models\WorkspaceTimeLog::count();
    $timePending    = \App\Models\WorkspaceTimeLog::where('status', 'submitted')->count();
    $reportTotal    = \App\Models\WorkspaceWeeklyReport::count();
    $reportPublished = \App\Models\WorkspaceWeeklyReport::where('status', 'published')->count();

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">
            Super Admin Overview
        </h2>
        <p class="font-body-md text-body-md text-outline mt-1">
            Real-time platform operational metrics and health status.
        </p>
    </div>
    <div class="flex gap-3">
        <a href="/admin" class="flex items-center gap-2 px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-label-md shadow-sm hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px;">admin_panel_settings</span>
            Ops Console
        </a>
    </div>
</div>

{{-- ── Bento metric grid (5 columns) ───────────────────────────────────── --}}
{{-- Stitch: 5-col grid, each card with icon, label, value --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">

    {{-- Companies --}}
    <a href="/admin/companies"
       class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-[0px_4px_20px_rgba(0,0,0,0.04)] hover:border-secondary/30 hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:20px;">corporate_fare</span>
            </div>
        </div>
        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Companies</p>
        <h3 class="font-headline-md text-headline-md text-primary font-bold">{{ $companyCount }}</h3>
    </a>

    {{-- Talents --}}
    <a href="/admin/talent-profiles"
       class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-[0px_4px_20px_rgba(0,0,0,0.04)] hover:border-secondary/30 hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(16,185,129,0.08)">
                <span class="material-symbols-outlined text-status-active" style="font-size:20px;">badge</span>
            </div>
        </div>
        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Talents</p>
        <h3 class="font-headline-md text-headline-md text-primary font-bold">{{ $talentCount }}</h3>
    </a>

    {{-- Managers --}}
    <a href="/admin/manager-profiles"
       class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-[0px_4px_20px_rgba(0,0,0,0.04)] hover:border-secondary/30 hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(139,92,246,0.08)">
                <span class="material-symbols-outlined text-status-trial" style="font-size:20px;">supervisor_account</span>
            </div>
        </div>
        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Managers</p>
        <h3 class="font-headline-md text-headline-md text-primary font-bold">{{ $managerCount }}</h3>
    </a>

    {{-- Clients --}}
    <a href="/admin/client-profiles"
       class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-[0px_4px_20px_rgba(0,0,0,0.04)] hover:border-secondary/30 hover:shadow-md transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(245,158,11,0.08)">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:20px;">person</span>
            </div>
        </div>
        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Clients</p>
        <h3 class="font-headline-md text-headline-md text-primary font-bold">{{ $clientCount }}</h3>
    </a>

    {{-- Workspaces — dark card (Stitch: last card is dark/primary) --}}
    <a href="/admin/workspaces"
       class="p-card-padding rounded-xl border shadow-lg flex flex-col justify-between hover:brightness-110 transition-all"
       style="background-color:#131b2e;border-color:#131b2e;">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-white/10 rounded-lg">
                <span class="material-symbols-outlined text-white" style="font-size:20px;">grid_view</span>
            </div>
            <span class="font-label-md text-label-md text-secondary-fixed">
                {{ $workspaceActive }} active
            </span>
        </div>
        <p class="font-label-md text-label-md text-on-primary-container uppercase tracking-wider mb-1">Workspaces</p>
        <h3 class="font-headline-md text-headline-md font-bold text-white">{{ $workspaceCount }}</h3>
    </a>

</div>

{{-- ── Second row: Lead pipeline + KPIs ───────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Lead Pipeline (spans 2 cols) --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-border-subtle p-card-padding shadow-[0px_4px_20px_rgba(0,0,0,0.04)]">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-headline-md text-headline-md text-primary font-bold">Lead Pipeline</h4>
            <a href="/admin/lead-requests" class="text-secondary font-label-md text-label-md hover:underline">View all</a>
        </div>

        {{-- Pipeline stages as horizontal bars --}}
        <div class="space-y-3">
            @php
                $stages = [
                    ['label' => 'New Requests',     'count' => $leadNew,            'color' => '#0058be', 'bg' => 'rgba(0,88,190,0.1)'],
                    ['label' => 'Under Review',     'count' => $leadUnderReview,    'color' => '#8B5CF6', 'bg' => 'rgba(139,92,246,0.1)'],
                    ['label' => 'Trial Approved',   'count' => $leadTrialApproved,  'color' => '#10B981', 'bg' => 'rgba(16,185,129,0.1)'],
                    ['label' => 'Trial Active',     'count' => $leadTrialActive,    'color' => '#059669', 'bg' => 'rgba(5,150,105,0.1)'],
                    ['label' => 'Payment Pending',  'count' => $leadPaymentPending, 'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.1)'],
                ];
                $maxCount = max(array_column($stages, 'count')) ?: 1;
            @endphp
            @foreach ($stages as $stage)
                <div class="flex items-center gap-4">
                    <p class="font-label-md text-label-md text-outline w-32 flex-shrink-0">{{ $stage['label'] }}</p>
                    <div class="flex-1 h-2 rounded-full overflow-hidden" style="background:rgba(0,0,0,0.05)">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ $maxCount > 0 ? round($stage['count'] / $maxCount * 100) : 0 }}%;background-color:{{ $stage['color'] }}"></div>
                    </div>
                    <span class="font-headline-md text-sm font-bold w-8 text-right" style="color:{{ $stage['color'] }}">
                        {{ $stage['count'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-border-subtle flex items-center justify-between">
            <p class="font-label-md text-label-md text-outline">Total leads in pipeline</p>
            <span class="font-headline-md text-headline-md text-primary font-bold">{{ $leadTotal }}</span>
        </div>
    </div>

    {{-- Sidebar KPIs --}}
    <div class="space-y-4">

        {{-- Trial leads --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(139,92,246,0.08)">
                <span class="material-symbols-outlined text-status-trial" style="font-size:20px;">rocket_launch</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Active Trials</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $leadTrialActive }}</h4>
            </div>
            <a href="/admin/trials" class="text-secondary font-label-md text-label-md hover:underline flex-shrink-0">View</a>
        </div>

        {{-- Payment pending --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(245,158,11,0.08)">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:20px;">hourglass_empty</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Payment Pending</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $leadPaymentPending }}</h4>
            </div>
            <a href="/admin/lead-requests" class="text-secondary font-label-md text-label-md hover:underline flex-shrink-0">Manage</a>
        </div>

        {{-- Tasks submitted for review --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:20px;">pending_actions</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Tasks Awaiting Review</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $taskSubmitted }}</h4>
            </div>
            <a href="/admin/workspace-tasks" class="text-secondary font-label-md text-label-md hover:underline flex-shrink-0">View</a>
        </div>

        {{-- Time logs pending review --}}
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(16,185,129,0.06)">
                <span class="material-symbols-outlined text-status-active" style="font-size:20px;">schedule</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Time Logs Pending</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $timePending }}</h4>
            </div>
            <a href="/admin/workspace-time-logs" class="text-secondary font-label-md text-label-md hover:underline flex-shrink-0">View</a>
        </div>

    </div>
</div>

{{-- ── Third row: Tasks + Communications ──────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:18px;">task_alt</span>
            </div>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Total Tasks</p>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $taskTotal }}</p>
        <p class="font-body-sm text-body-sm text-outline mt-1">{{ $taskOpen }} open &middot; {{ $taskBlocked }} blocked</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(239,68,68,0.06)">
                <span class="material-symbols-outlined text-status-blocked" style="font-size:18px;">block</span>
            </div>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Blocked Tasks</p>
        </div>
        <p class="font-headline-lg text-headline-lg text-status-blocked font-bold">{{ $taskBlocked }}</p>
        <p class="font-body-sm text-body-sm text-outline mt-1">Requires attention</p>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:18px;">forum</span>
            </div>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Messages</p>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $messageTotal }}</p>
        <a href="/admin/workspace-messages" class="font-label-md text-label-md text-secondary hover:underline mt-1 inline-block">
            View all messages
        </a>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:18px;">folder_open</span>
            </div>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider">Files</p>
        </div>
        <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $fileTotal }}</p>
        <a href="/admin/workspace-files" class="font-label-md text-label-md text-secondary hover:underline mt-1 inline-block">
            View all files
        </a>
    </div>
</div>

{{-- ── Quick nav to Ops Console ─────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="font-headline-md text-headline-md text-primary font-bold">Administration</h4>
            <p class="font-body-sm text-body-sm text-outline mt-1">Manage all platform resources via the GVOS Ops Console.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="/admin" class="inline-flex items-center gap-1.5 px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:16px;">admin_panel_settings</span>
                Open Console
            </a>
            <a href="{{ route('workspace.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-border-subtle text-primary rounded-lg font-label-md text-label-md hover:bg-surface-container-low transition-all">
                <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                Workspaces
            </a>
        </div>
    </div>
</div>

</x-layouts.gvos>
