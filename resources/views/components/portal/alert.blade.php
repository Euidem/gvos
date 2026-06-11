@props([
    'type'    => 'info',   // info | status | success | error | warning
    'title'   => null,
])

@php
    /* Professional alert styling — inline color tokens follow the established
       GVOS banner pattern (see partials/billing-banner) for CDN-Tailwind safety. */
    $__type = $type === 'status' ? 'info' : $type;

    $__styles = [
        'success' => ['bg' => 'rgba(16,185,129,0.08)', 'border' => 'rgba(16,185,129,0.25)', 'fg' => '#065F46', 'icon' => 'check_circle'],
        'error'   => ['bg' => 'rgba(220,38,38,0.06)',  'border' => 'rgba(220,38,38,0.25)',  'fg' => '#991B1B', 'icon' => 'error'],
        'warning' => ['bg' => 'rgba(245,158,11,0.08)', 'border' => 'rgba(245,158,11,0.28)', 'fg' => '#92400E', 'icon' => 'warning'],
        'info'    => ['bg' => 'rgba(0,88,190,0.06)',   'border' => 'rgba(0,88,190,0.20)',   'fg' => '#1E3A8A', 'icon' => 'info'],
    ];
    $__s = $__styles[$__type] ?? $__styles['info'];
@endphp

<div role="alert"
     {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-3 px-4 py-3.5 rounded-xl font-body-sm text-body-sm']) }}
     style="background:{{ $__s['bg'] }};border:1px solid {{ $__s['border'] }};color:{{ $__s['fg'] }};">
    <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;color:{{ $__s['fg'] }};">{{ $__s['icon'] }}</span>
    <div class="flex-1 min-w-0">
        @if ($title)
            <p class="font-semibold leading-tight">{{ $title }}</p>
            <div class="mt-0.5 text-on-surface-variant">{{ $slot }}</div>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
