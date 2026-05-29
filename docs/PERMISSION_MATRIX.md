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

## Implementation Notes

- Filament resources are protected at panel level (`canAccessPanel`) AND resource level.
- When React/Inertia pages are active (Phase 2+), role will also be in shared props.
- Always enforce on server — never rely on front-end hiding alone.
- Business client staff permissions are per-user, managed by Business Client Admin (Phase 2+).
