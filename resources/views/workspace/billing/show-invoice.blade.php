<x-layouts.gvos :title="$invoice->invoice_number">
    @php
        $statusColor = match ($invoice->status) {
            'draft' => '#94A3B8',
            'issued' => '#0058be',
            'partially_paid' => '#F59E0B',
            'paid' => '#059669',
            'overdue' => '#EF4444',
            'cancelled' => '#64748B',
            'void' => '#64748B',
            default => '#94A3B8',
        };

        $paymentStatusColor = function (string $status): string {
            return match ($status) {
                'confirmed' => '#059669',
                'pending' => '#F59E0B',
                'failed' => '#EF4444',
                'reversed' => '#8B5CF6',
                'cancelled' => '#94A3B8',
                default => '#94A3B8',
            };
        };

        $clientProfile = $invoice->clientProfile ?? $workspace->clientProfile;
        $company = $invoice->company ?? $workspace->company ?? $clientProfile?->company;
        $clientName = $clientProfile?->user?->name
            ?? $company?->primary_contact_name
            ?? $company?->name
            ?? $workspace->name;
        $clientEmail = $clientProfile?->user?->email ?? $company?->primary_contact_email;
        $subscription = $invoice->subscription;
    @endphp

    <style>
        @media print {
            aside,
            header,
            .invoice-print-hide {
                display: none !important;
            }

            main {
                margin-left: 0 !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .invoice-print-surface {
                box-shadow: none !important;
            }
        }
    </style>

    <div class="space-y-6 invoice-print-surface">
        <section class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="font-headline-lg text-headline-lg text-primary">GVOS Invoice</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                              style="background:{{ $statusColor }}18;color:{{ $statusColor }};">
                            {{ $invoice->statusLabel() }}
                        </span>
                    </div>
                    <p class="mt-2 font-mono text-sm font-semibold text-on-surface">{{ $invoice->invoice_number }}</p>
                    <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">
                        {{ $workspace->name }}@if ($workspace->workspace_code) - {{ $workspace->workspace_code }}@endif
                    </p>
                </div>

                <div class="invoice-print-hide flex flex-col sm:flex-row gap-2">
                    <button type="button"
                            onclick="window.print()"
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-border-subtle px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined" style="font-size: 18px;">print</span>
                        Print
                    </button>
                    <a href="{{ route('workspace.billing.index', $workspace) }}"
                       class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-on-secondary transition-all"
                       style="background:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                        Back to Billing
                    </a>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <p class="font-label-md text-label-md text-outline uppercase">Bill To</p>
                    <h3 class="mt-2 font-headline-md text-headline-md text-primary">{{ $clientName }}</h3>
                    @if ($clientEmail)
                        <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">{{ $clientEmail }}</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-outline">Workspace</dt>
                            <dd class="text-right font-semibold text-on-surface">{{ $workspace->name }}</dd>
                        </div>
                        @if ($company)
                            <div class="flex justify-between gap-4">
                                <dt class="text-outline">Company</dt>
                                <dd class="text-right font-semibold text-on-surface">{{ $company->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <p class="font-label-md text-label-md text-outline uppercase">Invoice Details</p>
                    <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="font-label-md text-label-md text-outline">Issue Date</dt>
                            <dd class="mt-1 font-semibold text-on-surface">{{ $invoice->issue_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="font-label-md text-label-md text-outline">Due Date</dt>
                            <dd class="mt-1 font-semibold {{ $invoice->status === 'overdue' ? 'text-status-blocked' : 'text-on-surface' }}">
                                {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Not set' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-label-md text-label-md text-outline">Subscription</dt>
                            <dd class="mt-1 font-semibold text-on-surface">
                                {{ $subscription?->billingPlan?->name ?? 'Custom billing' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-label-md text-label-md text-outline">Billing Cycle</dt>
                            <dd class="mt-1 font-semibold text-on-surface">
                                {{ $subscription ? $subscription->cycleLabel() : 'Not linked' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-label-md text-label-md text-outline">Currency</dt>
                            <dd class="mt-1 font-semibold text-on-surface">{{ $invoice->currency }}</dd>
                        </div>
                        @if ($invoice->paid_at)
                            <div>
                                <dt class="font-label-md text-label-md text-outline">Paid Date</dt>
                                <dd class="mt-1 font-semibold text-status-active">{{ $invoice->paid_at->format('d M Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-subtle">
                <h3 class="font-headline-md text-headline-md text-primary">Invoice Items</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Description</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Item Type</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-outline">Quantity</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-outline">Unit Amount</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-outline">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-on-surface">{{ $item->description }}</td>
                                <td class="px-6 py-4 text-on-surface-variant">{{ $item->typeLabel() }}</td>
                                <td class="px-6 py-4 text-right text-on-surface-variant">
                                    {{ rtrim(rtrim(number_format((float) $item->quantity, 4), '0'), '.') }}
                                </td>
                                <td class="px-6 py-4 text-right text-on-surface-variant">
                                    {{ $invoice->currency }} {{ number_format((float) $item->unit_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-on-surface">
                                    {{ $invoice->currency }} {{ number_format((float) $item->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">
                                    No line items are listed for this invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-border-subtle px-6 py-5 flex justify-end">
                <dl class="w-full sm:max-w-md space-y-3">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <dt class="text-outline">Subtotal</dt>
                        <dd class="font-semibold text-on-surface">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <dt class="text-outline">Discount</dt>
                        <dd class="font-semibold text-status-active">- {{ $invoice->currency }} {{ number_format((float) $invoice->discount_amount, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <dt class="text-outline">Tax</dt>
                        <dd class="font-semibold text-on-surface">{{ $invoice->currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-border-subtle pt-3">
                        <dt class="font-headline-md text-headline-md text-primary">Total Amount</dt>
                        <dd class="font-headline-md text-headline-md text-primary">
                            {{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <dt class="text-outline">Amount Paid</dt>
                        <dd class="font-semibold text-status-active">{{ $invoice->currency }} {{ number_format((float) $invoice->amount_paid, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-lg px-4 py-3"
                         style="background:{{ (float) $invoice->balance_due > 0 ? 'rgba(239,68,68,0.06)' : 'rgba(16,185,129,0.06)' }};">
                        <dt class="font-bold text-on-surface">Balance Due</dt>
                        <dd class="font-headline-md text-headline-md {{ (float) $invoice->balance_due > 0 ? 'text-status-blocked' : 'text-status-active' }}">
                            {{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-subtle">
                <h3 class="font-headline-md text-headline-md text-primary">Payment History</h3>
            </div>

            @if ($invoice->payments->isEmpty())
                <div class="p-8 text-center">
                    <p class="font-body-sm text-body-sm text-outline">No payments have been recorded for this invoice.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead>
                            <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Payment Reference</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Provider</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-outline">Amount</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Status</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-outline">Paid At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9]">
                            @foreach ($invoice->payments as $payment)
                                @php $pmtColor = $paymentStatusColor($payment->status); @endphp
                                <tr>
                                    <td class="px-6 py-4 font-mono text-xs text-on-surface">
                                        {{ $payment->payment_reference ?? 'Not assigned' }}
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $payment->providerLabel() }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-on-surface">
                                        {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                              style="background:{{ $pmtColor }}18;color:{{ $pmtColor }};">
                                            {{ $payment->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">
                                        {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : 'Not paid yet' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="bg-white rounded-xl border border-border-subtle shadow-sm p-6">
            <h3 class="font-headline-md text-headline-md text-primary">Notes</h3>
            <div class="mt-4 space-y-5">
                <div>
                    <p class="font-label-md text-label-md text-outline uppercase">Client Visible Notes</p>
                    <p class="mt-2 font-body-sm text-body-sm text-on-surface-variant whitespace-pre-line">
                        {{ $invoice->notes ?: 'No client-visible notes were added to this invoice.' }}
                    </p>
                </div>

                @if ($canViewInternal && $invoice->internal_notes)
                    <div class="border-t border-border-subtle pt-5">
                        <p class="font-label-md text-label-md text-outline uppercase">Internal Notes</p>
                        <p class="mt-2 font-body-sm text-body-sm text-on-surface-variant whitespace-pre-line">
                            {{ $invoice->internal_notes }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        <section class="bg-white rounded-xl border border-border-subtle shadow-sm p-6 invoice-print-hide"
                 style="border-color:rgba(0,88,190,0.2);">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">info</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-primary">Payment Instructions</h3>
                    <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">
                        Online payment is not enabled yet. Please contact your GVOS account manager and include invoice
                        number {{ $invoice->invoice_number }} when making a bank transfer or manual payment.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.gvos>
