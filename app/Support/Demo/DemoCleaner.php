<?php

namespace App\Support\Demo;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LeadRequest;
use App\Models\Payment;
use App\Models\PriceEstimate;
use App\Models\Trial;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceSubscription;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskComment;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceVaultAccessLog;
use App\Models\WorkspaceVaultItem;
use App\Models\WorkspaceWeeklyReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Phase 27 — Safe removal of CONTROLLED GVOS demo data.
 *
 * Two scopes:
 *
 *   contentScope() / deleteContent()
 *     Operational content inside the controlled demo workspaces (tasks, messages,
 *     files, time logs, reports, vault items, invitations, demo invoices/payments,
 *     demo-user notifications). Used by `gvos:demo-setup` to rebuild cleanly.
 *
 *   fullScope() / deleteAll()
 *     Everything in contentScope() PLUS subscriptions, members, workspaces,
 *     companies, billing plans, the demo lead/estimate/trial, and the 12 demo
 *     user accounts with their profiles. Used by `gvos:demo-clean --execute`.
 *
 * HARD SAFETY RULES
 *   – Every deletion is anchored to DemoDefinition's exact identifiers.
 *     A user is only deleted if its email is one of the 12 exact demo emails.
 *     A workspace is only deleted if its code is one of the 4 exact demo codes.
 *   – TRUNCATE is never used. migrate:fresh is never used.
 *   – audit_logs are never deleted (immutable; FKs null out on user delete).
 *   – Physical file removal is limited to storage/app/private/workspaces/{demo_id}/.
 *   – Nothing here matches on loose words such as "test".
 */
class DemoCleaner
{
    /** @var int[] */
    private array $workspaceIds;

    /** @var int[] */
    private array $userIds;

    /** @var int[] */
    private array $taskIds;

    /** @var int[] */
    private array $invoiceIds;

    public function __construct()
    {
        $this->refresh();
    }

    /** Re-resolve the controlled record IDs from the database. */
    public function refresh(): void
    {
        $this->workspaceIds = DemoDefinition::existingWorkspaceIds();
        $this->userIds      = DemoDefinition::existingUserIds();
        $this->invoiceIds   = DemoDefinition::existingInvoiceIds();
        $this->taskIds      = $this->workspaceIds === []
            ? []
            : WorkspaceTask::withTrashed()
                ->whereIn('workspace_id', $this->workspaceIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
    }

    // ── Counting (dry-run) ───────────────────────────────────────────────────

    /** @return array<string,int> Content-only record counts. */
    public function contentScope(): array
    {
        return [
            'task comments'   => $this->taskIds === [] ? 0
                : WorkspaceTaskComment::withTrashed()->whereIn('workspace_task_id', $this->taskIds)->count(),
            'workspace files' => $this->inWorkspaces(WorkspaceFile::withTrashed()),
            'time logs'       => $this->inWorkspaces(WorkspaceTimeLog::withTrashed()),
            'weekly reports'  => $this->inWorkspaces(WorkspaceWeeklyReport::withTrashed()),
            'chat messages'   => $this->inWorkspaces(WorkspaceMessage::withTrashed()),
            'tasks'           => $this->inWorkspaces(WorkspaceTask::withTrashed()),
            'vault access logs' => $this->inWorkspaces(WorkspaceVaultAccessLog::query()),
            'vault items'     => $this->inWorkspaces(WorkspaceVaultItem::withTrashed()),
            'invitations'     => $this->inWorkspaces(WorkspaceInvitation::query()),
            'payments'        => Payment::withTrashed()
                ->where('payment_reference', 'like', DemoDefinition::PAYMENT_REFERENCE_PREFIX . '%')
                ->count(),
            'invoice items'   => $this->invoiceIds === [] ? 0
                : InvoiceItem::whereIn('invoice_id', $this->invoiceIds)->count(),
            'invoices'        => count($this->invoiceIds),
            'notifications'   => $this->userIds === [] ? 0
                : DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->whereIn('notifiable_id', $this->userIds)
                    ->count(),
        ];
    }

    /** @return array<string,int> Full removal record counts. */
    public function fullScope(): array
    {
        $lead = DemoDefinition::existingLeadRequest();

        return array_merge($this->contentScope(), [
            'subscriptions'     => $this->inWorkspaces(WorkspaceSubscription::withTrashed()),
            'workspace members' => $this->inWorkspaces(WorkspaceMember::query()),
            'workspaces'        => count($this->workspaceIds),
            'billing plans'     => DB::table('billing_plans')
                ->whereIn('code', DemoDefinition::billingPlanCodes())
                ->count(),
            'trials'            => Trial::where('trial_code', DemoDefinition::TRIAL_CODE)->count(),
            'price estimates'   => $lead ? PriceEstimate::where('lead_request_id', $lead->id)->count() : 0,
            'lead requests'     => $lead ? 1 : 0,
            'companies'         => count(DemoDefinition::existingCompanies()),
            'users'             => count($this->userIds),
        ]);
    }

    // ── Deleting ─────────────────────────────────────────────────────────────

    /**
     * Delete operational demo content only. Users, companies, workspaces,
     * memberships, subscriptions and billing plans are preserved so that IDs
     * and logins stay stable across `gvos:demo-setup` runs.
     *
     * @return array<string,int> counts actually deleted
     */
    public function deleteContent(): array
    {
        $this->refresh();
        $deleted = [];

        // 1 — Task comments (reference tasks)
        $deleted['task comments'] = $this->taskIds === [] ? 0
            : WorkspaceTaskComment::withTrashed()->whereIn('workspace_task_id', $this->taskIds)->forceDelete();

        // 2 — Files: physical bytes first, then DB rows
        $deleted['workspace files'] = $this->deleteWorkspaceFiles();

        // 3 — Time logs and weekly reports (may reference tasks)
        $deleted['time logs']      = $this->forceDeleteIn(WorkspaceTimeLog::withTrashed());
        $deleted['weekly reports'] = $this->forceDeleteIn(WorkspaceWeeklyReport::withTrashed());

        // 4 — Chat messages (self-referential parent_id: replies first)
        $deleted['chat messages'] = $this->deleteMessages();

        // 5 — Tasks (now free of dependants)
        $deleted['tasks'] = $this->forceDeleteIn(WorkspaceTask::withTrashed());

        // 6 — Vault: access logs before items
        $deleted['vault access logs'] = $this->workspaceIds === [] ? 0
            : WorkspaceVaultAccessLog::whereIn('workspace_id', $this->workspaceIds)->delete();
        $deleted['vault items'] = $this->forceDeleteIn(WorkspaceVaultItem::withTrashed());

        // 7 — Invitations
        $deleted['invitations'] = $this->workspaceIds === [] ? 0
            : WorkspaceInvitation::whereIn('workspace_id', $this->workspaceIds)->delete();

        // 8 — Billing documents: payments → invoice items → invoices
        $deleted['payments'] = Payment::withTrashed()
            ->where('payment_reference', 'like', DemoDefinition::PAYMENT_REFERENCE_PREFIX . '%')
            ->forceDelete();

        $deleted['invoice items'] = 0;
        $deleted['invoices']      = 0;
        if ($this->invoiceIds !== []) {
            // Delete items via query builder so the InvoiceItem::deleted hook
            // (which recalculates and re-saves the parent invoice) does not run
            // against invoices that are about to be removed.
            $deleted['invoice items'] = DB::table('invoice_items')
                ->whereIn('invoice_id', $this->invoiceIds)
                ->delete();

            $deleted['invoices'] = Invoice::withTrashed()
                ->whereIn('id', $this->invoiceIds)
                ->forceDelete();
        }

        // 9 — Database notifications belonging to the demo users
        $deleted['notifications'] = $this->userIds === [] ? 0
            : DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $this->userIds)
                ->delete();

        $this->refresh();

        return $deleted;
    }

    /**
     * Delete everything controlled: content, then structure, then accounts.
     *
     * @return array<string,int> counts actually deleted
     */
    public function deleteAll(): array
    {
        $deleted = $this->deleteContent();

        // 10 — Subscriptions and memberships
        $deleted['subscriptions']     = $this->forceDeleteIn(WorkspaceSubscription::withTrashed());
        $deleted['workspace members'] = $this->workspaceIds === [] ? 0
            : WorkspaceMember::whereIn('workspace_id', $this->workspaceIds)->delete();

        // 11 — Workspaces (clears the physical demo storage directories too)
        $this->removeWorkspaceDirectories();
        $deleted['workspaces'] = $this->workspaceIds === [] ? 0
            : Workspace::withTrashed()->whereIn('id', $this->workspaceIds)->forceDelete();

        // 12 — Lead pipeline: trial → price estimates → lead request
        $deleted['trials'] = Trial::where('trial_code', DemoDefinition::TRIAL_CODE)->delete();

        $lead = DemoDefinition::existingLeadRequest();
        if ($lead) {
            $deleted['price estimates'] = PriceEstimate::where('lead_request_id', $lead->id)->delete();
            $deleted['lead requests']   = LeadRequest::withTrashed()->whereKey($lead->id)->forceDelete();
        } else {
            $deleted['price estimates'] = 0;
            $deleted['lead requests']   = 0;
        }

        // 13 — Billing plans (exact demo codes only)
        $deleted['billing plans'] = DB::table('billing_plans')
            ->whereIn('code', DemoDefinition::billingPlanCodes())
            ->delete();

        // 14 — Companies (exact demo names only)
        $companyIds = DemoDefinition::existingCompanies()->pluck('id')->all();
        $deleted['companies'] = $companyIds === [] ? 0
            : Company::withTrashed()->whereIn('id', $companyIds)->forceDelete();

        // 15 — Demo users. Role/permission pivots and profile rows are removed
        //      first; audit_logs are preserved (user_id nulls out on delete).
        $deleted['users'] = $this->deleteUsers();

        $this->refresh();

        return $deleted;
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function inWorkspaces($query): int
    {
        if ($this->workspaceIds === []) {
            return 0;
        }

        return (int) $query->whereIn('workspace_id', $this->workspaceIds)->count();
    }

    private function forceDeleteIn($query): int
    {
        if ($this->workspaceIds === []) {
            return 0;
        }

        return (int) $query->whereIn('workspace_id', $this->workspaceIds)->forceDelete();
    }

    /**
     * Chat messages are self-referential (parent_id). Delete replies before roots
     * so the foreign key never blocks the removal.
     */
    private function deleteMessages(): int
    {
        if ($this->workspaceIds === []) {
            return 0;
        }

        $replies = WorkspaceMessage::withTrashed()
            ->whereIn('workspace_id', $this->workspaceIds)
            ->whereNotNull('parent_id')
            ->forceDelete();

        $roots = WorkspaceMessage::withTrashed()
            ->whereIn('workspace_id', $this->workspaceIds)
            ->forceDelete();

        return (int) $replies + (int) $roots;
    }

    /**
     * Remove file rows for the demo workspaces and the physical bytes they point at.
     * Only paths inside workspaces/{demo_workspace_id}/ are touched.
     */
    private function deleteWorkspaceFiles(): int
    {
        if ($this->workspaceIds === []) {
            return 0;
        }

        $files = WorkspaceFile::withTrashed()
            ->whereIn('workspace_id', $this->workspaceIds)
            ->get();

        foreach ($files as $file) {
            $expectedPrefix = 'workspaces/' . $file->workspace_id . '/';

            if (! str_starts_with((string) $file->storage_path, $expectedPrefix)) {
                // Defensive: never delete bytes outside the demo workspace folder.
                continue;
            }

            try {
                if (Storage::disk('local')->exists($file->storage_path)) {
                    Storage::disk('local')->delete($file->storage_path);
                }
            } catch (\Throwable) {
                // A missing/locked file must not abort the cleanup.
            }
        }

        return (int) WorkspaceFile::withTrashed()
            ->whereIn('workspace_id', $this->workspaceIds)
            ->forceDelete();
    }

    /** Remove the private storage directory of each controlled demo workspace. */
    private function removeWorkspaceDirectories(): void
    {
        foreach ($this->workspaceIds as $id) {
            try {
                Storage::disk('local')->deleteDirectory('workspaces/' . $id);
            } catch (\Throwable) {
                // Never abort cleanup because of a filesystem quirk.
            }
        }
    }

    /**
     * Delete only the 12 exact demo accounts. Profiles cascade on user delete;
     * Spatie role pivots have no FK, so they are cleared explicitly.
     */
    private function deleteUsers(): int
    {
        if ($this->userIds === []) {
            return 0;
        }

        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereIn('model_id', $this->userIds)
            ->delete();

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->whereIn('model_id', $this->userIds)
            ->delete();

        // Email addresses are the authoritative anchor — re-assert them here so a
        // stale ID can never widen the deletion beyond the controlled set.
        return (int) User::whereIn('id', $this->userIds)
            ->whereIn('email', DemoDefinition::userEmails())
            ->delete();
    }
}
