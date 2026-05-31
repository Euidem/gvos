<x-layouts.gvos title="Welcome to GVOS">
{{-- Stitch reference: lead_dashboard_gvos_1/code.html --}}
@php
    $user    = auth()->user();
    $profile = $user->profile;

    $trial       = \App\Models\Trial::where('active_lead_user_id', $user->id)->latest()->first();
    $leadRequest = $trial?->leadRequest
        ?? \App\Models\LeadRequest::where('email', $user->email)->latest()->first();
    $estimate    = $trial?->priceEstimate ?? $leadRequest?->latestAcceptedEstimate();

    $workspace = $trial ? \App\Models\Workspace::where('trial_id', $trial->id)->first() : null;

    $hoursRemaining = $trial?->hoursRemaining() ?? 0;
    $trialStatus    = $trial?->status ?? null;
    $statusLabels   = [
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'active'    => 'Active',
        'completed' => 'Completed',
        'expired'   => 'Expired',
        'cancelled' => 'Cancelled',
        'converted' => 'Converted',
    ];
    $trialLabel = $trialStatus ? ($statusLabels[$trialStatus] ?? ucfirst($trialStatus)) : null;

    $name = $profile?->first_name ?? $user->name ?? 'there';

    // Countdown display
    $hoursLeft   = intdiv($hoursRemaining, 1);
    $minsLeft    = (int)(($hoursRemaining - $hoursLeft) * 60);
    $countdownStr = $hoursLeft > 0
        ? "{$hoursLeft}h {$minsLeft}m remaining"
        : ($hoursRemaining > 0 ? "{$minsLeft}m remaining" : 'Ended');
@endphp

{{-- ── Page header + Trial countdown ─────────────────────────────────── --}}
{{-- Stitch: 3-col grid — header (2 cols) + countdown card (1 col) --}}
<section class="mb-8 grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
    <div class="lg:col-span-2">
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">
            Welcome back, {{ $name }}.
        </h2>
        <p class="font-body-lg text-body-lg text-on-surface-variant">
            @if ($trial && $trialStatus === 'active')
                Your trial workspace is active.
                @if ($hoursRemaining > 0)
                    {{ $countdownStr }} of your trial.
                @endif
            @elseif ($trial && $trialStatus === 'completed')
                Your trial has completed. Contact us to discuss next steps.
            @elseif ($trial && $trialStatus === 'converted')
                Welcome aboard! Your trial has been converted to a full workspace.
            @elseif ($leadRequest)
                Your service request is being reviewed by our team.
            @else
                Thank you for your interest in GVOS.
            @endif
        </p>
    </div>

    {{-- Trial countdown card (Stitch: dark bg-primary-container with timer) --}}
    @if ($trial && $trialStatus === 'active' && $hoursRemaining > 0)
    <div class="p-card-padding rounded-xl border text-white shadow-lg relative overflow-hidden group"
         style="background-color:#131b2e;border-color:#131b2e;">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20 blur-2xl"
             style="background:#0058be;"></div>
        <div class="flex items-center justify-between mb-2 relative z-10">
            <span class="font-label-md text-label-md uppercase tracking-widest" style="color:#d8e2ff;">
                Trial Countdown
            </span>
            <span class="material-symbols-outlined" style="color:#d8e2ff;font-size:18px;">timer</span>
        </div>
        <div class="relative z-10">
            <p class="font-bold text-3xl text-secondary-fixed-dim tracking-tight">
                {{ $countdownStr }}
            </p>
        </div>
        <div class="mt-4 w-full h-1.5 rounded-full relative z-10" style="background:rgba(255,255,255,0.1)">
            @php $pct = min(100, max(0, 100 - ($hoursRemaining / max(1, 72) * 100))); @endphp
            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#d8e2ff;"></div>
        </div>
    </div>
    @elseif ($trial && in_array($trialStatus, ['completed', 'expired']))
    <div class="p-card-padding rounded-xl border shadow-sm"
         style="background:rgba(245,158,11,0.05);border-color:rgba(245,158,11,0.3);">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-status-payment-due" style="font-size:20px;">hourglass_empty</span>
            <p class="font-label-md text-label-md font-semibold text-on-surface">Trial {{ ucfirst($trialStatus) }}</p>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
            Contact the GVOS team to discuss continuing your engagement.
        </p>
    </div>
    @endif
</section>

{{-- ── Bento grid content ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">

    {{-- Service request summary (4 cols) --}}
    @if ($leadRequest)
    <div class="md:col-span-4 bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <span class="font-label-md text-[10px] px-2 py-0.5 rounded-full font-bold uppercase"
                  style="background:rgba(139,92,246,0.1);color:#8B5CF6;">
                Service Request
            </span>
            <span class="material-symbols-outlined text-outline" style="font-size:18px;">verified</span>
        </div>
        <div class="space-y-3">
            @if ($leadRequest->service_type)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Service Type</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ Str::limit($leadRequest->service_type, 18) }}
                </span>
            </div>
            @endif
            @if ($leadRequest->company_name)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Company</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ Str::limit($leadRequest->company_name, 18) }}
                </span>
            </div>
            @endif
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Status</span>
                @php
                    $reqStatusColor = match($leadRequest->status) {
                        'new'              => '#0058be',
                        'under_review'     => '#8B5CF6',
                        'trial_approved'   => '#10B981',
                        'trial_active'     => '#059669',
                        'payment_pending'  => '#F59E0B',
                        'converted'        => '#059669',
                        default            => '#76777d',
                    };
                @endphp
                <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                      style="color:{{ $reqStatusColor }};background:{{ $reqStatusColor }}18;">
                    {{ ucwords(str_replace('_', ' ', $leadRequest->status)) }}
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Trial / team info (spans remaining cols) --}}
    @if ($trial)
    <div class="md:col-span-{{ $leadRequest ? '5' : '8' }} bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Trial Details</h3>
            @if ($trialLabel)
            <span class="font-label-md text-[10px] font-bold px-2 py-0.5 rounded-full"
                  style="background:rgba(0,88,190,0.08);color:#0058be;">
                {{ $trialLabel }}
            </span>
            @endif
        </div>
        <div class="space-y-3">
            @if ($trial->assignedTalent)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Assigned Talent</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-status-active rounded-full"></span>
                    {{ $trial->assignedTalent->name }}
                </span>
            </div>
            @endif
            @if ($trial->assignedManager)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Your Manager</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ $trial->assignedManager->name }}
                </span>
            </div>
            @endif
            @if ($estimate)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Price Estimate</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ $estimate->currency ?? 'USD' }} {{ number_format($estimate->estimated_amount ?? 0, 2) }}/mo
                </span>
            </div>
            @endif
            @if ($trial->started_at)
            <div class="flex justify-between items-center">
                <span class="font-label-md text-label-md text-outline">Trial Started</span>
                <span class="font-label-md text-label-md font-semibold text-on-surface">
                    {{ $trial->started_at->format('d M Y') }}
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Workspace access card (spans 3 cols) --}}
    @if ($workspace)
    <div class="md:col-span-3 bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold"
                 style="background-color:#0058be;">
                {{ strtoupper(substr($workspace->name, 0, 2)) }}
            </div>
            <div>
                <p class="font-label-md text-label-md font-semibold text-on-surface">{{ Str::limit($workspace->name, 20) }}</p>
                <p class="font-label-md text-[10px] text-outline">{{ $workspace->workspace_code }}</p>
            </div>
        </div>
        <a href="{{ route('workspace.show', $workspace) }}"
           class="mt-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-secondary text-white rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
            Open Workspace
        </a>
    </div>
    @endif

</div>

{{-- ── Next steps cards ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    @if ($workspace)
    <a href="{{ route('workspace.show', $workspace) }}"
       class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm hover:border-secondary/30 hover:shadow-md transition-all group">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-3"
             style="background:rgba(0,88,190,0.06);">
            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">workspaces</span>
        </div>
        <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">
            My Trial Workspace
        </p>
        <p class="font-label-md text-[10px] text-outline mt-0.5">View tasks and activity</p>
    </a>
    @endif

    <a href="{{ route('profile.show') }}"
       class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm hover:border-secondary/30 hover:shadow-md transition-all group">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-3"
             style="background:rgba(16,185,129,0.06);">
            <span class="material-symbols-outlined text-status-active" style="font-size:18px;">person</span>
        </div>
        <p class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-secondary transition-colors">
            Complete Profile
        </p>
        <p class="font-label-md text-[10px] text-outline mt-0.5">Update your details</p>
    </a>

    <div class="bg-white p-5 rounded-xl border border-border-subtle shadow-sm">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-3"
             style="background:rgba(139,92,246,0.06);">
            <span class="material-symbols-outlined text-status-trial" style="font-size:18px;">support_agent</span>
        </div>
        <p class="font-label-md text-label-md font-semibold text-on-surface">GVOS Support</p>
        <p class="font-label-md text-[10px] text-outline mt-0.5">Contact your dedicated team</p>
    </div>

</div>

</x-layouts.gvos>
