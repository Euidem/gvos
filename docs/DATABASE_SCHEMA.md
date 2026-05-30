# GVOS — Database Schema

## Status: Phase 5 migrations created and active

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

### subscriptions / invoices (Phase 8)
See billing schema in `BILLING_RULES.md`

---

## Key Relationships

- `users` → many roles (via Spatie model_has_roles)
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

---

## Engineering Notes

- All monetary values use `decimal(10,2)`.
- All timestamps store UTC; display in user's `timezone` field.
- `audit_logs` has no `updated_at` and model boot() blocks updates/deletes.
- `companies` uses soft deletes (`deleted_at`).
- Encrypted columns (vault) use Laravel's `encrypt()`/`decrypt()` helpers (Phase 10).
