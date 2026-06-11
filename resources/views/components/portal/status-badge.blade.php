@props([
    'status',           // raw status string (e.g. active, pending, completed, blocked)
    'label' => null,    // optional override label
])

@php
    $__key = strtolower(trim((string) $status));

    /* Map common GVOS statuses to inline color pairs (CDN-safe, banner pattern). */
    $__map = [
        'active'             => ['#10B981', 'rgba(16,185,129,0.10)'],
        'approved'           => ['#059669', 'rgba(5,150,105,0.10)'],
        'completed'          => ['#059669', 'rgba(5,150,105,0.10)'],
        'pending'            => ['#D97706', 'rgba(245,158,11,0.12)'],
        'in_progress'        => ['#0058be', 'rgba(0,88,190,0.10)'],
        'submitted'          => ['#7C3AED', 'rgba(124,58,237,0.10)'],
        'revision_requested' => ['#EA580C', 'rgba(234,88,12,0.10)'],
        'trial'              => ['#8B5CF6', 'rgba(139,92,246,0.10)'],
        'paused'             => ['#0058be', 'rgba(0,88,190,0.08)'],
        'payment_due'        => ['#D97706', 'rgba(245,158,11,0.12)'],
        'overdue'            => ['#EF4444', 'rgba(239,68,68,0.10)'],
        'blocked'            => ['#EF4444', 'rgba(239,68,68,0.10)'],
        'cancelled'          => ['#EF4444', 'rgba(239,68,68,0.10)'],
        'suspended'          => ['#64748B', 'rgba(100,116,139,0.10)'],
        'inactive'           => ['#64748B', 'rgba(100,116,139,0.10)'],
        'closed'             => ['#6B7280', 'rgba(107,114,128,0.10)'],
    ];
    [$__fg, $__bg] = $__map[$__key] ?? ['#64748B', 'rgba(100,116,139,0.10)'];

    $__label = $label ?? ucwords(str_replace('_', ' ', $__key));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-label-md text-label-md font-semibold px-2.5 py-0.5 rounded-full']) }}
      style="color:{{ $__fg }};background:{{ $__bg }};">
    {{ $__label }}
</span>
