<x-layouts.gvos title="Welcome to GVOS">
@php
    $user    = auth()->user();
    $profile = $user->profile;

    // Load latest trial and lead request for this user
    $trial       = \App\Models\Trial::where('active_lead_user_id', $user->id)->latest()->first();
    $leadRequest = $trial?->leadRequest
        ?? \App\Models\LeadRequest::where('email', $user->email)->latest()->first();
    $estimate    = $trial?->priceEstimate ?? $leadRequest?->latestAcceptedEstimate();

    // Workspace
    $workspace = $trial ? \App\Models\Workspace::where('trial_id', $trial->id)->first() : null;

    // Trial countdown
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
@endphp

    {{-- ── Welcome header ──────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">Your GVOS trial dashboard</p>
        </div>
        <span class="text-xs bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20 px-3 py-1 rounded-full font-medium">
            Active Lead
        </span>
    </div>

    @if ($leadRequest)
        {{-- ── Lead request summary ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle px-6 py-5 mb-6 shadow-card">
            <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Your Service Request</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-outline font-medium mb-0.5">Role Requested</p>
                    <p class="text-on-surface font-medium">
                        {{ $leadRequest->role_needed
                            ? (\App\Models\LeadRequest::roleLabels()[$leadRequest->role_needed] ?? ucwords(str_replace('_', ' ', $leadRequest->role_needed)))
                            : '—' }}
                        @if ($leadRequest->role_needed === 'other' && $leadRequest->role_needed_other)
                            <span class="text-on-surface-variant">({{ $leadRequest->role_needed_other }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-outline font-medium mb-0.5">Hours / Week</p>
                    <p class="text-on-surface font-medium">{{ $leadRequest->estimated_hours_per_week ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-outline font-medium mb-0.5">Request Status</p>
                    <p class="text-on-surface font-medium">
                        {{ \App\Models\LeadRequest::statusLabels()[$leadRequest->status] ?? ucwords(str_replace('_', ' ', $leadRequest->status)) }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($trial)
        {{-- ── Trial status card ────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle px-6 py-5 mb-6 shadow-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-semibold text-outline uppercase tracking-wider">Trial Status</h3>
                <span class="text-xs font-semibold px-3 py-1 rounded-full
                    @if($trialStatus === 'active') bg-status-active/10 text-status-active border border-status-active/20
                    @elseif($trialStatus === 'approved') bg-secondary/5 text-secondary border border-secondary/20
                    @elseif($trialStatus === 'completed') bg-status-completed/10 text-status-completed border border-status-completed/20
                    @elseif($trialStatus === 'expired') bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20
                    @elseif($trialStatus === 'cancelled') bg-status-blocked/10 text-status-blocked border border-status-blocked/20
                    @else bg-surface-container-low text-on-surface-variant border border-border-subtle @endif">
                    {{ $trialLabel }}
                </span>
            </div>

            @if ($trialStatus === 'active')
                {{-- Countdown --}}
                <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-5 py-4 mb-4">
                    <p class="text-xs text-status-active font-medium mb-1 uppercase tracking-wider">Trial ends in</p>
                    <p class="text-3xl font-bold text-status-completed">
                        {{ $hoursRemaining > 0 ? number_format($hoursRemaining, 1) . ' hours' : 'Ending now' }}
                    </p>
                    @if ($trial->ends_at)
                        <p class="text-xs text-status-active mt-1">{{ $trial->ends_at->format('d M Y \a\t H:i') }}</p>
                    @endif
                </div>
            @elseif ($trialStatus === 'approved')
                <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-secondary font-medium">Your trial has been approved</p>
                    <p class="text-sm text-on-surface-variant mt-0.5">The GVOS team will start your trial shortly. You'll receive an update once it begins.</p>
                </div>
            @elseif ($trialStatus === 'completed')
                <div class="bg-status-completed/10 border border-status-completed/20 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-status-completed font-medium">Your trial has been completed</p>
                    <p class="text-sm text-on-surface-variant mt-0.5">The GVOS team is reviewing your trial results. You will be contacted with next steps.</p>
                </div>
            @elseif ($trialStatus === 'expired')
                <div class="bg-status-payment-due/10 border border-status-payment-due/20 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-status-payment-due font-medium">Your trial period has expired</p>
                    <p class="text-sm text-on-surface-variant mt-0.5">Please contact the GVOS team to discuss next steps.</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-sm">
                @if ($trial->starts_at)
                    <div>
                        <p class="text-xs text-outline font-medium mb-0.5">Started</p>
                        <p class="text-on-surface">{{ $trial->starts_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($trial->ends_at && $trialStatus !== 'active')
                    <div>
                        <p class="text-xs text-outline font-medium mb-0.5">Ended</p>
                        <p class="text-on-surface">{{ $trial->ends_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($trial->trial_duration_hours)
                    <div>
                        <p class="text-xs text-outline font-medium mb-0.5">Trial Duration</p>
                        <p class="text-on-surface">{{ $trial->trial_duration_hours }} hours</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Team assignment ──────────────────────────────────────────── --}}
        @if ($trial->assignedTalent || $trial->assignedManager)
            <div class="bg-white rounded-xl border border-border-subtle px-6 py-5 mb-6 shadow-card">
                <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Your Team</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if ($trial->assignedTalent)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-secondary/5 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">person</span>
                            </div>
                            <div>
                                <p class="text-xs text-outline font-medium">Assigned Talent</p>
                                <p class="text-on-surface font-medium">{{ $trial->assignedTalent->name }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($trial->assignedManager)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-status-active/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-status-active" style="font-size: 18px;">verified_user</span>
                            </div>
                            <div>
                                <p class="text-xs text-outline font-medium">Assigned Manager</p>
                                <p class="text-on-surface font-medium">{{ $trial->assignedManager->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Price estimate ───────────────────────────────────────────── --}}
        @if ($estimate)
            <div class="bg-white rounded-xl border border-border-subtle px-6 py-5 mb-6 shadow-card">
                <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Price Estimate</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-outline font-medium mb-0.5">Amount</p>
                        <p class="text-on-surface font-semibold text-lg">
                            {{ $estimate->currency }} {{ number_format((float) $estimate->estimated_amount, 2) }}
                        </p>
                        <p class="text-xs text-outline">{{ $estimate->billing_cycle === 'bi_weekly' ? 'Bi-Weekly' : 'Monthly' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-outline font-medium mb-0.5">Status</p>
                        <p class="text-on-surface font-medium capitalize">{{ $estimate->status }}</p>
                    </div>
                    @if ($estimate->estimated_hours_per_week)
                        <div>
                            <p class="text-xs text-outline font-medium mb-0.5">Hours / Week</p>
                            <p class="text-on-surface font-medium">{{ $estimate->estimated_hours_per_week }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Payment pending CTA ──────────────────────────────────────── --}}
        @if (in_array($leadRequest?->status, ['trial_completed', 'payment_pending']))
            <div class="bg-status-payment-due/10 border border-status-payment-due/20 rounded-xl px-6 py-5 mb-6">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-status-payment-due flex-shrink-0 mt-0.5" style="font-size: 20px;">payments</span>
                    <div>
                        <p class="text-sm font-semibold text-status-payment-due">Ready to continue?</p>
                        <p class="text-sm text-on-surface-variant mt-0.5">
                            Your trial is complete. Contact the GVOS team to proceed with payment and activate your full service.
                        </p>
                        <p class="text-xs text-outline mt-2">Full payment and billing features are coming in a later phase.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Trial workspace ───────────────────────────────────────────── --}}
        @if ($workspace)
            <a href="{{ route('workspace.show', $workspace) }}"
               class="block bg-white rounded-xl border border-secondary/20 hover:border-secondary/40 hover:shadow-card px-8 py-6 mb-6 transition-all group shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-secondary/5 rounded-xl flex items-center justify-center group-hover:bg-secondary/10 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">workspaces</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">{{ $workspace->name }}</p>
                            <p class="text-xs text-outline">{{ $workspace->workspace_code }} &middot; {{ ucfirst($workspace->type) }} workspace &middot; {{ ucfirst($workspace->status) }}</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors" style="font-size: 20px;">chevron_right</span>
                </div>
            </a>
        @else
            <div class="bg-white rounded-xl border border-dashed border-border-subtle px-8 py-10 text-center mb-6 shadow-sm">
                <div class="w-12 h-12 bg-surface-container-low rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-outline" style="font-size: 24px;">workspaces</span>
                </div>
                <p class="text-sm font-semibold text-on-surface-variant mb-1">Trial Workspace</p>
                <p class="text-xs text-outline">Your workspace is being prepared by the GVOS team.</p>
            </div>
        @endif

    @else
        {{-- ── No trial yet ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle px-8 py-10 text-center max-w-lg mx-auto mb-8 shadow-card">
            <div class="w-14 h-14 bg-secondary/5 rounded-full flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-secondary" style="font-size: 28px;">schedule</span>
            </div>
            <h3 class="text-lg font-semibold text-on-surface mb-2">Onboarding in progress</h3>
            <p class="text-sm text-on-surface-variant mb-6">
                Our team is reviewing your information and will be in touch shortly to complete your onboarding and assign your workspace.
            </p>
            <a href="{{ route('profile.show') }}"
               class="inline-flex items-center gap-2 bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary text-sm font-semibold px-5 py-2.5 rounded-lg transition-all shadow-md">
                Complete My Profile
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
            </a>
        </div>
    @endif

    <div class="flex gap-4 justify-center mt-2">
        <a href="{{ route('profile.show') }}"
           class="text-sm text-on-surface-variant hover:text-secondary transition-colors">
            My Profile
        </a>
        <span class="text-outline">·</span>
        <p class="text-xs text-outline self-center">Questions? Contact the GVOS support team via your onboarding email.</p>
    </div>

</x-layouts.gvos>
