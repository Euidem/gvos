@props([
    'href'    => null,
    'variant' => 'secondary',   // primary | secondary | ghost | danger
    'icon'    => null,
    'size'    => 'md',          // sm | md
    'type'    => 'submit',      // only used when rendering a <button>
])

@php
    /* Phase 28 action hierarchy — at most one `primary` above the fold per page
       (queue-driven pages may have none; each row carries its own action).
       Inline colours follow the established CDN-safe pattern. */
    $__pad = $size === 'sm' ? 'px-3 py-1.5 text-[12.5px]' : 'px-4 py-2 text-[13.5px]';

    $__variants = [
        'primary'   => 'color:#ffffff;background-color:#0058be;border:1px solid #0058be;',
        'secondary' => 'color:#191c1e;background-color:#ffffff;border:1px solid #E2E8F0;',
        'ghost'     => 'color:#45464d;background-color:transparent;border:1px solid transparent;',
        'danger'    => 'color:#991B1B;background-color:#ffffff;border:1px solid rgba(220,38,38,0.30);',
    ];
    $__style = $__variants[$variant] ?? $__variants['secondary'];

    $__class = "inline-flex items-center justify-center gap-1.5 rounded-lg font-semibold whitespace-nowrap
                transition-all hover:brightness-[0.98] active:scale-[0.98] {$__pad}";

    $__tag = $href ? 'a' : 'button';
@endphp

<{{ $__tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $__class]) }}
    style="{{ $__style }}">
    @if ($icon)
        <span class="material-symbols-outlined flex-shrink-0" style="font-size:{{ $size === 'sm' ? 15 : 17 }}px;">{{ $icon }}</span>
    @endif
    {{ $slot }}
</{{ $__tag }}>
