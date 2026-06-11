@props([
    'label',
    'value',
    'hint'          => null,
    'icon'          => null,
    'accent'        => 'secondary',          // secondary | status-active | status-blocked | status-urgent | status-payment-due
    'href'          => null,
    'valueClass'    => 'text-primary',       // override value color
    'hintClass'     => 'text-outline',       // override hint color
    'progress'      => null,                 // integer 0–100 for optional progress bar
    'progressColor' => null,                 // hex/rgb override for progress fill (defaults to #0058be)
])

@php
    $__tag = $href ? 'a' : 'div';
    $__hoverBorder = "hover:border-{$accent}";
@endphp

<{{ $__tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "bg-white p-5 rounded-xl border border-border-subtle shadow-sm flex flex-col transition-all group {$__hoverBorder} hover:shadow-md" . ($href ? ' cursor-pointer' : '')]) }}>

    {{-- Label + icon row --}}
    <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-outline uppercase tracking-widest">{{ $label }}</span>
        @if ($icon)
            <span class="material-symbols-outlined text-{{ $accent }} transition-opacity"
                  style="font-size:15px;opacity:0.55;">{{ $icon }}</span>
        @endif
    </div>

    {{-- Value --}}
    <p class="font-headline-md text-headline-md leading-none {{ $valueClass }}">{{ $value }}</p>

    {{-- Hint --}}
    @if ($hint)
        <p class="text-[11px] mt-1.5 {{ $hintClass }}">{{ $hint }}</p>
    @endif

    {{-- Progress bar --}}
    @if ($progress !== null)
        <div class="mt-3">
            <div class="w-full h-1.5 rounded-full" style="background:rgba(0,88,190,0.08);">
                <div class="h-full rounded-full transition-all"
                     style="width:{{ min(100, max(0, (int)$progress)) }}%;background:{{ $progressColor ?? '#0058be' }};"></div>
            </div>
        </div>
    @endif

</{{ $__tag }}>
