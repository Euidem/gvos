@props([
    'icon'    => 'inbox',
    'title',
    'message' => null,
    'compact' => false,    // tighter padding for in-card empty states
])

<div {{ $attributes->merge(['class' => 'text-center ' . ($compact ? 'py-8 px-6' : 'py-12 px-8')]) }}>
    <span class="material-symbols-outlined block mx-auto mb-3 text-outline-variant" style="font-size:30px;">{{ $icon }}</span>
    <p class="text-[14px] font-semibold text-on-surface mb-1.5">{{ $title }}</p>
    @if ($message)
        <p class="text-[13px] text-on-surface-variant max-w-sm mx-auto leading-relaxed">{{ $message }}</p>
    @endif
    @isset($action)
        <div class="mt-4 flex items-center justify-center gap-3">
            {{ $action }}
        </div>
    @endisset
</div>
