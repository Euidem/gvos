<x-layouts.gvos title="Operations Dashboard">
{{-- Stitch reference: admin_overview_gvos/code.html --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $companyCount    = \App\Models\Company::count();
    $talentCount     = \App\Models\TalentProfile::count();
    $managerCount    = \App\Models\ManagerProfile::count();
    $clientCount     = \App\Models\ClientProfile::count();
    $workspaceCount  = \App\Models\Workspace::count();
    $workspaceActive = \App\Models\Workspace::where('status', 'active')->count();
    $workspaceTrial  = \App\Models\Workspace::where('type', 'trial')->whereIn('status', ['pending', 'active'])->count();

    $leadTotal          = \App\Models\LeadRequest::count();
    $leadNew            = \App\Models\LeadRequest::where('status', 'new')->count();
    $leadUnderReview    = \App\Models\LeadRequest::where('status', 'under_review')->count();
    $leadTrialActive    = \App\Models\LeadRequest::where('status', 'trial_active')->count();
    $leadPaymentPending = \App\Models\LeadRequest::where('status', 'payment_pending')->count();

    $taskTotal     = \App\Models\WorkspaceTask::count();
    $taskOpen      = \App\Models\WorkspaceTask::whereIn('status', ['pending', 'in_progress', 'revision_requested'])->count();
    $taskBlocked   = \App\Models\WorkspaceTask::where('status', 'blocked')->count();
    $taskSubmitted = \App\Models\WorkspaceTask::where('status', 'submitted')->count();

    $messageTotal = \App\Models\WorkspaceMessage::count();
    $fileTotal    = \App\Models\WorkspaceFile::count();

    $timePending        = \App\Models\WorkspaceTimeLog::where('status', 'submitted')->count();
    $reportsDraft       = \App\Models\WorkspaceWeeklyReport::where('status', 'submitted')->count();
    $invoiceOutstanding = \App\Models\Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->count();
    $invoiceOverdue     = \App\Models\Invoice::where('status', 'overdue')->count();

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">Operations Overview</h2>
        <p class="font-body-md text-body-md text-outline mt-1">
            Active workspace health, lead pipeline and task management.
        </p>
    </div>
    <div class="flex gap-3">
        <a href="/admin" class="flex items-center gap-2 px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px;">admin_panel_settings</span>
            Ops Console
        </a>
    </div>
</div>

{{-- ── Workspace health cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[140px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Active Workspaces</span>
            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">corporate_fare</span>
        </div>
        <div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $workspaceActive }}</p>
            <p class="font-label-md text-label-md text-outline">of {{ $workspaceCount }} total</p>
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[140px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Trial Workspaces</span>
            <span class="material-symbols-outlined text-status-trial" style="font-size:18px;">auto_awesome</span>
        </div>
        <div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $workspaceTrial }}</p>
            <p class="font-label-md text-label-md text-outline">{{ $leadTrialActive }} leads active</p>
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[140px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Tasks Awaiting Review</span>
            <span class="material-symbols-outlined text-status-payment-due" style="font-size:18px;">pending_actions</span>
        </div>
        <div>
            <p class="font-headline-lg text-headline-lg text-primary font-bold">{{ $taskSubmitted }}</p>
            <p class="font-label-md text-label-md text-outline">{{ $taskBlocked }} blocked</p>
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between h-[140px]">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline">Payment Pending</span>
            <span class="material-symbols-outlined text-status-blocked" style="font-size:18px;">warning</span>
        </div>
        <div>
            <p class="font-headline-lg text-headline-lg text-status-blocked font-bold">{{ $leadPaymentPending }}</p>
            <p class="font-label-md text-label-md text-outline">Requires action</p>
        </div>
    </div>

</div>

{{-- ── Lead pipeline + Actions ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Lead pipeline --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
        <div class="flex justify-between items-center mb-5">
            <h4 class="font-headline-md text-headline-md text-primary font-bold">Lead Pipeline</h4>
            <a href="/admin/lead-requests" class="text-secondary font-label-md text-label-md hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @php
                $stages = [
                    ['label' => 'New',           'count' => $leadNew,            'color' => '#0058be'],
                    ['label' => 'Under Review',  'count' => $leadUnderReview,    'color' => '#8B5CF6'],
                    ['label' => 'Trial Active',  'count' => $leadTrialActive,    'color' => '#10B981'],
                    ['label' => 'Pmt. Pending',  'count' => $leadPaymentPending, 'color' => '#F59E0B'],
                ];
                $maxCount = max(array_column($stages, 'count')) ?: 1;
            @endphp
            @foreach ($stages as $stage)
                <div class="flex items-center gap-4">
                    <p class="font-label-md text-label-md text-outline w-28 flex-shrink-0">{{ $stage['label'] }}</p>
                    <div class="flex-1 h-2 rounded-full overflow-hidden bg-surface-container-high">
                        <div class="h-full rounded-full"
                             style="width:{{ $maxCount > 0 ? round($stage['count'] / $maxCount * 100) : 0 }}%;background-color:{{ $stage['color'] }}"></div>
                    </div>
                    <span class="font-bold text-sm w-6 text-right" style="color:{{ $stage['color'] }}">{{ $stage['count'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-5 pt-4 border-t border-border-subtle flex justify-between items-center">
            <p class="font-label-md text-label-md text-outline">Total in pipeline</p>
            <span class="font-headline-md text-headline-md text-primary font-bold">{{ $leadTotal }}</span>
        </div>
    </div>

    {{-- Action items --}}
    <div class="space-y-4">
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(0,88,190,0.06)">
                <span class="material-symbols-outlined text-secondary" style="font-size:20px;">schedule</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Time Logs Pending</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $timePending }}</h4>
            </div>
            <a href="/admin/workspace-time-logs" class="text-secondary font-label-md text-label-md hover:underline">Review</a>
        </div>
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(139,92,246,0.06)">
                <span class="material-symbols-outlined text-status-trial" style="font-size:20px;">summarize</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Reports for Review</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $reportsDraft }}</h4>
            </div>
            <a href="/admin/workspace-weekly-reports" class="text-secondary font-label-md text-label-md hover:underline">Review</a>
        </div>
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(16,185,129,0.06)">
                <span class="material-symbols-outlined text-status-active" style="font-size:20px;">forum</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Messages / Files</p>
                <h4 class="font-headline-md text-headline-md text-primary font-bold">{{ $messageTotal + $fileTotal }}</h4>
            </div>
            <a href="/admin/workspace-messages" class="text-secondary font-label-md text-label-md hover:underline">View</a>
        </div>
        <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:rgba(245,158,11,0.08)">
                <span class="material-symbols-outlined text-status-payment-due" style="font-size:20px;">receipt_long</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-label-md text-label-md text-outline">Outstanding Invoices</p>
                <h4 class="font-headline-md text-headline-md {{ $invoiceOutstanding > 0 ? 'text-status-payment-due' : 'text-primary' }} font-bold">
                    {{ $invoiceOutstanding }}
                </h4>
            </div>
            <a href="/admin/invoices" class="text-secondary font-label-md text-label-md hover:underline">View</a>
        </div>
    </div>
</div>

{{-- ── Platform stats ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
    @foreach ([
        ['Companies', $companyCount, 'business', '#0058be'],
        ['Talents', $talentCount, 'badge', '#10B981'],
        ['Clients', $clientCount, 'person', '#F59E0B'],
        ['Managers', $managerCount, 'supervisor_account', '#8B5CF6'],
    ] as [$label, $count, $icon, $color])
    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined" style="color:{{ $color }};font-size:20px;">{{ $icon }}</span>
            <p class="font-label-md text-label-md text-outline uppercase tracking-wider">{{ $label }}</p>
        </div>
        <p class="font-headline-md text-headline-md text-primary font-bold">{{ $count }}</p>
    </div>
    @endforeach
</div>

</x-layouts.gvos>
