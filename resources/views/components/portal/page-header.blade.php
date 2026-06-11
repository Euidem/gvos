@props([
    'title',
    'subtitle' => null,
    'badge'    => null,        // optional short status/label string
    'badgeType'=> 'info',      // info | success | warning | neutral
])

@php
    $__badgeStyles = [
        'info'    => 'bg-secondary/10 text-secondary',
        'success' => 'bg-status-active/10 text-status-active',
        'warning' => 'bg-status-payment-due/10 text-status-payment-due',
        'neutral' => 'bg-surface-container-low text-on-surface-variant',
    ];
    $__badgeCls = $__badgeStyles[$badgeType] ?? $__badgeStyles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6']) }}>
    <div class="min-w-0">
        <div class="flex items-center gap-3 flex-wrap">
            <h2 class="font-headline-lg text-headline-lg text-primary leading-tight">{{ $title }}</h2>
            @if ($badge)
                <span class="font-label-md text-label-md px-2.5 py-1 rounded-full {{ $__badgeCls }}">{{ $badge }}</span>
            @endif
        </div>
        @if ($subtitle)
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex items-center gap-3 flex-shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
