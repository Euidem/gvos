<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\LeadRequest;
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

/**
 * php artisan gvos:demo-audit
 *
 * Phase 27 — READ-ONLY audit of demo/test data.
 *
 * Reports two separate groups:
 *
 *   1. CONTROLLED demo data — records anchored to DemoDefinition (exact emails,
 *      workspace codes, company names, plan codes, invoice/payment prefixes).
 *      These are the ONLY records `gvos:demo-clean` will ever delete.
 *
 *   2. OTHER likely demo/test data — matched by loose heuristics (emails
 *      containing "demo"/"test", workspace codes starting with "DEMO-", company
 *      names starting with "Demo"). These are REPORTED ONLY and are never
 *      deleted by any GVOS command.
 *
 * This command never writes, updates, or deletes anything.
 * It never prints passwords, password hashes, vault secrets, or invitation tokens.
 */
class GvosDemoAudit extends Command
{
    protected $signature = 'gvos:demo-audit
                            {--json : Output a machine-readable JSON summary instead of tables}';

    protected $description = 'Read-only audit of GVOS demo/test data. Never deletes anything.';

    public function handle(): int
    {
        $controlled = $this->collectControlled();
        $heuristic  = $this->collectHeuristic($controlled['user_ids'], $controlled['workspace_ids']);

        if ($this->option('json')) {
            $this->line(json_encode([
                'controlled' => $controlled['counts'],
                'heuristic'  => $heuristic['counts'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->renderControlled($controlled);
        $this->renderHeuristic($heuristic);
        $this->renderSafetyNotice();

        return self::SUCCESS;
    }

    // ── Controlled demo data ─────────────────────────────────────────────────

    private function collectControlled(): array
    {
        $users      = DemoDefinition::existingUsers();
        $companies  = DemoDefinition::existingCompanies();
        $workspaces = DemoDefinition::existingWorkspaces();

        $userIds      = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
        $workspaceIds = $workspaces->pluck('id')->map(fn ($id) => (int) $id)->all();
        $invoiceIds   = DemoDefinition::existingInvoiceIds();

        $counts = [
            'users'          => $users->count(),
            'companies'      => $companies->count(),
            'workspaces'     => $workspaces->count(),
            'members'        => $this->countIn(WorkspaceMember::class, $workspaceIds),
            'tasks'          => $this->countIn(WorkspaceTask::withTrashed(), $workspaceIds),
            'messages'       => $this->countIn(WorkspaceMessage::withTrashed(), $workspaceIds),
            'files'          => $this->countIn(WorkspaceFile::withTrashed(), $workspaceIds),
            'time_logs'      => $this->countIn(WorkspaceTimeLog::withTrashed(), $workspaceIds),
            'weekly_reports' => $this->countIn(WorkspaceWeeklyReport::withTrashed(), $workspaceIds),
            'vault_items'    => $this->countIn(WorkspaceVaultItem::withTrashed(), $workspaceIds),
            'invitations'    => $this->countIn(WorkspaceInvitation::query(), $workspaceIds),
            'subscriptions'  => $this->countIn(WorkspaceSubscription::withTrashed(), $workspaceIds),
            'invoices'       => count($invoiceIds),
            'payments'       => Payment::withTrashed()
                ->where('payment_reference', 'like', DemoDefinition::PAYMENT_REFERENCE_PREFIX . '%')
                ->count(),
            'billing_plans'  => DB::table('billing_plans')
                ->whereIn('code', DemoDefinition::billingPlanCodes())
                ->count(),
            'notifications'  => $userIds === []
                ? 0
                : DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->whereIn('notifiable_id', $userIds)
                    ->count(),
            'lead_requests'  => LeadRequest::withTrashed()->where('lead_code', DemoDefinition::LEAD_CODE)->count(),
            'trials'         => DB::table('trials')->where('trial_code', DemoDefinition::TRIAL_CODE)->count(),
        ];

        return [
            'users'         => $users,
            'companies'     => $companies,
            'workspaces'    => $workspaces,
            'user_ids'      => $userIds,
            'workspace_ids' => $workspaceIds,
            'counts'        => $counts,
        ];
    }

    private function renderControlled(array $data): void
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' GVOS DEMO AUDIT — CONTROLLED DEMO DATA');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->line(' These records are anchored to the exact identifiers declared in');
        $this->line(' App\Support\Demo\DemoDefinition. They are the ONLY records that');
        $this->line(' `php artisan gvos:demo-clean --execute` will ever delete.');
        $this->newLine();

        // Users
        $this->line('<options=bold>Demo users</> (expected ' . count(DemoDefinition::USERS) . ')');
        $rows = [];
        foreach (DemoDefinition::USERS as $spec) {
            $user = $data['users']->get($spec['email']);
            $rows[] = [
                $spec['email'],
                $spec['name'],
                $spec['role'],
                $user ? ($user->getRoleNames()->first() ?? '—') : '—',
                $user ? $user->status : 'MISSING',
                $user ? (string) $user->id : '—',
            ];
        }
        $this->table(['Email', 'Name', 'Expected role', 'Actual role', 'Status', 'ID'], $rows);

        // Companies
        $this->line('<options=bold>Demo companies</>');
        $rows = [];
        foreach (DemoDefinition::COMPANIES as $spec) {
            $company = $data['companies']->get($spec['name']);
            $rows[]  = [
                $spec['name'],
                $spec['type'],
                $company ? $company->status : 'MISSING',
                $company ? (string) $company->id : '—',
                $company?->deleted_at ? 'soft-deleted' : '—',
            ];
        }
        $this->table(['Company', 'Type', 'Status', 'ID', 'Trashed'], $rows);

        // Workspaces
        $this->line('<options=bold>Demo workspaces</>');
        $rows = [];
        foreach (DemoDefinition::WORKSPACES as $spec) {
            $workspace = $data['workspaces']->get($spec['code']);
            $rows[]    = [
                $spec['code'],
                $spec['name'],
                $workspace ? $workspace->status : 'MISSING',
                $workspace ? $workspace->type : '—',
                $workspace ? (string) $workspace->id : '—',
                $workspace?->deleted_at ? 'soft-deleted' : '—',
            ];
        }
        $this->table(['Code', 'Name', 'Status', 'Type', 'ID', 'Trashed'], $rows);

        // Related record counts
        $this->line('<options=bold>Related controlled demo records</>');
        $rows = [];
        foreach ($data['counts'] as $key => $count) {
            if (in_array($key, ['users', 'companies', 'workspaces'], true)) {
                continue;
            }
            $rows[] = [str_replace('_', ' ', $key), (string) $count];
        }
        $this->table(['Record type', 'Count'], $rows);
    }

    // ── Heuristic (report-only) demo data ────────────────────────────────────

    private function collectHeuristic(array $controlledUserIds, array $controlledWorkspaceIds): array
    {
        $userQuery = User::query()
            ->where(function ($q) {
                $q->where('email', 'like', '%@' . DemoDefinition::EMAIL_DOMAIN)
                  ->orWhere('email', 'like', '%demo%')
                  ->orWhere('email', 'like', '%test%');
            });

        if ($controlledUserIds !== []) {
            $userQuery->whereNotIn('id', $controlledUserIds);
        }

        $workspaceQuery = Workspace::withTrashed()
            ->where('workspace_code', 'like', 'DEMO-%');

        if ($controlledWorkspaceIds !== []) {
            $workspaceQuery->whereNotIn('id', $controlledWorkspaceIds);
        }

        $companyQuery = Company::withTrashed()
            ->where(function ($q) {
                $q->where('name', 'like', 'Demo%')
                  ->orWhere('name', 'like', 'GVOS Demo%');
            })
            ->whereNotIn('name', DemoDefinition::companyNames());

        $users      = $userQuery->orderBy('email')->get();
        $workspaces = $workspaceQuery->orderBy('workspace_code')->get();
        $companies  = $companyQuery->orderBy('name')->get();

        return [
            'users'      => $users,
            'workspaces' => $workspaces,
            'companies'  => $companies,
            'counts'     => [
                'users'      => $users->count(),
                'workspaces' => $workspaces->count(),
                'companies'  => $companies->count(),
            ],
        ];
    }

    private function renderHeuristic(array $data): void
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->info(' OTHER LIKELY DEMO / TEST DATA — REPORT ONLY');
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->line(' Matched by loose patterns:');
        foreach (DemoDefinition::auditHeuristics() as $description) {
            $this->line('   • ' . $description);
        }
        $this->newLine();
        $this->warn(' These records are NOT part of the controlled demo definition and');
        $this->warn(' will NEVER be deleted by gvos:demo-clean. Review them manually.');
        $this->newLine();

        $total = array_sum($data['counts']);

        if ($total === 0) {
            $this->line(' No additional demo-looking records found.');

            return;
        }

        if ($data['users']->isNotEmpty()) {
            $this->line('<options=bold>Users matching demo/test patterns (outside controlled set)</>');
            $this->table(
                ['ID', 'Email', 'Name', 'Role', 'Status'],
                $data['users']->map(fn (User $u) => [
                    $u->id,
                    $u->email,
                    $u->name,
                    $u->getRoleNames()->first() ?? '—',
                    $u->status,
                ])->all()
            );
        }

        if ($data['workspaces']->isNotEmpty()) {
            $this->line('<options=bold>Workspaces with a DEMO- code (outside controlled set)</>');
            $this->table(
                ['ID', 'Code', 'Name', 'Status'],
                $data['workspaces']->map(fn (Workspace $w) => [
                    $w->id, $w->workspace_code, $w->name, $w->status,
                ])->all()
            );
        }

        if ($data['companies']->isNotEmpty()) {
            $this->line('<options=bold>Companies named Demo* / GVOS Demo* (outside controlled set)</>');
            $this->table(
                ['ID', 'Name', 'Type', 'Status'],
                $data['companies']->map(fn (Company $c) => [
                    $c->id, $c->name, $c->type, $c->status,
                ])->all()
            );
        }
    }

    private function renderSafetyNotice(): void
    {
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════');
        $this->line(' This command is READ-ONLY. Nothing was created, updated or deleted.');
        $this->line(' No passwords, password hashes, vault secrets, or invitation tokens');
        $this->line(' are ever printed by this command.');
        $this->newLine();
        $this->line(' Next steps:');
        $this->line('   php artisan gvos:demo-setup     # create/refresh controlled demo data');
        $this->line('   php artisan gvos:demo-verify    # PASS/FAIL health report');
        $this->line('   php artisan gvos:demo-clean     # dry-run removal preview');
        $this->newLine();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Count rows of a model/builder scoped to the given workspace IDs.
     *
     * @param  string|\Illuminate\Database\Eloquent\Builder  $target
     * @param  int[]  $workspaceIds
     */
    private function countIn($target, array $workspaceIds): int
    {
        if ($workspaceIds === []) {
            return 0;
        }

        $query = is_string($target) ? $target::query() : $target;

        return (int) $query->whereIn('workspace_id', $workspaceIds)->count();
    }
}
