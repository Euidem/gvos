{{--
  Phase 18: Billing warning banner partial.

  Variables expected (pass in from parent view or set before @include):
    $__billingWorkspace  — the Workspace model (required)
    $__billingForClient  — bool: true shows client-facing messaging (optional, default false)

  Usage in Blade:
    @php
        $__billingWorkspace = $workspace;
        $__billingForClient = $isClient ?? false;
    @endphp
    @include('partials.billing-banner')

  This partial is a no-op (renders nothing) when:
  - No workspace is provided
  - No active subscription exists
  - Subscription status is active/trial with no due-soon condition
--}}
@php
    $__bws  = $__billingWorkspace ?? null;
    $__bfc  = $__billingForClient ?? false;

    // Resolve subscription without re-querying if already loaded
    $__bsub = $__bws ? $__bws->activeSubscription : null;

    // Determine banner state
    $__bState = 'none'; // none | due_soon | overdue | restricted | suspended

    if ($__bsub) {
        if ($__bsub->isSuspended()) {
            $__bState = 'suspended';
        } elseif ($__bsub->isRestricted()) {
            $__bState = 'restricted';
        } elseif ($__bsub->isOverdue()) {
            $__bState = 'overdue';
        } elseif ($__bsub->isDueSoon()) {
            $__bState = 'due_soon';
        }
    }

    // Check latest unpaid invoice for date display
    $__bInvoice = null;
    if ($__bws && in_array($__bState, ['due_soon', 'overdue', 'restricted', 'suspended'])) {
        $__bInvoice = $__bws->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->orderByDesc('issue_date')
            ->first();
    }
@endphp

@if ($__bState !== 'none' && $__bws)

    @if ($__bState === 'suspended')
        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
             style="background:rgba(100,116,139,0.06);border:1px solid rgba(100,116,139,0.25);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;color:#64748B;">block</span>
            <div class="flex-1">
                <p class="font-semibold text-on-surface">Workspace suspended</p>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    @if ($__bfc)
                        Access to this workspace has been suspended. Please contact GVOS support or complete payment to restore access.
                    @else
                        This workspace is suspended due to an outstanding billing issue. Contact operations to reactivate.
                    @endif
                </p>
            </div>
            @if ($__bfc && $__bws)
                <a href="{{ route('workspace.billing.index', $__bws) }}"
                   class="flex-shrink-0 self-center text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                   style="border-color:#64748B;color:#64748B;">
                    View Billing
                </a>
            @endif
        </div>

    @elseif ($__bState === 'restricted')
        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;color:#DC2626;">lock</span>
            <div class="flex-1">
                <p class="font-semibold text-on-surface">Workspace access restricted</p>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    @if ($__bfc)
                        Access is limited because an invoice is overdue. Please review your billing to restore full workspace access.
                    @else
                        Client access is restricted due to an overdue invoice. Resolve the payment to restore client access.
                    @endif
                    @if ($__bInvoice?->due_date)
                        Due date: {{ $__bInvoice->due_date->format('d M Y') }}.
                    @endif
                </p>
            </div>
            @if ($__bws)
                <a href="{{ route('workspace.billing.index', $__bws) }}"
                   class="flex-shrink-0 self-center text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                   style="border-color:#DC2626;color:#DC2626;">
                    View Billing
                </a>
            @endif
        </div>

    @elseif ($__bState === 'overdue')
        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
             style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.25);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;color:#EF4444;">warning</span>
            <div class="flex-1">
                <p class="font-semibold text-on-surface">Payment overdue</p>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    Please review your invoice to avoid workspace restrictions.
                    @if ($__bInvoice?->due_date)
                        Invoice {{ $__bInvoice->invoice_number }} was due on {{ $__bInvoice->due_date->format('d M Y') }}.
                    @endif
                    @if ($__bsub?->isWithinGracePeriod() && $__bsub->grace_ends_at)
                        Grace period ends {{ $__bsub->grace_ends_at->format('d M Y') }}.
                    @endif
                </p>
            </div>
            @if ($__bws)
                <a href="{{ route('workspace.billing.index', $__bws) }}"
                   class="flex-shrink-0 self-center text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                   style="border-color:#EF4444;color:#EF4444;">
                    View Invoice
                </a>
            @endif
        </div>

    @elseif ($__bState === 'due_soon')
        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
             style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.25);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;color:#D97706;">event</span>
            <div class="flex-1">
                <p class="font-semibold text-on-surface">Payment due soon</p>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    @if ($__bInvoice?->due_date)
                        Invoice {{ $__bInvoice->invoice_number }} is due on {{ $__bInvoice->due_date->format('d M Y') }}.
                    @elseif ($__bsub?->next_billing_date)
                        Your subscription payment is due on {{ $__bsub->next_billing_date->format('d M Y') }}.
                    @else
                        A payment is due soon. Please review your billing.
                    @endif
                </p>
            </div>
            @if ($__bws)
                <a href="{{ route('workspace.billing.index', $__bws) }}"
                   class="flex-shrink-0 self-center text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                   style="border-color:#D97706;color:#D97706;">
                    View Billing
                </a>
            @endif
        </div>

    @endif

@endif
