<x-layouts.gvos :title="$workspace->name . ' — Payments'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.billing.index', $workspace) }}" class="hover:text-secondary transition-colors">Billing</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Payments</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">payments</span>
                Payment History
            </h2>
            <p class="font-body-sm text-body-sm text-outline mt-1">{{ $workspace->workspace_code }}</p>
        </div>
        <a href="{{ route('workspace.billing.index', $workspace) }}"
           class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
            Billing
        </a>
    </div>

    {{-- ── Payments table ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">

        @if ($payments->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 26px;">payments</span>
                </div>
                <h4 class="font-headline-md text-headline-md text-on-surface mb-1">No payments yet</h4>
                <p class="font-body-sm text-body-sm text-outline max-w-xs mx-auto">
                    No payment records are available for this workspace.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Reference</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Invoice</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Provider</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Status</th>
                            @if ($canViewInternal)
                                <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Confirmed by</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Notes</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach ($payments as $pmt)
                            @php
                                $pmtColor = match($pmt->status) {
                                    'confirmed' => '#059669',
                                    'pending'   => '#F59E0B',
                                    'failed'    => '#EF4444',
                                    'reversed'  => '#8B5CF6',
                                    'cancelled' => '#94A3B8',
                                    default     => '#94A3B8',
                                };
                            @endphp
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-3 font-mono text-xs text-on-surface">
                                    {{ $pmt->payment_reference ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    @if ($pmt->invoice)
                                        <a href="{{ route('workspace.billing.invoice', [$workspace, $pmt->invoice]) }}"
                                           class="font-semibold hover:underline" style="color:#0058be;">
                                            {{ $pmt->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-outline">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">{{ $pmt->providerLabel() }}</td>
                                <td class="px-5 py-3 text-xs font-semibold text-on-surface">
                                    {{ $pmt->currency }} {{ number_format((float) $pmt->amount, 2) }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">
                                    {{ $pmt->paid_at ? $pmt->paid_at->format('d M Y') : ($pmt->created_at ? $pmt->created_at->format('d M Y') : '—') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:{{ $pmtColor }}18;color:{{ $pmtColor }};">
                                        {{ $pmt->statusLabel() }}
                                    </span>
                                </td>
                                @if ($canViewInternal)
                                    <td class="px-5 py-3 text-xs text-on-surface-variant">
                                        {{ $pmt->confirmedBy?->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-on-surface-variant max-w-xs">
                                        {{ $pmt->confirmation_notes ? Str::limit($pmt->confirmation_notes, 80) : '—' }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="px-5 py-3 border-t border-border-subtle">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>

</x-layouts.gvos>
