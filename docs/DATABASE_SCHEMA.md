# GVOS — Database Schema

## Status: Phase 0 Outline — Full migrations created per phase

This document describes the database entities, their fields, and relationships. Full SQL migrations are created during their respective build phases.

---

## Core Entities

### users
The main authentication table. All roles share this table.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | |
| password | string hashed | |
| avatar | string nullable | |
| timezone | string | default: UTC |
| status | enum | active, suspended, inactive |
| created_at | timestamp | |
| updated_at | timestamp | |

Roles stored via: `model_has_roles` (Spatie Permission)

---

### leads
Pre-client inquiries before conversion.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string | |
| phone | string nullable | |
| company_name | string nullable | |
| inquiry_details | text | |
| status | enum | new, contacted, quoted, trial_approved, converted, lost |
| price_estimate | decimal nullable | |
| assigned_admin_id | FK users | Operations Admin who owns this lead |
| trial_starts_at | timestamp nullable | |
| trial_ends_at | timestamp nullable | |
| converted_at | timestamp nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### companies
Business client organizations.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email_domain | string | for staff invitation validation |
| address | text nullable | |
| industry | string nullable | |
| admin_user_id | FK users | Business Client Admin |
| status | enum | active, suspended, inactive |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### departments
Sub-units within a company.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| company_id | FK companies | |
| name | string | |
| created_at | timestamp | |

---

### talent_profiles
Extended profile for talent users.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| skills | json | |
| shift_type | enum | fixed, flexible, hybrid |
| default_timezone | string | |
| phone | string nullable | |
| status | enum | active, inactive, on_leave |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### manager_profiles
Extended profile for manager users.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| phone | string nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### workspaces
The central operational unit.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| type | enum | trial, standard, business |
| status | enum | trial, active, suspended, closed |
| client_id | FK users | The primary client |
| company_id | FK companies nullable | For business workspaces |
| department_id | FK departments nullable | |
| trial_ends_at | timestamp nullable | |
| billing_starts_at | timestamp nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### workspace_members
Pivot: who is in each workspace.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| user_id | FK users | |
| role_in_workspace | enum | client, talent, manager |
| joined_at | timestamp | |

---

### tasks
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
| created_at | timestamp | |
| updated_at | timestamp | |

---

### task_comments
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| task_id | FK tasks | |
| user_id | FK users | |
| comment | text | |
| created_at | timestamp | |

---

### messages
Workspace chat.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| user_id | FK users | |
| body | text | |
| attachment_path | string nullable | |
| is_voice_note | boolean | default: false |
| flagged_at | timestamp nullable | |
| flagged_by | FK users nullable | |
| created_at | timestamp | |

---

### files
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| uploaded_by | FK users | |
| name | string | |
| path | string | |
| mime_type | string | |
| size | bigint | bytes |
| category | string nullable | |
| created_at | timestamp | |

---

### time_logs
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| talent_id | FK users | |
| clocked_in_at | timestamp | |
| clocked_out_at | timestamp nullable | |
| total_minutes | int nullable | |
| notes | text nullable | |
| created_at | timestamp | |

---

### daily_reports
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| talent_id | FK users | |
| report_date | date | |
| summary | text | |
| tasks_completed | json | |
| reviewed_by | FK users nullable | |
| reviewed_at | timestamp nullable | |
| created_at | timestamp | |

---

### subscriptions
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| plan_name | string | |
| amount | decimal | |
| billing_cycle | enum | biweekly |
| starts_at | timestamp | |
| next_billing_at | timestamp | |
| status | enum | active, past_due, suspended, cancelled |
| created_at | timestamp | |

---

### invoices
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| subscription_id | FK subscriptions | |
| amount | decimal | |
| due_date | date | |
| paid_at | timestamp nullable | |
| paid_by | FK users nullable | Admin who recorded payment |
| status | enum | draft, sent, paid, overdue |
| created_at | timestamp | |

---

### complaints
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| raised_by | FK users | |
| category | string | |
| description | text | |
| evidence_paths | json nullable | |
| status | enum | open, under_review, escalated, resolved, dismissed |
| assigned_to | FK users nullable | Manager |
| resolution_notes | text nullable | |
| resolved_at | timestamp nullable | |
| created_at | timestamp | |

---

### vault_credentials
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| workspace_id | FK workspaces | |
| label | string | |
| username | string encrypted | |
| password | string encrypted | AES-256 |
| url | string encrypted nullable | |
| notes | text encrypted nullable | |
| created_by | FK users | |
| created_at | timestamp | |

---

### vault_access_grants
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| credential_id | FK vault_credentials | |
| granted_to | FK users | |
| granted_by | FK users | |
| granted_at | timestamp | |
| revoked_at | timestamp nullable | |

---

### vault_reveal_logs
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| credential_id | FK vault_credentials | |
| revealed_by | FK users | |
| revealed_at | timestamp | |
| ip_address | string | |

---

### audit_logs
Immutable system audit trail.
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK users nullable | null for system actions |
| action | string | e.g. workspace.suspended |
| subject_type | string | morphable model name |
| subject_id | bigint | |
| context | json | full context snapshot |
| ip_address | string | |
| user_agent | string | |
| created_at | timestamp | Never updated |

---

### assets
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| serial_number | string nullable | |
| category | string nullable | |
| assigned_to | FK users nullable | |
| assigned_at | timestamp nullable | |
| returned_at | timestamp nullable | |
| status | enum | available, assigned, retired |
| notes | text nullable | |
| created_at | timestamp | |

---

## Key Relationships Summary
- `users` → many roles (via Spatie)
- `users` → one `talent_profile` or `manager_profile`
- `workspaces` → many `workspace_members` → many `users`
- `workspaces` → many `tasks`, `messages`, `files`, `time_logs`, `daily_reports`
- `workspaces` → one active `subscription` → many `invoices`
- `vault_credentials` → many `vault_access_grants` → many `vault_reveal_logs`
- `complaints` → one `workspace`, one `raised_by` user

---

## Notes
- All monetary values use decimal(10,2).
- All timestamps use UTC internally; display in user's timezone.
- Encrypted columns use Laravel's `encrypt()`/`decrypt()` helpers or a dedicated encryption service.
- `audit_logs` table should have no UPDATE or DELETE policies at the database level.
