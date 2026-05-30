# GVOS — Permission Matrix

## Overview
Authorization is enforced at three layers:
1. **Route middleware** — Spatie `role:` middleware in `routes/web.php`
2. **Panel / resource level** — Filament `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()`
3. **Policy level** — Laravel Policies (Phase 2+)

**Default rule: DENY. Access must be explicitly granted.**

---

## Role Reference

| Friendly Label | DB Slug | Portal |
|----------------|---------|--------|
| Super Admin | `super_admin` | Filament `/admin` |
| Operations Admin | `operations_admin` | Filament `/admin` |
| Line Manager | `line_manager` | `/manager/dashboard` |
| Talent | `talent` | `/talent/dashboard` |
| Individual Client | `individual_client` | `/client/dashboard` |
| Business Client Admin | `business_client_admin` | `/client/dashboard` |
| Business Client Staff | `business_client_staff` | `/client/dashboard` |
| Active Lead | `active_lead` | `/lead/dashboard` |

---

## Middleware Aliases (Laravel 11 — `bootstrap/app.php`)

```php
$middleware->alias([
    'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    'check.status'       => \App\Http\Middleware\CheckAccountStatus::class,
]);
```

> **Important:** In Laravel 11, Spatie aliases must be declared explicitly. They are NOT auto-registered.

---

## Phase 1 — Implemented Access Control

### Filament Panel (`/admin`)

| Action | super_admin | operations_admin | All others |
|--------|------------|-----------------|------------|
| Access `/admin` | ✅ | ✅ | ❌ 403 |
| View Users list | ✅ | ✅ | ❌ |
| Create User | ✅ | ❌ | ❌ |
| Edit User | ✅ | ❌ | ❌ |
| Delete User | ❌ disabled | ❌ | ❌ |

### Dashboard Route Middleware

| Route | Middleware stack |
|-------|-----------------|
| `/manager/dashboard` | `auth, check.status, role:line_manager` |
| `/talent/dashboard` | `auth, check.status, role:talent` |
| `/client/dashboard` | `auth, check.status, role:individual_client\|business_client_admin\|business_client_staff` |
| `/lead/dashboard` | `auth, check.status, role:active_lead` |
| `/profile` | `auth, check.status` |
| `/account/status` | `auth` |

### Account Status Gate

| Status | Dashboard | Profile | Filament |
|--------|-----------|---------|----------|
| active | ✅ | ✅ | Per role |
| pending | ✅ | ✅ | Per role |
| inactive | ❌ → `/account/status` | ❌ | ❌ |
| suspended | ❌ → `/account/status` | ❌ | ❌ |

---

## Full Permission Matrix (all phases)

Legend: ✅ Full | 👁 View only | ✏️ Own records | ❌ No access | 🔒 Encrypted

| Resource | Super Admin | Ops Admin | Line Mgr | Talent | Ind. Client | Biz Admin | Biz Staff | Active Lead |
|----------|------------|-----------|----------|--------|-------------|-----------|-----------|-------------|
| Platform Settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Role Management | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| User Management | ✅ | 👁 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Audit Logs | ✅ | 👁 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| User Profiles | ✅ | ✅ | ✏️ own | ✏️ own | ✏️ own | ✏️ own | ✏️ own | ✏️ own |
| Leads | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | 👁 own |
| Companies | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ own | ❌ | ❌ |
| Staff Invitations | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ own co. | ❌ | ❌ |
| Talent Profiles | ✅ | ✅ | 👁 assigned | 👁 own | ❌ | ❌ | ❌ | ❌ |
| Manager Profiles | ✅ | ✅ | 👁 own | ❌ | ❌ | ❌ | ❌ | ❌ |
| Workspaces | ✅ | ✅ | 👁 assigned | 👁 assigned | 👁 own | 👁 own | 👁 granted | 👁 trial |
| Task Board | ✅ | ✅ | ✅ assigned | ✏️ assigned | 👁 + approve | 👁 + approve | 👁 granted | 👁 trial |
| Chat | ✅ | ✅ | 👁 monitor | ✅ own ws | ✅ own ws | ✅ own ws | limited | ❌ |
| File Library | ✅ | ✅ | 👁 | ✅ own ws | ✅ own ws | ✅ own ws | limited | ❌ |
| Exact Time Logs | ✅ | ✅ | ✅ assigned | 👁 own | ❌ | ❌ | ❌ | ❌ |
| Daily Reports | ✅ | ✅ | ✅ review | ✏️ submit | ❌ | ❌ | ❌ | ❌ |
| Billing (admin) | ✅ | ✏️ record | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Billing (client) | ✅ | ✅ | ❌ | ❌ | 👁 own | 👁 own | ❌ | ❌ |
| Complaints (raise) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Complaints (manage) | ✅ | ✅ | ✅ assigned | ❌ | ❌ | ❌ | ❌ | ❌ |
| Password Vault | ✅ | ✅ | 👁 audit | 🔒 granted | 🔒 own | 🔒 own | ❌ | ❌ |
| Asset Tracking | ✅ | ✅ | 👁 | 👁 own | ❌ | ❌ | ❌ | ❌ |

---

## Phase 2 — People & Organizations Access Control

| Resource | super_admin | operations_admin | All others |
|----------|------------|-----------------|------------|
| Companies | ✅ CRUD | ✅ CRUD | ❌ |
| Departments | ✅ CRUD | ✅ CRUD | ❌ |
| Client Profiles | ✅ CRUD | ✅ CRUD | ❌ |
| Talent Profiles | ✅ CRUD | ✅ CRUD | ❌ |
| Manager Profiles | ✅ CRUD | ✅ CRUD | ❌ |

> No hard delete on any Phase 2 resource. Use status changes (inactive/suspended) instead.

---

## Phase 3 — Leads & Trials Access Control

| Resource | super_admin | operations_admin | active_lead | All others |
|----------|------------|-----------------|-------------|------------|
| Lead Requests | ✅ CRUD | ✅ CRUD | ❌ (view own via dashboard) | ❌ |
| Price Estimates | ✅ CRUD (incl. delete) | ✅ CRUD (incl. delete) | ❌ (view via dashboard) | ❌ |
| Trials | ✅ view/edit | ✅ view/edit | ❌ (view own via dashboard) | ❌ |
| Public form `/request-service` | ✅ (any) | ✅ (any) | ✅ (any) | ✅ (public) |

### Approve Trial Action (LeadRequestResource)
- Creates or finds a user by email from the lead request
- Assigns `active_lead` role via `syncRoles(['active_lead'])`
- Creates ClientProfile stub if missing
- Creates Trial record and links to user
- New users get a random password — must use password reset to log in

### Status Flow (Lead Request)
```
new → price_estimated → price_accepted → under_review → trial_approved
    → trial_active → trial_completed → payment_pending → converted
                                                        → lost / disqualified
```

### Status Flow (Trial)
```
pending → approved → active → completed → (payment_pending on lead)
                    → expired
                    → cancelled
```

---

## Public Routes (no auth required)

| Route | Method | Controller | Purpose |
|-------|--------|-----------|---------|
| `/request-service` | GET | LeadRequestController@show | Display lead form |
| `/request-service` | POST | LeadRequestController@store | Submit lead form |
| `/request-service/success` | GET | Closure | Success confirmation page |

---

## Phase 4 — Workspace Engine Access Control

| Resource | super_admin | operations_admin | line_manager | talent | client roles | active_lead |
|----------|------------|-----------------|--------------|--------|-------------|-------------|
| Workspaces (Filament) | ✅ CRUD | ✅ CRUD | ❌ | ❌ | ❌ | ❌ |
| Workspace Members (Filament RelationManager) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `/workspaces` (portal index) | ✅ (all) | ✅ (all) | 👁 assigned | 👁 assigned | 👁 own | 👁 trial only |
| `/workspaces/{workspace}` (portal detail) | ✅ | ✅ | 👁 if member/primary | 👁 if member/primary | 👁 if member | 👁 if trial ws |

> Workspace detail page (`/workspaces/{workspace}`) uses `Workspace::userHasAccess($user)` (model helper) which covers all tiers: admin roles, primary team, active members, and task-assigned users. See Phase 5 role resolution table.

---

## Phase 5 — Task Board Access Control

### Task Board Routes

All task routes require `auth` + `check.status` middleware. No additional `role:` middleware — access is enforced inside the controller using `Workspace::resolveUserWorkspaceRole()`.

| Action | admin/manager | talent / assigned_user | client | observer/none |
|--------|-------------|--------|--------|--------------|
| View task list (`index`) | ✅ | ✅ | ✅ | ❌ 403 |
| View task detail (`show`) | ✅ | ✅ (+ task-assigned fallback) | ✅ | ❌ 403 |
| Create task (`create` / `store`) | ✅ | ✅ talent only | ❌ 403 | ❌ 403 |
| Edit task (`edit` / `update`) | ✅ | ✅ (own pending tasks only) | ❌ 403 | ❌ 403 |
| Add public comment | ✅ | ✅ | ✅ | ❌ 403 |
| Add internal comment | ✅ | ❌ (forced public) | ❌ (forced public) | ❌ |
| Change task status | ✅ (any transition) | Allowed transitions only | Allowed transitions only | ❌ |
| Set internal_notes | ✅ | ❌ (stripped) | ❌ (stripped) | ❌ |

### Role Determination (`Workspace::resolveUserWorkspaceRole()`)

Centralised method on the `Workspace` model. Called by both `WorkspaceController` and `WorkspaceTaskController`. Uses `(int)` casts on both sides to avoid the Eloquent string/integer strict-comparison mismatch.

**Updated (Fix 4 — 2026-05-30): 7-tier resolution**

| Priority | Condition | Role returned |
|----------|-----------|---------------|
| 1 | `super_admin` or `operations_admin` system role | `admin` |
| 2 | Active member row with `role=workspace_admin` | `workspace_admin` |
| 3 | `primary_manager_id` matches user (int-cast) | `manager` |
| 4 | Active member row with `role=manager` | `manager` |
| 5 | `primary_talent_id` matches user (int-cast) | `talent` |
| 6a | Active member row with `role=talent` | `talent` |
| 6b | Active member row with `role=client_admin` | `client_admin` |
| 6c | Active member row with `role=client_staff` | `client_staff` |
| 6d | Active member row with `role=client` (legacy) | `client_admin` |
| 6e | Active member row with `role=observer` | `observer` |
| 7 | Assigned to any task in this workspace | `assigned_user` |
| — | None of the above | `none` → 403 |

`assigned_user` is mapped to `talent` via `transitionRole()` before passing to `allowedTransitions()`.
Legacy `client` member row is mapped to `client_admin` via `transitionRole()`.

For task `show()`, a user with role `none` may still view a specific task if they are the `assigned_to_user_id` for that task — they receive effective role `talent` for display purposes only.

### Task Status Allowed Transitions

| From Status | admin/workspace_admin/manager | talent/assigned_user | client_admin | client_staff/observer |
|------------|-------------------------------|----------------------|--------------|-----------------------|
| pending | in_progress, cancelled | in_progress | — | — |
| in_progress | blocked, submitted, pending, cancelled | blocked, submitted | — | — |
| blocked | in_progress, cancelled | in_progress | — | — |
| submitted | approved, revision_requested, in_progress | — | approved, revision_requested | — |
| revision_requested | in_progress, cancelled | in_progress | — | — |
| approved | closed | — | closed | — |
| closed | — | — | — | — |
| cancelled | — | — | — | — |

### Drag Handle Visibility (Kanban Board)

| Workspace Role | Drag Handle Shown On |
|----------------|----------------------|
| admin / workspace_admin / manager | All tasks |
| talent | Tasks assigned to self OR unassigned tasks |
| assigned_user | Only their explicitly assigned task |
| client_admin / client | Only on submitted or approved tasks |
| client_staff / observer | Never |

### Task Board Filament (WorkspaceTaskResource)

| Action | super_admin | operations_admin | All others |
|--------|------------|-----------------|------------|
| View tasks list | ✅ | ✅ | ❌ |
| Create task | ✅ | ✅ | ❌ |
| Edit task | ✅ | ✅ | ❌ |
| Hard delete | ❌ disabled | ❌ | ❌ |
| Archive (soft delete) | ✅ | ✅ | ❌ |

---

## Phase 6 — Chat & File Access Control

### Workspace Chat Routes

All chat routes require `auth` + `check.status`. Access is enforced via `WorkspaceMessageController` using `Workspace::resolveUserWorkspaceRole()`.

| Action | admin/workspace_admin/manager | talent/client_admin/client_staff | observer | none |
|--------|-------------------------------|----------------------------------|----------|------|
| View chat page (`index`) | ✅ all messages | ✅ public messages only | ✅ public only (view) | ❌ 403 |
| Post message (`store`) | ✅ any visibility | ✅ public only (forced) | ❌ 403 | ❌ 403 |
| Set `visibility=internal` on message | ✅ | ❌ (forced to public) | ❌ | ❌ |
| Delete own message (`destroy`) | ✅ | ✅ own only | ❌ | ❌ |
| Delete any message (`destroy`) | ✅ (admin/manager) | ❌ | ❌ | ❌ |

### Workspace File Routes

All file routes require `auth` + `check.status`. Access is enforced via `WorkspaceFileController`.

| Action | admin/workspace_admin/manager | talent/client_admin/client_staff | observer | none |
|--------|-------------------------------|----------------------------------|----------|------|
| View file list (`index`) | ✅ all visibility | ✅ public files only | ✅ public only (view) | ❌ 403 |
| Upload file (`store`) | ✅ any visibility | ✅ public only (forced) | ❌ 403 | ❌ 403 |
| Set `visibility=internal` on file | ✅ | ❌ (forced to public) | ❌ | ❌ |
| Download file (`download`) | ✅ all visibility | ✅ public only | ❌ | ❌ |
| Delete/archive file (`destroy`) | ✅ (any uploader) | ✅ own uploads only | ❌ | ❌ |
| Attach file to task (`storeForTask`) | ✅ | ✅ | ❌ 403 | ❌ 403 |

### Filament Chat & Files Resources

| Action | super_admin | operations_admin | All others |
|--------|------------|-----------------|------------|
| View messages list | ✅ | ✅ | ❌ |
| Create message via Filament | ❌ disabled | ❌ | ❌ |
| Moderate/remove message | ✅ | ✅ | ❌ |
| View files list | ✅ | ✅ | ❌ |
| Upload file via Filament | ❌ disabled | ❌ | ❌ |
| Archive file | ✅ | ✅ | ❌ |

> Portal is the source of truth for messages and files. Filament provides moderation/oversight only.

---

## Implementation Notes

- Filament resources are protected at panel level (`canAccessPanel`) AND resource level (`canViewAny`, `canCreate`, `canEdit`, `canDelete`).
- Phase 2 Filament navigation group: "People & Organizations" (sort positions 1–5).
- Phase 3 Filament navigation group: "Leads & Trials" (sort positions 1–3).
- Phase 4 Filament navigation group: "Workspace" (sort 1). Phase 5 adds WorkspaceTaskResource (sort 2). Phase 6 adds WorkspaceFileResource (sort 4) and WorkspaceMessageResource (sort 5).
- Always enforce on server — never rely on front-end hiding alone.
- Business client staff permissions are per-user, managed by Business Client Admin (Phase 4+).
- GetVirtual brand name must not appear in any visible app UI (screens, panels, dashboards, notices). Internal documentation only.
- Active leads can only see their own trial data via `/lead/dashboard` — they cannot access Filament.
- Task internal notes and internal comments are invisible to non-admin/non-manager roles — enforced in controller, not just hidden in Blade.
- Internal workspace messages and files are invisible to client/talent/observer roles — enforced in controller query filters and download access checks.
