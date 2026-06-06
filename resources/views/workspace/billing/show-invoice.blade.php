<x-layouts.gvos :title="$invoice->invoice_number">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.billing.index', $workspace) }}" class="hover:text-secondary transition-colors">Billing</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span class="font-mono">{{ $invoice->invoice_number }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Main invoice content ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Invoice header card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="font-mono text-sm font-bold text-on-surface">{{ $invoice->invoice_number }}</span>
                            @php
                                $sc = match($invoice->status) {
                                    'draft'          => '#94A3B8',
                                    'issued'         => '#0058be',
                                    'partially_paid' => '#F59E0B',
                                    'paid'           => '#059669',
                                    'overdue'        => '#EF4444',
                                    'cancelled'      => '#64748B',
                                    'void'           => '#64748B',
                                    default          => '#94A3B8',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:{{ $sc }}18;color:{{ $sc }};">
                                {{ $invoice->statusLabel() }}
                            </span>
                        </div>
                        <p class="font-body-sm text-body-sm text-outline">
                            Issued {{ $invoice->issue_date->format('d M Y') }}
                            @if ($invoice->due_date)
                                &middot; Due {{ $invoice->due_date->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-label-md text-label-md text-outline">Total Amount</p>
                        <p class="font-headline-md text-headline-md text-primary font-bold">
                            {{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}
                        </p>
                    </div>
                </div>

                {{-- Line items --}}
                @if ($invoice->items->isNotEmpty())
                    <div class="mb-5">
                        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-3">Items</p>
                        <div class="border border-border-subtle rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-outline">Description</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-outline">Qty</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-outline">Unit</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-outline">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F1F5F9]">
                                    @foreach ($invoice->items as $item)
                                        <tr>
                                            <td class="px-4 py-2.5 text-xs text-on-surface">
                                                {{ $item->description }}
                                                <span class="ml-1 text-outline">({{ $item->typeLabel() }})</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-xs text-on-surface-variant text-right">
                                                {{ rtrim(rtrim(number_format((float) $item->quantity, 4), '0'), '.') }}
                                            </td>
                                            <td class="px-4 py-2.5 text-xs text-on-surface-variant text-right">
                                                {{ $invoice->currency }} {{ number_format((float) $item->unit_amount, 2) }}
                                            </td>
                                            <td class="px-4 py-2.5 text-xs font-semibold text-on-surface text-right">
                                                {{ $invoice->currency }} {{ number_format((float) $item->total_amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Totals summary --}}
                <div class="border-t border-border-subtle pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-outline">Subtotal</span>
                        <span class="font-medium text-on-surface">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</span>
                    </div>
                    @if ((float) $invoice->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-outline">Discount</span>
                            <span class="font-medium text-status-active">− {{ $invoice->currency }} {{ number_format((float) $invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if ((float) $invoice->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-outline">Tax</span>
                            <span class="font-medium text-on-surface">{{ $invoice->currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm pt-2 border-t border-border-subtle">
                        <span class="font-bold text-on-surface">Total</span>
                        <span class="font-bold text-on-surface">{{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}</span>
                    </div>
                    @if ((float) $invoice->amount_paid > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-outline">Amount Paid</span>
                            <span class="font-semibold text-status-active">{{ $invoice->currency }} {{ number_format((float) $invoice->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-on-surface">Balance Due</span>
                            <span class="{{ $invoice->balance_due > 0 ? 'text-status-blocked' : 'text-status-active' }}">
                                {{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Notes (visible to all) --}}
                @if ($invoice->notes)
                    <div class="mt-5 pt-4 border-t border-border-subtle">
                        <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-2">Notes</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                @endif

                {{-- Internal notes (admin/manager only) --}}
                @if ($canViewInternal && $invoice->internal_notes)
                    <div class="mt-4 p-4 rounded-lg" style="background:rgba(139,92,246,0.04);border:1px solid rgba(139,92,246,0.12);">
                        <p class="font-label-md text-label-md font-semibold mb-1" style="color:#7C3AED;">Internal Notes</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant whitespace-pre-line">{{ $invoice->internal_notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Payment history --}}
            @if ($invoice->payments->isNotEmpty())
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle">
                    <h3 class="font-headline-md text-headline-md text-primary font-bold">Payment History</h3>
                </div>
                <div class="divide-y divide-[#F1F5F9]">
                    @foreach ($invoice->payments as $pmt)
                        @php $pmtColor = match($pmt->status) { 'confirmed' => '#059669', 'pending' => '#F59E0B', 'failed' => '#EF4444', default => '#94A3B8' }; @endphp
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <p class="font-label-md text-label-md font-semibold text-on-surface">
                                    {{ $pmt->currency }} {{ number_format((float) $pmt->amount, 2) }}
                                </p>
                                <p class="font-label-md text-[10px] text-outline mt-0.5">
                                    {{ $pmt->providerLabel() }}
                                    @if ($pmt->paid_at) &middot; {{ $pmt->paid_at->format('d M Y') }} @endif
                                    @if ($pmt->confirmedBy && $canViewInternal) &middot; Confirmed by {{ $pmt->confirmedBy->name }} @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                  style="background:{{ $pmtColor }}18;color:{{ $pmtColor }};">
                                {{ $pmt->statusLabel() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-4">
                <h3 class="font-label-md text-label-md text-outline uppercase tracking-wider mb-3">Invoice Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-outline">Number</dt>
                        <dd class="font-mono font-semibold text-on-surface text-xs">{{ $invoice->invoice_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-outline">Currency</dt>
                        <dd class="font-semibold text-on-surface">{{ $invoice->currency }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-outline">Issued</dt>
                        <dd class="font-semibold text-on-surface">{{ $invoice->issue_date->format('d M Y') }}</dd>
                    </div>
                    @if ($invoice->due_date)
                        <div class="flex justify-between">
                            <dt class="text-outline">Due</dt>
                            <dd class="font-semibold text-on-surface">{{ $invoice->due_date->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if ($invoice->paid_at)
                        <div class="flex justify-between">
                            <dt class="text-outline">Paid</dt>
                            <dd class="font-semibold text-status-active">{{ $invoice->paid_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if ($invoice->subscription?->billingPlan)
                        <div class="flex justify-between">
                            <dt class="text-outline">Plan</dt>
                            <dd class="font-semibold text-on-surface text-right max-w-[120px]">{{ $invoice->subscription->billingPlan->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Payment instruction placeholder for clients --}}
            @if ($isClient && in_array($invoice->status, ['issued', 'overdue', 'partially_paid']))
                <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-4"
                     style="border-color:rgba(0,88,190,0.2);">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size:18px;">info</span>
                        <p class="font-label-md text-label-md font-semibold text-secondary">Payment Instructions</p>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        To make a payment, please contact your dedicated GVOS account manager.
                        Provide your invoice number when making payment.
                    </p>
                </div>
            @endif

            <a href="{{ route('workspace.billing.index', $workspace) }}"
               class="flex items-center gap-2 text-sm hover:text-secondary transition-colors"
               style="color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Back to Billing
            </a>

        </div>
    </div>

</x-layouts.gvos>
