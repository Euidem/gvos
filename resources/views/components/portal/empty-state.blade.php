@props([
    'icon'    => 'inbox',
    'title',
    'message' => null,
    'compact' => false,    // tighter padding for in-card empty states
])

<div {{ $attributes->merge(['class' => 'text-center ' . ($compact ? 'py-8 px-6' : 'py-12 px-8')]) }}>
    <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4 bg-secondary/5">
        <span class="material-symbols-outlined text-secondary" style="font-size:28px;">{{ $icon }}</span>
    </div>
    <h3 class="font-body-md text-body-md font-semibold text-on-surface mb-2">{{ $title }}</h3>
    @if ($message)
        <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm mx-auto">{{ $message }}</p>
    @endif
    @isset($action)
        <div class="mt-4 flex items-center justify-center gap-3">
            {{ $action }}
        </div>
    @endisset
</div>
