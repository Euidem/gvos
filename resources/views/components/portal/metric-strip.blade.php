@props([
    'metrics' => [],   // [['label'=>..,'value'=>..,'href'=>null,'tone'=>'default|urgent|warn|good'], ...]
])

@php
    /* Phase 28: supporting numbers render as a caption strip, not as a wall of
       bordered cards. Only include a metric if a different value would change
       what the user does next. */
    $__tones = [
        'default' => '#191c1e',
        'urgent'  => '#B91C1C',
        'warn'    => '#B45309',
        'good'    => '#047857',
        'muted'   => '#76777d',
    ];
    $__metrics = array_values(array_filter($metrics));
@endphp

@if ($__metrics)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-stretch gap-x-8 gap-y-4 px-1']) }}>
        @foreach ($__metrics as $__m)
            @php
                $__tone  = $__tones[$__m['tone'] ?? 'default'] ?? $__tones['default'];
                $__href  = $__m['href'] ?? null;
                $__tag   = $__href ? 'a' : 'div';
            @endphp
            <{{ $__tag }} @if ($__href) href="{{ $__href }}" @endif
                class="min-w-[92px] {{ $__href ? 'group' : '' }}">
                <p class="font-headline-md text-[22px] font-bold leading-none {{ $__href ? 'group-hover:underline' : '' }}"
                   style="color:{{ $__tone }};">{{ $__m['value'] }}</p>
                <p class="text-[12px] text-outline mt-1.5 leading-tight">{{ $__m['label'] }}</p>
            </{{ $__tag }}>
        @endforeach
    </div>
@endif
