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

> Workspace detail page (`/workspaces/{workspace}`) aborts 403 if the authenticated user is not a member (active status) and not the primary_manager_id or primary_talent_id.

---

## Phase 5 — Task Board Access Control

### Task Board Routes

All task routes require `auth` + `check.status` middleware. No additional `role:` middleware — access is enforced inside the controller using `getUserWorkspaceRole()`.

| Action | admin/manager | talent | client | observer/none |
|--------|-------------|--------|--------|--------------|
| View task list (`index`) | ✅ | ✅ | ✅ | ❌ 403 |
| View task detail (`show`) | ✅ | ✅ | ✅ | ❌ 403 |
| Create task (`create` / `store`) | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| Edit task (`edit` / `update`) | ✅ | ✅ (own assigned only if not admin) | ❌ 403 | ❌ 403 |
| Add public comment | ✅ | ✅ | ✅ | ❌ 403 |
| Add internal comment | ✅ | ❌ (forced public) | ❌ (forced public) | ❌ |
| Change task status | ✅ (any transition) | Allowed transitions only | Allowed transitions only | ❌ |
| Set internal_notes | ✅ | ❌ (stripped) | ❌ (stripped) | ❌ |

### Role Determination (`getUserWorkspaceRole()`)

```
if user hasAnyRole(['super_admin', 'operations_admin']) → 'admin'
else if active workspace_member exists → member.role (client|talent|manager|observer)
else if workspace.primary_manager_id === user.id → 'manager'
else if workspace.primary_talent_id === user.id → 'talent'
else → 'none' (403 on requireWorkspaceAccess)
```

### Task Status Allowed Transitions

| From Status | admin/manager | talent | client |
|------------|-------------|--------|--------|
| pending | any | in_progress | — |
| in_progress | any | blocked, submitted | — |
| blocked | any | in_progress | — |
| submitted | any | — | revision_requested, approved |
| revision_requested | any | in_progress | — |
| approved | any | — | closed |
| closed | any | — | — |
| cancelled | any | — | — |

### Task Board Filament (WorkspaceTaskResource)

| Action | super_admin | operations_admin | All others |
|--------|------------|-----------------|------------|
| View tasks list | ✅ | ✅ | ❌ |
| Create task | ✅ | ✅ | ❌ |
| Edit task | ✅ | ✅ | ❌ |
| Hard delete | ❌ disabled | ❌ | ❌ |
| Archive (soft delete) | ✅ | ✅ | ❌ |

---

## Implementation Notes

- Filament resources are protected at panel level (`canAccessPanel`) AND resource level (`canViewAny`, `canCreate`, `canEdit`, `canDelete`).
- Phase 2 Filament navigation group: "People & Organizations" (sort positions 1–5).
- Phase 3 Filament navigation group: "Leads & Trials" (sort positions 1–3).
- Phase 4 Filament navigation group: "Workspace" (sort positions 1). Phase 5 adds WorkspaceTaskResource at sort 2.
- Always enforce on server — never rely on front-end hiding alone.
- Business client staff permissions are per-user, managed by Business Client Admin (Phase 4+).
- GetVirtual brand name must not appear in any visible app UI (screens, panels, dashboards, notices). Internal documentation only.
- Active leads can only see their own trial data via `/lead/dashboard` — they cannot access Filament.
- Task internal notes and internal comments are invisible to non-admin/non-manager roles — enforced in controller, not just hidden in Blade.
