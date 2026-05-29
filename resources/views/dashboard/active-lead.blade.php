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
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">Your GVOS trial dashboard</p>
        </div>
        <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full font-medium">
            Active Lead
        </span>
    </div>

    @if ($leadRequest)
        {{-- ── Lead request summary ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 px-6 py-5 mb-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">Your Service Request</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-0.5">Role Requested</p>
                    <p class="text-slate-800 font-medium">
                        {{ $leadRequest->role_needed
                            ? (\App\Models\LeadRequest::roleLabels()[$leadRequest->role_needed] ?? ucwords(str_replace('_', ' ', $leadRequest->role_needed)))
                            : '—' }}
                        @if ($leadRequest->role_needed === 'other' && $leadRequest->role_needed_other)
                            <span class="text-slate-500">({{ $leadRequest->role_needed_other }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-0.5">Hours / Week</p>
                    <p class="text-slate-800 font-medium">{{ $leadRequest->estimated_hours_per_week ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium mb-0.5">Request Status</p>
                    <p class="text-slate-800 font-medium">
                        {{ \App\Models\LeadRequest::statusLabels()[$leadRequest->status] ?? ucwords(str_replace('_', ' ', $leadRequest->status)) }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($trial)
        {{-- ── Trial status card ────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 px-6 py-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Trial Status</h3>
                <span class="text-xs font-semibold px-3 py-1 rounded-full
                    @if($trialStatus === 'active') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @elseif($trialStatus === 'approved') bg-indigo-50 text-indigo-700 border border-indigo-200
                    @elseif($trialStatus === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @elseif($trialStatus === 'expired') bg-amber-50 text-amber-700 border border-amber-200
                    @elseif($trialStatus === 'cancelled') bg-red-50 text-red-700 border border-red-200
                    @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                    {{ $trialLabel }}
                </span>
            </div>

            @if ($trialStatus === 'active')
                {{-- Countdown --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-4 mb-4">
                    <p class="text-xs text-emerald-700 font-medium mb-1">Trial ends in</p>
                    <p class="text-3xl font-bold text-emerald-800">
                        {{ $hoursRemaining > 0 ? number_format($hoursRemaining, 1) . ' hours' : 'Ending now' }}
                    </p>
                    @if ($trial->ends_at)
                        <p class="text-xs text-emerald-600 mt-1">{{ $trial->ends_at->format('d M Y \a\t H:i') }}</p>
                    @endif
                </div>
            @elseif ($trialStatus === 'approved')
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-indigo-800 font-medium">Your trial has been approved</p>
                    <p class="text-sm text-indigo-700 mt-0.5">The GVOS team will start your trial shortly. You'll receive an update once it begins.</p>
                </div>
            @elseif ($trialStatus === 'completed')
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-emerald-800 font-medium">Your trial has been completed</p>
                    <p class="text-sm text-emerald-700 mt-0.5">The GVOS team is reviewing your trial results. You will be contacted with next steps.</p>
                </div>
            @elseif ($trialStatus === 'expired')
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-4">
                    <p class="text-sm text-amber-800 font-medium">Your trial period has expired</p>
                    <p class="text-sm text-amber-700 mt-0.5">Please contact the GVOS team to discuss next steps.</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-sm">
                @if ($trial->starts_at)
                    <div>
                        <p class="text-xs text-slate-400 font-medium mb-0.5">Started</p>
                        <p class="text-slate-800">{{ $trial->starts_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($trial->ends_at && $trialStatus !== 'active')
                    <div>
                        <p class="text-xs text-slate-400 font-medium mb-0.5">Ended</p>
                        <p class="text-slate-800">{{ $trial->ends_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($trial->trial_duration_hours)
                    <div>
                        <p class="text-xs text-slate-400 font-medium mb-0.5">Trial Duration</p>
                        <p class="text-slate-800">{{ $trial->trial_duration_hours }} hours</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Team assignment ──────────────────────────────────────────── --}}
        @if ($trial->assignedTalent || $trial->assignedManager)
            <div class="bg-white rounded-2xl border border-slate-200 px-6 py-5 mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">Your Team</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if ($trial->assignedTalent)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-indigo-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Assigned Talent</p>
                                <p class="text-slate-800 font-medium">{{ $trial->assignedTalent->name }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($trial->assignedManager)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-emerald-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Assigned Manager</p>
                                <p class="text-slate-800 font-medium">{{ $trial->assignedManager->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Price estimate ───────────────────────────────────────────── --}}
        @if ($estimate)
            <div class="bg-white rounded-2xl border border-slate-200 px-6 py-5 mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">Price Estimate</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-slate-400 font-medium mb-0.5">Amount</p>
                        <p class="text-slate-800 font-semibold text-lg">
                            {{ $estimate->currency }} {{ number_format((float) $estimate->estimated_amount, 2) }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $estimate->billing_cycle === 'bi_weekly' ? 'Bi-Weekly' : 'Monthly' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium mb-0.5">Status</p>
                        <p class="text-slate-800 font-medium capitalize">{{ $estimate->status }}</p>
                    </div>
                    @if ($estimate->estimated_hours_per_week)
                        <div>
                            <p class="text-xs text-slate-400 font-medium mb-0.5">Hours / Week</p>
                            <p class="text-slate-800 font-medium">{{ $estimate->estimated_hours_per_week }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Payment pending CTA ──────────────────────────────────────── --}}
        @if (in_array($leadRequest?->status, ['trial_completed', 'payment_pending']))
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-5 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 text-amber-500 mt-0.5 flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Ready to continue?</p>
                        <p class="text-sm text-amber-700 mt-0.5">
                            Your trial is complete. Contact the GVOS team to proceed with payment and activate your full service.
                        </p>
                        <p class="text-xs text-amber-600 mt-2">Full payment and billing features are coming in a later phase.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Trial workspace ───────────────────────────────────────────── --}}
        @if ($workspace)
            <a href="{{ route('workspace.show', $workspace) }}"
               class="block bg-white rounded-2xl border border-indigo-200 hover:border-indigo-400 hover:shadow-md px-8 py-6 mb-6 transition-all group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $workspace->name }}</p>
                            <p class="text-xs text-slate-500">{{ $workspace->workspace_code }} &middot; {{ ucfirst($workspace->type) }} workspace &middot; {{ ucfirst($workspace->status) }}</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @else
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 px-8 py-10 text-center mb-6">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-600 mb-1">Trial workspace</p>
                <p class="text-xs text-slate-400">Your workspace is being prepared by the GVOS team.</p>
            </div>
        @endif

    @else
        {{-- ── No trial yet ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 px-8 py-10 text-center max-w-lg mx-auto mb-8">
            <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">Onboarding in progress</h3>
            <p class="text-sm text-slate-500 mb-6">
                Our team is reviewing your information and will be in touch shortly to complete your onboarding and assign your workspace.
            </p>
            <a href="{{ route('profile.show') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                Complete My Profile
            </a>
        </div>
    @endif

    <div class="flex gap-4 justify-center mt-2">
        <a href="{{ route('profile.show') }}"
           class="text-sm text-slate-500 hover:text-indigo-600 transition-colors">
            My Profile
        </a>
        <span class="text-slate-300">·</span>
        <p class="text-xs text-slate-400 self-center">Questions? Contact the GVOS support team via your onboarding email.</p>
    </div>

</x-layouts.gvos>
