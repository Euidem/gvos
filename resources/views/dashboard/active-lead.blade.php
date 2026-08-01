{{-- Phase 28 — Active lead. Deliberately narrow: status, next step, what we need
     from you, and how to proceed. No full client workspace navigation. --}}
<x-layouts.gvos title="My Request">

@php
    $first  = auth()->user()->profile?->first_name ?? explode(' ', auth()->user()->name)[0];
    $status = $leadRequest?->status;

    /* One clear "next step" per pipeline stage. */
    [$stepTitle, $stepBody, $stepAction, $stepHref] = match (true) {
        $trial && $trial->isActive() && $workspace => [
            'Your trial is running',
            'Work is underway in your trial workspace. Review what the team has produced and share feedback in the workspace messages.',
            'Open Trial Workspace',
            route('workspace.show', $workspace),
        ],
        $status === 'trial_approved' => [
            'Your trial has been approved',
            'Your GVOS manager is preparing the workspace. You will be notified as soon as it opens.',
            null, null,
        ],
        $status === 'price_estimated' => [
            'Review your estimate',
            'We have prepared a cost estimate for your request. Your GVOS contact will walk you through it and answer any questions.',
            null, null,
        ],
        $status === 'price_accepted' => [
            'Estimate accepted',
            'Thank you. We are matching you with a specialist and will confirm your trial shortly.',
            null, null,
        ],
        $status === 'trial_completed', $status === 'payment_pending' => [
            'Ready to continue',
            'Your trial is complete. Your GVOS contact will confirm the next steps to move to a full engagement.',
            null, null,
        ],
        $status === 'converted' => [
            'You are now a GVOS client',
            'Your full workspace is being set up. Your dashboard will update automatically.',
            null, null,
        ],
        default => [
            'We have your request',
            'Your request is with our team. We will be in touch to confirm the details and prepare an estimate.',
            null, null,
        ],
    };

    /* What we still need from the requester, derived from empty fields. */
    $missing = collect([
        ! auth()->user()->profile?->phone            ? 'A contact phone number on your profile' : null,
        ! $leadRequest?->work_description            ? 'A short description of the work you need' : null,
        ! $leadRequest?->estimated_hours_per_week    ? 'Roughly how many hours per week you need' : null,
        ! $leadRequest?->preferred_start_date        ? 'Your preferred start date' : null,
    ])->filter()->values();
@endphp

<x-portal.page-header
    title="Hello, {{ $first }}"
    :subtitle="$leadRequest ? 'Your GVOS request' . ($leadRequest->lead_code ? ' · ' . $leadRequest->lead_code : '') : 'Your GVOS request'"
    :badge="$leadRequest ? \App\Models\LeadRequest::statusLabels()[$status] ?? null : null"
    badge-type="info"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    <div class="lg:col-span-2 space-y-5">

        {{-- 1. Next step — the single most important thing on this page --}}
        <x-portal.section title="What happens next">
            <p class="text-[17px] font-semibold text-on-surface leading-snug">{{ $stepTitle }}</p>
            <p class="text-[14px] text-on-surface-variant mt-2 leading-relaxed max-w-[640px]">{{ $stepBody }}</p>

            @if ($trial && $trial->isActive() && $trial->ends_at)
                <p class="text-[12.5px] mt-3" style="color:#92400E;">
                    Trial ends {{ $trial->ends_at->format('j M Y, H:i') }}
                    ({{ number_format($trial->hoursRemaining(), 0) }} hours remaining)
                </p>
            @endif

            @if ($stepAction && $stepHref)
                <x-portal.btn variant="primary" icon="open_in_new" class="mt-4" :href="$stepHref">
                    {{ $stepAction }}
                </x-portal.btn>
            @endif
        </x-portal.section>

        {{-- 2. What we need from you --}}
        @if ($missing->isNotEmpty())
            <x-portal.section title="What we still need from you">
                <ul class="space-y-2.5">
                    @foreach ($missing as $item)
                        <li class="flex items-start gap-2.5 text-[13.5px] text-on-surface-variant">
                            <span class="material-symbols-outlined flex-shrink-0 mt-0.5"
                                  style="font-size:17px;color:#B45309;">radio_button_unchecked</span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <x-portal.btn variant="secondary" icon="person" class="mt-4" :href="route('profile.show')">
                    Update My Details
                </x-portal.btn>
            </x-portal.section>
        @endif

        {{-- 3. Your request --}}
        @if ($leadRequest)
            <x-portal.section title="Your request">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                    @if ($leadRequest->role_needed)
                        <div>
                            <dt class="text-[12px] text-outline">Role requested</dt>
                            <dd class="text-[13.5px] text-on-surface mt-0.5">
                                {{ \App\Models\LeadRequest::roleLabels()[$leadRequest->role_needed] ?? $leadRequest->role_needed_other ?? 'Not specified' }}
                            </dd>
                        </div>
                    @endif
                    @if ($leadRequest->company_name)
                        <div>
                            <dt class="text-[12px] text-outline">Organisation</dt>
                            <dd class="text-[13.5px] text-on-surface mt-0.5">{{ $leadRequest->company_name }}</dd>
                        </div>
                    @endif
                    @if ($leadRequest->estimated_hours_per_week)
                        <div>
                            <dt class="text-[12px] text-outline">Hours per week</dt>
                            <dd class="text-[13.5px] text-on-surface mt-0.5">{{ $leadRequest->estimated_hours_per_week }}</dd>
                        </div>
                    @endif
                    @if ($leadRequest->preferred_start_date)
                        <div>
                            <dt class="text-[12px] text-outline">Preferred start</dt>
                            <dd class="text-[13.5px] text-on-surface mt-0.5">{{ $leadRequest->preferred_start_date->format('j M Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-portal.section>
        @endif
    </div>

    <div class="space-y-5">

        @if ($estimate)
            <x-portal.section title="Your estimate">
                <p class="font-headline-md text-[22px] font-bold text-on-surface leading-none">
                    {{ $estimate->currency }} {{ number_format((float) $estimate->estimated_amount, 2) }}
                </p>
                <p class="text-[12.5px] text-on-surface-variant mt-1.5">
                    {{ $estimate->billing_cycle === 'bi_weekly' ? 'every two weeks' : 'per month' }}
                    @if ($estimate->estimated_hours_per_week)
                        · {{ $estimate->estimated_hours_per_week }} hours per week
                    @endif
                </p>
                <p class="text-[12px] mt-3">
                    <x-portal.status-badge :status="$estimate->status" />
                </p>
            </x-portal.section>
        @endif

        @if ($trial)
            <x-portal.section title="Your GVOS team">
                <div class="space-y-3">
                    @if ($trial->assignedManager)
                        <div>
                            <p class="text-[12px] text-outline">Your manager</p>
                            <p class="text-[13.5px] text-on-surface mt-0.5">{{ $trial->assignedManager->name }}</p>
                        </div>
                    @endif
                    @if ($trial->assignedTalent)
                        <div>
                            <p class="text-[12px] text-outline">Your specialist</p>
                            <p class="text-[13.5px] text-on-surface mt-0.5">{{ $trial->assignedTalent->name }}</p>
                        </div>
                    @endif
                </div>
                @if ($workspace)
                    <a href="{{ route('workspace.chat.index', $workspace) }}"
                       class="inline-flex items-center gap-1 mt-4 text-[12.5px] font-semibold text-secondary hover:underline">
                        Message your team
                        <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                    </a>
                @endif
            </x-portal.section>
        @endif

    </div>
</div>

</x-layouts.gvos>
