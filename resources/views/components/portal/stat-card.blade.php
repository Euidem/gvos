@props([
    'label',
    'value',
    'hint'       => null,
    'icon'       => null,
    'accent'     => 'secondary',          // secondary | status-active | status-blocked | status-urgent | status-payment-due
    'href'       => null,
    'valueClass' => 'text-primary',       // override value color (e.g. text-status-blocked when alerting)
    'hintClass'  => 'text-outline',       // override hint color
])

@php
    $__tag = $href ? 'a' : 'div';
    $__hoverBorder = "hover:border-{$accent}";
@endphp

<{{ $__tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "bg-white p-card-padding rounded-xl border border-border-subtle shadow-card flex flex-col justify-between transition-all group {$__hoverBorder} hover:shadow-md" . ($href ? ' cursor-pointer' : '')]) }}>
    <div class="flex justify-between items-start">
        <span class="font-label-md text-label-md text-outline uppercase tracking-wider">{{ $label }}</span>
        @if ($icon)
            <span class="material-symbols-outlined text-{{ $accent }} opacity-0 group-hover:opacity-100 transition-opacity"
                  style="font-size:18px;">{{ $icon }}</span>
        @endif
    </div>
    <div class="mt-4 flex items-baseline gap-2">
        <span class="font-headline-lg text-headline-lg {{ $valueClass }}">{{ $value }}</span>
        @if ($hint)
            <span class="font-label-md text-label-md {{ $hintClass }}">{{ $hint }}</span>
        @endif
    </div>
</{{ $__tag }}>
