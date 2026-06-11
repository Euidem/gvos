@props([
    'title'   => null,
    'subtitle'=> null,
    'flush'   => false,   // remove body padding (e.g. for divided lists / tables)
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden']) }}>
    @if ($title || isset($actions))
        <div class="px-card-padding py-4 border-b border-border-subtle flex items-center justify-between gap-3">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="font-headline-md text-headline-md text-primary font-bold leading-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2 flex-shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $flush ? '' : 'p-card-padding' }}">
        {{ $slot }}
    </div>
</div>
