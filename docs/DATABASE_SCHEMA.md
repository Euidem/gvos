# GVOS — Database Schema

## Status: Phase 10 migrations created and active

---

## Phase 1 Tables (live)

### users
The main authentication table. All roles share this table.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | Display name |
| email | string unique | |
| email_verified_at | timestamp nullable | |
| password | string hashed | |
| timezone | string | default: UTC |
| status | enum | **active, suspended, inactive, pending** (pending added Phase 1) |
| avatar | string nullable | |
| remember_token | string nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

Roles stored via: `model_has_roles` (Spatie Permission)

---

### user_profiles
Extended profile data for all GVOS users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | cascadeOnDelete |
| first_name | string nullable | |
| last_name | string nullable | |
| phone | string(30) nullable | |
| country | string(100) nullable | |
| city | string(100) nullable | |
| bio | text nullable | max 500 chars at UI layer |
| onboarding_status | enum | pending, in_progress, complete (default: pending) |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### audit_logs
Immutable system audit trail. No `updated_at` column. App-level boot() blocks updates and deletes.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users nullable | null for system actions; nullOnDelete |
| action | string(100) index | dot-namespaced: user.created, user.role_changed |
| subject_type | string(100) nullable | morphable model class name |
| subject_id | bigint unsigned nullable | morphable model PK |
| context | json nullable | from→to values, extra metadata |
| ip_address | string(45) nullable | IPv4 or IPv6 |
| user_agent | text nullable | |
| created_at | timestamp | useCurrent default |

Phase 1 actions: `user.login`, `user.created`, `user.updated`, `user.role_changed`, `user.status_changed`, `user.password_changed`, `user.profile_updated`

Phase 2 actions: `company.created`, `company.updated`, `department.created`, `department.updated`, `client_profile.created`, `client_profile.updated`, `talent_profile.created`, `talent_profile.updated`, `manager_profile.created`, `manager_profile.updated`

---

### Spatie Permission Tables (Phase 0)

| Table | Purpose |
|-------|---------|
| roles | 8 GVOS roles |
| permissions | named permissions (Phase 2+) |
| model_has_roles | user→role pivot |
| model_has_permissions | user→permission pivot |
| role_has_permissions | role→permission pivot |

---

## Phase 2 Tables (live)

### companies
Client-company accounts (individual or business).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | Company display name |
| legal_name | string nullable | |
| type | enum | individual, business |
| industry | string nullable | |
| website | string nullable | |
| country | string nullable | |
| city | string nullable | |
| timezone | string | default: Africa/Lagos |
| company_email_domain | string nullable | used for staff invite matching |
| primary_contact_name | string nullable | |
| primary_contact_email | string nullable | |
| primary_contact_phone | string nullable | |
| status | enum | active, pending, inactive, suspended |
| notes | text nullable | internal only |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |

---

### departments
Sub-units within a company.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| company_id | FK companies | cascadeOnDelete |
| name | string | |
| description | text nullable | |
| manager_name | string nullable | |
| manager_email | string nullable | |
| status | enum | active, inactive |
| created_at / updated_at | timestamps | |

---

### client_profiles
Profile record for all client-role users (individual, business_admin, business_staff).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | cascadeOnDelete |
| company_id | FK companies nullable | nullOnDelete |
| client_type | enum | individual, business_admin, business_staff |
| job_title | string nullable | |
| department_id | FK departments nullable | nullOnDelete |
| preferred_contact_window | string nullable | e.g. "Mon–Fri 9am–5pm WAT" |
| service_interest | string nullable | |
| status | enum | active, pending, inactive, suspended |
| notes | text nullable | internal only |
| created_at / updated_at | timestamps | |

---

### talent_profiles
Profile record for Talent role users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | cascadeOnDelete |
| talent_code | string unique nullable | e.g. GVT-001 |
| role_type | string nullable | job title / category |
| skill_summary | text nullable | |
| availability_type | enum | fixed, flexible, hybrid |
| weekly_capacity_hours | smallint unsigned | default: 40 |
| work_timezone | string | default: Africa/Lagos |
| training_status | enum | not_started, in_training, prequalified, active, paused, suspended |
| equipment_status | enum | not_assigned, assigned, returned, damaged, maintenance |
| internal_notes | text nullable | |
| status | enum | active, pending, inactive, suspended |
| created_at / updated_at | timestamps | |

---

### manager_profiles
Profile record for Line Manager role users.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | cascadeOnDelete |
| manager_code | string unique nullable | e.g. GVM-001 |
| department | string nullable | department or speciality area |
| capacity_limit | smallint unsigned | default: 10 |
| current_load | smallint unsigned | default: 0 |
| specialization | string nullable | |
| status | enum | active, pending, inactive, suspended |
| internal_notes | text nullable | |
| created_at / updated_at | timestamps | |

---

Phase 3 actions: `lead_request.created`, `lead_request.updated`, `lead_request.status_changed`, `price_estimate.created`, `price_estimate.updated`, `price_estimate.accepted`, `trial.created`, `trial.updated`, `trial.started`, `trial.completed`, `trial.cancelled`, `trial.payment_pending`

Phase 4 actions: `workspace.created`, `workspace.updated`, `workspace.status_changed`, `workspace.member_added`, `workspace.member_updated`, `workspace.member_removed`, `trial.workspace_created`

Phase 5 actions: `workspace_task.created`, `workspace_task.updated`, `workspace_task.status_changed`, `workspace_task.assigned`, `workspace_task.comment_added`, `workspace_task.internal_comment_added`, `workspace_task.deleted`

Phase 7 actions: `time_log.created`, `time_log.updated`, `time_log.reviewed`, `time_log.deleted`, `weekly_report.created`, `weekly_report.updated`, `weekly_report.deleted`, `weekly_report.published`, `weekly_report.status_changed`

Phase 8 actions: `billing_plan.created`, `billing_plan.updated`, `workspace_subscription.created`, `workspace_subscription.updated`, `invoice.created`, `invoice.updated`, `invoice.issued`, `invoice.cancelled`, `invoice.marked_paid`, `payment.recorded`, `payment.confirmed`, `payment.failed_or_cancelled`

Phase 9 actions: `workspace_time_tracker.started`, `workspace_time_tracker.stopped`, `workspace_time_tracker.completed`

Phase 10 actions: `workspace_vault_item.created`, `workspace_vault_item.updated`, `workspace_vault_item.archived`, `workspace_vault_item.restored`, `workspace_vault_item.secret_revealed`, `workspace_vault_item.access_logs_viewed`

Phase 11 actions: `notification_preferences.updated`

---

## Phase 3 Tables (live)

### lead_requests
Inbound service requests from prospective clients via the public form or admin entry.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| lead_code | string(50) unique nullable | internal reference e.g. GVL-001 |
| first_name | string(100) | |
| last_name | string(100) | |
| email | string(255) | |
| phone | string(50) nullable | |
| country | string(100) nullable | |
| city | string(100) nullable | |
| timezone | string nullable | |
| client_type | enum | individual, business |
| company_name | string(255) nullable | |
| company_website | string(255) nullable | |
| company_email_domain | string(255) nullable | |
| role_needed | enum nullable | virtual_assistant, executive_assistant, social_media_manager, video_editor, developer, designer, motion_graphics, other |
| role_needed_other | string(255) nullable | free text when role_needed = 'other' |
| estimated_hours_per_week | integer nullable | 1–168 |
| preferred_start_date | date nullable | |
| preferred_work_schedule | string(255) nullable | |
| required_skills | text nullable | |
| work_description | longtext nullable | |
| budget_range | string nullable | enum key from controller constants |
| source | string(255) nullable | referral source |
| status | enum indexed | **new, price_estimated, price_accepted, under_review, trial_approved, trial_active, trial_completed, payment_pending, converted, lost, disqualified** |
| admin_notes | longtext nullable | internal only |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |

---

### price_estimates
Cost proposals created by admins for a specific lead request.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| lead_request_id | FK lead_requests | cascadeOnDelete |
| currency | enum | USD, GBP, EUR, NGN |
| estimated_amount | decimal(10,2) | |
| billing_cycle | enum | bi_weekly, monthly |
| estimated_hours_per_week | integer nullable | |
| role_needed | string nullable | |
| notes | text nullable | |
| status | enum | draft, sent, accepted, rejected, expired |
| accepted_at | timestamp nullable | set when accepted |
| expires_at | timestamp nullable | optional expiry |
| created_at / updated_at | timestamps | |

---

### trials
Trial records linking a lead request to an active lead user and assigned team.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| trial_code | string(50) unique nullable | internal reference e.g. TRL-001 |
| lead_request_id | FK lead_requests nullable | nullOnDelete |
| active_lead_user_id | FK users nullable | nullOnDelete |
| assigned_talent_user_id | FK users nullable | nullOnDelete |
| assigned_manager_user_id | FK users nullable | nullOnDelete |
| price_estimate_id | FK price_estimates nullable | nullOnDelete |
| status | enum | pending, approved, active, completed, expired, cancelled, converted |
| starts_at | timestamp nullable | |
| ends_at | timestamp nullable | computed from starts_at + trial_duration_hours |
| trial_duration_hours | integer unsigned | default: 24 |
| trial_task_limit | integer unsigned | default: 3 |
| trial_file_limit_mb | integer unsigned | default: 100 |
| notes | text nullable | |
| created_at / updated_at | timestamps | |

---

## Phase 4 Tables (live)

### workspaces
Workspace records linking a trial/company/client to a team and task board.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_code | string(30) unique nullable | e.g. WS-00001 |
| name | string | |
| description | text nullable | |
| lead_request_id | FK lead_requests nullable | nullOnDelete |
| trial_id | FK trials nullable | nullOnDelete |
| company_id | FK companies nullable | nullOnDelete |
| client_profile_id | FK client_profiles nullable | nullOnDelete |
| primary_manager_id | FK users nullable | nullOnDelete |
| primary_talent_id | FK users nullable | nullOnDelete |
| status | enum | pending, active, paused, completed, cancelled |
| type | enum | trial, ongoing, project |
| starts_at | timestamp nullable | |
| ends_at | timestamp nullable | |
| task_limit | integer unsigned nullable | |
| file_limit_mb | integer unsigned nullable | |
| notes | text nullable | internal only |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |

---

### workspace_members
Pivot table tracking which users belong to each workspace with what role.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| user_id | FK users | cascadeOnDelete |
| role | enum | client, talent, manager, observer |
| status | enum | active, removed |
| joined_at | timestamp nullable | |
| removed_at | timestamp nullable | |
| notes | text nullable | |
| created_at / updated_at | timestamps | |
| UNIQUE | (workspace_id, user_id) | prevents duplicate membership |

---

## Phase 5 Tables (live)

### workspace_tasks
Task records within a workspace. Central entity of the Phase 5 task board.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| task_code | string(30) unique nullable | e.g. TASK-00001 |
| workspace_id | FK workspaces | cascadeOnDelete |
| created_by_user_id | FK users | |
| assigned_to_user_id | FK users nullable | nullOnDelete |
| title | string | |
| description | longtext nullable | |
| priority | enum | low, normal, high, urgent |
| status | enum | pending, in_progress, blocked, submitted, revision_requested, approved, closed, cancelled |
| due_date | date nullable | |
| started_at | timestamp nullable | set when → in_progress |
| submitted_at | timestamp nullable | set when → submitted |
| approved_at | timestamp nullable | set when → approved |
| closed_at | timestamp nullable | set when → closed |
| sort_order | integer | default: 0 |
| internal_notes | text nullable | admin/manager only |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |
| INDEX | (workspace_id, status) | |
| INDEX | assigned_to_user_id | |

#### Status Flow

```
pending → in_progress → blocked → in_progress (loop)
                     → submitted → revision_requested → in_progress
                                → approved → closed
                                           → cancelled (any stage, admin/manager)
```

#### Role Transitions

| Role | Allowed transitions |
|------|-------------------|
| admin / manager | Any status (full control, including back-transitions) |
| talent | pending→in_progress, in_progress→blocked/submitted, blocked→in_progress, revision_requested→in_progress |
| client | submitted→revision_requested/approved, approved→closed |
| observer / none | None |

---

### workspace_task_comments
Comments on workspace tasks. Supports public and internal (admin/manager-only) visibility.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_task_id | FK workspace_tasks | cascadeOnDelete |
| user_id | FK users | |
| comment | longtext | |
| visibility | enum | public, internal |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |
| INDEX | (workspace_task_id, visibility) | |

---

---

## Phase 6 Tables (live)

### workspace_messages
Chat messages within a workspace. Supports public and internal visibility, thread replies, and system messages.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| user_id | FK users | cascadeOnDelete |
| parent_id | FK workspace_messages nullable | self-referential for thread replies |
| message | longtext | max 5000 chars at UI layer |
| visibility | enum | public, internal |
| message_type | enum | text, system |
| edited_at | timestamp nullable | future edit support |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |
| INDEX | (workspace_id, visibility) | |
| INDEX | parent_id | |

#### Access Rules
- `visibility=internal` messages are only shown to admin, workspace_admin, manager roles
- Clients and talent only see `visibility=public` messages
- Observers can view but cannot post
- Authors and admin/manager can delete (soft delete)

---

### workspace_files
Files attached to a workspace. Supports task attachment, visibility control, and download tracking.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| uploaded_by_user_id | FK users | |
| workspace_task_id | FK workspace_tasks nullable | task attachment |
| title | string nullable | user-provided label |
| original_filename | string | original upload filename |
| stored_filename | string | UUID-based stored filename |
| storage_path | string | relative path on local disk |
| mime_type | string nullable | |
| file_size | unsignedBigInteger nullable | bytes |
| visibility | enum | public, internal |
| category | string nullable | general, task_attachment, brief, deliverable, invoice_support, other |
| description | text nullable | |
| downloads_count | unsignedInteger | default: 0; incremented on each download |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes; physical file preserved |
| INDEX | (workspace_id, visibility) | |
| INDEX | workspace_task_id | |
| INDEX | uploaded_by_user_id | |

#### Storage
Files are stored at `storage/app/workspaces/{workspace_id}/{uuid}.{ext}` via `Storage::disk('local')`.
They are NOT publicly accessible via URL. Downloads are served through `WorkspaceFileController@download` which verifies access before streaming.

#### Allowed MIME Types
PDF, JPEG, PNG, GIF, WebP, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV, ZIP — max 10 MB.

---

---

## Phase 7 Tables (live)

### workspace_time_logs
Individual work session logs submitted by talents and reviewed by managers.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| user_id | FK users | who logged the time |
| workspace_task_id | FK workspace_tasks nullable | nullOnDelete |
| log_date | date | the date worked |
| started_at | timestamp nullable | session start datetime |
| ended_at | timestamp nullable | session end datetime |
| duration_minutes | integer nullable | explicit override; auto-derived from start/end if null |
| work_summary | text | required brief description |
| work_details | longText nullable | internal detail notes |
| status | enum | running, draft, submitted, reviewed, approved, rejected; default: draft |
| reviewed_by_user_id | FK users nullable | nullOnDelete |
| reviewed_at | timestamp nullable | |
| manager_notes | text nullable | internal manager feedback |
| client_visible_summary | text nullable | what clients see when visibility=client_summary |
| visibility | enum | internal, client_summary; default: internal |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |
| INDEX | (workspace_id, status) | |
| INDEX | (user_id, log_date) | |
| INDEX | workspace_task_id | |

#### Client Visibility Rule
Clients see a time log only when `status = 'approved' AND visibility = 'client_summary'`. The `client_visible_summary` text is shown instead of `work_summary` for clients.

#### Phase 9 Timer Behavior
- `running` means a timer has started, `started_at` is set, and `ended_at` is null.
- Stop/complete actions set `ended_at`, calculate `duration_minutes` on the server, and move the log to `draft` or `submitted`.
- Browser timers are display-only; stored duration is not trusted from the frontend.
- One running timer is enforced per user globally by the timer start action.
- No screenshots, keystrokes, screen monitoring, billing automation, payroll, or password vault behavior is attached to time tracking.

---

### workspace_weekly_reports
Weekly work summary reports prepared by managers and published to clients.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| week_start_date | date | |
| week_end_date | date | |
| prepared_by_user_id | FK users nullable | nullOnDelete |
| reviewed_by_user_id | FK users nullable | nullOnDelete |
| total_minutes | integer | default: 0; auto-suggested from approved time logs |
| summary | longText | required |
| achievements | longText nullable | |
| blockers | longText nullable | internal — clients do not see this |
| next_steps | longText nullable | internal — clients do not see this |
| client_notes | longText nullable | shown to clients |
| status | enum | draft, submitted, approved, published; default: draft |
| published_at | timestamp nullable | set when status transitions to published |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | soft deletes |
| INDEX | (workspace_id, status) | |
| INDEX | (workspace_id, week_start_date) | |

#### Status Flow
`draft` → `submitted` → `approved` → `published`

Clients see only `published` reports. Talents see `submitted`, `approved`, `published` (not draft). Managers/admins see all.

---

---

## Phase 8 Tables (live)

### billing_plans
Reusable subscription templates. Plans can be assigned to workspace subscriptions.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| code | string nullable unique | Short identifier |
| description | text nullable | |
| currency | enum | USD, GBP, EUR, NGN, CAD |
| amount | decimal(10,2) | |
| billing_cycle | enum | bi_weekly, monthly, one_time |
| included_talents | integer | default 1 |
| included_hours_per_week | integer nullable | |
| status | enum | active, inactive, archived |
| notes | text nullable | |
| timestamps + softDeletes | | |

### workspace_subscriptions
Active billing relationship between a workspace and a billing plan.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| billing_plan_id | FK billing_plans nullable | nullOnDelete |
| client_profile_id | FK client_profiles nullable | |
| company_id | FK companies nullable | |
| currency | enum | |
| amount | decimal(10,2) | override or copy from plan |
| billing_cycle | enum | bi_weekly, monthly, one_time |
| status | enum | trial, active, payment_due, overdue, suspended, cancelled, ended |
| starts_at | date nullable | |
| next_billing_date | date nullable | |
| ends_at | date nullable | |
| last_paid_at | timestamp nullable | updated on payment confirm |
| grace_ends_at | timestamp nullable | |
| notes | text nullable | |
| timestamps + softDeletes | | |
| INDEX | (workspace_id, status) | |

### invoices
Client-facing billing documents.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| invoice_number | string unique | Auto: GVOS-INV-YYYYMM-XXXX |
| workspace_id | FK workspaces | cascadeOnDelete |
| workspace_subscription_id | FK nullable | nullOnDelete |
| client_profile_id | FK nullable | |
| company_id | FK nullable | |
| currency | enum | |
| subtotal | decimal(10,2) | |
| discount_amount | decimal(10,2) | default 0 |
| tax_amount | decimal(10,2) | default 0 |
| total_amount | decimal(10,2) | |
| amount_paid | decimal(10,2) | default 0 |
| balance_due | decimal(10,2) | total_amount - amount_paid |
| status | enum | draft, issued, partially_paid, paid, overdue, cancelled, void |
| issue_date | date | |
| due_date | date nullable | |
| paid_at | timestamp nullable | set when fully paid |
| notes | text nullable | client-visible |
| internal_notes | text nullable | admin/manager only |
| timestamps + softDeletes | | |
| INDEX | (workspace_id, status) | |

### invoice_items
Line items within an invoice.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| invoice_id | FK invoices | cascadeOnDelete |
| description | string | |
| quantity | decimal(10,4) | default 1 |
| unit_amount | decimal(10,2) | |
| total_amount | decimal(10,2) | auto: quantity × unit_amount |
| item_type | enum | subscription, extra_hours, setup_fee, adjustment, other |
| timestamps | | |

### payments
Payment records (manual and gateway-ready).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| payment_reference | string nullable unique | Auto: GVOS-PAY-XXXXXXXXXX |
| invoice_id | FK invoices nullable | nullOnDelete |
| workspace_id | FK workspaces nullable | |
| workspace_subscription_id | FK nullable | |
| provider | enum | manual, bank_transfer, fincra, flutterwave, paystack, stripe, other |
| provider_reference | string nullable | gateway transaction ID |
| currency | enum | |
| amount | decimal(10,2) | |
| status | enum | pending, confirmed, failed, reversed, cancelled |
| paid_at | timestamp nullable | |
| confirmed_by_user_id | FK users nullable | admin who confirmed |
| confirmation_notes | text nullable | |
| raw_payload | json nullable | gateway webhook data |
| timestamps + softDeletes | | |
| INDEX | (invoice_id, status) | |
| INDEX | (workspace_id, status) | |

#### Payment Confirmation Flow
1. `Payment::confirm(userId, notes)` is idempotent once confirmed_by_user_id and paid_at are set.
2. → status=confirmed, paid_at=now(), confirmed_by_user_id=userId.
3. → if linked to an invoice, inherit missing workspace/subscription references from that invoice.
4. → `Invoice::applyPayment(amount)` → amount_paid += amount; recalc balance_due.
5. → if balance_due ≤ 0: invoice status=paid, paid_at=now(); else: partially_paid.
6. → if subscription linked and was payment_due/overdue/suspended: status=active, last_paid_at=now().

#### Invoice Total Recalculation
Invoice totals are recalculated from line items when available. Manual invoices without line items preserve the manually entered total and only refresh balance_due from total_amount - amount_paid.

---

### subscriptions / invoices — future gateway integration
Provider enum is designed to be extended. `raw_payload` JSON field accepts gateway webhook data. No gateway is integrated yet.

---

## Phase 10 Tables (live)

### workspace_vault_items
Encrypted credential records scoped to a workspace.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | cascadeOnDelete |
| created_by | FK users nullable | nullOnDelete |
| updated_by | FK users nullable | nullOnDelete |
| title | string | Credential display name |
| category | string nullable | login, server, app, api_key, email, social_media, billing, other |
| login_url | string(2048) nullable | Optional login URL |
| username | string nullable | Username/email only; not secret |
| secret_value | text encrypted | Encrypted by `WorkspaceVaultItem` model cast |
| notes | text nullable | Context only; do not store additional secrets here |
| visibility | string | restricted, workspace_admins, assigned_users |
| status | string | active, archived |
| allowed_roles | json nullable | Explicit reveal/access roles |
| allowed_user_ids | json nullable | Explicit reveal/access users |
| last_revealed_at | timestamp nullable | Updated on reveal/copy |
| last_revealed_by | FK users nullable | nullOnDelete |
| timestamps + softDeletes | | |
| INDEX | (workspace_id, status) | |
| INDEX | (workspace_id, visibility) | |

### workspace_vault_access_logs
Metadata-only vault activity log. Never stores plaintext secrets.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_vault_item_id | FK workspace_vault_items | cascadeOnDelete |
| workspace_id | FK workspaces | cascadeOnDelete |
| user_id | FK users nullable | nullOnDelete |
| action | string(100) | created, updated, archived, restored, viewed_metadata, revealed_secret, copied_secret, viewed_logs |
| ip_address | string(45) nullable | IPv4/IPv6 |
| user_agent | text nullable | |
| metadata | json nullable | Source/action metadata only, no secret values |
| created_at | timestamp | useCurrent |
| INDEX | (workspace_id, action) | |
| INDEX | (workspace_vault_item_id, action) | |

#### Vault Secret Handling
- `WorkspaceVaultItem.secret_value` uses Laravel's encrypted cast.
- `secret_value` is hidden from model array/JSON serialization.
- Portal and Filament list/table views show metadata only.
- Reveals and copies update `last_revealed_at`, `last_revealed_by`, `workspace_vault_access_logs`, and `audit_logs`.
- No auto-login, browser extension, screenshot, keystroke, screen monitoring, payroll, or billing automation is attached to the vault.

---

## Phase 11 Tables (live)

### notifications
Laravel database notifications table for in-app GVOS notifications.

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | Standard Laravel notification UUID |
| type | string | Notification class name |
| notifiable_type | string | Morph target type, usually `App\Models\User` |
| notifiable_id | bigint | User ID |
| data | json | Safe payload only: title, message, action_url, workspace_id, related_type, related_id, level, notification_key |
| read_at | timestamp nullable | Set when user marks notification read |
| created_at / updated_at | timestamps | |
| INDEX | notifiable_type, notifiable_id | via `morphs` |

### user_notification_preferences
Per-user notification channel preferences.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | cascadeOnDelete |
| notification_key | string(100) | task_assigned, task_status_changed, task_comment_added, file_uploaded, workspace_message, time_log_submitted, weekly_report_published, invoice_issued, payment_recorded, trial_approved |
| in_app_enabled | boolean | default true |
| email_enabled | boolean | default true at DB level; app defaults disable email for noisy keys until user opts in |
| created_at / updated_at | timestamps | |
| UNIQUE | user_id + notification_key | one preference row per key per user |

#### Notification Payload Safety
- Payloads intentionally exclude vault secrets, raw storage paths, payment raw payloads, internal admin notes, internal invoice notes, manager notes, tokens, and API keys.
- Chat/message, task comment, task status, and file upload email are disabled by default to reduce noise.
- Database notifications continue to work even when mail is not configured.

---

## Key Relationships

- `users` → many roles (via Spatie model_has_roles)
- `users` → many `notifications` via Laravel Notifiable trait (Phase 11)
- `users` → many `user_notification_preferences` (Phase 11)
- `users` → one `user_profile` (Phase 1)
- `users` → one `talent_profile` (Phase 2, talent role)
- `users` → one `manager_profile` (Phase 2, line_manager role)
- `users` → one `client_profile` (Phase 2, client roles)
- `users` → many `audit_logs` as actor
- `companies` → many `departments` (Phase 2)
- `companies` → many `client_profiles` (Phase 2)
- `lead_requests` → many `price_estimates` (Phase 3)
- `lead_requests` → many `trials` (Phase 3)
- `trials` → one `price_estimate` (Phase 3)
- `trials` → one `active_lead_user` (via FK to users) (Phase 3)
- `trials` → one `assigned_talent_user` (via FK to users) (Phase 3)
- `trials` → one `assigned_manager_user` (via FK to users) (Phase 3)
- `workspaces` → many `workspace_members` → many `users` (Phase 4)
- `workspaces` → many `workspace_tasks` (Phase 5)
- `workspace_tasks` → many `workspace_task_comments` (Phase 5)
- `workspaces` → many `messages`, `files`, `time_logs` (Phase 6/7)
- `workspaces` → one active `subscription` → many `invoices` (Phase 8)
- `workspaces` → many `workspace_vault_items` → many `workspace_vault_access_logs` (Phase 10)

---

## Engineering Notes

- All monetary values use `decimal(10,2)`.
- All timestamps store UTC; display in user's `timezone` field.
- `audit_logs` has no `updated_at` and model boot() blocks updates/deletes.
- `companies` uses soft deletes (`deleted_at`).
- Encrypted columns (vault) use Laravel encrypted casts / encryption helpers (Phase 10).
- Production email notifications require standard Laravel `MAIL_*` configuration; no mail secrets are committed.
