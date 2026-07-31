<?php

namespace App\Support\Demo;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\LeadRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;

/**
 * Phase 27 — Controlled GVOS demo environment definition.
 *
 * This class is the SINGLE SOURCE OF TRUTH for what counts as "controlled demo data".
 *
 * Every demo record is anchored to one of the exact identifiers declared here:
 *   – exact user emails            (USERS[*]['email'])
 *   – exact workspace codes        (WORKSPACES[*] keys)
 *   – exact company names          (COMPANIES[*]['name'])
 *   – exact billing plan codes     (BILLING_PLAN_CODES)
 *   – exact invoice numbers        (INVOICE_NUMBER_PREFIX)
 *   – exact payment references     (PAYMENT_REFERENCE_PREFIX)
 *   – exact lead code              (LEAD_CODE) / trial code (TRIAL_CODE)
 *
 * `gvos:demo-clean` only ever deletes records reachable from these anchors.
 * Anything matched by looser heuristics (the "test"/"demo" word patterns used by
 * `gvos:demo-audit`) is REPORTED ONLY and is never deleted.
 *
 * SECURITY NOTES
 *  – No password, vault secret, or invitation token is stored in this file.
 *  – The shared demo password is supplied at runtime to `gvos:demo-setup`.
 *  – Vault secret values below are obviously fake placeholders used only so the
 *    vault UI has something to reveal; they are still stored through the normal
 *    encrypted model cast and are never printed by any command.
 */
class DemoDefinition
{
    /** Email domain reserved for controlled demo accounts. */
    public const EMAIL_DOMAIN = 'gvos.test';

    /** Marker written into `notes` fields so demo rows are obvious in the admin panel. */
    public const MARKER = '[GVOS-DEMO]';

    /** Controlled billing plan codes. */
    public const BILLING_PLAN_CODES = [
        'biweekly' => 'DEMO-PLAN-BIWEEKLY',
        'monthly'  => 'DEMO-PLAN-MONTHLY',
    ];

    /** Controlled invoice numbers always start with this prefix. */
    public const INVOICE_NUMBER_PREFIX = 'GVOS-INV-DEMO-';

    /** Controlled payment references always start with this prefix. */
    public const PAYMENT_REFERENCE_PREFIX = 'GVOS-PAY-DEMO-';

    /** Controlled lead / trial codes. */
    public const LEAD_CODE  = 'DEMO-LEAD-001';
    public const TRIAL_CODE = 'DEMO-TRIAL-001';

    /** Stored filenames for demo workspace files (deterministic → idempotent). */
    public const FILE_PREFIX = 'demo-';

    // ── Users ────────────────────────────────────────────────────────────────

    /**
     * The 12 controlled demo accounts.
     *
     * `role` values are existing GVOS platform roles seeded by RoleSeeder.
     * There is no `observer` PLATFORM role in GVOS — "observer" is a workspace
     * member role only. The Observer demo account therefore holds the lowest
     * privilege client platform role and gets `observer` membership in a
     * workspace. No role escalation is introduced.
     */
    public const USERS = [
        'super_admin' => [
            'email'      => 'superadmin.demo@gvos.test',
            'name'       => 'Sarah Admin',
            'first_name' => 'Sarah',
            'last_name'  => 'Admin',
            'role'       => 'super_admin',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0001',
            'bio'        => 'Demo super administrator account for GVOS internal testing.',
            'label'      => 'Super Admin',
        ],
        'operations_admin' => [
            'email'      => 'operations.demo@gvos.test',
            'name'       => 'Michael Operations',
            'first_name' => 'Michael',
            'last_name'  => 'Operations',
            'role'       => 'operations_admin',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Abuja',
            'phone'      => '+000 000 0002',
            'bio'        => 'Demo operations administrator account for GVOS internal testing.',
            'label'      => 'Operations Admin',
        ],
        'manager' => [
            'email'      => 'manager.demo@gvos.test',
            'name'       => 'Grace Manager',
            'first_name' => 'Grace',
            'last_name'  => 'Manager',
            'role'       => 'line_manager',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0003',
            'bio'        => 'Demo line manager overseeing the GVOS demo workspaces.',
            'label'      => 'Line Manager',
        ],
        'talent_one' => [
            'email'      => 'talent.one.demo@gvos.test',
            'name'       => 'Daniel Okafor',
            'first_name' => 'Daniel',
            'last_name'  => 'Okafor',
            'role'       => 'talent',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Enugu',
            'phone'      => '+000 000 0004',
            'bio'        => 'Demo executive assistant talent account.',
            'label'      => 'Talent 1',
        ],
        'talent_two' => [
            'email'      => 'talent.two.demo@gvos.test',
            'name'       => 'Mariam Bello',
            'first_name' => 'Mariam',
            'last_name'  => 'Bello',
            'role'       => 'talent',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Ibadan',
            'phone'      => '+000 000 0005',
            'bio'        => 'Demo customer experience and research talent account.',
            'label'      => 'Talent 2',
        ],
        'individual_client' => [
            'email'      => 'individual.client.demo@gvos.test',
            'name'       => 'Amina Yusuf',
            'first_name' => 'Amina',
            'last_name'  => 'Yusuf',
            'role'       => 'individual_client',
            'status'     => 'active',
            'timezone'   => 'Europe/London',
            'country'    => 'United Kingdom',
            'city'       => 'London',
            'phone'      => '+000 000 0006',
            'bio'        => 'Demo individual client account.',
            'label'      => 'Individual Client',
        ],
        'business_admin' => [
            'email'      => 'business.admin.demo@gvos.test',
            'name'       => 'Chinedu Eze',
            'first_name' => 'Chinedu',
            'last_name'  => 'Eze',
            'role'       => 'business_client_admin',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0007',
            'bio'        => 'Demo business client administrator for Northstar Retail Group.',
            'label'      => 'Business Client Admin',
        ],
        'business_staff' => [
            'email'      => 'business.staff.demo@gvos.test',
            'name'       => 'Lara Adeyemi',
            'first_name' => 'Lara',
            'last_name'  => 'Adeyemi',
            'role'       => 'business_client_staff',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0008',
            'bio'        => 'Demo business client staff member for Northstar Retail Group.',
            'label'      => 'Business Client Staff',
        ],
        'lead' => [
            'email'      => 'lead.demo@gvos.test',
            'name'       => 'Tunde Williams',
            'first_name' => 'Tunde',
            'last_name'  => 'Williams',
            'role'       => 'active_lead',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Port Harcourt',
            'phone'      => '+000 000 0009',
            'bio'        => 'Demo active lead currently running a market research trial.',
            'label'      => 'Active Lead',
        ],
        'observer' => [
            'email'      => 'observer.demo@gvos.test',
            'name'       => 'Naomi Observer',
            'first_name' => 'Naomi',
            'last_name'  => 'Observer',
            'role'       => 'business_client_staff',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0010',
            'bio'        => 'Demo read-only stakeholder. Workspace membership role is "observer".',
            'label'      => 'Observer (read-only workspace member)',
        ],
        'suspended' => [
            'email'      => 'suspended.demo@gvos.test',
            'name'       => 'Suspended Demo User',
            'first_name' => 'Suspended',
            'last_name'  => 'Demo User',
            'role'       => 'talent',
            'status'     => 'suspended',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Lagos',
            'phone'      => '+000 000 0011',
            'bio'        => 'Demo account intentionally suspended to test the blocked-account flow.',
            'label'      => 'Suspended User',
        ],
        'restricted_client' => [
            'email'      => 'restricted.client.demo@gvos.test',
            'name'       => 'Restricted Client',
            'first_name' => 'Restricted',
            'last_name'  => 'Client',
            'role'       => 'individual_client',
            'status'     => 'active',
            'timezone'   => 'Africa/Lagos',
            'country'    => 'Nigeria',
            'city'       => 'Kano',
            'phone'      => '+000 000 0012',
            'bio'        => 'Demo client whose workspace is billing-restricted.',
            'label'      => 'Restricted Billing Client',
        ],
    ];

    // ── Companies ────────────────────────────────────────────────────────────

    public const COMPANIES = [
        'northstar' => [
            'name'                  => 'Northstar Retail Group',
            'legal_name'            => 'Northstar Retail Group (Demo) Ltd',
            'type'                  => 'business',
            'industry'              => 'Retail',
            'website'               => 'https://northstar-retail.demo.invalid',
            'country'               => 'Nigeria',
            'city'                  => 'Lagos',
            'timezone'              => 'Africa/Lagos',
            'company_email_domain'  => 'gvos.test',
            'primary_contact_name'  => 'Chinedu Eze',
            'primary_contact_email' => 'business.admin.demo@gvos.test',
            'primary_contact_phone' => '+000 000 0007',
            'status'                => 'active',
        ],
        'apexbridge' => [
            'name'                  => 'ApexBridge Consulting',
            'legal_name'            => 'ApexBridge Consulting (Demo)',
            'type'                  => 'individual',
            'industry'              => 'Professional Services',
            'website'               => 'https://apexbridge.demo.invalid',
            'country'               => 'United Kingdom',
            'city'                  => 'London',
            'timezone'              => 'Europe/London',
            'company_email_domain'  => 'gvos.test',
            'primary_contact_name'  => 'Amina Yusuf',
            'primary_contact_email' => 'individual.client.demo@gvos.test',
            'primary_contact_phone' => '+000 000 0006',
            'status'                => 'active',
        ],
    ];

    // ── Workspaces ───────────────────────────────────────────────────────────

    /** Controlled workspace codes, keyed by internal slug. */
    public const WORKSPACES = [
        'exec' => [
            'code'   => 'DEMO-EXEC-001',
            'name'   => 'Executive Support Operations',
            'status' => 'active',
            'type'   => 'ongoing',
        ],
        'cx' => [
            'code'   => 'DEMO-CX-002',
            'name'   => 'Customer Experience Support',
            'status' => 'active',
            'type'   => 'ongoing',
        ],
        'research' => [
            'code'   => 'DEMO-RESEARCH-003',
            'name'   => 'Market Research Sprint',
            'status' => 'active',
            'type'   => 'trial',
        ],
        'restricted' => [
            'code'   => 'DEMO-RESTRICTED-004',
            'name'   => 'Finance Operations Support',
            'status' => 'active',
            'type'   => 'ongoing',
        ],
    ];

    // ── Convenience accessors ────────────────────────────────────────────────

    /** @return string[] The 12 controlled demo email addresses. */
    public static function userEmails(): array
    {
        return array_values(array_column(self::USERS, 'email'));
    }

    /** @return string[] The 4 controlled demo workspace codes. */
    public static function workspaceCodes(): array
    {
        return array_values(array_column(self::WORKSPACES, 'code'));
    }

    /** @return string[] The 2 controlled demo company names. */
    public static function companyNames(): array
    {
        return array_values(array_column(self::COMPANIES, 'name'));
    }

    /** @return string[] The 2 controlled demo billing plan codes. */
    public static function billingPlanCodes(): array
    {
        return array_values(self::BILLING_PLAN_CODES);
    }

    /** Returns the controlled demo users that currently exist, keyed by email. */
    public static function existingUsers(): Collection
    {
        return User::whereIn('email', self::userEmails())->get()->keyBy('email');
    }

    /** Returns the controlled demo companies that currently exist, keyed by name. */
    public static function existingCompanies(): Collection
    {
        return Company::withTrashed()->whereIn('name', self::companyNames())->get()->keyBy('name');
    }

    /** Returns the controlled demo workspaces that currently exist, keyed by workspace_code. */
    public static function existingWorkspaces(): Collection
    {
        return Workspace::withTrashed()
            ->whereIn('workspace_code', self::workspaceCodes())
            ->get()
            ->keyBy('workspace_code');
    }

    /** @return int[] IDs of the controlled demo workspaces (may be empty). */
    public static function existingWorkspaceIds(): array
    {
        return self::existingWorkspaces()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** @return int[] IDs of the controlled demo users (may be empty). */
    public static function existingUserIds(): array
    {
        return self::existingUsers()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** Returns the controlled demo lead request, if it exists. */
    public static function existingLeadRequest(): ?LeadRequest
    {
        return LeadRequest::withTrashed()->where('lead_code', self::LEAD_CODE)->first();
    }

    /** @return int[] IDs of the controlled demo invoices. */
    public static function existingInvoiceIds(): array
    {
        return Invoice::withTrashed()
            ->where('invoice_number', 'like', self::INVOICE_NUMBER_PREFIX . '%')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Loose heuristics used ONLY by `gvos:demo-audit` to surface data that
     * *looks* like demo/test data but is NOT part of the controlled definition.
     * These patterns are never used for deletion.
     *
     * @return array<string, string> label => human-readable pattern description
     */
    public static function auditHeuristics(): array
    {
        return [
            'email_domain'   => 'Email ends with @' . self::EMAIL_DOMAIN,
            'email_demo'     => 'Email contains "demo"',
            'email_test'     => 'Email contains "test"',
            'workspace_code' => 'Workspace code starts with "DEMO-"',
            'company_name'   => 'Company name starts with "Demo" or "GVOS Demo"',
            'marker'         => 'Record notes contain the ' . self::MARKER . ' marker',
        ];
    }
}
