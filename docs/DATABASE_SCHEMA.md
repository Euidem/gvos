# GVOS — Database Schema

## Status: Phase 1 migrations created and active

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

Phase 1 actions logged: `user.login`, `user.created`, `user.updated`, `user.role_changed`, `user.status_changed`, `user.password_changed`, `user.profile_updated`

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

## Phase 2+ Tables (planned — not yet created)

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

### companies
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email_domain | string | for staff invitation validation |
| address | text nullable | |
| industry | string nullable | |
| admin_user_id | FK users | Business Client Admin |
| status | enum | active, suspended, inactive |
| created_at / updated_at | timestamps | |

---

### departments
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| company_id | FK companies | |
| name | string | |
| created_at | timestamp | |

---

### talent_profiles
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| skills | json | |
| shift_type | enum | fixed, flexible, hybrid |
| default_timezone | string | |
| phone | string nullable | |
| status | enum | active, inactive, on_leave |
| created_at / updated_at | timestamps | |

---

### manager_profiles
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| phone | string nullable | |
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
- `users` → many `audit_logs` as actor
- `users` → one `talent_profile` or `manager_profile` (Phase 2)
- `workspaces` → many `workspace_members` → many `users` (Phase 4)
- `workspaces` → many `tasks`, `messages`, `files`, `time_logs` (Phase 5+)
- `workspaces` → one active `subscription` → many `invoices` (Phase 8)

---

## Engineering Notes

- All monetary values use `decimal(10,2)`.
- All timestamps store UTC; display in user's `timezone` field.
- `audit_logs` has no `updated_at` and model boot() blocks updates/deletes.
- Encrypted columns (vault) use Laravel's `encrypt()`/`decrypt()` helpers (Phase 10).
