<x-layouts.gvos :title="$workspace->name . ' — Access Restricted'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.index') }}" class="hover:text-secondary transition-colors">Workspaces</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>{{ $workspace->name }}</span>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Access Restricted</span>
    </div>

    <div class="max-w-2xl mx-auto space-y-6">

        {{-- ── Main restriction card ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden"
             style="border-color:{{ $isSuspended ? '#DC2626' : '#F59E0B' }}40;">

            {{-- Header band --}}
            <div class="px-6 py-4 flex items-center gap-3"
                 style="background:{{ $isSuspended ? 'rgba(220,38,38,0.06)' : 'rgba(245,158,11,0.06)' }};
                        border-bottom:1px solid {{ $isSuspended ? 'rgba(220,38,38,0.15)' : 'rgba(245,158,11,0.15)' }};">
                <span class="material-symbols-outlined flex-shrink-0" style="font-size:24px;color:{{ $isSuspended ? '#DC2626' : '#D97706' }};">
                    {{ $isSuspended ? 'block' : 'lock' }}
                </span>
                <div>
                    <h2 class="text-base font-bold text-on-surface">
                        @if ($isSuspended)
                            Workspace Suspended
                        @else
                            Workspace Access Restricted
                        @endif
                    </h2>
                    <p class="text-xs text-outline mt-0.5">{{ $workspace->name }} &middot; {{ $workspace->workspace_code }}</p>
                </div>
                @php
                    $statusColor = $isSuspended ? '#DC2626' : '#D97706';
                    $statusLabel = $isSuspended ? 'Suspended' : 'Restricted';
                @endphp
                <span class="ml-auto text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0"
                      style="background:{{ $statusColor }}18;color:{{ $statusColor }};">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="px-6 py-5 space-y-5">
                {{-- Message --}}
                <p class="text-sm text-on-surface leading-relaxed">
                    @if ($isSuspended)
                        This workspace has been suspended. Access to workspace features is currently unavailable.
                        Please review your outstanding invoices and contact the GVOS team to restore access.
                    @else
                        Access to workspace features is restricted because an invoice is overdue and the grace period
                        has passed. Please review your outstanding balance to restore full access.
                    @endif
                </p>

                {{-- Outstanding balance --}}
                @if ($outstandingBalance > 0)
                    <div class="rounded-lg px-4 py-3 flex items-center justify-between"
                         style="background:rgba(220,38,38,0.04);border:1px solid rgba(220,38,38,0.15);">
                        <div>
                            <p class="text-xs font-semibold text-outline uppercase tracking-wide">Outstanding Balance</p>
                            @if ($latestUnpaidInvoice)
                                <p class="text-xs text-outline mt-0.5">
                                    Invoice {{ $latestUnpaidInvoice->invoice_number }}
                                    @if ($latestUnpaidInvoice->due_date)
                                        &middot; Due {{ $latestUnpaidInvoice->due_date->format('d M Y') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <p class="text-xl font-bold" style="color:#DC2626;">
                            {{ $subscription?->currency ?? 'USD' }}
                            {{ number_format((float) $outstandingBalance, 2) }}
                        </p>
                    </div>
                @endif

                {{-- Grace period info --}}
                @if ($subscription && $subscription->isRestricted() && $subscription->grace_ends_at)
                    <p class="text-xs text-outline">
                        Grace period ended: {{ $subscription->grace_ends_at->format('d M Y') }}
                    </p>
                @endif

                {{-- Restriction reason (admin note, shown if set) --}}
                @if ($subscription && $subscription->restriction_reason)
                    <div class="rounded-lg px-4 py-3 text-sm text-on-surface-variant"
                         style="background:rgba(100,116,139,0.05);border:1px solid rgba(100,116,139,0.15);">
                        <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-1">Note</p>
                        {{ $subscription->restriction_reason }}
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="{{ route('workspace.billing.index', $workspace) }}"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                       style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:18px;">receipt_long</span>
                        View Billing & Invoices
                    </a>
                    @if ($latestUnpaidInvoice)
                        <a href="{{ route('workspace.billing.invoice', [$workspace, $latestUnpaidInvoice]) }}"
                           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold border transition-all"
                           style="border-color:#0058be;color:#0058be;">
                            <span class="material-symbols-outlined" style="font-size:18px;">description</span>
                            View Invoice
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Support / payment instructions card ───────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-5">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">support_agent</span>
                </div>
                <div>
                    <p class="font-semibold text-sm text-on-surface">Need help?</p>
                    <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                        Online payment is not enabled yet. Please contact your GVOS account manager and
                        include your invoice number when making a payment. Once your payment is confirmed,
                        access will be restored promptly.
                    </p>
                    <p class="text-xs text-outline mt-2">
                        Reference: {{ $workspace->workspace_code }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Back link ──────────────────────────────────────────────────── --}}
        <div class="text-center">
            <a href="{{ route('workspace.index') }}"
               class="text-xs font-semibold text-secondary hover:brightness-110 transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px;">arrow_back</span>
                Back to Workspaces
            </a>
        </div>

    </div>

</x-layouts.gvos>
