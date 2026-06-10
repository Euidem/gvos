<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceBillingController extends Controller
{
    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve workspace role and abort 403 if the user has no access.
     */
    private function requireAccess(Request $request, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($request->user());

        if ($role === 'none') {
            abort(403, 'You do not have access to this workspace.');
        }

        return $role;
    }

    /**
     * Returns true for roles that may view billing information.
     * Talent does NOT see billing.
     */
    private function canViewBilling(string $role): bool
    {
        return in_array($role, [
            'admin', 'workspace_admin', 'manager',
            'client_admin', 'client_staff', 'client',
        ], true);
    }

    /**
     * Returns true for admin/manager roles that see internal notes.
     */
    private function canViewInternalNotes(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * Workspace billing overview.
     */
    public function index(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canViewBilling($role)) {
            abort(403, 'You do not have access to billing information.');
        }

        $subscription = $workspace->activeSubscription;
        $recentInvoices = $workspace->invoices()
            ->with(['subscription'])
            ->whereNotIn('status', ['void'])
            ->limit(10)
            ->get();

        $recentPayments = $workspace->payments()
            ->with(['confirmedBy'])
            ->limit(5)
            ->get();

        // Outstanding balance
        $outstandingBalance = $workspace->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        $canViewInternal = $this->canViewInternalNotes($role);
        $isClient = in_array($role, ['client_admin', 'client_staff', 'client'], true);

        return view('workspace.billing.index', compact(
            'workspace', 'subscription', 'recentInvoices', 'recentPayments',
            'outstandingBalance', 'role', 'canViewInternal', 'isClient'
        ));
    }

    /**
     * Show a single invoice.
     */
    public function showInvoice(Request $request, Workspace $workspace, Invoice $invoice)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canViewBilling($role)) {
            abort(403, 'You do not have access to billing information.');
        }

        // Verify invoice belongs to this workspace
        if ((int) $invoice->workspace_id !== (int) $workspace->id) {
            abort(404, 'Invoice not found in this workspace.');
        }

        // Clients cannot see void invoices
        $isClient = in_array($role, ['client_admin', 'client_staff', 'client'], true);
        if ($isClient && $invoice->status === 'void') {
            abort(403, 'This invoice is not available.');
        }

        $invoice->load(['items', 'payments.confirmedBy', 'subscription.billingPlan']);
        $canViewInternal = $this->canViewInternalNotes($role);

        return view('workspace.billing.show-invoice', compact(
            'workspace', 'invoice', 'role', 'canViewInternal', 'isClient'
        ));
    }

    /**
     * Phase 18: Restricted access landing page.
     * Always accessible — this is the page clients land on when their workspace is
     * restricted or suspended. They must be able to see what they owe and who to contact.
     */
    public function restricted(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        $subscription = $workspace->activeSubscription;

        $outstandingBalance = $workspace->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        $latestUnpaidInvoice = $workspace->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->orderByDesc('issue_date')
            ->first();

        $isSuspended = $subscription && $subscription->isSuspended();

        return view('workspace.billing.restricted', compact(
            'workspace', 'subscription', 'outstandingBalance',
            'latestUnpaidInvoice', 'isSuspended', 'role'
        ));
    }

    /**
     * Workspace payment history.
     */
    public function payments(Request $request, Workspace $workspace)
    {
        $role = $this->requireAccess($request, $workspace);

        if (! $this->canViewBilling($role)) {
            abort(403, 'You do not have access to billing information.');
        }

        $payments = $workspace->payments()
            ->with(['invoice', 'confirmedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $canViewInternal = $this->canViewInternalNotes($role);
        $isClient = in_array($role, ['client_admin', 'client_staff', 'client'], true);

        return view('workspace.billing.payments', compact(
            'workspace', 'payments', 'role', 'canViewInternal', 'isClient'
        ));
    }
}
