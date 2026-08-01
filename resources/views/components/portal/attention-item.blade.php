@props([
    'href',
    'title',
    'meta'     => null,      // secondary line: workspace · person · date
    'tone'     => 'default', // default | urgent | warn | good | info
    'badge'    => null,      // short state word, e.g. "Blocked"
    'icon'     => null,
    'action'   => null,      // call-to-action label shown on the right
])

@php
    /* Phase 28: the core row of every "needs attention" block. Always names the
       actual item and links straight to it — never "1 item needs review". */
    $__tones = [
        'default' => ['#76777d', 'rgba(100,116,139,0.10)', '#5b6472'],
        'urgent'  => ['#EF4444', 'rgba(239,68,68,0.10)',  '#B91C1C'],
        'warn'    => ['#F59E0B', 'rgba(245,158,11,0.12)', '#92400E'],
        'good'    => ['#10B981', 'rgba(16,185,129,0.10)', '#047857'],
        'info'    => ['#0058be', 'rgba(0,88,190,0.09)',   '#0058be'],
    ];
    [$__rail, $__chipBg, $__chipFg] = $__tones[$tone] ?? $__tones['default'];
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex items-center gap-3 px-4 py-3 transition-colors hover:bg-surface-container-low']) }}>

    <span class="w-1 self-stretch rounded-full flex-shrink-0" style="background:{{ $__rail }};"></span>

    @if ($icon)
        <span class="material-symbols-outlined flex-shrink-0" style="font-size:19px;color:{{ $__rail }};">{{ $icon }}</span>
    @endif

    <span class="min-w-0 flex-1">
        <span class="block text-[14px] font-medium text-on-surface leading-snug group-hover:text-secondary transition-colors">
            {{ $title }}
        </span>
        @if ($meta)
            <span class="block text-[12px] text-outline mt-0.5 truncate">{{ $meta }}</span>
        @endif
    </span>

    @if ($badge)
        <span class="hidden sm:inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0"
              style="color:{{ $__chipFg }};background:{{ $__chipBg }};">{{ $badge }}</span>
    @endif

    <span class="flex items-center gap-1 text-[12.5px] font-semibold text-secondary flex-shrink-0">
        <span class="hidden md:inline">{{ $action ?? 'Open' }}</span>
        <span class="material-symbols-outlined" style="font-size:17px;">chevron_right</span>
    </span>
</a>
