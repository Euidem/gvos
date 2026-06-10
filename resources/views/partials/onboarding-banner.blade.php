@php
    /* Onboarding banner — shown until profile setup is complete (Phase 16) */
    $__obUser      = $__obUser ?? auth()->user();
    $__obProfile   = $__obUser?->profile;
    $__obComplete  = $__obProfile?->onboarding_status === 'complete';
    $__obPct       = $__obComplete ? 100 : ($__obUser?->onboardingCompletionPercentage() ?? 0);
@endphp

@if (! $__obComplete)
<div class="mb-6 rounded-xl border border-secondary/20 bg-secondary/5 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-start sm:items-center gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5 sm:mt-0" style="font-size:22px">account_circle</span>
        <div>
            <p class="font-body-md text-body-md text-on-surface font-semibold leading-tight">
                Complete your setup — {{ $__obPct }}% done
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                @if ($__obPct === 0)
                    Add your name and timezone to finish setting up your account.
                @elseif ($__obPct < 50)
                    You are almost started — a few more steps and you will be fully set up.
                @else
                    Almost there! Finish your profile to unlock all features.
                @endif
            </p>
        </div>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        {{-- Mini progress bar --}}
        <div class="hidden sm:flex items-center gap-2">
            <div class="w-24 h-1.5 bg-border-subtle rounded-full overflow-hidden">
                <div class="h-full bg-secondary rounded-full transition-all" style="width:{{ $__obPct }}%"></div>
            </div>
            <span class="font-body-sm text-body-sm text-on-surface-variant text-xs">{{ $__obPct }}%</span>
        </div>
        <a href="{{ route('onboarding.index') }}"
           class="inline-flex items-center gap-1.5 bg-secondary text-on-secondary px-4 py-2 rounded-lg
                  font-label-md text-label-md text-sm hover:brightness-110 transition-all whitespace-nowrap">
            <span class="material-symbols-outlined" style="font-size:14px">arrow_forward</span>
            Continue Setup
        </a>
    </div>
</div>
@endif
