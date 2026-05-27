# GVOS — Permission Matrix

## Overview
This document defines what each role can do within GVOS. It is used to configure Spatie Laravel Permission and Laravel Policies.

**Authorization stack:**
- Role assignment: Spatie Laravel Permission (roles stored in DB)
- Per-resource access: Laravel Policies
- Middleware: Role-based route guards using `role:` middleware

**Key Principle:** Default is DENY. Access must be explicitly granted.

---

## Permission Matrix

Legend:
- ✅ Full access
- 👁 View only
- ✏️ Create / Edit own
- ❌ No access
- 🔒 Encrypted / special handling

| Resource | super_admin | ops_admin | line_manager | talent | ind_client | biz_admin | biz_staff | active_lead |
|----------|------------|-----------|-------------|--------|------------|-----------|-----------|-------------|
| **Platform Settings** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Role Management** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Audit Logs** | ✅ | 👁 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Leads** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | 👁 (own) |
| **Client Accounts** | ✅ | ✅ | ❌ | ❌ | 👁 (own) | 👁 (own) | ❌ | ❌ |
| **Company Accounts** | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ (own) | ❌ | ❌ |
| **Staff Invitations** | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ (own co.) | ❌ | ❌ |
| **Talent Profiles** | ✅ | ✅ | 👁 (assigned) | 👁 (own) | ❌ | ❌ | ❌ | ❌ |
| **Manager Profiles** | ✅ | ✅ | 👁 (own) | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Workspaces** | ✅ | ✅ | 👁 (assigned) | 👁 (assigned) | 👁 (own) | 👁 (own) | 👁 (granted) | 👁 (trial) |
| **Workspace Settings** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Task Board** | ✅ | ✅ | ✅ (assigned) | ✏️ (assigned) | 👁 + approve | 👁 + approve | 👁 (if granted) | 👁 (trial) |
| **Chat** | ✅ | ✅ | 👁 monitor | ✅ (own ws.) | ✅ (own ws.) | ✅ (own ws.) | limited | ❌ |
| **File Library** | ✅ | ✅ | 👁 | ✅ (own ws.) | ✅ (own ws.) | ✅ (own ws.) | limited | ❌ |
| **Exact Time Logs** | ✅ | ✅ | ✅ (assigned) | 👁 (own) | ❌ | ❌ | ❌ | ❌ |
| **Weekly Summaries** | ✅ | ✅ | ✅ | ✅ (submit) | 👁 | 👁 | 👁 (if granted) | ❌ |
| **Daily Reports** | ✅ | ✅ | ✅ (review) | ✏️ (submit) | ❌ | ❌ | ❌ | ❌ |
| **Billing (Admin)** | ✅ | ✏️ record | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Billing (Client view)** | ✅ | ✅ | ❌ | ❌ | 👁 (own) | 👁 (own) | ❌ | ❌ |
| **Complaints (raise)** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Complaints (manage)** | ✅ | ✅ | ✅ (assigned) | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Password Vault** | ✅ | ✅ | 👁 audit | 🔒 (granted) | 🔒 (own) | 🔒 (own) | ❌ | ❌ |
| **Asset Tracking** | ✅ | ✅ | 👁 | 👁 (own) | ❌ | ❌ | ❌ | ❌ |
| **Satisfaction Surveys** | ✅ | 👁 | 👁 | respond | respond | respond | respond | ❌ |

---

## Spatie Permission Setup

### Roles (seeded in Phase 0)
```
super_admin
operations_admin
line_manager
talent
individual_client
business_client_admin
business_client_staff
active_lead
```

### Key Permissions to Seed (Phase 1)
These will be expanded per phase. Starter set:
```
view-dashboard
manage-users
manage-leads
manage-workspaces
manage-companies
manage-billing
view-audit-logs
manage-time-logs
view-reports
submit-reports
manage-complaints
manage-vault
manage-assets
manage-platform-settings
```

### Middleware Usage
```php
// In routes/web.php
Route::middleware(['auth', 'role:super_admin|operations_admin'])->group(...);
Route::middleware(['auth', 'role:line_manager'])->group(...);
Route::middleware(['auth', 'role:talent'])->group(...);
Route::middleware(['auth', 'role:individual_client|business_client_admin|business_client_staff'])->group(...);
Route::middleware(['auth', 'role:active_lead'])->group(...);
```

---

## Notes for Implementation
- Filament panels are protected at panel level and resource level.
- Inertia pages are protected via route middleware AND Inertia shared data (user role in page props).
- Policies are defined in `app/Policies/` and registered in `AuthServiceProvider`.
- Never rely solely on front-end hiding — always enforce on the server side.
- Business client staff permissions are stored per-user, managed by their Business Client Admin.
