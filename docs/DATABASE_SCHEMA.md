# GVOS — Database Schema

## Status: Phase 2 migrations created and active

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

## Phase 3+ Tables (planned)

### leads
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string | |
| phone | string nullable | |
| company_name | string nullable | |
| inquiry_details | text | |
| status | enum | new, contacted, quoted, trial_approved, converted, lost |
| price_estimate | decimal(10,2) nullable | |
| assigned_admin_id | FK users | |
| trial_starts_at | timestamp nullable | |
| trial_ends_at | timestamp nullable | |
| converted_at | timestamp nullable | |
| created_at / updated_at | timestamps | |

---

### workspaces (Phase 4)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| type | enum | trial, standard, business |
| status | enum | trial, active, suspended, closed |
| client_id | FK users | |
| company_id | FK companies nullable | |
| department_id | FK departments nullable | |
| trial_ends_at | timestamp nullable | |
| billing_starts_at | timestamp nullable | |
| created_at / updated_at | timestamps | |

---

### workspace_members (Phase 4)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| user_id | FK users | |
| role_in_workspace | enum | client, talent, manager |
| joined_at | timestamp | |

---

### tasks (Phase 5)
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| title | string | |
| description | text nullable | |
| status | enum | backlog, todo, in_progress, in_review, done, rejected |
| priority | enum | low, medium, high, urgent |
| assignee_id | FK users nullable | |
| created_by | FK users | |
| due_date | date nullable | |
| approved_at | timestamp nullable | |
| approved_by | FK users nullable | |
| created_at / updated_at | timestamps | |

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
- `workspaces` → many `workspace_members` → many `users` (Phase 4)
- `workspaces` → many `tasks`, `messages`, `files`, `time_logs` (Phase 5+)
- `workspaces` → one active `subscription` → many `invoices` (Phase 8)

---

## Engineering Notes

- All monetary values use `decimal(10,2)`.
- All timestamps store UTC; display in user's `timezone` field.
- `audit_logs` has no `updated_at` and model boot() blocks updates/deletes.
- `companies` uses soft deletes (`deleted_at`).
- Encrypted columns (vault) use Laravel's `encrypt()`/`decrypt()` helpers (Phase 10).
