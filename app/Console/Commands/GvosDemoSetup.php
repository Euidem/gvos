<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Support\Demo\DemoBuilder;
use App\Support\Demo\DemoDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * php artisan gvos:demo-setup
 *
 * Phase 27 — Creates or refreshes the controlled GVOS demo environment.
 *
 * Idempotent. Safe to run repeatedly:
 *   – demo users, companies, workspaces, memberships, billing plans and
 *     subscriptions are created/updated in place (stable IDs and logins);
 *   – demo operational content is removed and rebuilt so counts stay stable.
 *
 * PASSWORD HANDLING
 *   The shared temporary password is supplied at RUNTIME. It is prompted for
 *   with a hidden input when `--password` is not given. It is hashed
 *   immediately, never written to logs, never echoed, and never stored in the
 *   repository. `--password` exists only for non-interactive deployment; be
 *   aware it will appear in your shell history if you use it.
 *
 * SAFETY
 *   – The mail transport is forced to `array` for the duration of the command,
 *     so no email can be sent while seeding.
 *   – Notifications are raised on the `database` channel only.
 *   – No payment gateway, webhook, or other external service is contacted.
 *   – Nothing outside the controlled demo definition is created or modified.
 */
class GvosDemoSetup extends Command
{
    protected $signature = 'gvos:demo-setup
                            {--password= : Shared temporary password (non-interactive use only; appears in shell history)}
                            {--force : Skip the confirmation prompt (non-interactive deployment)}';

    protected $description = 'Create or refresh the controlled GVOS demo environment (idempotent).';

    public function handle(): int
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' GVOS DEMO SETUP');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->line(' Creates/refreshes ' . count(DemoDefinition::USERS) . ' demo accounts, '
            . count(DemoDefinition::COMPANIES) . ' companies and '
            . count(DemoDefinition::WORKSPACES) . ' workspaces, plus tasks, chat,');
        $this->line(' files, time logs, reports, billing, vault items, invitations and');
        $this->line(' notifications. Only controlled demo records are touched.');
        $this->newLine();

        if (! $this->confirmRun()) {
            $this->warn('Aborted. Nothing was changed.');

            return self::SUCCESS;
        }

        $password = $this->resolvePassword();

        if ($password === null) {
            $this->error('No password supplied. Aborted — nothing was changed.');

            return self::FAILURE;
        }

        // Hash immediately and discard the plaintext from this scope.
        $hashed = Hash::make($password);
        $password = null;
        unset($password);

        $this->suppressOutboundMail();

        $summary = [];

        try {
            DB::transaction(function () use ($hashed, &$summary) {
                $summary = (new DemoBuilder($hashed))->build();
            });
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Demo setup failed and was rolled back:');
            $this->error('  ' . $e->getMessage());
            $this->line('  ' . $e->getFile() . ':' . $e->getLine());

            return self::FAILURE;
        }

        // Audit the run. The password is never part of the audit context.
        AuditLogger::log('gvos_demo.setup_ran', null, [
            'summary' => $summary,
            'source'  => 'artisan gvos:demo-setup',
        ]);

        $this->renderSummary($summary);

        return self::SUCCESS;
    }

    // ── Confirmation ─────────────────────────────────────────────────────────

    private function confirmRun(): bool
    {
        if ($this->option('force') || ! $this->input->isInteractive()) {
            return true;
        }

        return $this->confirm('Create or refresh the controlled GVOS demo environment now?', true);
    }

    // ── Password ─────────────────────────────────────────────────────────────

    private function resolvePassword(): ?string
    {
        $supplied = $this->option('password');

        if (is_string($supplied) && $supplied !== '') {
            if (mb_strlen($supplied) < 8) {
                $this->error('The supplied password is shorter than 8 characters.');

                return null;
            }

            $this->warn('Using --password. Remember that this value is visible in your shell history.');

            return $supplied;
        }

        if (! $this->input->isInteractive()) {
            $this->error('No --password given and the terminal is not interactive.');

            return null;
        }

        $this->line(' Enter the shared temporary password for all demo accounts.');
        $this->line(' Input is hidden. The value is hashed immediately and never logged.');
        $this->newLine();

        $first = $this->secret('Temporary demo password');

        if (! is_string($first) || mb_strlen($first) < 8) {
            $this->error('Password must be at least 8 characters.');

            return null;
        }

        $confirm = $this->secret('Confirm temporary demo password');

        if ($first !== $confirm) {
            $this->error('Passwords did not match.');

            return null;
        }

        return $first;
    }

    // ── Mail suppression ─────────────────────────────────────────────────────

    /**
     * Force the mail transport to `array` for this process so that nothing can
     * leave the server while seeding, regardless of the configured MAIL_MAILER.
     */
    private function suppressOutboundMail(): void
    {
        config([
            'mail.default'          => 'array',
            'mail.mailers.array'    => ['transport' => 'array'],
        ]);

        $this->line(' Outbound mail suppressed for this run (transport forced to "array").');
    }

    // ── Output ───────────────────────────────────────────────────────────────

    private function renderSummary(array $summary): void
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' DEMO ENVIRONMENT READY');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->newLine();

        $rows = [];
        foreach ($summary as $key => $count) {
            $rows[] = [str_replace('_', ' ', $key), (string) $count];
        }
        $this->table(['Created / refreshed', 'Count'], $rows);

        $this->newLine();
        $this->line('<options=bold>Demo accounts</> — all share the password you just supplied.');

        $accountRows = [];
        foreach (DemoDefinition::USERS as $spec) {
            $accountRows[] = [$spec['label'], $spec['name'], $spec['email'], $spec['role'], $spec['status']];
        }
        $this->table(['Scenario', 'Name', 'Email', 'Platform role', 'Account status'], $accountRows);

        $this->newLine();
        $this->line('<options=bold>Demo workspaces</>');
        $this->table(
            ['Code', 'Name', 'Scenario'],
            [
                ['DEMO-EXEC-001', 'Executive Support Operations', 'Active · bi-weekly billing · individual client'],
                ['DEMO-CX-002', 'Customer Experience Support', 'Active · monthly billing · business client + observer'],
                ['DEMO-RESEARCH-003', 'Market Research Sprint', 'Trial subscription · active lead'],
                ['DEMO-RESTRICTED-004', 'Finance Operations Support', 'Billing-restricted · overdue invoice'],
            ]
        );

        $this->newLine();
        $this->line('<options=bold>Scenarios available for testing</>');
        foreach ([
            'Kanban board with pending / in progress / blocked / submitted / revision / approved / closed tasks',
            'Workspace chat with public and internal (manager-only) messages',
            'Private file library: one internal-only file, client-visible files, one task attachment, one PDF',
            'Time logs in draft, submitted, approved and rejected states (approved client summaries visible to clients)',
            'Weekly reports: published (client-visible) and draft (internal only)',
            'Billing: paid invoice, invoice due soon, overdue invoice, confirmed payment, pending payment',
            'Billing restriction: DEMO-RESTRICTED-004 blocks client access but not internal staff',
            'Password vault: talent-accessible item and manager-only item',
            'Invitations in pending, accepted, revoked and expired states',
            'Suspended account flow via suspended.demo@gvos.test',
        ] as $line) {
            $this->line('   • ' . $line);
        }

        $this->newLine();
        $this->warn(' No running timer was seeded. A seeded running timer would occupy the');
        $this->warn(' one-timer-per-user slot and block testers from starting their own.');
        $this->newLine();
        $this->line(' No email was sent. No payment provider was contacted.');
        $this->line(' The password is not stored anywhere in the repository or in logs.');
        $this->newLine();
        $this->line(' Next: php artisan gvos:demo-verify');
        $this->newLine();
    }
}
