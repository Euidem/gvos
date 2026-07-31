<?php

namespace App\Support\Demo;

use App\Models\BillingPlan;
use App\Models\ClientProfile;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LeadRequest;
use App\Models\ManagerProfile;
use App\Models\Payment;
use App\Models\PriceEstimate;
use App\Models\TalentProfile;
use App\Models\Trial;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Workspace;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceSubscription;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskComment;
use App\Models\WorkspaceTimeLog;
use App\Models\WorkspaceVaultItem;
use App\Models\WorkspaceWeeklyReport;
use App\Notifications\BillingOverdueNotification;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TimeLogSubmittedNotification;
use App\Notifications\WeeklyReportPublishedNotification;
use App\Notifications\WorkspaceFileUploadedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Phase 27 — Builds the controlled GVOS demo environment.
 *
 * Idempotent: users, companies, workspaces, memberships, billing plans and
 * subscriptions are created with updateOrCreate (stable IDs and logins across
 * runs). Operational content (tasks, messages, files, time logs, reports,
 * vault items, invitations, demo invoices/payments, demo notifications) is
 * removed by DemoCleaner::deleteContent() and rebuilt, so counts stay stable.
 *
 * SECURITY
 *   – The plaintext password never enters this class; only a pre-hashed value.
 *   – Vault secrets are written through the model's normal `encrypted` cast and
 *     are never returned in the summary.
 *   – Notifications are raised on the `database` channel only (GvosNotification
 *     defaults to ['database']); the calling command additionally forces the
 *     mail transport to `array` so nothing can leave the server.
 *   – No payment gateway, webhook, or external service is contacted.
 */
class DemoBuilder
{
    /** @var array<string,User> keyed by DemoDefinition::USERS slug */
    private array $users = [];

    /** @var array<string,Company> keyed by DemoDefinition::COMPANIES slug */
    private array $companies = [];

    /** @var array<string,ClientProfile> keyed by user slug */
    private array $clientProfiles = [];

    /** @var array<string,Workspace> keyed by DemoDefinition::WORKSPACES slug */
    private array $workspaces = [];

    /** @var array<string,BillingPlan> */
    private array $plans = [];

    /** @var array<string,WorkspaceTask> keyed by "{workspaceSlug}:{taskSlug}" */
    private array $tasks = [];

    /** @var array<string,int> */
    private array $summary = [];

    private Carbon $today;

    public function __construct(private readonly string $hashedPassword)
    {
        $this->today = Carbon::now()->startOfDay();
    }

    /**
     * Build (or rebuild) the whole controlled demo environment.
     *
     * @return array<string,int> summary counts
     */
    public function build(): array
    {
        $this->ensureRoles();
        $this->buildCompanies();
        $this->buildUsers();
        $this->buildBillingPlans();
        $this->buildLeadAndTrial();
        $this->buildWorkspaces();
        $this->buildMembers();
        $this->buildSubscriptions();

        // Rebuild controlled operational content from a clean slate.
        (new DemoCleaner)->deleteContent();

        $this->buildTasks();
        $this->buildTaskComments();
        $this->buildMessages();
        $this->buildFiles();
        $this->buildTimeLogs();
        $this->buildWeeklyReports();
        $this->buildBillingDocuments();
        $this->buildVaultItems();
        $this->buildInvitations();
        $this->buildNotifications();

        return $this->summary;
    }

    // ── Roles ────────────────────────────────────────────────────────────────

    private function ensureRoles(): void
    {
        foreach (array_unique(array_column(DemoDefinition::USERS, 'role')) as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    // ── Companies ────────────────────────────────────────────────────────────

    private function buildCompanies(): void
    {
        foreach (DemoDefinition::COMPANIES as $slug => $spec) {
            $company = Company::withTrashed()->firstOrNew(['name' => $spec['name']]);
            $company->fill($spec);
            $company->notes = DemoDefinition::MARKER . ' Controlled demo company. Not a real client.';
            $company->deleted_at = null;
            $company->save();

            $this->companies[$slug] = $company;
        }

        $this->summary['companies'] = count($this->companies);
    }

    // ── Users and profiles ───────────────────────────────────────────────────

    private function buildUsers(): void
    {
        foreach (DemoDefinition::USERS as $slug => $spec) {
            $user = User::firstOrNew(['email' => $spec['email']]);

            $user->name              = $spec['name'];
            $user->password          = $this->hashedPassword;
            $user->timezone          = $spec['timezone'];
            $user->status            = $spec['status'];
            $user->email_verified_at = $user->email_verified_at ?? $this->daysAgo(21, 9);
            $user->save();

            $user->syncRoles([$spec['role']]);

            // Base profile — onboarding complete for every account except the
            // suspended one, which stays mid-onboarding for realism.
            $complete = $spec['status'] === 'active';

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name'              => $spec['first_name'],
                    'last_name'               => $spec['last_name'],
                    'phone'                   => $spec['phone'],
                    'country'                 => $spec['country'],
                    'city'                    => $spec['city'],
                    'bio'                     => $spec['bio'],
                    'onboarding_status'       => $complete ? 'complete' : 'in_progress',
                    'onboarding_completed_at' => $complete ? $this->daysAgo(18, 10) : null,
                    'last_onboarding_step'    => $complete ? 'complete' : 'profile',
                ]
            );

            $this->users[$slug] = $user;
        }

        $this->buildRoleProfiles();

        $this->summary['users'] = count($this->users);
    }

    private function buildRoleProfiles(): void
    {
        // Line manager
        ManagerProfile::updateOrCreate(
            ['user_id' => $this->users['manager']->id],
            [
                'manager_code'   => 'DEMO-GVM-001',
                'department'     => 'Client Operations',
                'capacity_limit' => 10,
                'current_load'   => 4,
                'specialization' => 'Executive support and customer experience',
                'status'         => 'active',
                'internal_notes' => DemoDefinition::MARKER . ' Demo manager profile.',
            ]
        );

        // Talent
        $talentSpecs = [
            'talent_one' => [
                'talent_code'   => 'DEMO-GVT-001',
                'role_type'     => 'Executive Assistant',
                'skill_summary' => 'Calendar management, inbox triage, CRM upkeep, expense reporting.',
                'work_timezone' => 'Africa/Lagos',
            ],
            'talent_two' => [
                'talent_code'   => 'DEMO-GVT-002',
                'role_type'     => 'Customer Experience Associate',
                'skill_summary' => 'Support ticket handling, response templates, competitor research.',
                'work_timezone' => 'Africa/Lagos',
            ],
            'suspended' => [
                'talent_code'   => 'DEMO-GVT-003',
                'role_type'     => 'Virtual Assistant',
                'skill_summary' => 'General administrative support.',
                'work_timezone' => 'Africa/Lagos',
            ],
        ];

        foreach ($talentSpecs as $slug => $spec) {
            TalentProfile::updateOrCreate(
                ['user_id' => $this->users[$slug]->id],
                array_merge($spec, [
                    'availability_type'     => 'fixed',
                    'weekly_capacity_hours' => 40,
                    'training_status'       => $slug === 'suspended' ? 'paused' : 'active',
                    'equipment_status'      => $slug === 'suspended' ? 'returned' : 'assigned',
                    'status'                => $slug === 'suspended' ? 'suspended' : 'active',
                    'internal_notes'        => DemoDefinition::MARKER . ' Demo talent profile.',
                ])
            );
        }

        // Clients
        $clientSpecs = [
            'individual_client' => [
                'client_type'              => 'individual',
                'company'                  => 'apexbridge',
                'job_title'                => 'Founder',
                'preferred_contact_window' => 'Mon–Fri 09:00–17:00 BST',
                'service_interest'         => 'Executive assistance',
            ],
            'business_admin' => [
                'client_type'              => 'business_admin',
                'company'                  => 'northstar',
                'job_title'                => 'Head of Customer Operations',
                'preferred_contact_window' => 'Mon–Fri 08:00–16:00 WAT',
                'service_interest'         => 'Customer experience support',
            ],
            'business_staff' => [
                'client_type'              => 'business_staff',
                'company'                  => 'northstar',
                'job_title'                => 'Customer Support Lead',
                'preferred_contact_window' => 'Mon–Fri 09:00–17:00 WAT',
                'service_interest'         => 'Customer experience support',
            ],
            'observer' => [
                'client_type'              => 'business_staff',
                'company'                  => 'northstar',
                'job_title'                => 'Quality Analyst (read-only stakeholder)',
                'preferred_contact_window' => 'Mon–Fri 09:00–17:00 WAT',
                'service_interest'         => 'Reporting visibility only',
            ],
            'restricted_client' => [
                'client_type'              => 'individual',
                'company'                  => null,
                'job_title'                => 'Operations Lead',
                'preferred_contact_window' => 'Mon–Fri 09:00–17:00 WAT',
                'service_interest'         => 'Finance operations support',
            ],
        ];

        foreach ($clientSpecs as $slug => $spec) {
            $this->clientProfiles[$slug] = ClientProfile::updateOrCreate(
                ['user_id' => $this->users[$slug]->id],
                [
                    'company_id'               => $spec['company'] ? $this->companies[$spec['company']]->id : null,
                    'client_type'              => $spec['client_type'],
                    'job_title'                => $spec['job_title'],
                    'department_id'            => null,
                    'preferred_contact_window' => $spec['preferred_contact_window'],
                    'service_interest'         => $spec['service_interest'],
                    'status'                   => 'active',
                    'notes'                    => DemoDefinition::MARKER . ' Demo client profile.',
                ]
            );
        }
    }

    // ── Billing plans ────────────────────────────────────────────────────────

    private function buildBillingPlans(): void
    {
        $specs = [
            'biweekly' => [
                'name'                    => 'Demo Executive Support (Bi-Weekly)',
                'description'             => 'Controlled demo plan. Bi-weekly executive support retainer.',
                'currency'                => 'USD',
                'amount'                  => 900.00,
                'billing_cycle'           => 'bi_weekly',
                'included_talents'        => 1,
                'included_hours_per_week' => 20,
                'status'                  => 'active',
                'notes'                   => DemoDefinition::MARKER . ' Demo plan. Fake amounts.',
            ],
            'monthly' => [
                'name'                    => 'Demo Managed Operations (Monthly)',
                'description'             => 'Controlled demo plan. Monthly managed operations retainer.',
                'currency'                => 'USD',
                'amount'                  => 1800.00,
                'billing_cycle'           => 'monthly',
                'included_talents'        => 2,
                'included_hours_per_week' => 40,
                'status'                  => 'active',
                'notes'                   => DemoDefinition::MARKER . ' Demo plan. Fake amounts.',
            ],
        ];

        foreach ($specs as $slug => $spec) {
            // firstOrNew (not updateOrCreate) so `deleted_at` can be reset —
            // it is not a fillable attribute and would be dropped by mass assignment.
            $plan = BillingPlan::withTrashed()
                ->firstOrNew(['code' => DemoDefinition::BILLING_PLAN_CODES[$slug]]);
            $plan->fill($spec);
            $plan->deleted_at = null;
            $plan->save();

            $this->plans[$slug] = $plan;
        }

        $this->summary['billing_plans'] = count($this->plans);
    }

    // ── Lead request, estimate, trial ────────────────────────────────────────

    private ?Trial $trial = null;

    private ?LeadRequest $lead = null;

    private function buildLeadAndTrial(): void
    {
        // firstOrNew (not updateOrCreate) so `deleted_at` can be reset.
        $this->lead = LeadRequest::withTrashed()->firstOrNew(['lead_code' => DemoDefinition::LEAD_CODE]);
        $this->lead->fill([
                'first_name'               => 'Tunde',
                'last_name'                => 'Williams',
                'email'                    => DemoDefinition::USERS['lead']['email'],
                'phone'                    => '+000 000 0009',
                'country'                  => 'Nigeria',
                'city'                     => 'Port Harcourt',
                'timezone'                 => 'Africa/Lagos',
                'client_type'              => 'business',
                'company_name'             => 'Demo Logistics Startup',
                'company_website'          => 'https://demo-logistics.demo.invalid',
                'company_email_domain'     => DemoDefinition::EMAIL_DOMAIN,
                'role_needed'              => 'virtual_assistant',
                'estimated_hours_per_week' => 20,
                'preferred_start_date'     => $this->today->copy()->addDays(7)->toDateString(),
                'preferred_work_schedule'  => 'Mon–Fri, 09:00–13:00 WAT',
                'required_skills'          => 'Market research, spreadsheet management, competitor analysis.',
                'work_description'         => 'Needs help running a short market research sprint across five logistics competitors before a funding conversation.',
                'budget_range'             => '500_1000',
                'source'                   => 'Demo environment',
                'status'                   => 'trial_active',
                'admin_notes'              => DemoDefinition::MARKER . ' Controlled demo lead. Contact details are fake.',
        ]);
        $this->lead->deleted_at = null;
        $this->lead->save();

        $estimate = PriceEstimate::updateOrCreate(
            [
                'lead_request_id' => $this->lead->id,
                'role_needed'     => 'virtual_assistant',
            ],
            [
                'currency'                 => 'USD',
                'estimated_amount'         => 750.00,
                'billing_cycle'            => 'monthly',
                'estimated_hours_per_week' => 20,
                'notes'                    => DemoDefinition::MARKER . ' Demo estimate. Fake amount.',
                'status'                   => 'accepted',
                'accepted_at'              => $this->daysAgo(9, 14),
                'expires_at'               => $this->today->copy()->addDays(14),
            ]
        );

        $this->trial = Trial::updateOrCreate(
            ['trial_code' => DemoDefinition::TRIAL_CODE],
            [
                'lead_request_id'          => $this->lead->id,
                'active_lead_user_id'      => $this->users['lead']->id,
                'assigned_talent_user_id'  => $this->users['talent_two']->id,
                'assigned_manager_user_id' => $this->users['manager']->id,
                'price_estimate_id'        => $estimate->id,
                'status'                   => 'active',
                'starts_at'                => $this->daysAgo(6, 9),
                'ends_at'                  => $this->today->copy()->addDays(2)->setTime(17, 0),
                'trial_duration_hours'     => 24,
                'trial_task_limit'         => 5,
                'trial_file_limit_mb'      => 100,
                'notes'                    => DemoDefinition::MARKER . ' Controlled demo trial.',
            ]
        );

        $this->summary['lead_requests'] = 1;
        $this->summary['trials']        = 1;
    }

    // ── Workspaces ───────────────────────────────────────────────────────────

    private function buildWorkspaces(): void
    {
        $specs = [
            'exec' => [
                'description'       => 'Day-to-day executive assistance: calendar, inbox, CRM upkeep and expense reporting.',
                'company_id'        => $this->companies['apexbridge']->id,
                'client_profile_id' => $this->clientProfiles['individual_client']->id,
                'primary_talent_id' => $this->users['talent_one']->id,
                'trial_id'          => null,
                'lead_request_id'   => null,
                'starts_at'         => $this->daysAgo(30, 9),
                // workspaces.task_limit / file_limit_mb are NOT NULL with 0 = unlimited.
                'task_limit'        => 0,
                'file_limit_mb'     => 500,
            ],
            'cx' => [
                'description'       => 'Customer experience support desk for Northstar Retail Group: enquiries, templates and reporting.',
                'company_id'        => $this->companies['northstar']->id,
                'client_profile_id' => $this->clientProfiles['business_admin']->id,
                'primary_talent_id' => $this->users['talent_two']->id,
                'trial_id'          => null,
                'lead_request_id'   => null,
                'starts_at'         => $this->daysAgo(45, 9),
                'task_limit'        => 0,
                'file_limit_mb'     => 500,
            ],
            'research' => [
                'description'       => 'Short market research sprint running under an active GVOS trial.',
                'company_id'        => null,
                'client_profile_id' => null,
                'primary_talent_id' => $this->users['talent_two']->id,
                'trial_id'          => $this->trial?->id,
                'lead_request_id'   => $this->lead?->id,
                'starts_at'         => $this->daysAgo(6, 9),
                'task_limit'        => 5,
                'file_limit_mb'     => 100,
            ],
            'restricted' => [
                'description'       => 'Finance operations support. Client access is restricted while an invoice is outstanding.',
                'company_id'        => null,
                'client_profile_id' => $this->clientProfiles['restricted_client']->id,
                'primary_talent_id' => $this->users['talent_one']->id,
                'trial_id'          => null,
                'lead_request_id'   => null,
                'starts_at'         => $this->daysAgo(60, 9),
                'task_limit'        => 0,
                'file_limit_mb'     => 500,
            ],
        ];

        foreach (DemoDefinition::WORKSPACES as $slug => $base) {
            $spec = $specs[$slug];

            $workspace = Workspace::withTrashed()->firstOrNew(['workspace_code' => $base['code']]);
            $workspace->fill([
                'name'               => $base['name'],
                'description'        => $spec['description'],
                'status'             => $base['status'],
                'type'               => $base['type'],
                'company_id'         => $spec['company_id'],
                'client_profile_id'  => $spec['client_profile_id'],
                'lead_request_id'    => $spec['lead_request_id'],
                'trial_id'           => $spec['trial_id'],
                'primary_manager_id' => $this->users['manager']->id,
                'primary_talent_id'  => $spec['primary_talent_id'],
                'starts_at'          => $spec['starts_at'],
                'ends_at'            => null,
                'task_limit'         => $spec['task_limit'],
                'file_limit_mb'      => $spec['file_limit_mb'],
                'notes'              => DemoDefinition::MARKER . ' Controlled demo workspace.',
            ]);
            $workspace->deleted_at = null;
            $workspace->save();

            $this->workspaces[$slug] = $workspace;
        }

        $this->summary['workspaces'] = count($this->workspaces);
    }

    // ── Members ──────────────────────────────────────────────────────────────

    private function buildMembers(): void
    {
        $memberships = [
            'exec' => [
                ['manager', 'manager'],
                ['talent_one', 'talent'],
                ['individual_client', 'client_admin'],
            ],
            'cx' => [
                ['manager', 'manager'],
                ['talent_one', 'talent'],
                ['talent_two', 'talent'],
                ['business_admin', 'client_admin'],
                ['business_staff', 'client_staff'],
                ['observer', 'observer'],
            ],
            'research' => [
                ['manager', 'manager'],
                ['talent_two', 'talent'],
                ['lead', 'client_admin'],
            ],
            'restricted' => [
                ['manager', 'manager'],
                ['talent_one', 'talent'],
                ['restricted_client', 'client_admin'],
            ],
        ];

        $count = 0;

        foreach ($memberships as $workspaceSlug => $rows) {
            $workspace = $this->workspaces[$workspaceSlug];

            foreach ($rows as [$userSlug, $role]) {
                WorkspaceMember::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'user_id'      => $this->users[$userSlug]->id,
                    ],
                    [
                        'role'       => $role,
                        'status'     => 'active',
                        'joined_at'  => $this->daysAgo(20, 9),
                        'removed_at' => null,
                        'notes'      => DemoDefinition::MARKER . ' Demo membership.',
                    ]
                );
                $count++;
            }
        }

        $this->summary['workspace_members'] = $count;
    }

    // ── Subscriptions ────────────────────────────────────────────────────────

    private function buildSubscriptions(): void
    {
        $specs = [
            'exec' => [
                'plan'              => 'biweekly',
                'client_profile'    => 'individual_client',
                'company'           => 'apexbridge',
                'amount'            => 900.00,
                'billing_cycle'     => 'bi_weekly',
                'status'            => 'active',
                'next_billing_date' => $this->today->copy()->addDays(9),
                'last_paid_at'      => $this->daysAgo(5, 11),
            ],
            'cx' => [
                'plan'              => 'monthly',
                'client_profile'    => 'business_admin',
                'company'           => 'northstar',
                'amount'            => 1800.00,
                'billing_cycle'     => 'monthly',
                'status'            => 'active',
                'next_billing_date' => $this->today->copy()->addDays(2),
                'last_paid_at'      => $this->daysAgo(28, 11),
            ],
            'research' => [
                'plan'              => null,
                'client_profile'    => null,
                'company'           => null,
                'amount'            => 0.00,
                'billing_cycle'     => 'monthly',
                'status'            => 'trial',
                'next_billing_date' => $this->today->copy()->addDays(2),
                'last_paid_at'      => null,
            ],
            'restricted' => [
                'plan'              => 'monthly',
                'client_profile'    => 'restricted_client',
                'company'           => null,
                'amount'            => 1800.00,
                'billing_cycle'     => 'monthly',
                'status'            => 'overdue',
                'next_billing_date' => $this->daysAgo(12, 0),
                'last_paid_at'      => $this->daysAgo(42, 11),
            ],
        ];

        foreach ($specs as $slug => $spec) {
            $workspace = $this->workspaces[$slug];
            $restricted = $slug === 'restricted';

            // firstOrNew (not updateOrCreate) so `deleted_at` can be reset.
            $subscription = WorkspaceSubscription::withTrashed()
                ->firstOrNew(['workspace_id' => $workspace->id]);
            $subscription->fill([
                    'billing_plan_id'    => $spec['plan'] ? $this->plans[$spec['plan']]->id : null,
                    'client_profile_id'  => $spec['client_profile'] ? $this->clientProfiles[$spec['client_profile']]->id : null,
                    'company_id'         => $spec['company'] ? $this->companies[$spec['company']]->id : null,
                    'currency'           => 'USD',
                    'amount'             => $spec['amount'],
                    'billing_cycle'      => $spec['billing_cycle'],
                    'status'             => $spec['status'],
                    'starts_at'          => $workspace->starts_at?->toDateString(),
                    'next_billing_date'  => $spec['next_billing_date'],
                    'ends_at'            => null,
                    'last_paid_at'       => $spec['last_paid_at'],
                    'grace_ends_at'      => $restricted ? $this->daysAgo(9, 0) : null,
                    // Restriction is expressed through restricted_at (suspended_at
                    // stays null → WorkspaceSubscription::isRestricted() is true).
                    'restricted_at'      => $restricted ? $this->daysAgo(9, 0) : null,
                    'suspended_at'       => null,
                    'suspended_by'       => null,
                    'reactivated_at'     => null,
                    'reactivated_by'     => null,
                    'restriction_reason' => $restricted
                        ? 'Demo scenario: invoice ' . DemoDefinition::INVOICE_NUMBER_PREFIX . '0003 is outstanding beyond the grace period.'
                        : null,
                    'notes'              => DemoDefinition::MARKER . ' Demo subscription. Fake amounts.',
            ]);
            $subscription->deleted_at = null;
            $subscription->save();
        }

        $this->summary['subscriptions'] = count($specs);
    }

    // ── Tasks ────────────────────────────────────────────────────────────────

    private function buildTasks(): void
    {
        // [slug, title, status, priority, assignee slug, due offset (days), sort]
        $definitions = [
            'exec' => [
                ['calendar',  'Prepare weekly executive calendar',      'pending',            'high',   'talent_one', 2],
                ['crm',       'Update CRM contact records',             'in_progress',        'normal', 'talent_one', 4],
                ['expenses',  'Prepare monthly expense summary',        'blocked',            'high',   'talent_one', 1],
                ['notes',     'Review meeting notes and action points', 'submitted',          'normal', 'talent_one', 0],
                ['opsreport', 'Draft weekly operations report',         'approved',           'normal', 'talent_one', -1],
                ['folder',    'Compile client delivery folder',         'closed',             'low',    'talent_one', -4],
            ],
            'cx' => [
                ['templates', 'Organise customer support response templates', 'pending',            'normal', 'talent_two', 5],
                ['enquiries', 'Follow up outstanding customer enquiries',     'in_progress',        'urgent', 'talent_two', 1],
                ['leadsheet', 'Update lead qualification spreadsheet',        'in_progress',        'normal', 'talent_one', 3],
                ['crm',       'Update CRM contact records',                   'blocked',            'normal', 'talent_two', -2],
                ['opsreport', 'Draft weekly operations report',               'submitted',          'high',   'talent_one', 0],
                ['revision',  'Compile client delivery folder',               'revision_requested', 'normal', 'talent_two', 2],
                ['notes',     'Review meeting notes and action points',       'approved',           'low',    'talent_two', -3],
                ['closed',    'Prepare weekly executive calendar',            'closed',             'low',    'talent_two', -6],
            ],
            'research' => [
                ['competitors', 'Research five logistics competitors',   'in_progress', 'urgent', 'talent_two', 1],
                ['leadsheet',   'Update lead qualification spreadsheet', 'pending',     'normal', 'talent_two', 3],
                ['folder',      'Compile client delivery folder',        'submitted',   'normal', 'talent_two', 0],
                ['notes',       'Review meeting notes and action points', 'approved',   'low',    'talent_two', -2],
            ],
            'restricted' => [
                ['expenses',  'Prepare monthly expense summary',          'pending',     'high',   'talent_one', 2],
                ['crm',       'Update CRM contact records',               'blocked',     'normal', 'talent_one', -5],
                ['opsreport', 'Draft weekly operations report',           'submitted',   'normal', 'talent_one', 0],
                ['enquiries', 'Follow up outstanding customer enquiries', 'closed',      'low',    'talent_one', -8],
            ],
        ];

        $count = 0;

        foreach ($definitions as $workspaceSlug => $rows) {
            $workspace = $this->workspaces[$workspaceSlug];
            $sort      = 0;

            foreach ($rows as [$taskSlug, $title, $status, $priority, $assignee, $dueOffset]) {
                $stamps = $this->taskTimestamps($status);

                $task = new WorkspaceTask(array_merge([
                    'task_code'           => WorkspaceTask::generateCode(),
                    'workspace_id'        => $workspace->id,
                    'created_by_user_id'  => $this->users['manager']->id,
                    'assigned_to_user_id' => $this->users[$assignee]->id,
                    'title'               => $title,
                    'description'         => $this->taskDescription($title),
                    'priority'            => $priority,
                    'status'              => $status,
                    'due_date'            => $this->today->copy()->addDays($dueOffset)->toDateString(),
                    'sort_order'          => $sort++,
                    'internal_notes'      => in_array($status, ['blocked', 'submitted'], true)
                        ? DemoDefinition::MARKER . ' Internal note: visible to managers and admins only.'
                        : null,
                ], $stamps));

                // Explicit timestamps survive because Eloquent only auto-fills
                // created_at/updated_at when they are not already dirty.
                $task->created_at = isset($stamps['started_at'])
                    ? $stamps['started_at']->copy()->subDay()
                    : $this->daysAgo(7, 9);
                $task->updated_at = $this->daysAgo(1, 12);
                $task->save();

                $this->tasks["{$workspaceSlug}:{$taskSlug}"] = $task;
                $count++;
            }
        }

        $this->summary['tasks'] = $count;
    }

    /** Realistic started/submitted/approved/closed timestamps for a status. */
    private function taskTimestamps(string $status): array
    {
        return match ($status) {
            'in_progress'        => ['started_at' => $this->daysAgo(3, 10)],
            'blocked'            => ['started_at' => $this->daysAgo(5, 10)],
            'submitted'          => ['started_at' => $this->daysAgo(4, 9), 'submitted_at' => $this->daysAgo(1, 16)],
            'revision_requested' => ['started_at' => $this->daysAgo(6, 9), 'submitted_at' => $this->daysAgo(2, 15)],
            'approved'           => [
                'started_at'   => $this->daysAgo(8, 9),
                'submitted_at' => $this->daysAgo(4, 15),
                'approved_at'  => $this->daysAgo(3, 11),
            ],
            'closed'             => [
                'started_at'   => $this->daysAgo(12, 9),
                'submitted_at' => $this->daysAgo(9, 15),
                'approved_at'  => $this->daysAgo(8, 11),
                'closed_at'    => $this->daysAgo(8, 12),
            ],
            default              => [],
        };
    }

    private function taskDescription(string $title): string
    {
        return match ($title) {
            'Prepare weekly executive calendar'       => 'Build next week\'s calendar, confirm all meeting invites, and flag any scheduling conflicts before Friday.',
            'Organise customer support response templates' => 'Group the existing response templates by enquiry type and remove duplicates. Note any gaps that still need a template.',
            'Update lead qualification spreadsheet'   => 'Add this week\'s new enquiries to the qualification sheet and update the status column for anything that has moved.',
            'Research five logistics competitors'     => 'Capture pricing, service coverage, turnaround times and public positioning for five comparable logistics providers.',
            'Draft weekly operations report'          => 'Summarise completed work, hours logged, and anything blocked, ready for the manager to review before publishing.',
            'Follow up outstanding customer enquiries' => 'Work through the open enquiry list, respond where possible, and escalate anything that needs a decision.',
            'Prepare monthly expense summary'         => 'Collate receipts by category, reconcile against the statement, and note any items missing supporting documents.',
            'Review meeting notes and action points'  => 'Clean up the notes from this week\'s calls and turn every agreed action into a tracked item with an owner.',
            'Update CRM contact records'              => 'Correct outdated contact details, merge duplicate records, and tag accounts by segment.',
            'Compile client delivery folder'          => 'Assemble the finished deliverables into a single folder with a short index so the client can review everything in one place.',
            default                                   => 'Demo task created for GVOS internal testing.',
        };
    }

    // ── Task comments ────────────────────────────────────────────────────────

    private function buildTaskComments(): void
    {
        $comments = [
            ['exec:crm',        'talent_one', 'public',   'Started with the duplicate records — about 40 cleaned so far. Will continue tomorrow morning.'],
            ['exec:crm',        'manager',    'public',   'Thanks Daniel. Prioritise the accounts flagged as active before the rest.'],
            ['exec:expenses',   'talent_one', 'public',   'Blocked: three receipts are missing for the travel category. I have requested them.'],
            ['exec:expenses',   'manager',    'internal', 'Internal: if the receipts do not arrive by Thursday we will submit without them and note the gap.'],
            ['exec:notes',      'talent_one', 'public',   'Notes are cleaned up and every action has an owner. Submitted for review.'],
            ['cx:enquiries',    'talent_two', 'public',   'Working through the backlog — 12 of 18 enquiries answered.'],
            ['cx:enquiries',    'business_staff', 'public', 'Two of the remaining ones are mine, I will confirm the refund policy and come back to you.'],
            ['cx:opsreport',    'manager',    'internal', 'Internal: check the hours total against the approved time logs before this goes to the client.'],
            ['cx:revision',     'business_admin', 'public', 'Could we add the January deliverables to the same folder? Otherwise this looks good.'],
            ['research:competitors', 'talent_two', 'public', 'Three of five competitors profiled. Pricing pages for the last two are behind a contact form.'],
            ['restricted:crm',  'talent_one', 'public',   'Blocked pending access to the finance system. Raised with the manager.'],
        ];

        $count = 0;
        $offset = 12;

        foreach ($comments as [$taskKey, $userSlug, $visibility, $body]) {
            $task = $this->tasks[$taskKey] ?? null;
            if (! $task) {
                continue;
            }

            $created = $this->daysAgo(max(1, $offset % 13), 10 + ($count % 6));

            $comment = new WorkspaceTaskComment([
                'workspace_task_id' => $task->id,
                'user_id'           => $this->users[$userSlug]->id,
                'comment'           => $body,
                'visibility'        => $visibility,
            ]);
            $comment->created_at = $created;
            $comment->updated_at = $created;
            $comment->save();

            $count++;
            $offset--;
        }

        $this->summary['task_comments'] = $count;
    }

    // ── Chat messages ────────────────────────────────────────────────────────

    private function buildMessages(): void
    {
        $threads = [
            'exec' => [
                ['manager',           'public',   9,  'Morning all — priorities for this week are the executive calendar, the CRM clean-up and the expense summary. Calendar first.'],
                ['talent_one',        'public',   9,  'Understood. I will start on the calendar this morning and pick up the CRM records after lunch.'],
                ['individual_client', 'public',   6,  'Could I get a quick progress update before Thursday? I have a board call that afternoon.'],
                ['manager',           'public',   6,  'Of course. I will publish the weekly report on Wednesday so you have it in good time.'],
                ['manager',           'public',   2,  'The weekly report for last week has now been published and is available in the Reports tab.'],
                ['manager',           'internal', 5,  'Internal note: expense receipts are still outstanding. Keep this off the client thread for now.'],
            ],
            'cx' => [
                ['manager',        'public',   10, 'This week: clear the outstanding enquiry backlog first, then tidy up the response templates.'],
                ['talent_two',     'public',   10, 'Got it. I will work the backlog from oldest to newest and flag anything that needs a decision.'],
                ['business_admin', 'public',   7,  'Please treat anything tagged as a delivery complaint as urgent — those affect our SLA reporting.'],
                ['business_staff', 'public',   7,  'To clarify: refunds above the standard threshold still need my sign-off before they are confirmed.'],
                ['talent_two',     'public',   4,  'Noted, thank you. I have separated those into their own list and will send them across for approval.'],
                ['manager',        'public',   2,  'The weekly report has been published. Hours and completed work are summarised there.'],
                ['manager',        'internal', 3,  'Internal: Daniel is covering the lead qualification sheet this week while Mariam clears the backlog.'],
            ],
            'research' => [
                ['manager',    'public', 5, 'Welcome to the trial workspace. The goal is five competitor profiles by the end of the sprint.'],
                ['lead',       'public', 5, 'Thanks — pricing and turnaround times are the two things I most need for the funding conversation.'],
                ['talent_two', 'public', 3, 'Three profiles are done. The last two do not publish pricing, so I will note the ranges from their case studies.'],
            ],
            'restricted' => [
                ['manager',           'public', 8, 'Work continues on the operations tasks. Note that client access is limited while the invoice is outstanding.'],
                ['restricted_client', 'public', 7, 'Understood — I am chasing the payment internally and will confirm once it has been sent.'],
                ['talent_one',        'public', 4, 'The expense summary is ready to start as soon as I have access to the finance system.'],
            ],
        ];

        $count = 0;

        foreach ($threads as $workspaceSlug => $rows) {
            $workspace = $this->workspaces[$workspaceSlug];

            foreach ($rows as $index => [$userSlug, $visibility, $daysAgo, $body]) {
                $created = $this->daysAgo($daysAgo, 9 + ($index % 7));

                $message = new WorkspaceMessage([
                    'workspace_id' => $workspace->id,
                    'user_id'      => $this->users[$userSlug]->id,
                    'parent_id'    => null,
                    'message'      => $body,
                    'visibility'   => $visibility,
                    'message_type' => 'text',
                ]);
                $message->created_at = $created;
                $message->updated_at = $created;
                $message->save();

                $count++;
            }
        }

        $this->summary['messages'] = $count;
    }

    // ── Files ────────────────────────────────────────────────────────────────

    private function buildFiles(): void
    {
        $files = [
            [
                'workspace'  => 'exec',
                'name'       => 'Demo Operations Brief.txt',
                'title'      => 'Demo Operations Brief',
                'visibility' => 'public',
                'category'   => 'brief',
                'uploader'   => 'manager',
                'task'       => null,
                'days_ago'   => 11,
                'body'       => $this->operationsBrief(),
            ],
            [
                'workspace'  => 'exec',
                'name'       => 'Demo Weekly Checklist.txt',
                'title'      => 'Demo Weekly Checklist (Internal)',
                'visibility' => 'internal',
                'category'   => 'general',
                'uploader'   => 'manager',
                'task'       => null,
                'days_ago'   => 9,
                'body'       => $this->weeklyChecklist(),
            ],
            [
                'workspace'  => 'exec',
                'name'       => 'Demo Client Summary.pdf',
                'title'      => 'Demo Client Summary',
                'visibility' => 'public',
                'category'   => 'deliverable',
                'uploader'   => 'talent_one',
                'task'       => 'exec:notes',
                'days_ago'   => 2,
                'body'       => $this->samplePdf(),
            ],
            [
                'workspace'  => 'cx',
                'name'       => 'Demo Customer Response Guide.txt',
                'title'      => 'Demo Customer Response Guide',
                'visibility' => 'public',
                'category'   => 'deliverable',
                'uploader'   => 'talent_two',
                'task'       => 'cx:templates',
                'days_ago'   => 4,
                'body'       => $this->responseGuide(),
            ],
            [
                'workspace'  => 'research',
                'name'       => 'Demo Competitor Research Notes.txt',
                'title'      => 'Demo Competitor Research Notes',
                'visibility' => 'public',
                'category'   => 'general',
                'uploader'   => 'talent_two',
                'task'       => 'research:competitors',
                'days_ago'   => 3,
                'body'       => $this->researchNotes(),
            ],
        ];

        $count = 0;

        foreach ($files as $spec) {
            $workspace = $this->workspaces[$spec['workspace']];
            $extension = pathinfo($spec['name'], PATHINFO_EXTENSION);
            $slug      = Str::slug(pathinfo($spec['name'], PATHINFO_FILENAME));
            if (! str_starts_with($slug, DemoDefinition::FILE_PREFIX)) {
                $slug = DemoDefinition::FILE_PREFIX . $slug;
            }
            $stored = $slug . '.' . $extension;
            $path   = 'workspaces/' . $workspace->id . '/' . $stored;

            // Write real bytes to the PRIVATE disk. `local` is rooted at
            // storage/app/private and is never web-accessible.
            Storage::disk('local')->makeDirectory('workspaces/' . $workspace->id);
            Storage::disk('local')->put($path, $spec['body']);

            $created = $this->daysAgo($spec['days_ago'], 11);

            $file = new WorkspaceFile([
                'workspace_id'        => $workspace->id,
                'uploaded_by_user_id' => $this->users[$spec['uploader']]->id,
                'workspace_task_id'   => $spec['task'] ? ($this->tasks[$spec['task']] ?? null)?->id : null,
                'title'               => $spec['title'],
                'original_filename'   => $spec['name'],
                'stored_filename'     => $stored,
                'storage_path'        => $path,
                'mime_type'           => $extension === 'pdf' ? 'application/pdf' : 'text/plain',
                'file_size'           => strlen($spec['body']),
                'visibility'          => $spec['visibility'],
                'category'            => $spec['category'],
                'description'         => DemoDefinition::MARKER . ' Safe demo file created by gvos:demo-setup.',
                'downloads_count'     => 0,
            ]);
            $file->created_at = $created;
            $file->updated_at = $created;
            $file->save();

            $count++;
        }

        $this->summary['files'] = $count;
    }

    // ── Time logs ────────────────────────────────────────────────────────────

    private function buildTimeLogs(): void
    {
        // [workspace, user, task key|null, days ago, start hour, minutes,
        //  status, visibility, summary, client summary|null]
        $logs = [
            ['exec', 'talent_one', 'exec:folder', 12, 9, 180, 'approved', 'client_summary',
                'Assembled and indexed the client delivery folder.',
                'Delivery folder assembled and indexed for review.'],
            ['exec', 'talent_one', 'exec:opsreport', 8, 10, 120, 'approved', 'client_summary',
                'Drafted the weekly operations report from the week\'s activity.',
                'Weekly operations report drafted.'],
            ['exec', 'talent_one', 'exec:crm', 5, 9, 240, 'approved', 'internal',
                'CRM clean-up: merged duplicates and corrected outdated contact details.', null],
            ['exec', 'talent_one', 'exec:notes', 1, 14, 90, 'submitted', 'internal',
                'Cleaned up meeting notes and converted agreed points into tracked actions.', null],
            ['exec', 'talent_one', 'exec:calendar', 0, 9, 45, 'draft', 'internal',
                'Started building next week\'s executive calendar.', null],

            ['cx', 'talent_two', 'cx:enquiries', 10, 9, 300, 'approved', 'client_summary',
                'Worked through the enquiry backlog and responded to standard cases.',
                'Customer enquiry backlog reduced; standard cases answered.'],
            ['cx', 'talent_two', 'cx:notes', 6, 11, 150, 'approved', 'internal',
                'Reviewed call notes and assigned owners to each action point.', null],
            ['cx', 'talent_one', 'cx:leadsheet', 3, 13, 120, 'submitted', 'internal',
                'Updated the lead qualification spreadsheet with this week\'s enquiries.', null],
            ['cx', 'talent_two', 'cx:templates', 2, 10, 75, 'rejected', 'internal',
                'Template tidy-up started before the enquiry backlog was cleared.', null],
            ['cx', 'talent_two', 'cx:enquiries', 0, 10, 60, 'draft', 'internal',
                'Continuing the enquiry follow-ups for today.', null],

            ['research', 'talent_two', 'research:competitors', 4, 9, 210, 'approved', 'client_summary',
                'Profiled three logistics competitors across pricing and service coverage.',
                'Three competitor profiles completed.'],
            ['research', 'talent_two', 'research:folder', 1, 14, 90, 'submitted', 'internal',
                'Assembled the interim research pack for review.', null],

            ['restricted', 'talent_one', 'restricted:enquiries', 9, 9, 180, 'approved', 'client_summary',
                'Cleared the outstanding finance enquiries queue.',
                'Outstanding finance enquiries cleared.'],
            ['restricted', 'talent_one', 'restricted:opsreport', 2, 10, 105, 'submitted', 'internal',
                'Drafted the operations report covering the past two weeks.', null],
        ];

        $count = 0;

        foreach ($logs as [$workspaceSlug, $userSlug, $taskKey, $daysAgo, $startHour, $minutes, $status, $visibility, $summary, $clientSummary]) {
            $workspace = $this->workspaces[$workspaceSlug];
            $start     = $this->daysAgo($daysAgo, $startHour);
            $end       = $start->copy()->addMinutes($minutes);
            $reviewed  = in_array($status, ['approved', 'rejected'], true);

            $log = new WorkspaceTimeLog([
                'workspace_id'           => $workspace->id,
                'user_id'                => $this->users[$userSlug]->id,
                'workspace_task_id'      => $taskKey ? ($this->tasks[$taskKey] ?? null)?->id : null,
                'log_date'               => $start->toDateString(),
                'started_at'             => $start,
                'ended_at'               => $end,
                'duration_minutes'       => $minutes,
                'work_summary'           => $summary,
                'work_details'           => DemoDefinition::MARKER . ' Internal detail notes for the demo environment.',
                'status'                 => $status,
                'reviewed_by_user_id'    => $reviewed ? $this->users['manager']->id : null,
                'reviewed_at'            => $reviewed ? $end->copy()->addDay()->setTime(10, 0) : null,
                'manager_notes'          => $status === 'rejected'
                    ? 'Please resubmit with the enquiry backlog time logged separately.'
                    : ($reviewed ? 'Approved — hours match the task activity.' : null),
                'client_visible_summary' => $clientSummary,
                'visibility'             => $visibility,
            ]);
            $log->created_at = $start;
            $log->updated_at = $reviewed ? $end->copy()->addDay() : $end;
            $log->save();

            $count++;
        }

        // Deliberately no `running` timer is seeded. A seeded running timer would
        // occupy the one-running-timer-per-user slot and block testers from
        // starting their own. Testers should start a timer manually to test it.
        $this->summary['time_logs'] = $count;
    }

    // ── Weekly reports ───────────────────────────────────────────────────────

    private function buildWeeklyReports(): void
    {
        $lastWeekStart = $this->today->copy()->subWeek()->startOfWeek();
        $thisWeekStart = $this->today->copy()->startOfWeek();

        $reports = [
            [
                'workspace'    => 'exec',
                'status'       => 'published',
                'week_start'   => $lastWeekStart,
                'total_minutes' => 540,
                'summary'      => 'Executive support ran to plan last week. The delivery folder was completed and indexed, the weekly operations report was drafted, and the CRM clean-up moved forward substantially.',
                'achievements' => "• Client delivery folder assembled and indexed\n• Weekly operations report drafted\n• 40+ duplicate CRM records merged",
                'blockers'     => 'Three travel receipts are still outstanding, which is holding up the expense summary.',
                'next_steps'   => 'Chase the outstanding receipts, finish the CRM clean-up, and build next week\'s calendar.',
                'client_notes' => 'Everything scheduled for last week is complete apart from the expense summary, which is waiting on a few outstanding receipts. Nothing needs action from you at this stage.',
            ],
            [
                'workspace'     => 'exec',
                'status'        => 'draft',
                'week_start'    => $thisWeekStart,
                'total_minutes' => 135,
                'summary'       => 'Work in progress for the current week. Calendar preparation has started and meeting notes have been submitted for review.',
                'achievements'  => "• Meeting notes cleaned up and actions assigned\n• Calendar preparation started",
                'blockers'      => 'Expense receipts still outstanding.',
                'next_steps'    => 'Complete the calendar, close out the CRM clean-up.',
                'client_notes'  => 'Draft — not yet shared with the client.',
            ],
            [
                'workspace'     => 'cx',
                'status'        => 'published',
                'week_start'    => $lastWeekStart,
                'total_minutes' => 450,
                'summary'       => 'Customer experience support focused on clearing the enquiry backlog. Standard cases were answered and the remaining items were separated for approval.',
                'achievements'  => "• Enquiry backlog substantially reduced\n• Call notes reviewed and actions assigned\n• Refund cases separated for client sign-off",
                'blockers'      => 'Refunds above the standard threshold require client sign-off, which slows the queue.',
                'next_steps'    => 'Finish the response template tidy-up and clear the remaining approval-dependent cases.',
                'client_notes'  => 'The enquiry backlog is substantially reduced. A short list of refund cases is waiting on your approval before they can be closed.',
            ],
        ];

        $count = 0;

        foreach ($reports as $spec) {
            $workspace = $this->workspaces[$spec['workspace']];
            $weekStart = $spec['week_start']->copy();
            $weekEnd   = $weekStart->copy()->addDays(6);
            $published = $spec['status'] === 'published';

            $report = new WorkspaceWeeklyReport([
                'workspace_id'         => $workspace->id,
                'week_start_date'      => $weekStart->toDateString(),
                'week_end_date'        => $weekEnd->toDateString(),
                'prepared_by_user_id'  => $this->users['manager']->id,
                'reviewed_by_user_id'  => $published ? $this->users['manager']->id : null,
                'total_minutes'        => $spec['total_minutes'],
                'summary'              => $spec['summary'],
                'achievements'         => $spec['achievements'],
                // blockers + next_steps are internal — never shown to clients.
                'blockers'             => $spec['blockers'],
                'next_steps'           => $spec['next_steps'],
                'client_notes'         => $spec['client_notes'],
                'status'               => $spec['status'],
                'published_at'         => $published ? $weekEnd->copy()->addDay()->setTime(9, 0) : null,
                'generated_at'         => $weekEnd->copy()->setTime(17, 0),
                'generated_by_user_id' => $this->users['manager']->id,
            ]);
            $report->created_at = $weekEnd->copy()->setTime(17, 0);
            $report->updated_at = $published
                ? $weekEnd->copy()->addDay()->setTime(9, 0)
                : $weekEnd->copy()->setTime(17, 30);
            $report->save();

            $count++;
        }

        $this->summary['weekly_reports'] = $count;
    }

    // ── Invoices and payments ────────────────────────────────────────────────

    /** @var array<string,Invoice> */
    private array $invoices = [];

    private function buildBillingDocuments(): void
    {
        $specs = [
            // Fully paid
            [
                'key'         => 'paid',
                'number'      => DemoDefinition::INVOICE_NUMBER_PREFIX . '0001',
                'workspace'   => 'exec',
                'client'      => 'individual_client',
                'company'     => 'apexbridge',
                'issue_days'  => 18,
                'due_days'    => 11,
                'status'      => 'paid',
                'paid_amount' => 900.00,
                'items'       => [
                    ['Executive support retainer — bi-weekly (demo)', 1, 900.00, 'subscription'],
                ],
            ],
            // Due soon
            [
                'key'         => 'due_soon',
                'number'      => DemoDefinition::INVOICE_NUMBER_PREFIX . '0002',
                'workspace'   => 'cx',
                'client'      => 'business_admin',
                'company'     => 'northstar',
                'issue_days'  => 5,
                'due_days'    => -2, // due in 2 days
                'status'      => 'issued',
                'paid_amount' => 0.00,
                'items'       => [
                    ['Managed operations retainer — monthly (demo)', 1, 1800.00, 'subscription'],
                    ['Additional support hours (demo)', 4, 45.00, 'extra_hours'],
                ],
            ],
            // Overdue
            [
                'key'         => 'overdue',
                'number'      => DemoDefinition::INVOICE_NUMBER_PREFIX . '0003',
                'workspace'   => 'restricted',
                'client'      => 'restricted_client',
                'company'     => null,
                'issue_days'  => 26,
                'due_days'    => 12,
                'status'      => 'overdue',
                'paid_amount' => 0.00,
                'items'       => [
                    ['Finance operations support — monthly (demo)', 1, 1800.00, 'subscription'],
                ],
            ],
        ];

        foreach ($specs as $spec) {
            $workspace    = $this->workspaces[$spec['workspace']];
            $subscription = WorkspaceSubscription::where('workspace_id', $workspace->id)->first();

            $invoice = Invoice::create([
                'invoice_number'            => $spec['number'],
                'workspace_id'              => $workspace->id,
                'workspace_subscription_id' => $subscription?->id,
                'client_profile_id'         => $this->clientProfiles[$spec['client']]->id,
                'company_id'                => $spec['company'] ? $this->companies[$spec['company']]->id : null,
                'currency'                  => 'USD',
                'subtotal'                  => 0,
                'discount_amount'           => 0,
                'tax_amount'                => 0,
                'total_amount'              => 0,
                'amount_paid'               => 0,
                'balance_due'               => 0,
                'status'                    => 'draft',
                'issue_date'                => $this->daysAgo($spec['issue_days'], 0)->toDateString(),
                'due_date'                  => $this->today->copy()->subDays($spec['due_days'])->toDateString(),
                'notes'                     => 'Demo invoice for GVOS internal testing. Amounts are fictional.',
                'internal_notes'            => DemoDefinition::MARKER . ' Internal note: not visible to clients.',
            ]);

            foreach ($spec['items'] as [$description, $qty, $unit, $type]) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $description,
                    'quantity'    => $qty,
                    'unit_amount' => $unit,
                    'item_type'   => $type,
                ]);
            }

            // InvoiceItem's saved hook recalculates subtotal/total/balance on the
            // parent invoice. Reload, then apply the demo payment state.
            $invoice->refresh();
            $invoice->amount_paid = $spec['paid_amount'];
            $invoice->balance_due = max(0, (float) $invoice->total_amount - $spec['paid_amount']);
            $invoice->status      = $spec['status'];
            $invoice->paid_at     = $spec['status'] === 'paid' ? $this->daysAgo($spec['due_days'] + 2, 11) : null;
            $invoice->save();

            $this->invoices[$spec['key']] = $invoice;
        }

        // One manually confirmed payment against the fully paid invoice.
        // Payment::confirm() is deliberately NOT called: the invoice and
        // subscription are already seeded in their final consistent state, and
        // confirm() would re-apply the amount and mutate subscription status.
        $paid = $this->invoices['paid'];

        Payment::create([
            'payment_reference'         => DemoDefinition::PAYMENT_REFERENCE_PREFIX . '000001',
            'invoice_id'                => $paid->id,
            'workspace_id'              => $paid->workspace_id,
            'workspace_subscription_id' => $paid->workspace_subscription_id,
            'provider'                  => 'manual',
            'provider_reference'        => 'DEMO-BANK-REF-0001',
            'currency'                  => 'USD',
            'amount'                    => 900.00,
            'status'                    => 'confirmed',
            'paid_at'                   => $this->daysAgo(9, 11),
            'confirmed_by_user_id'      => $this->users['operations_admin']->id,
            'confirmation_notes'        => DemoDefinition::MARKER . ' Manually confirmed demo payment. No payment provider was contacted.',
            'raw_payload'               => null,
        ]);

        // A pending (unconfirmed) payment on the overdue invoice, so the
        // payments screen shows more than one state.
        $overdue = $this->invoices['overdue'];

        Payment::create([
            'payment_reference'         => DemoDefinition::PAYMENT_REFERENCE_PREFIX . '000002',
            'invoice_id'                => $overdue->id,
            'workspace_id'              => $overdue->workspace_id,
            'workspace_subscription_id' => $overdue->workspace_subscription_id,
            'provider'                  => 'bank_transfer',
            'provider_reference'        => 'DEMO-BANK-REF-0002',
            'currency'                  => 'USD',
            'amount'                    => 1800.00,
            'status'                    => 'pending',
            'paid_at'                   => null,
            'confirmed_by_user_id'      => null,
            'confirmation_notes'        => DemoDefinition::MARKER . ' Awaiting manual confirmation in the demo environment.',
            'raw_payload'               => null,
        ]);

        $this->summary['invoices'] = count($this->invoices);
        $this->summary['payments'] = 2;
    }

    // ── Vault ────────────────────────────────────────────────────────────────

    private function buildVaultItems(): void
    {
        $items = [
            [
                'workspace'   => 'exec',
                'title'       => 'Demo CRM Login',
                'category'    => 'login',
                'login_url'   => 'https://crm.demo.invalid/login',
                'username'    => 'demo.assistant@demo.invalid',
                // Obviously fake placeholder. Stored via the model's encrypted
                // cast and never printed by any GVOS command.
                'secret'      => 'DemoOnly-CRM-NotARealSecret-001',
                'visibility'  => 'assigned_users',
                'roles'       => ['manager', 'talent'],
                'users'       => ['talent_one'],
                'notes'       => 'Demo credential. Not a real system.',
            ],
            [
                'workspace'   => 'exec',
                'title'       => 'Demo Shared Support Inbox',
                'category'    => 'email',
                'login_url'   => 'https://mail.demo.invalid',
                'username'    => 'support@demo.invalid',
                'secret'      => 'DemoOnly-Inbox-NotARealSecret-002',
                'visibility'  => 'workspace_admins',
                'roles'       => [],
                'users'       => [],
                'notes'       => 'Demo credential restricted to workspace admins and managers.',
            ],
            [
                'workspace'   => 'cx',
                'title'       => 'Demo Social Media Scheduler',
                'category'    => 'social_media',
                'login_url'   => 'https://scheduler.demo.invalid/signin',
                'username'    => 'northstar.demo@demo.invalid',
                'secret'      => 'DemoOnly-Scheduler-NotARealSecret-003',
                'visibility'  => 'assigned_users',
                'roles'       => ['talent'],
                'users'       => ['talent_two'],
                'notes'       => 'Demo credential available to the assigned talent.',
            ],
        ];

        $count = 0;

        foreach ($items as $spec) {
            $workspace = $this->workspaces[$spec['workspace']];
            $created   = $this->daysAgo(14, 12);

            $item = new WorkspaceVaultItem([
                'workspace_id'     => $workspace->id,
                'created_by'       => $this->users['manager']->id,
                'updated_by'       => $this->users['manager']->id,
                'title'            => $spec['title'],
                'category'         => $spec['category'],
                'login_url'        => $spec['login_url'],
                'username'         => $spec['username'],
                'secret_value'     => $spec['secret'],
                'notes'            => DemoDefinition::MARKER . ' ' . $spec['notes'],
                'visibility'       => $spec['visibility'],
                'status'           => 'active',
                'allowed_roles'    => $spec['roles'],
                'allowed_user_ids' => array_map(fn ($slug) => $this->users[$slug]->id, $spec['users']),
            ]);
            $item->created_at = $created;
            $item->updated_at = $created;
            $item->save();

            $count++;
        }

        $this->summary['vault_items'] = $count;
    }

    // ── Invitations ──────────────────────────────────────────────────────────

    private function buildInvitations(): void
    {
        // Tokens are auto-generated by the model and are never printed anywhere.
        $invitations = [
            [
                'workspace'      => 'cx',
                'email'          => 'new.talent.demo@' . DemoDefinition::EMAIL_DOMAIN,
                'name'           => 'Pending Demo Invitee',
                'platform_role'  => 'talent',
                'workspace_role' => 'talent',
                'status'         => 'pending',
                'expires_at'     => $this->today->copy()->addDays(6)->setTime(17, 0),
                'accepted'       => null,
            ],
            [
                'workspace'      => 'cx',
                'email'          => DemoDefinition::USERS['business_staff']['email'],
                'name'           => DemoDefinition::USERS['business_staff']['name'],
                'platform_role'  => 'business_client_staff',
                'workspace_role' => 'client_staff',
                'status'         => 'accepted',
                'expires_at'     => $this->daysAgo(8, 17),
                'accepted'       => 'business_staff',
            ],
            [
                'workspace'      => 'cx',
                'email'          => 'revoked.invite.demo@' . DemoDefinition::EMAIL_DOMAIN,
                'name'           => 'Revoked Demo Invitee',
                'platform_role'  => 'talent',
                'workspace_role' => 'talent',
                'status'         => 'revoked',
                'expires_at'     => $this->today->copy()->addDays(3)->setTime(17, 0),
                'accepted'       => null,
            ],
            [
                'workspace'      => 'exec',
                'email'          => 'expired.invite.demo@' . DemoDefinition::EMAIL_DOMAIN,
                'name'           => 'Expired Demo Invitee',
                'platform_role'  => 'individual_client',
                'workspace_role' => 'observer',
                'status'         => 'expired',
                'expires_at'     => $this->daysAgo(4, 17),
                'accepted'       => null,
            ],
        ];

        $count = 0;

        foreach ($invitations as $spec) {
            $workspace = $this->workspaces[$spec['workspace']];
            $created   = $this->daysAgo(13, 10);

            $invitation = new WorkspaceInvitation([
                'workspace_id'   => $workspace->id,
                'invited_by'     => $this->users['manager']->id,
                'email'          => $spec['email'],
                'name'           => $spec['name'],
                'platform_role'  => $spec['platform_role'],
                'workspace_role' => $spec['workspace_role'],
                'status'         => $spec['status'],
                'expires_at'     => $spec['expires_at'],
                'accepted_at'    => $spec['accepted'] ? $this->daysAgo(11, 15) : null,
                'accepted_by'    => $spec['accepted'] ? $this->users[$spec['accepted']]->id : null,
            ]);
            $invitation->created_at = $created;
            $invitation->updated_at = $created;
            $invitation->save();

            $count++;
        }

        $this->summary['invitations'] = $count;
    }

    // ── Notifications (database channel only) ────────────────────────────────

    private function buildNotifications(): void
    {
        $count = 0;

        $notify = function (string $userSlug, $notification) use (&$count) {
            $this->users[$userSlug]->notify($notification);
            $count++;
        };

        // Task assigned
        if (isset($this->tasks['exec:calendar'])) {
            $notify('talent_one', new TaskAssignedNotification($this->tasks['exec:calendar']));
        }
        if (isset($this->tasks['cx:enquiries'])) {
            $notify('talent_two', new TaskAssignedNotification($this->tasks['cx:enquiries']));
        }

        // Time log submitted → manager
        $submittedLog = WorkspaceTimeLog::whereIn('workspace_id', array_map(
            fn (Workspace $w) => $w->id,
            $this->workspaces
        ))->where('status', 'submitted')->first();

        if ($submittedLog) {
            $notify('manager', new TimeLogSubmittedNotification($submittedLog));
        }

        // Weekly report published → client side
        $publishedReport = WorkspaceWeeklyReport::where('workspace_id', $this->workspaces['exec']->id)
            ->where('status', 'published')
            ->first();

        if ($publishedReport) {
            $notify('individual_client', new WeeklyReportPublishedNotification($publishedReport));
        }

        $cxReport = WorkspaceWeeklyReport::where('workspace_id', $this->workspaces['cx']->id)
            ->where('status', 'published')
            ->first();

        if ($cxReport) {
            $notify('business_admin', new WeeklyReportPublishedNotification($cxReport));
            $notify('business_staff', new WeeklyReportPublishedNotification($cxReport));
        }

        // Invoice issued
        if (isset($this->invoices['paid'])) {
            $notify('individual_client', new InvoiceIssuedNotification($this->invoices['paid']));
        }
        if (isset($this->invoices['due_soon'])) {
            $notify('business_admin', new InvoiceIssuedNotification($this->invoices['due_soon']));
        }

        // File uploaded
        $file = WorkspaceFile::where('workspace_id', $this->workspaces['cx']->id)->first();
        if ($file) {
            $notify('manager', new WorkspaceFileUploadedNotification($file));
            $notify('business_admin', new WorkspaceFileUploadedNotification($file));
        }

        // Billing overdue → restricted client + manager
        $overdueSub = WorkspaceSubscription::where('workspace_id', $this->workspaces['restricted']->id)->first();
        if ($overdueSub) {
            $notify('restricted_client', new BillingOverdueNotification($overdueSub));
            $notify('manager', new BillingOverdueNotification($overdueSub));
        }

        $this->summary['notifications'] = $count;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function daysAgo(int $days, int $hour = 9, int $minute = 0): Carbon
    {
        return $this->today->copy()->subDays($days)->setTime($hour, $minute);
    }

    // ── Demo file contents ───────────────────────────────────────────────────

    private function operationsBrief(): string
    {
        return <<<TXT
        GVOS DEMO — OPERATIONS BRIEF
        ============================

        This is a sample file created by the GVOS demo environment. It contains no
        real client information.

        Scope of support
        ----------------
        1. Calendar and scheduling management
        2. Inbox triage and prioritisation
        3. CRM record maintenance
        4. Monthly expense collation
        5. Weekly reporting

        Working pattern
        ---------------
        Monday to Friday, 09:00-17:00 local time. Urgent items are flagged in the
        workspace chat rather than by email.

        Escalation
        ----------
        Anything blocked for more than one working day should be raised with the
        line manager and marked as blocked on the task board.
        TXT;
    }

    private function weeklyChecklist(): string
    {
        return <<<TXT
        GVOS DEMO — WEEKLY CHECKLIST (INTERNAL)
        =======================================

        Internal-only file. Clients cannot see files marked with internal visibility.

        Monday
        ------
        [ ] Review the task board and confirm priorities with the client
        [ ] Check for any blocked items carried over from last week

        Midweek
        -------
        [ ] Confirm all time logs are submitted for review
        [ ] Chase anything outstanding from the client

        Friday
        ------
        [ ] Approve or reject submitted time logs
        [ ] Generate the weekly report draft
        [ ] Publish the report once reviewed
        TXT;
    }

    private function responseGuide(): string
    {
        return <<<TXT
        GVOS DEMO — CUSTOMER RESPONSE GUIDE
        ===================================

        Sample deliverable created by the GVOS demo environment. No real customer
        data is included.

        Delivery delay
        --------------
        Acknowledge within one working hour, confirm the revised delivery window,
        and offer the standard goodwill option where applicable.

        Refund request
        --------------
        Confirm the order reference and the reason. Refunds above the standard
        threshold require client sign-off before they are confirmed.

        Product question
        ----------------
        Answer directly where the information is published. Where it is not,
        route the question to the client team rather than guessing.

        Tone
        ----
        Plain, direct and courteous. Avoid internal terminology.
        TXT;
    }

    private function researchNotes(): string
    {
        return <<<TXT
        GVOS DEMO — COMPETITOR RESEARCH NOTES
        =====================================

        Sample working notes created by the GVOS demo environment. All company
        names below are fictional.

        Competitor A (fictional)
        ------------------------
        Positioning: regional same-day delivery
        Pricing: published, banded by weight
        Turnaround: same day within the metro area

        Competitor B (fictional)
        ------------------------
        Positioning: nationwide freight
        Pricing: quote only
        Turnaround: 2-4 working days

        Competitor C (fictional)
        ------------------------
        Positioning: e-commerce fulfilment
        Pricing: published subscription tiers
        Turnaround: next working day

        Outstanding
        -----------
        Two further competitors do not publish pricing. Ranges will be estimated
        from published case studies and clearly marked as estimates.
        TXT;
    }

    /**
     * A minimal, valid, single-page PDF built by hand (no external library).
     * Byte offsets in the xref table are computed at runtime so the file is
     * structurally correct and opens in a normal PDF reader.
     */
    private function samplePdf(): string
    {
        $lines = [
            'GVOS DEMO - CLIENT SUMMARY',
            '',
            'This is a sample PDF generated by the GVOS demo environment.',
            'It contains no real client information.',
            '',
            'Meeting notes have been reviewed and every agreed action',
            'now has a named owner and a due date on the task board.',
        ];

        $y = 760;
        $text = "BT\n/F1 12 Tf\n";
        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $text .= "1 0 0 1 60 {$y} Tm\n({$escaped}) Tj\n";
            $y -= 18;
        }
        $text .= "ET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] "
                . "/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($text) . " >>\nstream\n{$text}\nendstream\nendobj\n",
        ];

        $pdf     = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }
}
