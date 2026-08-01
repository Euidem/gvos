@props([
    'title',
    'subtitle'    => null,
    'badge'       => null,        // optional short status/label string
    'badgeType'   => 'info',      // info | success | warning | neutral
    'eyebrow'     => null,        // parent context, e.g. the workspace name
    'eyebrowHref' => null,        // where the eyebrow links back to
    'divider'     => true,        // hairline rule under the header
])

@php
    /* Phase 28: exactly one <h1> per page. The eyebrow replaces the old ad-hoc
       "arrow_back" links so each page has a single back affordance. */
    $__badgeStyles = [
        'info'    => ['#0058be', 'rgba(0,88,190,0.09)'],
        'success' => ['#047857', 'rgba(16,185,129,0.10)'],
        'warning' => ['#92400E', 'rgba(245,158,11,0.12)'],
        'neutral' => ['#5b6472', 'rgba(100,116,139,0.10)'],
    ];
    [$__bfg, $__bbg] = $__badgeStyles[$badgeType] ?? $__badgeStyles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>

    @if ($eyebrow)
        <div class="mb-1.5">
            @if ($eyebrowHref)
                <a href="{{ $eyebrowHref }}"
                   class="inline-flex items-center gap-1 text-[12.5px] text-outline hover:text-secondary transition-colors">
                    <span class="material-symbols-outlined" style="font-size:15px;">chevron_left</span>
                    {{ $eyebrow }}
                </a>
            @else
                <p class="text-[12.5px] text-outline">{{ $eyebrow }}</p>
            @endif
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="font-headline-lg text-headline-lg text-on-surface leading-tight">{{ $title }}</h1>
                @if ($badge)
                    <span class="inline-flex items-center text-[11.5px] font-semibold px-2 py-0.5 rounded-full"
                          style="color:{{ $__bfg }};background:{{ $__bbg }};">{{ $badge }}</span>
                @endif
            </div>
            @if ($subtitle)
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 max-w-2xl">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if ($divider)
        <div class="mt-5 border-b border-border-subtle"></div>
    @endif
</div>
