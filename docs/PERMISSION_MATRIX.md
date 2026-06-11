# GVOS — Permission Matrix

## Overview
Authorization is enforced at three layers:
1. **Route middleware** — Spatie `role:` middleware in `routes/web.php`
2. **Panel / resource level** — Filament `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()`
3. **Policy level** — Laravel Policies (Phase 2+)

**Default rule: DENY. Access must be explicitly granted.**

---

## Phase 24 — Permission Audit Confirmation (No permission changes)

A full permission audit was performed in Phase 24 across all 11 roles and 22 modules. **No permission changes were made** — all gates were verified correct:

- **Vault:** `requireAccess` blocks `none`/`observer`; `canReveal` blocks archived items and enforces role/owner/allowed-user rules; reveal is POST + `throttle:vault-reveal`.
- **Files:** `requireAccess` blocks `none`; internal files visible to admin/workspace_admin/manager only; downloads verify workspace ownership + visibility + existence.
- **Billing:** talent excluded from billing entirely; internal notes gated to admin/workspace_admin/manager; clients cannot view `void` invoices.
- **Weekly reports:** `visibleStatusesFor($role)` — clients see `published` only, talent sees non-draft, observers blocked.
- **Notifications:** every query scoped to `$request->user()` — no cross-user access.
- **Invitations:** `resolveSafePlatformRole` forbids `super_admin`/`operations_admin`; email lock enforced on accept; existing-account guard on register.
- **Middleware:** `check.status` blocks suspended/inactive; `check.billing` redirects restricted/suspended clients to the restricted page while always allowing billing/index/show.

Result: **PASS — no leaks, no accidental blocks, no privilege escalation.**

---

## Phase 23 — Portal UI Changes (No permission changes)

Phase 23 made no permission changes. All changes were cosmetic Blade view polish:
- Mobile sidebar toggle (layout-level, no auth)
- Dashboard subtitle copy (reads existing computed variables, no new queries)
- Module page empty state copy (role-checked via `auth()->user()->hasRole()` on existing user object)
- max-w change on workspace show (CSS only)

No new routes, no new middleware, no policy changes.

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
    'check.billing'      => \App\Http\Middleware\CheckWorkspaceBillingAccess::class,
]);
```

> **Important:** In Laravel 11, Spatie aliases must be declared explicitly. They are NOT auto-registered.

---

## Phase 18–19 — Billing Access Layer (`check.billing` middleware)

All workspace routes are protected by `check.billing` (`CheckWorkspaceBillingAccess`). The middleware fires after `auth` and `check.status`.

### Always-Allowed Routes (bypass billing check)
| Route name prefix | Reason |
|-------------------|--------|
| `workspace.billing.*` | Client must always access billing pages to pay or view status |
| `workspace.index` | Workspace list page (no subscription context) |
| `workspace.show` | Workspace overview always accessible |

### Workspace Role → Access Decision
| Workspace Role | Restricted Workspace | Suspended Workspace |
|----------------|---------------------|---------------------|
| `admin` | ✅ Pass | ✅ Pass |
| `workspace_admin` | ✅ Pass | ✅ Pass |
| `manager` | ✅ Pass | ✅ Pass |
| `talent` | ✅ Pass | ✅ Pass |
| `assigned_user` | ✅ Pass | ✅ Pass |
| `client_admin` | ❌ → billing.restricted | ❌ → billing.restricted |
| `client_staff` | ❌ → billing.restricted | ❌ → billing.restricted |
| `client` (legacy) | ❌ → billing.restricted | ❌ → billing.restricted |
| `observer` | ❌ → billing.restricted | ❌ → billing.restricted |
| `none` (not a member) | ✅ Pass* | ✅ Pass* |

> *`none` role passes the billing middleware — the workspace controller/policy handles the 403 for non-members (separation of concerns). Billing access is a workspace-member-specific gate.

### Restriction vs Suspension
| State | Condition | Effect |
|-------|-----------|--------|
| Restricted | `restricted_at IS NOT NULL` AND `suspended_at IS NULL` | Client roles redirected to `workspace.billing.restricted` |
| Suspended | `suspended_at IS NOT NULL` AND `status = 'suspended'` | Client roles redirected to `workspace.billing.restricted` (same page, different message) |
| Manual suspension | `suspended_by IS NOT NULL` | Cannot be auto-cleared by `Payment::confirm()` — requires Filament `Reactivate` action |
| Auto suspension | `suspended_at IS NOT NULL` AND `suspended_by IS NULL` | Can be cleared automatically when payment is confirmed |

---

## Phase 21 — Rate Limiting Rules

All rate limiters are defined in `AppServiceProvider::boot()` and applied via `throttle:<name>` on individual routes.

| Limiter Name | Limit | Window | Key | Routes |
|---|---|---|---|---|
| `vault-reveal` | 10 requests | 1 minute | user ID (or IP) | `POST /workspaces/{w}/vault/{item}/reveal` |
| `file-upload` | 20 requests | 1 minute | user ID (or IP) | `POST /workspaces/{w}/files`, `POST /workspaces/{w}/tasks/{t}/files` |
| `chat-send` | 30 requests | 1 minute | user ID (or IP) | `POST /workspaces/{w}/chat` |
| `invitation` | 10 requests | 1 minute | IP address | `POST /invitations/{token}/register`, `POST /invitations/{token}/accept` |

**Pre-existing rate limits (not changed in Phase 21):**

| Endpoint | Limit | Mechanism |
|---|---|---|
| `POST /login` | 5 attempts per email+IP | `LoginRequest::ensureIsNotRateLimited()` |
| `GET /email/verify/{id}/{hash}` | 6/min | `throttle:6,1` in `routes/auth.php` |
| `POST /email/verification-notification` | 6/min | `throttle:6,1` in `routes/auth.php` |

**429 response behaviour:**
- AJAX endpoints (vault reveal): JSON `{"message":"Too Many Attempts."}` — JS catches and shows error message
- Form-submit endpoints: standard Laravel 429 page (future improvement: custom `errors/429.blade.php`)

---

## Phase 22 — Admin Dashboard and Audit Log Access

### New AuditLogResource
| Role | Access |
|------|--------|
| `super_admin` | Full read access to audit logs via Filament |
| `operations_admin` | Full read access to audit logs via Filament |
| All other roles | No access — `canAccess()` returns false; Filament returns 403 |

### Dashboard Widgets (Filament Admin Panel)
All dashboard widgets are rendered only for users who can access the Filament panel (`canAccessPanel()` returns true for `super_admin` and `operations_admin`). No widget exposes:
- Vault `secret_value` (widget only shows counts)
- SMTP credentials (email delivery log widget only shows counts)
- Invitation tokens (not stored in any widget query)
- Raw context objects that could contain sensitive data (recent activity widget shows workspace_code/workspace_name only)

### Navigation Groups (Phase 22)
| Group | Resources |
|-------|-----------|
| Operations | Workspaces, Tasks, Time Logs, Weekly Reports, Files, Messages |
| People | Users, Companies, Departments, Client Profiles, Talent Profiles, Manager Profiles |
| Billing | Billing Plans, Subscriptions, Invoices, Payments |
| Security | Password Vault, Vault Access Logs, Audit Logs |
| Communications | Notification Preferences, Mail Delivery Log, Mail Test |
| Leads & Trials | Lead Requests, Price Estimates, Trials |

---

## Phase 20 — File Storage Access Rules

All file actions (upload, download, delete, index) are in the workspace route group (`auth` + `check.status` + `check.billing`). The controller enforces workspace membership + file ownership on top.

### File Download Authorization
| Check | Where enforced |
|-------|---------------|
| User is authenticated | `auth` route middleware |
| User account is active | `check.status` middleware |
| Billing restrictions apply | `check.billing` middleware (restricted/suspended clients cannot reach file routes) |
| User is a workspace member (or admin) | `WorkspaceFileController::requireAccess()` |
| File belongs to the workspace in the URL | `(int) $file->workspace_id !== (int) $workspace->id` → 404 |
| Internal file requires internal role | `$file->isInternal() && ! canViewInternal($role)` → 403 |
| File exists on disk | `Storage::disk('local')->exists()` → 404 |

### File Visibility by Workspace Role
| Workspace Role | Can see `public` files | Can see `internal` files | Can upload | Can delete own | Can delete any |
|----------------|----------------------|--------------------------|-----------|---------------|----------------|
| `admin` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `workspace_admin` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manager` | ✅ | ✅ | ✅ | ✅ | ✅ |
| `talent` | ✅ | ❌ | ✅ | ✅ | ❌ |
| `client_admin` | ✅ | ❌ | ✅ | ✅ | ❌ |
| `client_staff` | ✅ | ❌ | ✅ | ✅ | ❌ |
| `client` (legacy) | ✅ | ❌ | ✅ | ✅ | ❌ |
| `assigned_user` | ✅ | ❌ | ✅ | ✅ | ❌ |
| `observer` | ✅ | ❌ | ❌ | ❌ | ❌ |

> `internal` files are always hidden from client roles and observers — enforced on query (index), download action, and task show attachment list.

### Upload Validation (Phase 20 hardened)
| Rule | Detail |
|------|--------|
| Max size | 10 MB (`max:10240`) |
| Allowed extensions | pdf, jpg, jpeg, png, webp, doc, docx, xls, xlsx, ppt, pptx, txt, csv, mp4, mov, zip |
| MIME whitelist | `mimes:` rule — validates actual file content, not just extension |
| MIME blocklist | Explicit closure check: PHP, HTML, JS, SVG, shell scripts, executables rejected |
| Extension blocklist | Explicit closure check: php, js, html, htm, svg, exe, sh, asp, etc. rejected |
| Stored extension safety net | If stored extension would be in blocked list, overridden to `bin` |
| Stored filename | UUID-based (`{uuid}.{ext}`) — never user-supplied, never guessable |
| Original filename | Sanitized via `WorkspaceFile::sanitizeFilename()` before DB storage |

### Storage Disk
| Property | Value |
|----------|-------|
| Disk | `local` |
| Root | `storage_path('app/private')` |
| Web-accessible? | No — outside `public/` |
| `serve` setting | `false` — Storage::url() disabled |
| Symlink | `public/storage` → `storage_path('app/public')` (public disk only) |

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
| `/onboarding` (GET) | `auth, check.status` — all authenticated roles |
| `/onboarding/profile` (POST) | `auth, check.status` — all authenticated roles |
| `/onboarding/complete` (POST) | `auth, check.status` — all authenticated roles |

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

## Phase 7 — Time Tracking & Work Reports Access Control

### Time Log Routes (`workspaces/{workspace}/time-logs/...`)

| Route | admin | workspace_admin | manager | talent / assigned_user | client_admin | client_staff | observer |
|-------|-------|-----------------|---------|------------------------|--------------|--------------|----------|
| index | all logs | all logs | all logs | own logs only | approved+client_summary | approved+client_summary | ❌ 403 |
| create / store | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| show | any log | any log | any log | own only | approved+client_summary | approved+client_summary | ❌ |
| edit / update | any log | any log | any log | own (draft/rejected) | ❌ | ❌ | ❌ |
| review | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| destroy | ✅ | ✅ | ✅ | own draft only | ❌ | ❌ | ❌ |

### Phase 9 Timer Routes (`workspaces/{workspace}/time-tracker/...`)

All timer routes require `auth` + `check.status`. Access is enforced in `WorkspaceTimeTrackerController` through `Workspace::resolveUserWorkspaceRole()`.

| Route | admin | workspace_admin | manager | talent / assigned_user | client_admin | client_staff | observer / none |
|-------|-------|-----------------|---------|------------------------|--------------|--------------|-----------------|
| `GET /time-tracker/current` | own active timer | own active timer | own active timer | own active timer | ❌ no timer use | ❌ no timer use | ❌ no timer use |
| start | ✅ | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| stop own timer | ✅ | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| stop another user's timer | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| complete own timer | ✅ | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| complete another user's timer | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

Timer rules:
- One running timer is enforced per user globally.
- Running logs cannot be manually edited, reviewed, or deleted until stopped.
- Completing a timer submits it for manager/admin review.
- Clients never see running timers; they still only see approved logs with `visibility=client_summary`.
- Timer JavaScript is display-only; server timestamps and server duration calculation are authoritative.

### Weekly Report Routes (`workspaces/{workspace}/reports/...`)

| Route | admin | workspace_admin | manager | talent | client_admin | client_staff | observer |
|-------|-------|-----------------|---------|--------|--------------|--------------|----------|
| index | all statuses | all statuses | all statuses | submitted/approved/published | published only | published only | ❌ |
| create / store | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| show | any status | any status | any status | submitted+ | published | published | ❌ |
| edit / update | draft/submitted | draft/submitted | draft/submitted | ❌ | ❌ | ❌ | ❌ |
| destroy | draft/submitted | draft/submitted | draft/submitted | ❌ | ❌ | ❌ | ❌ |

### Phase 7 Filament Resources (Workspace nav group)

| Resource | View | Create | Edit | Delete | Nav sort |
|----------|------|--------|------|--------|---------|
| WorkspaceTimeLogResource | super_admin, ops_admin | ❌ (portal only) | super_admin, ops_admin | super_admin, ops_admin | 7 |
| WorkspaceWeeklyReportResource | super_admin, ops_admin | ❌ (portal only) | super_admin, ops_admin | super_admin, ops_admin | 8 |

> Client roles (client_admin, client_staff, legacy client) never access Filament admin panel.
> Time log visibility: clients see only `status=approved AND visibility=client_summary` records.
> Weekly report visibility: clients see only `status=published` records.
> Blockers and next_steps fields in weekly reports are hidden from client-role views in Blade templates.

### Phase 17 Report Routes (additional)

| Route | Roles that can access |
|-------|-----------------------|
| `GET /workspaces/{workspace}/reports/generate` | admin, workspace_admin, manager |
| `POST /workspaces/{workspace}/reports/generate` | admin, workspace_admin, manager |
| `POST /workspaces/{workspace}/reports/{report}/publish` | admin, workspace_admin, manager |

> Generate routes are placed BEFORE `/{report}` in the route group to prevent slug collision.
> The `publish` action validates that `summary` is non-empty before setting `status=published`.
> After publishing, `notifyWeeklyReportPublished` fires — clients in the workspace are notified.
> After auto-generation, `notifyWeeklyReportGenerated` fires — only workspace managers/admins are notified (never clients).

---

## Phase 18 — Billing Enforcement Access Control

### Billing Access Middleware (`check.billing`)

Registered as a route middleware alias. Applied to ALL workspace routes.

**Internal roles** (admin, workspace_admin, manager, talent, assigned_user): always pass through — never blocked regardless of subscription state.

**Client roles** (client_admin, client_staff, client/legacy, observer): blocked with redirect to `workspace.billing.restricted` when:
- `subscription->isRestricted()` — grace period expired, unpaid invoice (restricted_at NOT NULL, suspended_at IS NULL)
- `subscription->isSuspended()` — manually suspended by admin (suspended_at NOT NULL, status = suspended)

**Always-allowed routes** (even for blocked client roles):
- `workspace.billing.*` (billing index, invoice detail, payments, restricted page)
- `workspace.index`
- `workspace.show`

### Restriction vs Suspension

| State | How triggered | Who is blocked | How cleared |
|-------|--------------|----------------|-------------|
| `restricted` | `restricted_at IS NOT NULL AND suspended_at IS NULL` | Client roles only | Admin reactivate action, or payment auto-clears if `!wasManuallySuspended()` |
| `suspended` | `suspended_at IS NOT NULL AND status = suspended` | All users (internally shown banner; billing page still accessible) | Admin reactivate action only (never auto-cleared) |

### Filament — WorkspaceSubscriptionResource Actions

| Action | Who can use | What it does |
|--------|------------|--------------|
| Restrict | super_admin, operations_admin | Sets `restricted_at = now()`; audits; optional notification to internal staff |
| Suspend | super_admin, operations_admin | Sets `status=suspended`, `suspended_at`, `suspended_by`; optional reason; optional notification |
| Reactivate | super_admin, operations_admin | Clears restriction/suspension; sets `status=active`, `reactivated_at`, `reactivated_by`; optional notification |

> The **Restrict** action is hidden when already restricted or suspended.
> The **Reactivate** action is visible only when `isRestricted() || isSuspended()`.
> Manual suspensions (`suspended_by IS NOT NULL`) are NEVER auto-cleared by `Payment::confirm()` — only admin reactivation can clear them.

---

## Phase 8 — Billing Access Control

### Portal Billing Routes (`workspaces/{workspace}/billing/...`)

All billing routes require `auth` + `check.status`. Access is enforced in `WorkspaceBillingController` using `Workspace::resolveUserWorkspaceRole()`.

| Route | admin | workspace_admin | manager | talent / assigned_user | client_admin | client_staff | observer / none |
|-------|-------|-----------------|---------|------------------------|--------------|--------------|-----------------|
| billing index | ✅ | 👁 read-only | 👁 read-only | ❌ 403 | 👁 own workspace | 👁 own workspace | ❌ 403 |
| invoice detail | ✅ | 👁 read-only | 👁 read-only | ❌ 403 | 👁 own workspace | 👁 own workspace | ❌ 403 |
| payments history | ✅ | 👁 read-only | 👁 read-only | ❌ 403 | 👁 own workspace | 👁 own workspace | ❌ 403 |

### Billing Visibility Rules

- Non-members receive 403 through the workspace access check.
- Talent, assigned_user, and observer roles do not see billing by default.
- Clients cannot see invoice `internal_notes`, internal payment confirmation metadata, or void invoices.
- Workspace admins and managers can view billing status for their workspace but cannot edit amounts or confirm payments from the portal.
- Filament remains the only write surface for billing records.

### Phase 8 Filament Resources (Billing nav group)

| Resource | View | Create | Edit | Operational actions | Delete |
|----------|------|--------|------|---------------------|--------|
| BillingPlanResource | super_admin, ops_admin | super_admin, ops_admin | super_admin, ops_admin | Archive plan | ❌ |
| WorkspaceSubscriptionResource | super_admin, ops_admin | super_admin, ops_admin | super_admin, ops_admin | Status/date tracking | ❌ |
| InvoiceResource | super_admin, ops_admin | super_admin, ops_admin | draft/issued/partial only | Issue, mark paid, cancel | ❌ |
| PaymentResource | super_admin, ops_admin | super_admin, ops_admin | pending only | Confirm, cancel | ❌ |

> Phase 8 is foundation only: no live gateway collection, no recurring billing job, and no payroll.

---

## Phase 10 — Password Vault Access Control

### Portal Vault Routes (`workspaces/{workspace}/vault/...`)

All vault routes require `auth` + `check.status`. Access is enforced in `WorkspaceVaultController` using `Workspace::resolveUserWorkspaceRole()` and `WorkspaceVaultItem` access helpers.

| Action | admin | workspace_admin | manager | client_admin | client_staff | talent / assigned_user | observer / none |
|--------|-------|-----------------|---------|--------------|--------------|------------------------|-----------------|
| Vault index | all workspace items | all workspace items | all workspace items | own + explicitly allowed | explicitly allowed only | explicitly allowed only | 403 |
| Create item | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit item | ✅ | ✅ | ✅ | own created only | ❌ | ❌ | ❌ |
| Archive item | ✅ | ✅ | ✅ | own created only | ❌ | ❌ | ❌ |
| View metadata | ✅ | ✅ | ✅ | own + explicitly allowed | explicitly allowed only | explicitly allowed only | ❌ |
| Reveal/copy secret | ✅ logged | ✅ logged | workspace_admins/own/explicit only, logged | own/explicit only, logged | explicit only, logged | explicit only, logged | ❌ |
| View access logs | ✅ | ✅ | ✅ | own created only | ❌ | ❌ | ❌ |

### Vault Visibility Rules

- `secret_value` is encrypted and hidden from model serialization.
- Vault list, workspace cards, dashboards, Filament tables, audit logs, and access logs never display plaintext secrets.
- Reveal and copy actions are POST-only and log both `workspace_vault_access_logs` and `audit_logs`.
- Allowed user IDs must belong to the workspace member/primary team/task-assignee set.
- Client staff, talent, and assigned users can only access items through explicit role or user assignment.
- No auto-login, browser extension, password injection, screenshot capture, keystroke capture, or screen monitoring exists in Phase 10.

### Phase 10 Filament Resources (Workspace nav group)

| Resource | View | Create | Edit | Operational actions | Delete |
|----------|------|--------|------|---------------------|--------|
| WorkspaceVaultItemResource | super_admin, ops_admin | super_admin, ops_admin | super_admin, ops_admin | Archive, restore | ❌ |
| WorkspaceVaultAccessLogResource | super_admin, ops_admin | ❌ | ❌ | Read-only | ❌ |

---

## Phase 11 - Notifications Access Control

### Portal Notification Routes

All notification routes require `auth` + `check.status`.

| Route | Access |
|-------|--------|
| `GET /notifications` | Authenticated user sees only their own notifications |
| `POST /notifications/{id}/read` | Authenticated user can mark only their own notification as read |
| `POST /notifications/read-all` | Authenticated user marks only their own unread notifications as read |
| `GET /settings/notifications` | Authenticated user sees only their own preferences |
| `PUT /settings/notifications` | Authenticated user updates only their own preferences |

### Recipient Rules

- Task assignment notifies the assigned user.
- Task status changes notify relevant workspace managers/admins, task creator/assignee, and client-side users only for review-facing statuses.
- Internal task comments, internal messages, and internal files notify only admin/workspace_admin/manager recipients.
- Public workspace messages notify workspace members except the sender; email is disabled by default.
- Time log submissions notify managers/workspace admins, not clients.
- Published weekly reports notify client-side workspace users.
- Issued invoices notify client-side workspace users.
- Recorded/confirmed payments notify client-side workspace users and non-actor system admins where appropriate.
- Trial approval notifies the active lead user.

### Payload Safety

- Notification payloads contain only safe metadata: title, message, action_url, workspace_id, related_type, related_id, level, notification_key.
- Notification payloads do not include vault secrets, raw file paths, payment raw_payload, internal admin notes, internal invoice notes, manager notes, tokens, or API keys.
- Action URLs are generated only for recipients who should be able to access the target route.

### Phase 11 Filament Resource

| Resource | View | Create | Edit | Delete |
|----------|------|--------|------|--------|
| UserNotificationPreferenceResource | super_admin, ops_admin | no | no | no |

---

## Phase 12 - Stabilization Audit Notes

- Portal task create/edit forms now require `assigned_to_user_id` to already belong to the workspace active-member or primary-team set. This prevents task assignment from becoming an arbitrary workspace-access grant.
- Filament `WorkspaceTimeLogResource` is read-only in Phase 12. Super Admin and Operations Admin can inspect the table, but create/edit/delete actions are disabled from that resource.
- Notification mark-read routes remain current-user scoped. Mark-all-read now uses a current-user unread notification query update instead of loading all unread models.
- Workspace file lists remain workspace/visibility filtered and are paginated. Linked task data is eager-loaded to avoid per-row task lookups.
- Workspace chat remains workspace/visibility filtered and is capped to the latest 100 messages.
- No Phase 12 change loosens billing, vault, timer, notification, file, chat, task, or report access.

---

## Phase 13 - Workspace Membership and Invitations

### Portal Member Routes

All member management routes require `auth` + `check.status` except the invitation review page. Access is enforced in `WorkspaceMemberController` with `Workspace::resolveUserWorkspaceRole()`.

| Action | admin | workspace_admin | manager | client_admin | client_staff | talent / assigned_user | observer / none |
|--------|-------|-----------------|---------|--------------|--------------|------------------------|-----------------|
| View member page | all | own workspace | view only | own workspace | view only | team list | 403 |
| Add existing user | yes | yes | no | client_staff only | no | no | no |
| Change workspace role | yes | yes | no | client_staff only | no | no | no |
| Deactivate member | yes | yes | no | client_staff only | no | no | no |
| Create invitation | yes | yes | no | client_staff only | no | no | no |
| Resend/revoke invitation | yes | yes | no | client_staff only | no | no | no |
| Accept invitation | invited authenticated email only | invited authenticated email only | invited authenticated email only | invited authenticated email only | invited authenticated email only | invited authenticated email only | invited authenticated email only |

### Phase 13 Boundaries

- Super Admin and Operations Admin can manage all workspace membership.
- Workspace Admin can manage members in that workspace but cannot create platform super/operations admins.
- Client Admin can add or invite `client_staff` only and is restricted to the client company boundary where company data is available.
- Manager, Client Staff, Talent, and assigned users can view member context only; they cannot manage membership.
- Observer and non-member users cannot access member management.
- Membership removal sets `workspace_members.status=removed` and `removed_at`; users are not hard-deleted.
- Invitation audit logs never include the invitation token.
- Filament Workspace admin exposes separate Members and Invitations relation managers.
- Phase 13 does not change billing, payments, vault encryption, timer logic, payroll, gateways, or monitoring/surveillance features.

---

## Phase 14 - Invitation Account Activation

### Invitation Route Access

| Route | Auth Required | Who Can Use |
|-------|--------------|-------------|
| `GET /invitations/{token}` | No | Anyone with the link |
| `POST /invitations/{token}/accept` | Yes (auth middleware) | Logged-in user whose email matches invitation |
| `POST /invitations/{token}/register` | No | Anyone — only valid for invitations with no existing account |

### Registration Constraints

- Email is locked to the invited email — the registrant cannot choose a different one.
- Workspace role comes from the invitation — the registrant cannot change it.
- Platform role is safely inferred from workspace_role or taken from `invitation.platform_role` if set and safe.
- `super_admin` and `operations_admin` can never be assigned via invitation under any code path.
- Client admin invitations remain limited to `client_staff` workspace role.
- Workspace admin invitations infer `line_manager` platform role.

### Phase 14 Boundaries

- No token is logged in any audit event.
- No password is logged in any audit event.
- Accepted/revoked/expired invitations cannot be re-accepted.
- A logged-in user with a non-matching email cannot accept even if they POST directly.
- A duplicate active workspace membership is blocked.
- Phase 14 does not change billing, payments, vault encryption, timer logic, payroll, gateways, or monitoring/surveillance features.

---

## Phase 15 - Email Configuration and System Mail

### Mail Test Page (Filament — `/admin/mail-test`)

| Role | Access |
|------|--------|
| super_admin | ✅ Full access |
| operations_admin | ✅ Full access |
| All other roles | ❌ Denied (Filament panel restriction) |

### Email Delivery Log (Filament — `/admin/email-delivery-logs`)

| Role | Access |
|------|--------|
| super_admin | ✅ Read-only view |
| operations_admin | ✅ Read-only view |
| All other roles | ❌ Denied (Filament panel restriction) |

### Phase 15 Security Constraints

- Mail test page is accessible only to admin roles via `canAccess()` static method on the Filament page class.
- Emails must not include vault secrets, invitation tokens in plain text, SMTP credentials, raw payment payloads, private file paths, or internal notes.
- SMTP errors are sanitized to remove `password=` and `username=` patterns before any logging or display.
- `email_delivery_logs` stores only sha256 hash of recipient email — no plain email addresses.
- Delivery log stores a sanitized error message (max 255 chars) — no credentials or full stack traces.
- Phase 15 does not change billing calculations, payment confirmation logic, vault encryption, timer core, or invitation token logic.

---

## Implementation Notes

- Filament resources are protected at panel level (`canAccessPanel`) AND resource level (`canViewAny`, `canCreate`, `canEdit`, `canDelete`).
- Phase 2 Filament navigation group: "People & Organizations" (sort positions 1–5).
- Phase 3 Filament navigation group: "Leads & Trials" (sort positions 1–3).
- Phase 4 Filament navigation group: "Workspace" (sort 1). Phase 5 adds WorkspaceTaskResource (sort 2). Phase 6 adds WorkspaceFileResource (sort 4) and WorkspaceMessageResource (sort 5). Phase 7 adds WorkspaceTimeLogResource (sort 7) and WorkspaceWeeklyReportResource (sort 8). Phase 8 adds "Billing" resources: Billing Plans, Subscriptions, Invoices, Payments. Phase 10 adds WorkspaceVaultItemResource (sort 9) and WorkspaceVaultAccessLogResource (sort 10) under Workspace. Phase 11 adds UserNotificationPreferenceResource under User Management (sort 2). Phase 15 adds EmailDeliveryLogResource (System nav group, sort 98) and MailTest page (System nav group, sort 99).
- Always enforce on server — never rely on front-end hiding alone.
- Business client staff permissions are per-user, managed by Business Client Admin (Phase 4+).
- GetVirtual brand name must not appear in any visible app UI (screens, panels, dashboards, notices). Internal documentation only.
- Active leads can only see their own trial data via `/lead/dashboard` — they cannot access Filament.
- Task internal notes and internal comments are invisible to non-admin/non-manager roles — enforced in controller, not just hidden in Blade.
- Time log `client_visible_summary` is shown to clients instead of `work_summary` when `visibility=client_summary`. Always enforced in controller query, not only in Blade.
- Internal workspace messages and files are invisible to client/talent/observer roles — enforced in controller query filters and download access checks.
