# GVOS — Permission Matrix

## Overview
Defines what each role can do. Authorization is enforced at:
1. **Route level** — Spatie `role:` middleware in `routes/web.php`
2. **Panel level** — `canAccessPanel()` on User model (Filament)
3. **Resource level** — `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()` on Filament resources
4. **Policy level** — Laravel Policies (Phase 2+)

**Default rule: DENY. Access must be explicitly granted.**

---

## Role Definitions

| Role | Slug | Portal |
|------|------|--------|
| Super Administrator | `super_admin` | Filament `/admin` |
| Operations Administrator | `operations_admin` | Filament `/admin` |
| Line Manager | `line_manager` | `/manager/dashboard` |
| Talent | `talent` | `/talent/dashboard` |
| Individual Client | `individual_client` | `/client/dashboard` |
| Business Client Admin | `business_client_admin` | `/client/dashboard` |
| Business Client Staff | `business_client_staff` | `/client/dashboard` |
| Active Lead | `active_lead` | `/lead/dashboard` |

---

## Phase 1 — Implemented Access Control

### Filament Panel (`/admin`)

| Action | super_admin | operations_admin | All other roles |
|--------|------------|-----------------|-----------------|
| Access `/admin` | ✅ | ✅ | ❌ (403) |
| View Users list | ✅ | ✅ | ❌ |
| Create User | ✅ | ❌ | ❌ |
| Edit User (name, email, status, role) | ✅ | ❌ | ❌ |
| Delete User | ❌ (disabled) | ❌ | ❌ |

### Route Middleware Protection

| Route | Middleware | Access |
|-------|-----------|--------|
| `/manager/dashboard` | `auth, check.status, role:line_manager` | line_manager only |
| `/talent/dashboard` | `auth, check.status, role:talent` | talent only |
| `/client/dashboard` | `auth, check.status, role:individual_client\|business_client_admin\|business_client_staff` | client roles only |
| `/lead/dashboard` | `auth, check.status, role:active_lead` | active_lead only |
| `/profile` | `auth, check.status` | all authenticated, non-blocked users |
| `/account/status` | `auth` | any authenticated user |

### Account Status Gate

| Status | Dashboard access | Profile access | Filament access |
|--------|-----------------|----------------|-----------------|
| active | ✅ | ✅ | Per role |
| pending | ✅ | ✅ | Per role |
| inactive | ❌ → `/account/status` | ❌ | ❌ |
| suspended | ❌ → `/account/status` | ❌ | ❌ |

---

## Full Permission Matrix (all phases)

Legend: ✅ Full | 👁 View only | ✏️ Own records | ❌ No access | 🔒 Encrypted

| Resource | super_admin | ops_admin | line_manager | talent | ind_client | biz_admin | biz_staff | active_lead |
|----------|------------|-----------|-------------|--------|------------|-----------|-----------|-------------|
| **Platform Settings** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Role Management** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **User Management** | ✅ | 👁 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Audit Logs** | ✅ | 👁 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **User Profiles** | ✅ | ✅ | ✏️ own | ✏️ own | ✏️ own | ✏️ own | ✏️ own | ✏️ own |
| **Leads** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | 👁 own |
| **Client Accounts** | ✅ | ✅ | ❌ | ❌ | 👁 own | 👁 own | ❌ | ❌ |
| **Company Accounts** | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ own | ❌ | ❌ |
| **Staff Invitations** | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ own co. | ❌ | ❌ |
| **Talent Profiles** | ✅ | ✅ | 👁 assigned | 👁 own | ❌ | ❌ | ❌ | ❌ |
| **Manager Profiles** | ✅ | ✅ | 👁 own | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Workspaces** | ✅ | ✅ | 👁 assigned | 👁 assigned | 👁 own | 👁 own | 👁 granted | 👁 trial |
| **Task Board** | ✅ | ✅ | ✅ assigned | ✏️ assigned | 👁 + approve | 👁 + approve | 👁 granted | 👁 trial |
| **Chat** | ✅ | ✅ | 👁 monitor | ✅ own ws | ✅ own ws | ✅ own ws | limited | ❌ |
| **File Library** | ✅ | ✅ | 👁 | ✅ own ws | ✅ own ws | ✅ own ws | limited | ❌ |
| **Exact Time Logs** | ✅ | ✅ | ✅ assigned | 👁 own | ❌ | ❌ | ❌ | ❌ |
| **Daily Reports** | ✅ | ✅ | ✅ review | ✏️ submit | ❌ | ❌ | ❌ | ❌ |
| **Billing (admin)** | ✅ | ✏️ record | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Billing (client view)** | ✅ | ✅ | ❌ | ❌ | 👁 own | 👁 own | ❌ | ❌ |
| **Complaints (raise)** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Complaints (manage)** | ✅ | ✅ | ✅ assigned | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Password Vault** | ✅ | ✅ | 👁 audit | 🔒 granted | 🔒 own | 🔒 own | ❌ | ❌ |
| **Asset Tracking** | ✅ | ✅ | 👁 | 👁 own | ❌ | ❌ | ❌ | ❌ |

---

## Implementation Notes

- Filament panels are protected at panel level (`canAccessPanel`) AND resource level (`canViewAny`, `canCreate`, `canEdit`, `canDelete`).
- Blade pages enforce role at route middleware level.
- When React/Inertia pages are active (Phase 2+), roles will also be in Inertia shared props so the frontend can adapt UI.
- Never rely solely on front-end hiding — always enforce on the server.
- Business client staff permissions are per-user, managed by their Business Client Admin (Phase 2+).
