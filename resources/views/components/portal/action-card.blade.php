@props([
    'href',
    'icon'        => 'arrow_forward',
    'title',
    'description' => null,
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex items-start gap-4 bg-white p-card-padding rounded-xl border border-border-subtle shadow-card transition-all hover:border-secondary/30 hover:shadow-md card-lift']) }}>
    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-secondary/5 group-hover:bg-secondary/10 transition-colors">
        <span class="material-symbols-outlined text-secondary" style="font-size:20px;">{{ $icon }}</span>
    </div>
    <div class="min-w-0 flex-1">
        <p class="font-body-md text-body-md font-semibold text-on-surface group-hover:text-secondary transition-colors leading-snug">{{ $title }}</p>
        @if ($description)
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">{{ $description }}</p>
        @endif
    </div>
    <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors flex-shrink-0 mt-1"
          style="font-size:18px;">chevron_right</span>
</a>
