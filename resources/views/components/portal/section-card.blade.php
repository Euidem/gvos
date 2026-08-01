@props([
    'title'   => null,
    'subtitle'=> null,
    'flush'   => false,   // remove body padding (e.g. for divided lists / tables)
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden']) }}>
    @if ($title || isset($actions))
        <div class="px-5 py-3.5 border-b border-border-subtle flex items-center justify-between gap-3">
            <div class="min-w-0">
                @if ($title)
                    {{-- Phase 28: section headings are h2/15px so the page h1 stays dominant. --}}
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

    <div class="{{ $flush ? '' : 'p-card-padding' }}">
        {{ $slot }}
    </div>
</div>
