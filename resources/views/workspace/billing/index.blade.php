<x-layouts.gvos :title="$workspace->name . ' — Billing'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Billing</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">receipt_long</span>
                Billing &amp; Payments
            </h2>
            <p class="font-body-sm text-body-sm text-outline mt-1">{{ $workspace->workspace_code }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('workspace.billing.payments', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">payments</span>
                Payments
            </a>
            <a href="{{ route('workspace.show', $workspace) }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Workspace
            </a>
        </div>
    </div>

    {{-- ── Session flash ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Subscription status card ─────────────────────────────────────── --}}
    @if ($subscription)
        @php
            $subStatusColors = [
                'trial'       => ['bg' => 'rgba(139,92,246,0.08)', 'text' => '#8B5CF6', 'border' => 'rgba(139,92,246,0.25)'],
                'active'      => ['bg' => 'rgba(16,185,129,0.08)',  'text' => '#10B981', 'border' => 'rgba(16,185,129,0.25)'],
                'payment_due' => ['bg' => 'rgba(245,158,11,0.08)',  'text' => '#F59E0B', 'border' => 'rgba(245,158,11,0.25)'],
                'overdue'     => ['bg' => 'rgba(239,68,68,0.08)',   'text' => '#EF4444', 'border' => 'rgba(239,68,68,0.25)'],
                'suspended'   => ['bg' => 'rgba(100,116,139,0.08)', 'text' => '#64748B', 'border' => 'rgba(100,116,139,0.25)'],
                'cancelled'   => ['bg' => 'rgba(100,116,139,0.08)', 'text' => '#64748B', 'border' => 'rgba(100,116,139,0.25)'],
            ];
            $sc = $subStatusColors[$subscription->status] ?? $subStatusColors['active'];
        @endphp
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Current Subscription</p>
                    <h3 class="font-headline-md text-headline-md text-primary font-bold">
                        {{ $subscription->billingPlan?->name ?? 'Custom Plan' }}
                    </h3>
                    <p class="font-body-sm text-body-sm text-outline mt-0.5">
                        {{ $subscription->formattedAmount() }} / {{ $subscription->cycleLabel() }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold"
                          style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};">
                        {{ $subscription->statusLabel() }}
                    </span>
                </div>
            </div>
            <div class="mt-5 pt-4 border-t border-border-subtle grid grid-cols-2 sm:grid-cols-4 gap-4">
                @if ($subscription->starts_at)
                    <div>
                        <p class="font-label-md text-label-md text-outline mb-0.5">Started</p>
                        <p class="font-label-md text-label-md font-semibold text-on-surface">{{ $subscription->starts_at->format('d M Y') }}</p>
                    </div>
                @endif
                @if ($subscription->next_billing_date)
                    <div>
                        <p class="font-label-md text-label-md text-outline mb-0.5">Next Billing</p>
                        <p class="font-label-md text-label-md font-semibold text-on-surface">{{ $subscription->next_billing_date->format('d M Y') }}</p>
                    </div>
                @endif
                @if ($subscription->last_paid_at)
                    <div>
                        <p class="font-label-md text-label-md text-outline mb-0.5">Last Payment</p>
                        <p class="font-label-md text-label-md font-semibold text-on-surface">{{ $subscription->last_paid_at->format('d M Y') }}</p>
                    </div>
                @endif
                <div>
                    <p class="font-label-md text-label-md text-outline mb-0.5">Outstanding</p>
                    <p class="font-label-md text-label-md font-bold {{ $outstandingBalance > 0 ? 'text-status-blocked' : 'text-status-active' }}">
                        {{ $subscription->currency }} {{ number_format((float) $outstandingBalance, 2) }}
                    </p>
                </div>
            </div>

            {{-- Payment due alert --}}
            @if ($subscription->requiresPayment())
                <div class="mt-4 flex items-start gap-3 p-4 rounded-lg"
                     style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.25);">
                    <span class="material-symbols-outlined flex-shrink-0 text-status-payment-due" style="font-size:20px;">warning</span>
                    <div>
                        <p class="font-label-md text-label-md font-semibold text-on-surface">Payment Required</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                            Your subscription has a payment outstanding. Please contact your GVOS account manager
                            to arrange payment and keep your workspace active.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-8 mb-6 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(0,88,190,0.06);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 26px;">receipt_long</span>
            </div>
            <h4 class="font-headline-md text-headline-md text-on-surface mb-1">No subscription yet</h4>
            <p class="font-body-sm text-body-sm text-outline max-w-xs mx-auto">
                No active billing subscription is configured for this workspace. Contact your GVOS account manager.
            </p>
        </div>
    @endif

    {{-- ── Payment instructions placeholder (no live gateway yet) ── --}}
    @if ($isClient)
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 mb-6"
             style="border-color:rgba(0,88,190,0.2);">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:20px;">info</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md font-semibold text-secondary">Payment Instructions</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                        Online payment is not enabled yet. Please contact your GVOS account manager and include the invoice number when making a bank transfer or manual payment.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Recent invoices ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between">
            <h3 class="font-headline-md text-headline-md text-primary font-bold">Invoices</h3>
        </div>

        @if ($recentInvoices->isEmpty())
            <div class="p-8 text-center">
                <p class="font-body-sm text-body-sm text-outline">No invoices yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Invoice #</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Issue Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Due Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Total</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Balance</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline">Status</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-outline"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach ($recentInvoices as $invoice)
                            @php
                                $invStatusColors = [
                                    'draft'          => ['#94A3B8', '#94A3B8'],
                                    'issued'         => ['#0058be', '#0058be'],
                                    'partially_paid' => ['#F59E0B', '#F59E0B'],
                                    'paid'           => ['#059669', '#059669'],
                                    'overdue'        => ['#EF4444', '#EF4444'],
                                    'cancelled'      => ['#64748B', '#64748B'],
                                    'void'           => ['#64748B', '#64748B'],
                                ];
                                $ic = $invStatusColors[$invoice->status] ?? ['#94A3B8', '#94A3B8'];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-3 font-mono text-xs font-semibold text-on-surface">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">
                                    {{ $invoice->issue_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-xs text-on-surface-variant">
                                    {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-xs font-semibold text-on-surface">
                                    {{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3 text-xs font-semibold {{ $invoice->balance_due > 0 ? 'text-status-blocked' : 'text-status-active' }}">
                                    {{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background:{{ $ic[0] }}18;color:{{ $ic[0] }};">
                                        {{ $invoice->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('workspace.billing.invoice', [$workspace, $invoice]) }}"
                                       class="text-xs font-semibold hover:underline" style="color:#0058be;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Recent payments ──────────────────────────────────────────────── --}}
    @if ($recentPayments->isNotEmpty())
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between">
            <h3 class="font-headline-md text-headline-md text-primary font-bold">Recent Payments</h3>
            <a href="{{ route('workspace.billing.payments', $workspace) }}"
               class="text-xs font-semibold text-secondary hover:underline">View all</a>
        </div>
        <div class="divide-y divide-[#F1F5F9]">
            @foreach ($recentPayments as $pmt)
                @php
                    $pmtColor = match($pmt->status) {
                        'confirmed' => '#059669',
                        'pending'   => '#F59E0B',
                        'failed'    => '#EF4444',
                        default     => '#94A3B8',
                    };
                @endphp
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="font-label-md text-label-md font-semibold text-on-surface">
                            {{ $pmt->currency }} {{ number_format((float) $pmt->amount, 2) }}
                        </p>
                        <p class="font-label-md text-[10px] text-outline mt-0.5">
                            {{ $pmt->providerLabel() }}
                            @if ($pmt->paid_at) &middot; {{ $pmt->paid_at->format('d M Y') }} @endif
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

</x-layouts.gvos>
