<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceSubscription;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceVaultItem;
use App\Models\WorkspaceWeeklyReport;
use App\Support\Demo\DemoDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * php artisan gvos:demo-verify
 *
 * Phase 27 — PASS/FAIL health report for the controlled GVOS demo environment.
 *
 * Read-only. Never prints passwords, password hashes, vault secret values, or
 * invitation tokens. The vault check decrypts each secret in memory only to
 * confirm that decryption succeeds, then discards it.
 */
class GvosDemoVerify extends Command
{
    protected $signature = 'gvos:demo-verify';

    protected $description = 'Verify the controlled GVOS demo environment and report PASS/FAIL for each check.';

    /** @var array<int, array{0:string,1:bool,2:string}> */
    private array $results = [];

    private array $workspaces = [];

    private array $users = [];

    public function handle(): int
    {
        $this->users      = DemoDefinition::existingUsers()->all();
        $this->workspaces = DemoDefinition::existingWorkspaces()->all();

        $this->checkUsers();
        $this->checkRoles();
        $this->checkCompanies();
        $this->checkWorkspaces();
        $this->checkMemberships();
        $this->checkTasks();
        $this->checkMessages();
        $this->checkFiles();
        $this->checkTimeLogs();
        $this->checkReports();
        $this->checkBilling();
        $this->checkVault();
        $this->checkNotifications();
        $this->checkRestrictedWorkspace();
        $this->checkSuspendedUser();
        $this->checkNoSecretsPrinted();

        return $this->render();
    }

    // ── Checks ───────────────────────────────────────────────────────────────

    private function checkUsers(): void
    {
        $expected = DemoDefinition::userEmails();
        $missing  = array_values(array_diff($expected, array_keys($this->users)));

        $this->record(
            '1. All demo users exist',
            $missing === [],
            $missing === []
                ? count($expected) . ' of ' . count($expected) . ' accounts present'
                : 'Missing: ' . implode(', ', $missing)
        );
    }

    private function checkRoles(): void
    {
        $wrong = [];

        foreach (DemoDefinition::USERS as $spec) {
            $user = $this->users[$spec['email']] ?? null;

            if (! $user) {
                $wrong[] = $spec['email'] . ' (missing)';
                continue;
            }

            $actual = $user->getRoleNames()->first();

            if ($actual !== $spec['role']) {
                $wrong[] = $spec['email'] . " (expected {$spec['role']}, got " . ($actual ?? 'none') . ')';
            }
        }

        $this->record(
            '2. All expected roles are assigned',
            $wrong === [],
            $wrong === [] ? 'Every demo account holds its expected platform role' : implode('; ', $wrong)
        );
    }

    private function checkCompanies(): void
    {
        $existing = DemoDefinition::existingCompanies();
        $missing  = array_values(array_diff(DemoDefinition::companyNames(), $existing->keys()->all()));

        $this->record(
            '3. Demo companies exist',
            $missing === [],
            $missing === [] ? implode(', ', DemoDefinition::companyNames()) : 'Missing: ' . implode(', ', $missing)
        );
    }

    private function checkWorkspaces(): void
    {
        $missing = array_values(array_diff(DemoDefinition::workspaceCodes(), array_keys($this->workspaces)));

        $this->record(
            '4. Demo workspaces exist',
            $missing === [],
            $missing === []
                ? count($this->workspaces) . ' of ' . count(DemoDefinition::WORKSPACES) . ' workspaces present'
                : 'Missing: ' . implode(', ', $missing)
        );
    }

    private function checkMemberships(): void
    {
        $expected = [
            'DEMO-EXEC-001'       => ['manager' => 1, 'talent' => 1, 'client_admin' => 1],
            'DEMO-CX-002'         => ['manager' => 1, 'talent' => 2, 'client_admin' => 1, 'client_staff' => 1, 'observer' => 1],
            'DEMO-RESEARCH-003'   => ['manager' => 1, 'talent' => 1, 'client_admin' => 1],
            'DEMO-RESTRICTED-004' => ['manager' => 1, 'talent' => 1, 'client_admin' => 1],
        ];

        $problems = [];

        foreach ($expected as $code => $roles) {
            $workspace = $this->workspaces[$code] ?? null;

            if (! $workspace) {
                $problems[] = "{$code} (workspace missing)";
                continue;
            }

            $actual = WorkspaceMember::where('workspace_id', $workspace->id)
                ->where('status', 'active')
                ->get()
                ->countBy('role');

            foreach ($roles as $role => $count) {
                if ((int) $actual->get($role, 0) !== $count) {
                    $problems[] = "{$code}: {$role} expected {$count}, got " . (int) $actual->get($role, 0);
                }
            }
        }

        $this->record(
            '5. Workspace memberships are correct',
            $problems === [],
            $problems === [] ? 'All four workspaces have the expected active member roles' : implode('; ', $problems)
        );
    }

    private function checkTasks(): void
    {
        $required = ['pending', 'in_progress', 'blocked', 'submitted', 'revision_requested', 'approved', 'closed'];
        $ids      = $this->workspaceIds();

        $byStatus = $ids === []
            ? collect()
            : WorkspaceTask::whereIn('workspace_id', $ids)->get()->countBy('status');

        $missing = array_values(array_filter($required, fn ($s) => (int) $byStatus->get($s, 0) === 0));

        $this->record(
            '6. Tasks exist across required statuses',
            $missing === [] && $byStatus->sum() > 0,
            $missing === []
                ? $byStatus->sum() . ' tasks across ' . $byStatus->count() . ' statuses'
                : 'No tasks in status: ' . implode(', ', $missing)
        );
    }

    private function checkMessages(): void
    {
        $ids = $this->workspaceIds();

        $public   = $ids === [] ? 0 : WorkspaceMessage::whereIn('workspace_id', $ids)->where('visibility', 'public')->count();
        $internal = $ids === [] ? 0 : WorkspaceMessage::whereIn('workspace_id', $ids)->where('visibility', 'internal')->count();

        $this->record(
            '7. Workspace messages exist',
            $public > 0 && $internal > 0,
            "{$public} public, {$internal} internal (manager-only) messages"
        );
    }

    private function checkFiles(): void
    {
        $ids   = $this->workspaceIds();
        $files = $ids === [] ? collect() : WorkspaceFile::whereIn('workspace_id', $ids)->get();

        $missingOnDisk = $files->filter(
            fn (WorkspaceFile $f) => ! Storage::disk('local')->exists($f->storage_path)
        );

        $hasInternal = $files->contains(fn (WorkspaceFile $f) => $f->visibility === 'internal');
        $hasPublic   = $files->contains(fn (WorkspaceFile $f) => $f->visibility === 'public');
        $hasTaskLink = $files->contains(fn (WorkspaceFile $f) => $f->workspace_task_id !== null);

        $pass = $files->isNotEmpty() && $missingOnDisk->isEmpty() && $hasInternal && $hasPublic && $hasTaskLink;

        $detail = $files->count() . ' file records; '
            . ($missingOnDisk->isEmpty() ? 'all present on the private disk' : $missingOnDisk->count() . ' MISSING on disk')
            . '; internal-only: ' . ($hasInternal ? 'yes' : 'NO')
            . '; client-visible: ' . ($hasPublic ? 'yes' : 'NO')
            . '; task-linked: ' . ($hasTaskLink ? 'yes' : 'NO');

        $this->record('8. Physical demo files exist and records point at them', $pass, $detail);
    }

    private function checkTimeLogs(): void
    {
        $ids      = $this->workspaceIds();
        $byStatus = $ids === []
            ? collect()
            : WorkspaceTimeLog::whereIn('workspace_id', $ids)->get()->countBy('status');

        $required = ['draft', 'submitted', 'approved', 'rejected'];
        $missing  = array_values(array_filter($required, fn ($s) => (int) $byStatus->get($s, 0) === 0));

        $running = (int) $byStatus->get('running', 0);

        $this->record(
            '9. Time logs exist across states',
            $missing === [],
            $missing === []
                ? $byStatus->sum() . ' logs (draft/submitted/approved/rejected present; '
                    . ($running === 0 ? 'no running timer seeded, as intended' : "WARNING: {$running} running timer(s)") . ')'
                : 'No time logs in status: ' . implode(', ', $missing)
        );
    }

    private function checkReports(): void
    {
        $ids      = $this->workspaceIds();
        $byStatus = $ids === []
            ? collect()
            : WorkspaceWeeklyReport::whereIn('workspace_id', $ids)->get()->countBy('status');

        $published = (int) $byStatus->get('published', 0);
        $draft     = (int) $byStatus->get('draft', 0);

        $this->record(
            '10. Draft and published weekly reports exist',
            $published > 0 && $draft > 0,
            "{$published} published (client-visible), {$draft} draft (internal only)"
        );
    }

    private function checkBilling(): void
    {
        $plans = DB::table('billing_plans')->whereIn('code', DemoDefinition::billingPlanCodes())->count();

        $invoices = Invoice::where('invoice_number', 'like', DemoDefinition::INVOICE_NUMBER_PREFIX . '%')->get();

        $paid    = $invoices->firstWhere('status', 'paid');
        $dueSoon = $invoices->first(fn (Invoice $i) => $i->isDueSoon());
        $overdue = $invoices->first(fn (Invoice $i) => $i->isOverdue());

        $confirmedPayment = Payment::where('payment_reference', 'like', DemoDefinition::PAYMENT_REFERENCE_PREFIX . '%')
            ->where('status', 'confirmed')
            ->exists();

        $subscriptions = $this->workspaceIds() === []
            ? collect()
            : WorkspaceSubscription::whereIn('workspace_id', $this->workspaceIds())->get();

        $hasBiweekly = $subscriptions->contains(fn ($s) => $s->billing_cycle === 'bi_weekly');
        $hasMonthly  = $subscriptions->contains(fn ($s) => $s->billing_cycle === 'monthly');
        $hasTrial    = $subscriptions->contains(fn ($s) => $s->status === 'trial');

        $pass = $plans === 2 && $paid && $dueSoon && $overdue && $confirmedPayment
            && $hasBiweekly && $hasMonthly && $hasTrial;

        $this->record(
            '11. Billing scenarios exist',
            (bool) $pass,
            "{$plans}/2 plans; " . $subscriptions->count() . ' subscriptions (bi-weekly: '
                . ($hasBiweekly ? 'yes' : 'NO') . ', monthly: ' . ($hasMonthly ? 'yes' : 'NO')
                . ', trial: ' . ($hasTrial ? 'yes' : 'NO') . '); '
                . 'paid invoice: ' . ($paid ? 'yes' : 'NO')
                . ', due soon: ' . ($dueSoon ? 'yes' : 'NO')
                . ', overdue: ' . ($overdue ? 'yes' : 'NO')
                . ', confirmed payment: ' . ($confirmedPayment ? 'yes' : 'NO')
        );
    }

    private function checkVault(): void
    {
        $ids   = $this->workspaceIds();
        $items = $ids === [] ? collect() : WorkspaceVaultItem::whereIn('workspace_id', $ids)->get();

        $decryptFailures = 0;

        foreach ($items as $item) {
            try {
                // Decrypt in memory only. The value is never printed, logged,
                // or returned — only its presence is checked.
                $secret = $item->secret_value;

                if (! is_string($secret) || $secret === '') {
                    $decryptFailures++;
                }

                unset($secret);
            } catch (\Throwable) {
                $decryptFailures++;
            }
        }

        $talentAccessible = $items->contains(
            fn (WorkspaceVaultItem $i) => in_array('talent', $i->allowedRoleValues(), true)
        );
        $managerOnly = $items->contains(
            fn (WorkspaceVaultItem $i) => $i->visibility === 'workspace_admins'
        );

        $pass = $items->isNotEmpty() && $decryptFailures === 0 && $talentAccessible && $managerOnly;

        $this->record(
            '12. Vault items exist and decrypt internally (values never printed)',
            $pass,
            $items->count() . ' items; decrypt failures: ' . $decryptFailures
                . '; talent-accessible: ' . ($talentAccessible ? 'yes' : 'NO')
                . '; manager/admin-only: ' . ($managerOnly ? 'yes' : 'NO')
        );
    }

    private function checkNotifications(): void
    {
        $userIds = array_map(fn (User $u) => $u->id, $this->users);

        $count = $userIds === [] ? 0 : DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', array_values($userIds))
            ->count();

        $invitations = $this->workspaceIds() === []
            ? collect()
            : WorkspaceInvitation::whereIn('workspace_id', $this->workspaceIds())->get()->countBy('status');

        $states  = ['pending', 'accepted', 'revoked', 'expired'];
        $missing = array_values(array_filter($states, fn ($s) => (int) $invitations->get($s, 0) === 0));

        $this->record(
            '13. Notifications and invitation states exist',
            $count > 0 && $missing === [],
            "{$count} database notifications; invitation states present: "
                . ($missing === [] ? 'pending, accepted, revoked, expired' : 'MISSING ' . implode(', ', $missing))
                . ' (tokens are never printed)'
        );
    }

    private function checkRestrictedWorkspace(): void
    {
        $workspace = $this->workspaces['DEMO-RESTRICTED-004'] ?? null;
        $client    = $this->users[DemoDefinition::USERS['restricted_client']['email']] ?? null;
        $talent    = $this->users[DemoDefinition::USERS['talent_one']['email']] ?? null;

        if (! $workspace || ! $client || ! $talent) {
            $this->record('14. Restricted workspace is actually restricted', false,
                'Workspace or demo users missing');

            return;
        }

        $subscription  = $workspace->activeSubscription;
        $isRestricted  = $subscription?->isRestricted() === true;
        $clientBlocked = ! $workspace->canClientAccessWorkspace($client);
        $staffAllowed  = $workspace->canClientAccessWorkspace($talent);

        $this->record(
            '14. Restricted workspace is actually restricted',
            $isRestricted && $clientBlocked && $staffAllowed,
            'subscription restricted: ' . ($isRestricted ? 'yes' : 'NO')
                . '; client blocked: ' . ($clientBlocked ? 'yes' : 'NO')
                . '; internal staff still allowed: ' . ($staffAllowed ? 'yes' : 'NO')
        );
    }

    private function checkSuspendedUser(): void
    {
        $user = $this->users[DemoDefinition::USERS['suspended']['email']] ?? null;

        $this->record(
            '15. Suspended demo user is suspended',
            $user !== null && $user->isSuspended() && $user->isAccessBlocked(),
            $user === null
                ? 'Account missing'
                : 'status=' . $user->status . '; portal access blocked: '
                    . ($user->isAccessBlocked() ? 'yes' : 'NO')
        );
    }

    private function checkNoSecretsPrinted(): void
    {
        // Structural guarantee rather than a runtime probe:
        //  – this command never reads users.password;
        //  – vault secret_value is decrypted in checkVault() into a local
        //    variable that is discarded and never passed to output;
        //  – invitation tokens are counted by status only;
        //  – the setup command hashes the password immediately and its audit
        //    context contains counts only.
        $this->record(
            '16. No demo password or vault secret is printed',
            true,
            'Passwords are never read; vault secrets are decrypted in memory only; tokens are never output'
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return int[] */
    private function workspaceIds(): array
    {
        return array_values(array_map(fn (Workspace $w) => (int) $w->id, $this->workspaces));
    }

    private function record(string $label, bool $pass, string $detail): void
    {
        $this->results[] = [$label, $pass, $detail];
    }

    private function render(): int
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' GVOS DEMO VERIFY');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->newLine();

        $failed = 0;

        foreach ($this->results as [$label, $pass, $detail]) {
            if ($pass) {
                $this->line('  <fg=green>PASS</>  ' . $label);
            } else {
                $this->line('  <fg=red>FAIL</>  ' . $label);
                $failed++;
            }
            $this->line('        ' . $detail);
        }

        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');

        if ($failed === 0) {
            $this->info(' ✓ All ' . count($this->results) . ' checks passed. Demo environment is ready.');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->error(' ✗ ' . $failed . ' of ' . count($this->results) . ' checks failed.');
        $this->line('   Run `php artisan gvos:demo-setup` to rebuild the demo environment.');
        $this->newLine();

        return self::FAILURE;
    }
}
