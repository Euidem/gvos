@props([
    'title'    => null,
    'subtitle' => null,
    'card'     => true,     // false = grouped by heading + space only, no border
    'flush'    => false,    // remove body padding (lists / tables)
    'id'       => null,
])

@php
    /* Phase 28: prefer space + a heading over another bordered rectangle.
       Pass card=false for grouping that does not need a container. */
    $__body = $flush ? '' : ($card ? 'p-5' : '');
@endphp

<section @if ($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'min-w-0']) }}>

    @if ($title || isset($actions))
        <div class="flex items-end justify-between gap-3 mb-3">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-[15px] font-semibold text-on-surface leading-tight">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="text-[12.5px] text-outline mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2 flex-shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    @if ($card)
        <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden {{ $__body }}">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</section>
