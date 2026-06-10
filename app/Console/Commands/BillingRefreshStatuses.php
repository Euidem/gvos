<?php

namespace App\Console\Commands;

use App\Models\WorkspaceSubscription;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * php artisan gvos:billing-refresh-statuses
 *
 * Idempotent command that evaluates every non-cancelled/non-ended subscription
 * and advances it through the billing status lifecycle:
 *
 *   active/trial → payment_due  (once next_billing_date has passed and there are unpaid invoices)
 *   payment_due  → overdue      (once next_billing_date has passed — same pass)
 *   overdue      → restricted   (grace period expired, restricted_at set)
 *
 * Manual suspensions are never touched.
 * Already-correct statuses are skipped (idempotent).
 *
 * Run via scheduler (e.g., daily) or ad-hoc:
 *   php artisan gvos:billing-refresh-statuses
 *   php artisan gvos:billing-refresh-statuses --dry-run
 */
class BillingRefreshStatuses extends Command
{
    protected $signature = 'gvos:billing-refresh-statuses
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Refresh billing subscription statuses: mark payment_due, overdue, restricted as appropriate';

    private NotificationService $notifications;

    private bool $dryRun = false;

    /** Running tally for the final report */
    private array $summary = [
        'evaluated'    => 0,
        'payment_due'  => 0,
        'overdue'      => 0,
        'restricted'   => 0,
        'restored'     => 0,
        'skipped'      => 0,
        'errors'       => 0,
    ];

    public function handle(NotificationService $notificationService): int
    {
        $this->notifications = $notificationService;
        $this->dryRun        = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY-RUN mode — no changes will be written.');
        }

        $this->info('GVOS billing status refresh started at ' . now()->toDateTimeString());

        $subscriptions = WorkspaceSubscription::query()
            ->with(['workspace'])
            ->whereNotIn('status', ['cancelled', 'ended'])
            ->whereNull('suspended_at') // never touch manually suspended subscriptions
            ->get();

        $this->summary['evaluated'] = $subscriptions->count();
        $this->info("Evaluating {$subscriptions->count()} subscription(s)...");

        foreach ($subscriptions as $subscription) {
            $this->processSubscription($subscription);
        }

        $this->printSummary();

        if (! $this->dryRun) {
            AuditLogger::billingStatusRefreshRan($this->summary);
        }

        return self::SUCCESS;
    }

    private function processSubscription(WorkspaceSubscription $subscription): void
    {
        try {
            $now = Carbon::now();
            $workspaceName = $subscription->workspace?->name ?? "sub#{$subscription->id}";

            // ── Check for unpaid invoices on this subscription ────────────────
            $hasUnpaidInvoices = $subscription->workspace_id
                ? \App\Models\Invoice::where('workspace_id', $subscription->workspace_id)
                    ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
                    ->where('balance_due', '>', 0)
                    ->exists()
                : false;

            // ── State: already manually suspended — skip ──────────────────────
            if ($subscription->wasManuallySuspended()) {
                $this->line("  [skip] {$workspaceName} — manually suspended, skipping.");
                $this->summary['skipped']++;
                return;
            }

            // ── State: no unpaid invoices — ensure status is not payment_due/overdue
            if (! $hasUnpaidInvoices) {
                if (in_array($subscription->status, ['payment_due', 'overdue'], true) && $subscription->restricted_at === null) {
                    // All invoices cleared — restore to active
                    if (! $this->dryRun) {
                        $subscription->update([
                            'status'         => 'active',
                            'reactivated_at' => $now,
                        ]);
                        AuditLogger::billingSubscriptionReactivated($subscription, null, ['reason' => 'all_invoices_cleared_by_refresh']);
                        $this->notifications->notifyWorkspaceReactivated($subscription->fresh());
                    }
                    $this->line("  [restored] {$workspaceName} — all invoices cleared, status restored to active.");
                    $this->summary['restored']++;
                } else {
                    $this->summary['skipped']++;
                }
                return;
            }

            // ── From here: has unpaid invoices ───────────────────────────────

            // ── Step 1: Mark restricted if grace period has expired ───────────
            if (
                $subscription->isOverdue()
                && $subscription->restricted_at === null
                && $subscription->shouldBeRestricted()
            ) {
                $this->line("  [restrict] {$workspaceName} — grace period expired, marking restricted.");

                if (! $this->dryRun) {
                    $subscription->update([
                        'restricted_at' => $now,
                    ]);
                    AuditLogger::billingSubscriptionRestricted($subscription->fresh());
                    $this->notifications->notifyWorkspaceRestricted($subscription->fresh());
                }

                $this->summary['restricted']++;
                return;
            }

            // ── Step 2: Mark overdue (next_billing_date passed, was payment_due) ─
            if (
                $subscription->status === 'payment_due'
                && $subscription->next_billing_date
                && $subscription->next_billing_date->isPast()
            ) {
                $graceEnds = $subscription->next_billing_date->addDays(WorkspaceSubscription::GRACE_PERIOD_DAYS);
                $this->line("  [overdue] {$workspaceName} — marking overdue. Grace ends {$graceEnds->format('d M Y')}.");

                if (! $this->dryRun) {
                    $subscription->update([
                        'status'        => 'overdue',
                        'grace_ends_at' => $graceEnds,
                    ]);
                    AuditLogger::billingSubscriptionOverdue($subscription->fresh());
                    $this->notifications->notifyBillingOverdue($subscription->fresh());
                }

                $this->summary['overdue']++;
                return;
            }

            // ── Step 3: Mark payment_due (next_billing_date has passed, was active/trial) ─
            // NOTE: We use notifyBillingOverdue here because the billing date has already
            // passed — "due soon" messaging is only appropriate while the date is upcoming.
            if (
                in_array($subscription->status, ['active', 'trial'], true)
                && $subscription->next_billing_date
                && $subscription->next_billing_date->isPast()
            ) {
                $this->line("  [payment_due] {$workspaceName} — next_billing_date passed, marking payment_due.");

                if (! $this->dryRun) {
                    $subscription->update([
                        'status' => 'payment_due',
                    ]);
                    AuditLogger::billingSubscriptionPaymentDue($subscription->fresh());
                    $this->notifications->notifyBillingOverdue($subscription->fresh());
                }

                $this->summary['payment_due']++;
                return;
            }

            // ── Step 4: Due-soon warning (approaching next_billing_date) ─────
            if (
                in_array($subscription->status, ['active', 'trial'], true)
                && $subscription->isDueSoon()
            ) {
                // Already in active/trial, just send the reminder (no status change)
                $this->line("  [due_soon] {$workspaceName} — payment due soon, sending reminder.");

                if (! $this->dryRun) {
                    $this->notifications->notifyBillingDueSoon($subscription);
                }

                $this->summary['skipped']++; // no status change
                return;
            }

            // ── Already in the correct state ──────────────────────────────────
            $this->summary['skipped']++;

        } catch (\Throwable $e) {
            $workspaceName = $subscription->workspace?->name ?? "sub#{$subscription->id}";
            $this->error("  [error] {$workspaceName}: " . $e->getMessage());

            Log::error('gvos:billing-refresh-statuses error', [
                'subscription_id' => $subscription->id,
                'workspace_id'    => $subscription->workspace_id,
                'error'           => $e->getMessage(),
            ]);

            $this->summary['errors']++;
        }
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('── Billing refresh summary ─────────────────────────────────');
        $this->line("  Evaluated  : {$this->summary['evaluated']}");
        $this->line("  payment_due: {$this->summary['payment_due']}");
        $this->line("  overdue    : {$this->summary['overdue']}");
        $this->line("  restricted : {$this->summary['restricted']}");
        $this->line("  restored   : {$this->summary['restored']}");
        $this->line("  skipped    : {$this->summary['skipped']}");

        if ($this->summary['errors'] > 0) {
            $this->error("  errors     : {$this->summary['errors']}");
        } else {
            $this->line('  errors     : 0');
        }

        if ($this->dryRun) {
            $this->warn('DRY-RUN: no changes written.');
        }

        $this->info('Completed at ' . now()->toDateTimeString());
    }
}
