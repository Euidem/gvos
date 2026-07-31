# GVOS — Demo Environment

**Created:** 2026-07-31 (Phase 27)
**Purpose:** A clean, realistic and repeatable demo environment so the internal
team can test GVOS under every role without touching genuine production data.

> **The shared demo password is NOT stored in this repository.**
> It is supplied at runtime when `php artisan gvos:demo-setup` prompts for it.
> Never commit it, never paste it into a document that is pushed to GitHub.

---

## 1. Purpose

The demo environment gives testers:

- one login per GVOS role, all sharing a single temporary password;
- workspaces that already contain believable operational history, so every
  dashboard, Kanban board, chat thread, report and invoice screen renders with
  meaningful content instead of empty states;
- deliberate edge-case scenarios (billing restriction, suspended account,
  blocked task, rejected time log, expired invitation) that would otherwise be
  tedious to reproduce by hand.

It is **not** a new product feature. It creates data only. No application
behaviour, permission, billing calculation, encryption or security control was
changed to build it.

---

## 2. Demo accounts

All twelve accounts share the temporary password entered during setup.

| # | Scenario | Name | Email | Platform role | Account status |
|---|----------|------|-------|---------------|----------------|
| 1 | Super Admin | Sarah Admin | `superadmin.demo@gvos.test` | `super_admin` | active |
| 2 | Operations Admin | Michael Operations | `operations.demo@gvos.test` | `operations_admin` | active |
| 3 | Manager | Grace Manager | `manager.demo@gvos.test` | `line_manager` | active |
| 4 | Talent 1 | Daniel Okafor | `talent.one.demo@gvos.test` | `talent` | active |
| 5 | Talent 2 | Mariam Bello | `talent.two.demo@gvos.test` | `talent` | active |
| 6 | Individual Client | Amina Yusuf | `individual.client.demo@gvos.test` | `individual_client` | active |
| 7 | Business Client Admin | Chinedu Eze | `business.admin.demo@gvos.test` | `business_client_admin` | active |
| 8 | Business Client Staff | Lara Adeyemi | `business.staff.demo@gvos.test` | `business_client_staff` | active |
| 9 | Active Lead | Tunde Williams | `lead.demo@gvos.test` | `active_lead` | active |
| 10 | Observer (read-only) | Naomi Observer | `observer.demo@gvos.test` | `business_client_staff` | active |
| 11 | Suspended User | Suspended Demo User | `suspended.demo@gvos.test` | `talent` | **suspended** |
| 12 | Restricted Billing Client | Restricted Client | `restricted.client.demo@gvos.test` | `individual_client` | active |

### Note on the Observer account

GVOS has **no `observer` platform role** — the eight platform roles are
`super_admin`, `operations_admin`, `line_manager`, `talent`,
`individual_client`, `business_client_admin`, `business_client_staff` and
`active_lead`. "Observer" exists only as a **workspace member role**.

Naomi Observer therefore holds the lowest-privilege client platform role
(`business_client_staff`) and is added to `DEMO-CX-002` with the workspace
member role `observer`, which is read-only. No new role was created and no
permission was changed.

### Note on the Super Admin account

`superadmin.demo@gvos.test` can reach the Filament admin panel at `/admin`.
Treat its password with the same care as a real admin credential and delete the
demo data (`gvos:demo-clean --execute`) once testing is finished.

---

## 3. Demo companies

| Company | Type | Linked demo users |
|---------|------|-------------------|
| Northstar Retail Group | `business` | Chinedu Eze (client admin), Lara Adeyemi (client staff), Naomi Observer |
| ApexBridge Consulting | `individual` | Amina Yusuf (individual client) |

`ApexBridge Consulting` is stored with `type = individual` so Amina Yusuf
remains a genuine individual client while still being attached to a named
organisation, which the current data model supports.

Restricted Client is deliberately **not** attached to a company — an individual
client with no organisation.

---

## 4. Demo workspaces

| Code | Name | Status | Billing | Manager | Talent | Client side |
|------|------|--------|---------|---------|--------|-------------|
| `DEMO-EXEC-001` | Executive Support Operations | active / ongoing | Bi-weekly, active | Grace Manager | Daniel Okafor | Amina Yusuf (client admin) |
| `DEMO-CX-002` | Customer Experience Support | active / ongoing | Monthly, active (due soon) | Grace Manager | Daniel Okafor + Mariam Bello | Chinedu Eze (client admin), Lara Adeyemi (client staff), Naomi Observer (observer) |
| `DEMO-RESEARCH-003` | Market Research Sprint | active / trial | Trial subscription | Grace Manager | Mariam Bello | Tunde Williams (active lead, client admin membership) |
| `DEMO-RESTRICTED-004` | Finance Operations Support | active / ongoing | **Overdue + restricted** | Grace Manager | Daniel Okafor | Restricted Client (client admin) |

---

## 5. Test scenarios available

### Tasks and Kanban
Every workspace has tasks spread across `pending`, `in_progress`, `blocked`,
`submitted`, `revision_requested`, `approved` and `closed`, with mixed
priorities (`low` → `urgent`), realistic due dates (some overdue, some due
soon), assignees, public comments and manager-only internal comments.

### Workspace chat
Public threads (manager assigns priorities → talent acknowledges → client asks
for an update → manager confirms the report is published → client staff adds a
clarification) plus **internal** messages in `DEMO-EXEC-001` and `DEMO-CX-002`
that clients and talent must not be able to see.

### Files
Five real files written to the private disk
(`storage/app/private/workspaces/{id}/`):

| File | Workspace | Visibility | Notes |
|------|-----------|------------|-------|
| Demo Operations Brief.txt | `DEMO-EXEC-001` | public | client-visible |
| Demo Weekly Checklist.txt | `DEMO-EXEC-001` | **internal** | must be hidden from clients |
| Demo Client Summary.pdf | `DEMO-EXEC-001` | public | valid PDF, linked to a task |
| Demo Customer Response Guide.txt | `DEMO-CX-002` | public | linked to a task |
| Demo Competitor Research Notes.txt | `DEMO-RESEARCH-003` | public | linked to a task |

Every record points at bytes that actually exist, so downloads work.

### Time logs
Logs in `draft`, `submitted`, `approved` and `rejected` states across the last
two weeks, with work summaries, mixed durations, and manager review notes.
Approved logs marked `client_summary` carry a separate client-facing summary —
clients never see raw internal work details.

**No running timer is seeded on purpose.** GVOS enforces one running timer per
user; a seeded timer would occupy that slot and stop testers from starting their
own. Start a timer manually to test the tracker.

### Weekly reports
- `DEMO-EXEC-001`: one **published** report (last week) + one **draft** (this week)
- `DEMO-CX-002`: one **published** report

Blockers and next steps are populated as internal fields; clients see only the
summary, achievements and client notes of published reports.

### Billing
- Two plans: `DEMO-PLAN-BIWEEKLY` (USD 900.00) and `DEMO-PLAN-MONTHLY` (USD 1,800.00)
- `GVOS-INV-DEMO-0001` — fully paid, with a confirmed manual payment
- `GVOS-INV-DEMO-0002` — issued, due in 2 days (due-soon state)
- `GVOS-INV-DEMO-0003` — overdue, with a *pending* (unconfirmed) bank-transfer payment
- `DEMO-RESTRICTED-004` — subscription is `overdue` with `restricted_at` set, so
  client access is blocked while internal staff keep working

All amounts and references are fictional. No payment provider is contacted and
no webhook fires.

### Password vault
| Item | Workspace | Who can reach it |
|------|-----------|------------------|
| Demo CRM Login | `DEMO-EXEC-001` | assigned talent + manager |
| Demo Shared Support Inbox | `DEMO-EXEC-001` | workspace admins / managers only |
| Demo Social Media Scheduler | `DEMO-CX-002` | assigned talent (Mariam Bello) |

Credentials are obviously fake and are stored through the normal encrypted
model cast. No GVOS command ever prints a secret value.

### Notifications and invitations
Database notifications for task assigned, time log submitted, weekly report
published, invoice issued, file uploaded and billing overdue.

Invitations exist in all four states — `pending`, `accepted`, `revoked`,
`expired`. **Invitation tokens are never printed by any command.**

> **Live invitation email testing:** the demo setup deliberately sends no email.
> To test real invitation delivery, confirm SMTP first, then invite a real
> controlled team address manually from a workspace's Members → Invite screen.

### Lead and trial
One active lead (`DEMO-LEAD-001`, Tunde Williams) with an accepted price
estimate and an active trial (`DEMO-TRIAL-001`) driving `DEMO-RESEARCH-003`.
All contact details are fake.

---

## 6. Commands

### `php artisan gvos:demo-audit`
Read-only. Reports two groups:

1. **Controlled demo data** — the records anchored to the exact demo
   identifiers. These are the only records `gvos:demo-clean` can delete.
2. **Other likely demo/test data** — anything matched by loose patterns
   (emails containing `demo`/`test`, other `DEMO-` workspace codes, companies
   named `Demo*`). Reported for manual review; **never deleted**.

Never writes. Never prints passwords, vault secrets or invitation tokens.
`--json` gives a machine-readable count summary.

### `php artisan gvos:demo-setup`
Creates or refreshes the controlled demo environment. Idempotent — safe to run
repeatedly.

```bash
php artisan gvos:demo-setup
```

Prompts twice for a hidden temporary password. Options:

| Option | Effect |
|--------|--------|
| `--password=...` | Non-interactive password supply. **Appears in shell history** — prefer the prompt. |
| `--force` | Skip the confirmation prompt (non-interactive deployment). |

Behaviour:
- accounts, companies, workspaces, memberships, plans and subscriptions are
  created/updated in place, so IDs and logins stay stable;
- operational content is deleted and rebuilt, so counts stay stable;
- the mail transport is forced to `array` for the whole run — no email can leave
  the server;
- the whole build runs in a database transaction and rolls back on error.

### `php artisan gvos:demo-verify`
PASS/FAIL health report over 16 checks: users, roles, companies, workspaces,
memberships, task statuses, messages, physical files, time logs, reports,
billing scenarios, vault decryption, notifications and invitation states, the
restricted workspace, the suspended user, and secret-handling. Exit code `0`
when everything passes, `1` otherwise.

### `php artisan gvos:demo-clean`
**Dry run by default.** Shows exactly what would be removed and deletes nothing.

```bash
php artisan gvos:demo-clean              # preview only
php artisan gvos:demo-clean --execute    # delete (asks you to type DELETE DEMO)
```

| Option | Effect |
|--------|--------|
| `--execute` | Required before anything is deleted. |
| `--force` | Skip the typed confirmation (non-interactive deployment only). Still needs `--execute`. |
| `--content-only` | Remove demo tasks/chat/files/logs/reports/billing/vault but keep the accounts, companies, workspaces and subscriptions. |

---

## 7. Resetting the demo data

**Refresh content, keep logins (fastest, most common):**

```bash
php artisan gvos:demo-setup
```

**Full reset — delete everything, then rebuild:**

```bash
php artisan gvos:demo-clean
php artisan gvos:demo-clean --execute
php artisan gvos:demo-setup
php artisan gvos:demo-verify
```

**Remove the demo environment permanently (after testing):**

```bash
php artisan gvos:demo-clean --execute
php artisan gvos:demo-audit
```

Run the audit afterwards to confirm nothing controlled remains.

---

## 8. Security warnings

1. **The shared password is a real credential.** One of the demo accounts is a
   super admin with full Filament access. Use a strong password, share it only
   inside the team, and delete the demo data when testing ends.
2. **Never commit the password.** It is not in this file, in any seeder, in any
   migration, or in any log. Keep it that way.
3. **`--password` leaks into shell history.** Prefer the interactive prompt.
4. **Do not run `gvos:demo-setup` against production unless you intend to.** It
   creates twelve real, loginable accounts.
5. **Delete demo data before public launch.** `gvos:demo-clean --execute`.
6. **Vault secrets are placeholders, not real credentials** — but they are
   still encrypted with `APP_KEY`. Rotating `APP_KEY` makes them undecryptable,
   exactly as it would for real vault entries.
7. **Never use `migrate:fresh` or `TRUNCATE`** to reset the demo data. Neither
   demo command uses them, and doing it manually would destroy genuine records.
8. **Cleanup is deliberately narrow.** It only ever removes records matching the
   exact controlled identifiers. Records that merely *look* like test data are
   reported by `gvos:demo-audit` and must be handled manually.

---

## 9. Controlled demo identifiers

`app/Support/Demo/DemoDefinition.php` is the single source of truth. Cleanup is
anchored to these and nothing else:

| Anchor | Value |
|--------|-------|
| Email domain | `@gvos.test` (the 12 exact addresses listed above) |
| Workspace codes | `DEMO-EXEC-001`, `DEMO-CX-002`, `DEMO-RESEARCH-003`, `DEMO-RESTRICTED-004` |
| Company names | `Northstar Retail Group`, `ApexBridge Consulting` |
| Billing plan codes | `DEMO-PLAN-BIWEEKLY`, `DEMO-PLAN-MONTHLY` |
| Invoice numbers | `GVOS-INV-DEMO-*` |
| Payment references | `GVOS-PAY-DEMO-*` |
| Lead / trial codes | `DEMO-LEAD-001`, `DEMO-TRIAL-001` |
| Notes marker | `[GVOS-DEMO]` |

Audit logs are **never** deleted. Deleting a demo user nulls the `user_id` on
their historical audit entries, preserving the trail.
