@props([
    'workspace',
    'meta'      => null,    // secondary line, e.g. "Manager · Grace Manager"
    'alerts'    => [],      // [['label'=>'2 blocked','tone'=>'urgent'], ...]
    'href'      => null,
])

@php
    /* Phase 28: one row per workspace. Replaces the old pattern of a card plus a
       row of four duplicate module chips per workspace. The row itself is the
       link; module navigation lives in the workspace tab bar. */
    $__href = $href ?? route('workspace.show', $workspace);
    $__tones = [
        'urgent' => ['#B91C1C', 'rgba(239,68,68,0.10)'],
        'warn'   => ['#92400E', 'rgba(245,158,11,0.12)'],
        'info'   => ['#0058be', 'rgba(0,88,190,0.09)'],
        'good'   => ['#047857', 'rgba(16,185,129,0.10)'],
    ];
    $__initials = strtoupper(mb_substr($workspace->name, 0, 2));
@endphp

<a href="{{ $__href }}"
   {{ $attributes->merge(['class' => 'group flex items-center gap-3.5 px-4 py-3.5 transition-colors hover:bg-surface-container-low']) }}>

    <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[12px] font-bold text-white flex-shrink-0"
          style="background-color:#0058be;">{{ $__initials }}</span>

    <span class="min-w-0 flex-1">
        <span class="block text-[14px] font-semibold text-on-surface leading-snug group-hover:text-secondary transition-colors">
            {{ $workspace->name }}
        </span>
        <span class="block text-[12px] text-outline mt-0.5 truncate">
            {{ $workspace->workspace_code }}@if ($meta) · {{ $meta }} @endif
        </span>
    </span>

    @if ($alerts)
        <span class="hidden sm:flex items-center gap-1.5 flex-shrink-0">
            @foreach ($alerts as $__a)
                @php [$__fg, $__bg] = $__tones[$__a['tone'] ?? 'info'] ?? $__tones['info']; @endphp
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap"
                      style="color:{{ $__fg }};background:{{ $__bg }};">{{ $__a['label'] }}</span>
            @endforeach
        </span>
    @endif

    <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors flex-shrink-0"
          style="font-size:18px;">chevron_right</span>
</a>
