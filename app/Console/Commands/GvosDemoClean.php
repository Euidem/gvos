<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Support\Demo\DemoCleaner;
use App\Support\Demo\DemoDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan gvos:demo-clean
 *
 * Phase 27 — Removes the controlled GVOS demo data.
 *
 * SAFETY MODEL
 *   – DRY-RUN BY DEFAULT. Nothing is deleted unless `--execute` is passed.
 *   – Interactive runs require an explicit typed confirmation.
 *   – `--force` skips confirmation for non-interactive deployment. It still
 *     requires `--execute`.
 *   – Only records anchored to App\Support\Demo\DemoDefinition are deleted:
 *     the 12 exact demo emails, the 4 exact workspace codes, the 2 exact
 *     company names, the 2 demo plan codes and the demo invoice/payment
 *     reference prefixes. Nothing is matched on loose words like "test".
 *   – TRUNCATE is never used. migrate:fresh is never used.
 *   – audit_logs are never deleted.
 *   – `--content-only` removes demo operational content but keeps the demo
 *     accounts, companies, workspaces and subscriptions in place.
 */
class GvosDemoClean extends Command
{
    protected $signature = 'gvos:demo-clean
                            {--execute : Actually delete. Without this flag the command is a dry run.}
                            {--force : Skip the interactive confirmation (non-interactive deployment only).}
                            {--content-only : Remove demo tasks/chat/files/logs/reports/billing/vault only; keep accounts and workspaces.}';

    protected $description = 'Remove controlled GVOS demo data. Dry run by default; requires --execute to delete.';

    public function handle(): int
    {
        $execute     = (bool) $this->option('execute');
        $contentOnly = (bool) $this->option('content-only');

        $cleaner = new DemoCleaner;
        $scope   = $contentOnly ? $cleaner->contentScope() : $cleaner->fullScope();
        $total   = array_sum($scope);

        $this->renderHeader($execute, $contentOnly);
        $this->renderScope($scope, $total);

        if ($total === 0) {
            $this->newLine();
            $this->info('Nothing to remove. No controlled demo records found.');

            return self::SUCCESS;
        }

        if (! $execute) {
            $this->newLine();
            $this->warn(' DRY RUN — nothing was deleted.');
            $this->line(' Re-run with --execute to remove the records listed above:');
            $this->line('   php artisan gvos:demo-clean --execute');
            $this->newLine();

            return self::SUCCESS;
        }

        if (! $this->confirmDeletion($contentOnly)) {
            $this->warn('Aborted. Nothing was deleted.');

            return self::SUCCESS;
        }

        $deleted = [];

        try {
            DB::transaction(function () use ($cleaner, $contentOnly, &$deleted) {
                $deleted = $contentOnly ? $cleaner->deleteContent() : $cleaner->deleteAll();
            });
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Cleanup failed and was rolled back. No records were removed.');
            $this->error('  ' . $e->getMessage());
            $this->newLine();
            $this->line(' A foreign key error here usually means a demo account is still');
            $this->line(' referenced by a NON-demo record (for example a task created by a');
            $this->line(' demo user inside a real workspace). Resolve that reference first.');

            return self::FAILURE;
        }

        AuditLogger::log('gvos_demo.cleaned', null, [
            'scope'   => $contentOnly ? 'content_only' : 'full',
            'deleted' => $deleted,
            'source'  => 'artisan gvos:demo-clean --execute',
        ]);

        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' DELETED');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->table(
            ['Record type', 'Deleted'],
            array_map(fn ($k, $v) => [$k, (string) $v], array_keys($deleted), array_values($deleted))
        );

        $this->newLine();
        $this->line(' Genuine (non-demo) users, workspaces and records were not touched.');
        $this->line(' Audit logs were preserved.');
        $this->newLine();

        return self::SUCCESS;
    }

    // ── Output ───────────────────────────────────────────────────────────────

    private function renderHeader(bool $execute, bool $contentOnly): void
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' GVOS DEMO CLEAN — ' . ($execute ? 'EXECUTE' : 'DRY RUN')
            . ($contentOnly ? ' (content only)' : ''));
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->line(' Only records anchored to the controlled demo definition are in scope:');
        $this->line('   • ' . count(DemoDefinition::USERS) . ' exact demo emails (@' . DemoDefinition::EMAIL_DOMAIN . ')');
        $this->line('   • ' . count(DemoDefinition::WORKSPACES) . ' exact workspace codes: '
            . implode(', ', DemoDefinition::workspaceCodes()));
        $this->line('   • ' . count(DemoDefinition::COMPANIES) . ' exact company names: '
            . implode(', ', DemoDefinition::companyNames()));
        $this->line('   • billing plan codes: ' . implode(', ', DemoDefinition::billingPlanCodes()));
        $this->line('   • invoices numbered ' . DemoDefinition::INVOICE_NUMBER_PREFIX . '*');
        $this->line('   • payments referenced ' . DemoDefinition::PAYMENT_REFERENCE_PREFIX . '*');
        $this->newLine();
        $this->warn(' Records that merely LOOK like test data (emails containing "test",');
        $this->warn(' other DEMO- workspaces, etc.) are NEVER deleted by this command.');
        $this->warn(' Use `php artisan gvos:demo-audit` to review those separately.');
        $this->newLine();
    }

    private function renderScope(array $scope, int $total): void
    {
        $rows = [];
        foreach ($scope as $type => $count) {
            $rows[] = [$type, (string) $count];
        }
        $rows[] = ['<options=bold>TOTAL</>', '<options=bold>' . $total . '</>'];

        $this->table(['Record type', 'In scope'], $rows);
    }

    // ── Confirmation ─────────────────────────────────────────────────────────

    private function confirmDeletion(bool $contentOnly): bool
    {
        if ($this->option('force')) {
            $this->warn(' --force supplied: skipping interactive confirmation.');

            return true;
        }

        if (! $this->input->isInteractive()) {
            $this->error(' Non-interactive terminal detected. Pass --force to proceed without a prompt.');

            return false;
        }

        $this->newLine();
        if (! $contentOnly) {
            $this->warn(' This will permanently delete the ' . count(DemoDefinition::USERS)
                . ' demo accounts and all of their demo data.');
        }

        $answer = $this->ask('Type DELETE DEMO to confirm');

        return $answer === 'DELETE DEMO';
    }
}
