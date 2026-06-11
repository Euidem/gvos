# GVOS — Testing Checklist

## Overview
Run the relevant checklist at the end of each phase before requesting approval to proceed.

---

## Phase 25 — MVP Launch Validation and Live cPanel Bug Fixes

> Live launch validation. The full deployment sequence, env requirements, role smoke tests,
> and backup/restore steps are in **`docs/PRODUCTION_READINESS_CHECKLIST.md`** (§2, §4, §7, §8).

### Static validation (completed Phase 25)
- [x] All web routes controller-backed → `php artisan route:cache` compatible (closures removed)
- [x] No `env()` outside `config/` → `php artisan config:cache` safe
- [x] No `dd`/`dump`/`var_dump`/Ray debug statements in app or views
- [x] Rate limiters defined: vault-reveal, file-upload, chat-send, invitation
- [x] Mail `from.name` defaults to GVOS; no real secrets in `.env.example`

### Live cPanel tests still required (PHP unavailable locally)
- [ ] `git pull origin main`
- [ ] `composer install --no-dev --optimize-autoloader` (if vendor missing / lock changed)
- [ ] `php artisan migrate --force` (on a backed-up DB)
- [ ] `php artisan optimize:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache` — **all succeed**
- [ ] `php artisan permission:cache-reset`
- [ ] `php artisan route:list` — no missing controller methods
- [ ] `php artisan list | grep gvos` — both gvos commands listed
- [ ] `php artisan gvos:storage-check` — green
- [ ] `php artisan gvos:billing-refresh-statuses --dry-run` — clean
- [ ] Role-based smoke tests (admin, talent, manager, clients, lead, suspended) per checklist §4
- [ ] Confirm `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `APP_URL=https://gvos.afbs.ng`

---

## Phase 24 — Final Production QA, Bug Bash and Launch Readiness

> Full production-readiness manual test pass. The deployment commands, environment
> requirements, and complete role-based smoke tests live in
> **`docs/PRODUCTION_READINESS_CHECKLIST.md`** — run that document end-to-end before launch.

### Static audit (completed in Phase 24 — re-verify after any change)
- [x] No "GetVirtual" string in any Blade view
- [x] No rendered "Phase X" / "PART X" labels (only server-side comments)
- [x] Vault reveal route is POST + `throttle:vault-reveal`
- [x] File download route auth-gated; internal files hidden from clients
- [x] Notifications scoped to `$request->user()` everywhere
- [x] Invitation registration cannot assign super_admin / operations_admin
- [x] Billing internal notes gated to admin/manager; void invoices hidden from clients
- [x] Weekly report drafts hidden from client roles
- [x] `.env.example` contains no real secrets; APP_KEY warning present

### Manual tests still required on cPanel (PHP unavailable locally)
- [ ] Run `php artisan migrate --force` on a backed-up DB
- [ ] Run `php artisan gvos:storage-check` — all checks green
- [ ] Run `php artisan gvos:billing-refresh-statuses --dry-run` then the real run
- [ ] Run `php artisan route:list` — confirm no missing controller methods
- [ ] All role-based smoke tests in PRODUCTION_READINESS_CHECKLIST §4
- [ ] `/admin/mail-test` sends a branded test email
- [ ] Confirm `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true` in production `.env`

---

## Phase 23 — Portal Dashboard and Workspace Experience Polish

Run after `git pull && php artisan optimize:clear && php artisan view:clear`. No migrations required.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan permission:cache-reset
```

### Mobile Sidebar Tests
- [ ] On a mobile viewport (< 768px), the sidebar is hidden by default
- [ ] Tapping the hamburger button in the header slides the sidebar in
- [ ] Tapping the backdrop closes the sidebar
- [ ] Tapping a nav link on mobile closes the sidebar and navigates
- [ ] On desktop (≥ 768px), the hamburger button is hidden and the sidebar is always visible

### Talent Dashboard Tests
- [ ] With active tasks: subtitle shows task count and workspace count
- [ ] With workspaces but no tasks: subtitle shows "All caught up"
- [ ] With no workspaces: subtitle shows "No workspaces assigned yet"
- [ ] Time Logs quick link navigates to `/workspaces/{id}/time-logs` when a workspace exists
- [ ] Notifications quick link is present and navigates to notifications page

### Line Manager Dashboard Tests
- [ ] With no workspaces: empty state shows icon + "No workspaces yet" heading + guidance copy
- [ ] Notifications quick link is present

### Individual Client Dashboard Tests
- [ ] With no workspaces: subtitle shows workspace setup message
- [ ] With outstanding balance: subtitle shows balance warning
- [ ] With tasks awaiting approval: subtitle shows pending count
- [ ] Otherwise: subtitle shows "Everything is up to date"

### Business Client Admin Dashboard Tests
- [ ] When published reports = 0: Reports card shows "Published when ready"
- [ ] When published reports > 0: Reports card shows "Ready to view"

### Business Client Staff Dashboard Tests
- [ ] With no workspaces: subtitle shows "No workspaces yet. Contact your business admin to get access."
- [ ] With pending approvals: subtitle shows count
- [ ] Otherwise: subtitle shows active workspace count

### Workspace Show Tests
- [ ] Workspace detail page uses wider layout (max-w-5xl) on wide screens

### Module Empty State Tests
- [ ] Reports index as client with no reports: shows "Your manager will publish weekly progress reports here once your engagement is underway."
- [ ] Reports index as manager with no reports: shows "Create the first weekly report for this workspace."
- [ ] Files index as talent with no files: shows "No files shared yet. Your manager will upload project files here."
- [ ] Files index as client with no files: shows "No files shared yet. Your team will upload project deliverables and briefs here."
- [ ] Vault index: page title uses em dash (` — Password Vault`), not hyphen

---

## Phase 22 — Admin Dashboard and Operational Command Center Polish

Run after `git pull && php artisan optimize:clear && php artisan view:clear`. No migrations required.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan permission:cache-reset
```

### Dashboard Heading Tests
- [ ] Super admin visits `/admin` — page heading is "GVOS Command Center"
- [ ] Subheading "Monitor clients, workspaces, billing, reports and operations from one place." appears
- [ ] No "GetVirtual" text visible anywhere on the dashboard
- [ ] No development/phase language visible on the dashboard

### Widget Tests
- [ ] **PlatformOverviewWidget** — 6 stat cards appear with non-zero or zero values (no PHP errors)
- [ ] **WorkspaceOperationsWidget** — 6 stat cards appear
- [ ] **BillingHealthWidget** — 6 stat cards appear
- [ ] **TimeProductivityWidget** — 5 stat cards appear
- [ ] **ReportsWidget** — 4 stat cards appear
- [ ] **SecurityVaultWidget** — 5 stat cards appear (no secret values visible anywhere)
- [ ] **OperationalAlertsWidget** — renders either "No active alerts" green banner, or a list of alert cards
- [ ] **RecentActivityWidget** — renders last audit events or "no events yet" message
- [ ] **QuickActionsWidget** — 10 action buttons visible; clicking "Create Workspace" opens workspace create form

### Navigation Group Tests
- [ ] Sidebar shows group: **Operations** containing Workspaces, Tasks, Time Logs, Weekly Reports, Files, Messages
- [ ] Sidebar shows group: **People** containing Users, Companies, Departments, Client Profiles, Talent Profiles, Manager Profiles
- [ ] Sidebar shows group: **Billing** containing Billing Plans, Subscriptions, Invoices, Payments
- [ ] Sidebar shows group: **Security** containing Password Vault, Vault Access Logs, Audit Logs
- [ ] Sidebar shows group: **Communications** containing Notification Preferences, Mail Delivery Log, Mail Test
- [ ] Sidebar shows group: **Leads & Trials** containing Lead Requests, Price Estimates, Trials
- [ ] No "Workspace" group visible (old group name)
- [ ] No "People & Organizations" group visible (old group name)
- [ ] No "User Management" group visible (old group name)
- [ ] No "System" group visible (old group name)

### AuditLogResource Tests
- [ ] Visiting `/admin/audit-logs` shows a list of audit events
- [ ] No create/edit/delete buttons visible on audit log list
- [ ] Actor name column is searchable
- [ ] Today filter works correctly
- [ ] Operations admin can access audit logs; client/talent cannot

### Security / Privacy Tests
- [ ] Vault widget does not display any `secret_value` data
- [ ] Recent activity widget does not show vault secret values in context
- [ ] Email delivery log widget does not show SMTP credentials
- [ ] Client/talent roles cannot access `/admin` (403 expected)

### Quick Action Link Tests
- [ ] Create Workspace → `/admin/workspaces/create`
- [ ] Add User → `/admin/users/create`
- [ ] Create Invoice → `/admin/invoices/create`
- [ ] Record Payment → `/admin/payments/create`
- [ ] Mail Test → `/admin/mail-test`
- [ ] Vault Items → `/admin/workspace-vault-items`

---

## Phase 21 — Portal Security, Rate Limiting and CSRF Audit

Run after `git pull && php artisan optimize:clear`. No migrations required.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
```

### Rate Limiting Tests
- [ ] **Vault reveal rate limit:** Hit `POST /workspaces/{w}/vault/{item}/reveal` 11 times in 60 seconds from same user — expect 429 on the 11th request
- [ ] **File upload rate limit:** Upload 21 files in 60 seconds — expect 429 on the 21st
- [ ] **Chat rate limit:** Send 31 messages in 60 seconds — expect 429 on the 31st
- [ ] **Invitation register rate limit:** Submit the invitation register form 11 times in 60 seconds from same IP — expect 429 on 11th
- [ ] **Invitation accept rate limit:** Submit accept form 11 times from same IP in 60 seconds — expect 429 on 11th
- [ ] **Login rate limit:** Confirm 6th failed login attempt per email+IP shows "too many requests" message (pre-existing)

### CSRF Verification
- [ ] Attempt POST to any workspace form without a CSRF token (e.g., via curl) — expect 419 Page Expired
- [ ] Confirm all POST forms in the browser work normally (token is present and valid)
- [ ] Confirm vault reveal JS fetch works in browser (X-CSRF-TOKEN header is sent correctly)
- [ ] Confirm vault reveal fails with 419 if attempted without CSRF token via curl

### Vault Security Checks
- [ ] Archived vault item: attempt to reveal secret — controller should return 403 (`canReveal()` returns false for archived)
- [ ] Vault reveal response: confirm `Cache-Control: no-store` header in browser DevTools → Network tab
- [ ] Vault reveal: confirm secret does NOT appear in any request URL (only in JSON response body)
- [ ] Audit log for vault reveal: confirm `secret_value` is NOT present in the `context` column of `audit_logs` table

### Session Security
- [ ] Confirm `SESSION_SECURE_COOKIE=false` in local `.env` (development)
- [ ] On production: confirm `SESSION_SECURE_COOKIE=true` is set in production `.env`
- [ ] Confirm `APP_DEBUG=false` on production server

### Route Audit
- [ ] Confirm no state-changing actions are accessible via GET (use `php artisan route:list`)
- [ ] Confirm `check.billing` middleware applies to `/workspaces/{workspace}/files/*` routes
- [ ] Confirm unauthenticated user cannot access any workspace route (redirect to login)

### Invitation Security
- [ ] Register via invitation: confirm registered user email matches invitation email (cannot be changed)
- [ ] Register via invitation: confirm registered user cannot choose `super_admin` or `operations_admin` role
- [ ] Accept invitation: confirm only the invited email address can accept

### Notification Security
- [ ] Confirm `/notifications/{id}/read` with another user's notification ID returns 404 (not found for current user)
- [ ] Confirm `/notifications/read-all` only marks current user's notifications as read

---

## Phase 20 — File Storage Security and Access Hardening

Run after `git pull && php artisan optimize:clear`. No migrations required.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
php artisan gvos:storage-check
php artisan route:list | grep file
php artisan route:list | grep download
```

### Storage Configuration
- [ ] `php artisan gvos:storage-check` runs with no errors — all checks pass
- [ ] Verify `storage_path('app/private')` is not inside `public/` directory
- [ ] Verify `public/storage` symlink points to `storage_path('app/public')` (not `app/private`)
- [ ] `config/filesystems.php` local disk has `serve: false`

### Upload Validation
- [ ] Try uploading a `.php` file → rejected with validation error
- [ ] Try uploading a `.exe` file → rejected with validation error
- [ ] Try uploading a `.html` file → rejected with validation error
- [ ] Try uploading a `.svg` file → rejected with validation error
- [ ] Try uploading a PDF over 10 MB → rejected with "max file size" error
- [ ] Try uploading a valid PDF (< 10 MB) → succeeds
- [ ] Try uploading a valid image (jpg/png/webp) → succeeds
- [ ] Try uploading a valid DOCX → succeeds
- [ ] Try uploading a valid MP4 (< 10 MB) → succeeds
- [ ] Try uploading a `file.php.pdf` (double extension, actual PHP content) → rejected (MIME validation catches it)
- [ ] Upload form shows allowed types and 10 MB limit hint

### Download Authorization
- [ ] As workspace member: download a public file → succeeds, file streams correctly
- [ ] As non-member: attempt to access download URL directly → 403
- [ ] As client_admin: download a public file → succeeds
- [ ] As client_admin: attempt to download an internal file → 403
- [ ] As manager: download an internal file → succeeds
- [ ] As restricted client (billing restricted): attempt to access file download → redirected to billing.restricted (check.billing middleware)
- [ ] Try accessing `/workspaces/{otherWorkspace}/files/{fileFromWorkspace1}/download` → 404 (workspace mismatch)
- [ ] Try accessing soft-deleted file ID via download URL → 404 (model binding excludes soft-deleted)
- [ ] Verify downloaded file has sanitized filename in browser (no path separators)

### File Upload URL Not Exposed
- [ ] View page source of file library — no `/storage/app/private` URLs visible
- [ ] View page source of task show page — no raw storage paths visible
- [ ] Download link is `route('workspace.files.download', ...)` not a Storage URL

### Task Attachments
- [ ] Upload file on task detail page → succeeds, appears in Attachments card
- [ ] Upload file on task detail page for task in a different workspace (if URL manipulated) → 404
- [ ] Observer on workspace: no upload form visible, no delete button
- [ ] Client: can upload and download public files, cannot see internal badge files

### Soft Delete
- [ ] Delete a file → disappears from file list
- [ ] Physical file still exists on disk at `storage_path('app/private/workspaces/{id}/{uuid}.ext')`
- [ ] Deleted file row exists in DB with `deleted_at` set

### Audit Log
- [ ] Upload a file → `workspace_file.uploaded` audit entry created
- [ ] Download a file → `workspace_file.downloaded` audit entry created; `downloads_count` incremented
- [ ] Delete a file → `workspace_file.deleted` audit entry created with `deleted_by`

### Security Bypass Scenarios (reason through each)
- [ ] Cannot bypass by guessing file ID (controller verifies workspace_id match)
- [ ] Cannot use file ID from another workspace (404 on workspace mismatch)
- [ ] Cannot open raw storage URL (local disk not web-accessible)
- [ ] Cannot download internal file as client (403 in download action)
- [ ] Cannot upload executable script (mimes whitelist + MIME/extension blocklist)
- [ ] Cannot upload disguised PHP file (getMimeType() content-based check rejects it)
- [ ] Cannot upload oversized file (max:10240 rule)
- [ ] Restricted client cannot access file routes at all (check.billing middleware)
- [ ] Soft-deleted file cannot be re-downloaded (model binding 404)
- [ ] Cannot attach file to task in a different workspace (storeForTask workspace check)

---

## Phase 19 — Billing Middleware QA and Production Readiness Pass

Run after `git pull && php artisan optimize:clear`. No migrations required for Phase 19.

### cPanel Commands (PHP not available locally)
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
```

### Billing Banner — `payment_due` State (Bug 4 fix)
- [ ] Set a subscription status to `payment_due` directly in DB (or run the artisan command against a workspace with billing date in the past and an unpaid invoice)
- [ ] Visit `workspace/show` for that workspace — billing banner should appear with "Payment overdue" (red) styling
- [ ] Visit `workspace/billing/index` for that workspace — billing banner should appear
- [ ] Banner should show invoice reference and due date if available

### Artisan Command — `restored` Counter (Bug 2 fix)
- [ ] Create a subscription with status `payment_due` or `overdue`, no `restricted_at`, and clear all its invoices (or mark them paid)
- [ ] Run `php artisan gvos:billing-refresh-statuses`
- [ ] Summary output should show `restored   : 1` (or matching count)
- [ ] Subscription status should be set back to `active`

### Artisan Command — Step 3 Notification (Bug 3 fix)
- [ ] With a subscription newly in `payment_due` state, verify no "due soon" notification was sent
- [ ] Check `notifications` table — the notification type should be `BillingOverdueNotification`, not `BillingDueSoonNotification`

### Manual Test Matrix
- [ ] Workspace with `active` subscription + no warnings → no banner on workspace/show
- [ ] Workspace with `trial` subscription + billing date 2 days away → "Payment due soon" amber banner
- [ ] Workspace with `payment_due` subscription → "Payment overdue" red banner (intermediate state)
- [ ] Workspace with `overdue` subscription, within grace period → "Payment overdue" red banner + grace end date
- [ ] Workspace with `restricted_at` set → "Workspace access restricted" dark red banner; client redirected to restricted page; manager/talent access normal
- [ ] Workspace with `suspended` → "Workspace suspended" slate banner; client redirected to restricted page; manager/talent access normal
- [ ] Client dashboard (individual or business) shows billing banner when workspace is in billing-alert state
- [ ] Internal role (manager, talent) on restricted workspace → all pages accessible, no redirect
- [ ] `php artisan gvos:billing-refresh-statuses --dry-run` → no DB writes, preview output only
- [ ] Filament Reactivate action on suspended workspace → `restricted_at`, `suspended_at`, `suspended_by` cleared; `status=active`

---

## Phase 18 — Billing Subscription Enforcement and Workspace Access Restrictions

Run after `git pull && php artisan migrate && php artisan optimize:clear`.

### Migration
- [ ] `php artisan migrate` adds `restricted_at`, `suspended_at`, `reactivated_at`, `restriction_reason`, `suspended_by`, `reactivated_by` to `workspace_subscriptions`

### Billing Status Middleware
- [ ] As a client member of a workspace with `restricted_at` set — visiting workspace tasks/chat/files redirects to `workspace.billing.restricted`
- [ ] As a client member of a suspended workspace — visiting workspace tasks redirects to `workspace.billing.restricted`
- [ ] As a manager/talent/admin on a restricted workspace — all workspace pages load normally
- [ ] `workspace.billing.index`, `workspace.billing.invoice`, `workspace.billing.restricted` always load even for restricted clients

### Restricted Page
- [ ] Restricted page shows amber styling, outstanding balance, invoice reference, grace period end date if set
- [ ] Suspended page shows slate/gray styling with suspension message
- [ ] Restriction reason (admin note) appears when set on the subscription

### Billing Banner
- [ ] Billing banner appears on `workspace/show` when subscription is `payment_due` (amber / "Payment due soon")
- [ ] Billing banner appears on `workspace/show` when subscription is `overdue` (red / "Payment overdue" + grace end date)
- [ ] Billing banner appears on `workspace/show` when subscription is `restricted` (dark red / "Workspace access restricted")
- [ ] Billing banner appears on `workspace/show` when subscription is `suspended` (slate / "Workspace suspended")
- [ ] Billing banner appears on `workspace/billing/index` for the same states
- [ ] Billing banner appears on individual-client dashboard when client's workspace is in a billing-alert state
- [ ] Billing banner appears on business-client-admin dashboard for the same
- [ ] Billing banner renders nothing for `active` or `trial` subscriptions with no due-soon condition

### Artisan Command
- [ ] `php artisan gvos:billing-refresh-statuses --dry-run` outputs a summary table; writes no DB changes
- [ ] Running without `--dry-run` on a subscription past `next_billing_date` with unpaid invoice → advances status to `payment_due`
- [ ] Running again → idempotent (no double-notification, no re-firing)
- [ ] Manually suspended workspaces are skipped and logged as `[skip]`

### Filament Enforcement Actions
- [ ] Open a subscription in Filament — `restricted_at` and `suspended_at` icon columns show correct state
- [ ] `Restrict` action is visible for active/trial subscriptions; hidden when already restricted/suspended
- [ ] Clicking `Restrict` shows confirmation modal; confirms sets `restricted_at`, shows success notification
- [ ] `Suspend` action opens modal with reason textarea; confirms sets `status=suspended`, `suspended_at`, `suspended_by`
- [ ] `Reactivate` action visible on restricted/suspended subscriptions; confirms clears all restriction fields, sets `status=active`
- [ ] Reactivated subscription can be accessed by clients normally

### Manual Suspension Safety
- [ ] Set `suspended_by` on a subscription; run `Payment::confirm()` for an associated payment → suspension NOT cleared
- [ ] Subscription with `suspended_by=NULL` and `status=suspended` (auto) + confirm payment → subscription restored to active

### Notifications
- [ ] `BillingDueSoonNotification` creates in-app notification for internal + client members
- [ ] `BillingOverdueNotification` creates in-app notification for internal + client members
- [ ] `WorkspaceRestrictedNotification` creates in-app notification for internal staff only (not clients)
- [ ] `WorkspaceSuspendedNotification` creates in-app notification for all workspace members
- [ ] `WorkspaceReactivatedNotification` creates in-app notification for all workspace members

### Audit Log
- [ ] Filament Restrict action creates `billing.subscription_restricted` audit entry
- [ ] Filament Suspend action creates `billing.subscription_suspended` audit entry with `suspended_by`
- [ ] Filament Reactivate action creates `billing.subscription_reactivated` audit entry with `reactivated_by`
- [ ] Artisan command creates `billing.status_refresh_ran` audit entry with summary counts

---

## Phase 17 — Weekly Report Automation and Client Summary Workflow

Run after `git pull && php artisan migrate && php artisan optimize:clear`.

### Migration
- [ ] `php artisan migrate` adds `generated_at` and `generated_by_user_id` to `workspace_weekly_reports`
- [ ] Existing reports are unaffected (both columns are nullable)

### Generation Flow
- [ ] As manager: navigate to Workspace → Weekly Reports → "Generate Report"
- [ ] Select a date range with existing time logs — preview counts show approved sessions, submitted sessions, completed tasks
- [ ] Select a date range with no logs — warning message appears; confirm dialog fires on clicking "Generate Draft Report"
- [ ] Click "Generate Draft Report" — draft created, redirected to edit page
- [ ] Edit page shows auto-generated banner with date
- [ ] Edit page shows green "Client-Visible Sections" panel (Summary, Achievements, Client Notes) and amber "Internal Sections" panel (Blockers, Next Steps)
- [ ] Generated draft has correct week dates and total_minutes filled in
- [ ] `generated_at` and `generated_by_user_id` are set on the new report record

### Edit and Publish Flow
- [ ] Edit + save as Draft — status stays draft
- [ ] Edit + save as "Submit for Review" — status becomes submitted
- [ ] Edit + save as Approved — status becomes approved
- [ ] No "Published" option in "Save as" — must use the dedicated Publish button on show page
- [ ] On show page: "Publish to Client" button fires confirm dialog, then sets status=published and published_at
- [ ] Publish fails with error if summary is empty (tested by clearing summary field)
- [ ] After publish: client notification sent (check `notifications` table)

### Client View
- [ ] As client: cannot see draft, submitted, or approved reports (403 or empty list)
- [ ] As client: can see published report in list
- [ ] Published report show page: Summary, Work Completed, Hours This Week block (if total_minutes > 0), Message from Your Team (if client_notes set)
- [ ] Blockers and Next Steps NOT visible to client
- [ ] "Published by your GVOS team" footer visible at bottom
- [ ] No "Edit Report" or "Publish" buttons visible to client

### Dashboard and Workspace Show
- [ ] Manager dashboard: bento card shows time log count + report draft count as amber link when drafts exist
- [ ] Workspace show: Weekly Reports card shows latest report week + status badge
- [ ] Workspace show: "Generate" button visible for manager; not visible for client
- [ ] Workspace show: "View Latest Report" button visible for client when published report exists
- [ ] Client dashboard (individual and business-admin): Published Reports card is a link to reports index when count > 0

### Filament Admin
- [ ] `/admin` → Weekly Reports: workspace column shows workspace name + code
- [ ] Duration column shows formatted hours/minutes (not raw minutes)
- [ ] Auto-Gen column shows sparkle icon for auto-generated reports
- [ ] Workspace filter dropdown lets admin filter by workspace
- [ ] Status filter works correctly

### Security
- [ ] Talent user cannot access `GET /workspaces/{ws}/reports/generate` (expect 403)
- [ ] Client user cannot access generate or publish routes (expect 403)
- [ ] Client cannot see `blockers`, `next_steps`, or manager internal notes via any route

---

## Phase 16 — User Onboarding Completion

Run after `git pull && php artisan migrate && php artisan optimize:clear`.

### Onboarding Page
- [ ] New user via invitation redirect lands at `/onboarding` after registerAndAccept
- [ ] Existing user with incomplete profile lands at `/onboarding` after accept
- [ ] `/onboarding` page shows welcome with first name and role label
- [ ] Progress ring shows 0% on first visit with no profile data
- [ ] Filling first name and last name and saving advances progress to at least required steps done
- [ ] "Mark Setup Complete" button appears only when required profile fields are filled
- [ ] Clicking "Mark Setup Complete" sets `onboarding_status = complete` and `onboarding_completed_at`
- [ ] After completion, redirect goes to primary workspace (if exists) or role dashboard
- [ ] "I'll finish this later" and "Go to Dashboard" links work on onboarding page
- [ ] Workspace card on onboarding shows correct workspace name and "Open Workspace" link
- [ ] If no workspace, empty workspace card shows waiting message

### Checklist Accuracy
- [ ] Talent checklist includes tasks and time-log optional items
- [ ] Manager checklist includes team review and timelogs optional items
- [ ] Client checklist includes billing optional item
- [ ] Required items count correctly for percentage (optional items excluded)
- [ ] Percentage shows 100 when all required items done

### Dashboard Banners
- [ ] Talent dashboard shows onboarding banner when `onboarding_status !== complete`
- [ ] Manager dashboard shows onboarding banner when `onboarding_status !== complete`
- [ ] Individual client dashboard shows onboarding banner when `onboarding_status !== complete`
- [ ] Business client admin dashboard shows onboarding banner when `onboarding_status !== complete`
- [ ] Business client staff dashboard shows onboarding banner when `onboarding_status !== complete`
- [ ] Banner disappears after setup is marked complete
- [ ] "Continue Setup" link in banner goes to `/onboarding`

### Workspace Orientation Card
- [ ] Workspace show page shows orientation card for users who joined in the last 7 days
- [ ] Workspace show page shows orientation card for users with incomplete onboarding
- [ ] Role-specific tips shown: talent sees task/time-log/chat; manager sees team/tasks/timelogs; client sees tasks/billing/chat
- [ ] "Complete your profile setup" link appears in card when `needsOnboarding()` is true
- [ ] Orientation card absent for established members with complete onboarding

### Empty States
- [ ] Workspace index shows role-aware empty state with onboarding link when profile incomplete
- [ ] Tasks index shows role-specific message for talent (manager hasn't assigned tasks yet)
- [ ] Tasks index shows role-specific message for clients (deliverables coming from manager)
- [ ] Time logs index shows role-specific message for manager (team hasn't submitted yet)
- [ ] Time logs index shows role-specific message for clients (approved logs coming from manager)

### Audit and Security
- [ ] `onboarding.profile_updated` event appears in audit_logs after saving profile from onboarding
- [ ] `onboarding.completed` event appears in audit_logs after marking setup complete
- [ ] No raw email, token, or password in any audit log entry
- [ ] `/onboarding` requires auth (unauthenticated gets login redirect)
- [ ] `POST /onboarding/complete` only works for authenticated users

---

## Phase 15 — Email Configuration and Templates

Run after `git pull && php artisan migrate && php artisan optimize:clear`.

### Mail Theme
- [ ] Password reset email renders with GVOS dark header and footer (check log or live)
- [ ] Workspace invitation email renders with GVOS brand (dark header, blue button, GVOS footer)
- [ ] Notification email subject is prefixed with `GVOS:`
- [ ] Notification email salutation says "The GVOS Team"
- [ ] No GetVirtual or Laravel branding visible in any email

### Invitation Mail Content
- [ ] Invitation email subject includes workspace name
- [ ] Invitation email body includes inviter's name (when inviter is set)
- [ ] Invitation email expiry line uses human-readable date format
- [ ] "If you were not expecting this invitation" ignore note is present
- [ ] Invitation action button links to correct `/invitations/{token}` URL

### Mail Test Tool
- [ ] `/admin/mail-test` loads for super_admin
- [ ] `/admin/mail-test` loads for operations_admin
- [ ] Talent user gets 403 accessing `/admin/mail-test` (panel-level restriction)
- [ ] Sending test email with `MAIL_MAILER=log` writes to `storage/logs/laravel.log` without error
- [ ] Sending test email with valid cPanel SMTP delivers to the recipient address
- [ ] A deliberate bad SMTP config shows a user-friendly error in the Filament panel without exposing credentials
- [ ] Mail test success logs `admin_user_id` and `recipient_hash` (not raw email) to laravel.log
- [ ] Mail test failure logs `admin_user_id` and `error` (sanitized) to laravel.log

### Email Delivery Log
- [ ] `email_delivery_logs` table exists after migration
- [ ] Sending a workspace invitation mail creates a `success` or `failed` row in the table
- [ ] Triggering a mail notification (e.g. task assigned with email enabled) creates a `success` row
- [ ] A delivery failure creates a `failed` row with sanitized `error_message` (no password/credentials)
- [ ] `/admin/email-delivery-logs` loads in Filament for super_admin
- [ ] Log table shows notification_key, channel, status, recipient user, created_at
- [ ] No raw email addresses are stored in the log (only sha256 hash in `recipient_email_hash`)

### Error Handling and Security
- [ ] SMTP connection failure in NotificationService does not expose credentials in log output
- [ ] SMTP failure in `mailInvitationSafely` logs workspace_id, invitation_id, email_hash — no raw email, no token
- [ ] `sanitizeErrorMessage()` strips `password=` and `username=` strings from error messages

### Environment Documentation
- [ ] `.env.example` has `MAIL_MARKDOWN_THEME=gvos` entry
- [ ] `.env.example` has cPanel SMTP block with SSL (port 465) and TLS (port 587) examples
- [ ] `.env.example` references mail test tool in the cPanel block comment
- [ ] No visible `GetVirtual` text in any visible UI or email

### Regression
- [ ] Existing workspace invitation flow still works end to end
- [ ] Notification inbox still shows notifications correctly
- [ ] Notification preferences page still saves correctly
- [ ] No billing, vault, timer, or payment changes

---

## Phase 0 — Foundation Setup ✅ PASSED

- [x] Laravel loads on cPanel staging
- [x] Login page displays correctly
- [x] `admin@gvos.local / password` logs in successfully
- [x] Admin redirected to Filament `/admin`
- [x] All 8 roles seeded in DB
- [x] `.env` not in Git, vendor not in Git

---

## Phase 1 — Identity and Access Foundation

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Role Middleware (CRITICAL — was broken, now fixed)
- [ ] `php artisan route:list` shows role middleware on dashboard routes
- [ ] Logging in as Talent → `/talent/dashboard` loads without error
- [ ] Logging in as Line Manager → `/manager/dashboard` loads
- [ ] Logging in as Individual Client → `/client/dashboard` loads
- [ ] Logging in as Active Lead → `/lead/dashboard` loads
- [ ] No `Target class [role] does not exist` error anywhere

### Role Redirects
- [ ] `super_admin` → `/admin`
- [ ] `operations_admin` → `/admin`
- [ ] `line_manager` → `/manager/dashboard`
- [ ] `talent` → `/talent/dashboard`
- [ ] `individual_client` → `/client/dashboard`
- [ ] `business_client_admin` → `/client/dashboard`
- [ ] `business_client_staff` → `/client/dashboard`
- [ ] `active_lead` → `/lead/dashboard`

### Access Control
- [ ] Talent cannot access `/manager/dashboard` — gets 403 or redirect
- [ ] Client cannot access `/talent/dashboard`
- [ ] Non-admin cannot access `/admin` — gets 403
- [ ] Suspended user → `/account/status` on any dashboard route
- [ ] Inactive user → `/account/status` on any dashboard route
- [ ] Pending user → can access their dashboard normally

### Filament User Management
- [ ] `/admin/users` loads — shows name (with first/last description), email, role badge, status badge
- [ ] Role badges show friendly labels (e.g. "Line Manager" not "line_manager")
- [ ] Role filter shows friendly labels
- [ ] Status filter works
- [ ] Search by name and email works
- [ ] Super Admin can create a new user with first name, last name, display name, email, password, role, status, timezone
- [ ] Display name auto-generates from first + last if left blank
- [ ] Created user has matching `user_profiles` row with first_name and last_name
- [ ] Super Admin can edit user — form pre-fills first_name and last_name from profile
- [ ] Changing role in edit saves correctly
- [ ] Operations Admin can VIEW users but cannot create or edit (no Create button shown)
- [ ] Delete button is NOT present

### Timezone Dropdown
- [ ] Filament create/edit form shows timezone dropdown (not free text)
- [ ] Default timezone is Africa/Lagos
- [ ] Profile page at `/profile` shows timezone dropdown with same 11 options
- [ ] Saving a profile with a valid timezone works
- [ ] Submitting a timezone not in the list → validation error

### Profile Editing
- [ ] `/profile` loads for all authenticated roles
- [ ] Shows current first_name, last_name, email, phone, country, city, bio, timezone
- [ ] Saving profile updates both `users` (name, email, timezone) and `user_profiles`
- [ ] Filling first + last name sets `onboarding_status` to `complete`
- [ ] Role shown as read-only (e.g. "Talent" — cannot be changed)
- [ ] `audit_logs` has `user.profile_updated` entry after saving

### Password Change
- [ ] Wrong `current_password` → validation error
- [ ] Non-matching confirmation → validation error
- [ ] Correct inputs → password updated
- [ ] Old password no longer works after change
- [ ] `audit_logs` has `user.password_changed` entry

### Dashboards
- [ ] All 8 dashboards load without error
- [ ] Personalised welcome with first_name (if profile filled)
- [ ] Status badge shows correct status
- [ ] Role badge shows friendly name
- [ ] Profile link in sidebar and dashboard card works

### Audit Log
- [ ] `audit_logs` table has entries after login, create, edit, profile update, password change
- [ ] Rows have meaningful `context` JSON (from/to values for changed fields)
- [ ] Audit rows cannot be deleted

---

## Phase 2 — People and Organizations

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### GetVirtual UI Removal
- [ ] Login page subtitle shows "Operations Management Platform" (not "GetVirtual Operations System")
- [ ] Forgot password page subtitle updated
- [ ] Register page: no "GetVirtual" text visible
- [ ] Account status page: subtitle updated
- [ ] Sidebar "Managed Operations" (not "GetVirtual Operations")
- [ ] Active lead dashboard: no GetVirtual email link

### Companies
- [ ] `/admin/companies` loads — shows name, type, country, primary contact, status
- [ ] Super Admin can create a company — all fields save correctly
- [ ] Status badge colors: active=green, pending=amber, inactive=gray, suspended=red
- [ ] Type filter works (individual / business)
- [ ] No delete button present
- [ ] Company creation fires `company.created` audit log entry

### Departments
- [ ] `/admin/departments` loads — shows name, company, manager, status
- [ ] Can create a department linked to a company
- [ ] Company relationship shown in list
- [ ] Company filter works
- [ ] Department creation fires `department.created` audit log entry

### Client Profiles
- [ ] `/admin/client-profiles` loads — shows user email, company, client type, status
- [ ] Can create a client profile linked to a user
- [ ] Client type options: Individual, Business Admin, Business Staff
- [ ] Status filter works
- [ ] Client profile creation fires `client_profile.created` audit log entry

### Talent Profiles
- [ ] `/admin/talent-profiles` loads — shows user email, talent code, training status, equipment status, status
- [ ] Can create a talent profile linked to a user
- [ ] Training status badge colors correct
- [ ] Equipment status badge colors correct
- [ ] Talent profile creation fires `talent_profile.created` audit log entry

### Manager Profiles
- [ ] `/admin/manager-profiles` loads — shows user email, manager code, load / capacity, status
- [ ] Can create a manager profile linked to a user
- [ ] current_load / capacity_limit shown as description under load count
- [ ] Manager profile creation fires `manager_profile.created` audit log entry

### UserResource — Stub Profile Auto-Creation
- [ ] Create a Talent user in Filament → `talent_profiles` row created (status: pending, training_status: not_started)
- [ ] Create a Line Manager user → `manager_profiles` row created (status: pending)
- [ ] Create an Individual Client user → `client_profiles` row created (status: pending, client_type: individual)
- [ ] Create a Business Client Admin → `client_profiles` row created (client_type: business_admin)
- [ ] Create a Business Client Staff → `client_profiles` row created (client_type: business_staff)

### Dashboard Updates
- [ ] Super Admin dashboard: shows count cards for Companies, Talent Profiles, Manager Profiles, Client Profiles
- [ ] Operations Admin dashboard: shows same count cards
- [ ] Talent dashboard: shows talent profile status card with training + equipment status
- [ ] Line Manager dashboard: shows manager profile card with capacity
- [ ] Individual Client dashboard: shows client profile status card
- [ ] Business Client Admin dashboard: shows company card (name, status, country)
- [ ] Business Client Staff dashboard: shows staff profile card
- [ ] All 8 dashboards: notice updated from "Phase 1" to "Phase 2"

---

## Phase 3 — Leads and Trial Flow

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Public Lead Form (UX upgrade — multi-step)

Run after `git pull && php artisan optimize:clear` (no new migrations for UX update).

#### Load and Branding
- [ ] `GET /request-service` loads — GVOS header, gradient progress bar, step 1 visible
- [ ] No "GetVirtual" text visible anywhere on the page
- [ ] Page is scrollable; side panel visible on desktop (≥1024px), stacked on mobile
- [ ] "No payment required" badge visible in header on desktop

#### Step Navigation
- [ ] Step 1 shows "Your details" — Back button hidden, Next button visible
- [ ] Clicking Next with blank required fields: first_name, last_name, or email highlighted in red — does NOT advance
- [ ] Filling required fields and clicking Next advances to Step 2
- [ ] Progress bar fills to 50% on Step 2, 75% on Step 3, 100% on Step 4
- [ ] Step labels update: completed steps dim, current step bold white
- [ ] Back button appears from Step 2 onward
- [ ] Back correctly returns to previous step without losing entered data
- [ ] Submit button appears only on Step 4 — not on Steps 1–3

#### Step 1 — Your Details
- [ ] First name, last name, email fields present and required
- [ ] Phone, country, city marked optional — form advances without them
- [ ] Timezone dropdown has 11 options + "Other (specify below)"
- [ ] Selecting "Other" reveals the custom timezone free-text field
- [ ] Selecting a named timezone hides the custom field
- [ ] Entering a custom timezone and submitting: `lead_requests.timezone` stores the custom value

#### Step 2 — Support Needed
- [ ] Individual card selected by default (border highlighted)
- [ ] Clicking Business card highlights it and shows company fields (name, website, email domain)
- [ ] Clicking Individual hides company fields again
- [ ] 8 role icon cards visible: Virtual Assistant, Executive Assistant, Social Media Manager, Video Editor, Developer, Designer, Motion Graphics, Other
- [ ] Clicking a role card highlights it (coloured border)
- [ ] Clicking "Other (please specify)" shows free-text role field
- [ ] Clicking a different role hides the other field

#### Step 3 — Work Details
- [ ] Hours per week, start date, schedule, skills, description all present
- [ ] All fields optional — form advances without them
- [ ] textarea for work description is resizable
- [ ] Start date in the past shows Laravel validation error on submit

#### Step 4 — Final Details
- [ ] 6 budget range radio cards with sub-labels visible
- [ ] Clicking a budget card highlights it
- [ ] Source field present
- [ ] Privacy note visible ("Your information is only used...")
- [ ] "No payment required at this stage" text present
- [ ] Submit button present with arrow icon

#### Form Submission
- [ ] Valid form submission creates a `lead_requests` row with status = 'new'
- [ ] Redirects to `/request-service/success`
- [ ] `audit_logs` has `lead_request.created` entry with `actor_id = null`
- [ ] Non-authenticated users can access the form (no login required)
- [ ] Client type defaults to 'individual' even if user didn't interact with Step 2

#### Validation Errors (server-side)
- [ ] Submitting with invalid start date: Laravel returns error, form restores to Step 3 with error displayed
- [ ] Error message box visible with red border at top of the form
- [ ] All previously entered values restored via `old()` across all steps

#### Success Page
- [ ] `/request-service/success` shows emerald accent stripe and double-ring checkmark icon
- [ ] Heading "We've got your request!" visible
- [ ] 4-step "What happens next" card visible with correct copy
- [ ] "Sign In to GVOS" button links to login
- [ ] "Submit Another Request" links back to `/request-service`
- [ ] No "GetVirtual" text anywhere

#### Mobile Layout
- [ ] On mobile (< 1024px): form card appears above side panel
- [ ] Progress bar and step counter visible on mobile
- [ ] Step labels ("Your details" etc.) hidden on xs — step inline label visible instead
- [ ] Buttons are full-width or tap-friendly
- [ ] No horizontal scroll at 375px viewport width
- [ ] Role cards and budget cards wrap correctly on small screens

### Lead Request Filament Resource
- [ ] `/admin/lead-requests` loads — "Leads & Trials" navigation group visible
- [ ] Navigation badge shows count of 'new' leads in amber
- [ ] Table shows lead name + email + company description column, status badge, type badge
- [ ] Status badges show correct colors (gray=new, warning=under_review, success=trial_approved etc.)
- [ ] Filters work: status, client_type, role_needed
- [ ] Search works: by name, email, company
- [ ] "Under Review" action advances lead status
- [ ] "Price Estimated" action advances status
- [ ] "Mark Lost" and "Disqualify" actions work
- [ ] Creating a lead request in Filament fires `lead_request.created` audit log
- [ ] Editing a lead fires `lead_request.updated` with before/after diff

### Price Estimates
- [ ] `/admin/price-estimates` loads
- [ ] Can create a price estimate linked to a lead — lead status advances to 'price_estimated' (if new/under_review)
- [ ] Mark Sent action changes status to 'sent'
- [ ] Mark Accepted action: status → accepted, accepted_at set, lead status → price_accepted
- [ ] Mark Rejected and Mark Expired actions work
- [ ] Audit entries fire for each status change

### Approve Trial Action
- [ ] "Approve Trial" modal opens with talent/manager select, price estimate select, date/time, duration, notes
- [ ] Submitting with a new email: creates user with active_lead role, creates user_profile, creates client_profile stub, creates trial
- [ ] Submitting with an existing email: updates role to active_lead, creates trial
- [ ] Lead status updates to 'trial_approved'
- [ ] Trial record exists in `trials` table with correct fields
- [ ] Filament notification shows correct message (new vs existing user)
- [ ] Two audit entries: `trial.created` + `lead_request.status_changed`

### Trial Resource
- [ ] `/admin/trials` loads — shows trial code, lead email, active lead user, talent, status, dates
- [ ] "Start Trial" action: sets starts_at = now(), ends_at = now() + duration, status → active, lead → trial_active
- [ ] "Complete" action: status → completed, lead → trial_completed
- [ ] "Expire" and "Cancel" actions work
- [ ] "Payment Pending" action (on completed trial): lead status → payment_pending
- [ ] All actions fire correct audit log entries

### Active Lead Dashboard
- [ ] Active lead with no trial sees "Onboarding in progress" message
- [ ] Active lead with approved trial sees trial status card and approval message
- [ ] Active lead with active trial sees countdown (hours remaining), ends_at date
- [ ] Active lead with assigned talent/manager sees team card
- [ ] Active lead with accepted price estimate sees estimate card (currency, amount, billing cycle)
- [ ] Active lead with completed/payment_pending status sees "Ready to continue?" CTA
- [ ] Trial workspace placeholder shown for active leads

### Admin Dashboard Lead Pipeline
- [ ] Super Admin dashboard shows Lead Pipeline section with 6 metric cards
- [ ] Operations Admin dashboard shows same section
- [ ] Counts are correct (match lead_requests table)
- [ ] Phase 3 notice replaces Phase 2 notice on both dashboards

## Phase 4 — Workspace Engine

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Country Dropdown Cleanup
- [ ] Profile page `/profile` — Country is a dropdown (not a text input), has ≥17 options
- [ ] Selecting a country and saving updates `user_profiles.country`
- [ ] Filament `/admin/companies` create form — Country is a searchable Select
- [ ] Filament `/admin/companies` edit form — saved country pre-selects correctly
- [ ] Lead request form `/request-service` Step 1 — Country is a dropdown (not text input)
- [ ] Selecting a country on lead form and submitting stores value in `lead_requests.country`
- [ ] "Other" option at bottom of country list present on all dropdowns

### Workspaces — Filament Admin
- [ ] `/admin/workspaces` loads — nav group "Workspace" visible
- [ ] Can create a workspace manually — workspace_code auto-generated if blank
- [ ] Status badges: pending=amber, active=green, paused=blue, completed=gray, cancelled=red
- [ ] Type badges: trial=violet, ongoing=green, project=teal
- [ ] Activate action: visible on pending workspaces → status changes to active
- [ ] Pause action: visible on active workspaces → status changes to paused
- [ ] Complete action: visible on active/paused → status changes to completed, ends_at set to now
- [ ] No delete button present
- [ ] Workspace creation fires `workspace.created` audit log entry
- [ ] Workspace edit fires `workspace.updated` with before/after diff

### Workspace Members — Relation Manager
- [ ] Edit page of a workspace shows "Members" tab
- [ ] "Add Member" action opens modal — user select, role, status
- [ ] Adding a member creates workspace_members row, fires `workspace.member_added` audit log
- [ ] Editing a member fires `workspace.member_updated` audit log
- [ ] "Remove" action sets status=removed, removed_at=now, fires `workspace.member_removed`
- [ ] Removed member no longer shows in active members list (status filter)

### Create Workspace from Trial
- [ ] TrialResource table: "Create Workspace" action visible on approved/active/completed trial with no workspace
- [ ] "Create Workspace" action NOT visible if workspace already exists for the trial
- [ ] Clicking: creates `workspaces` row with auto-generated code
- [ ] Lead name used in workspace name (e.g. "Jane Smith Trial Workspace")
- [ ] Primary manager and primary talent copied from trial
- [ ] Active lead, talent, and manager added as workspace members with correct roles
- [ ] Audit log: `trial.workspace_created` entry created
- [ ] Filament notification confirms success with workspace code

### Workspace Portal — Blade Pages
- [ ] `/workspaces` loads for authenticated users
- [ ] Shows list of workspaces where user is a member (or primary team)
- [ ] Empty state shown when user has no workspaces
- [ ] Workspace cards show name, code, type, status badges, team, dates
- [ ] Clicking a workspace card navigates to `/workspaces/{workspace}`
- [ ] `/workspaces/{workspace}` detail page loads — status banner, team card, schedule card, members list
- [ ] Active workspace: green status banner with hours remaining (if ends_at set)
- [ ] Pending workspace: amber banner "Workspace pending activation"
- [ ] Completed workspace: gray banner
- [ ] Non-member accessing workspace detail → 403 Forbidden

### Dashboard Updates
- [ ] Super Admin: workspace count card shows active/total (e.g. "2/5")
- [ ] Operations Admin: same workspace count card
- [ ] Talent: "My Workspaces" card links to `/workspaces`; count correct
- [ ] Line Manager: "My Workspaces" card links to `/workspaces`; count correct
- [ ] Individual Client: "My Workspace" card links to `/workspaces`
- [ ] Business Client Admin: "My Workspace" card links to `/workspaces`
- [ ] Business Client Staff: "Workspace Access" card links to `/workspaces`
- [ ] Active Lead: if workspace exists, shows clickable workspace card linking to workspace detail
- [ ] Active Lead: if no workspace yet, shows "Your workspace is being prepared" placeholder
- [ ] All 8 dashboards: Phase 4 notice replaces Phase 3/2 notice

## UI Fidelity Audit — Design System Alignment

Run after `git pull && php artisan optimize:clear` (no migrations needed).

### Typography and Fonts
- [ ] Login page: GVOS heading rendered in Manrope (bold, wider letterforms than Inter)
- [ ] All body text and labels use Inter font (not browser default system-ui)
- [ ] No visible fallback to system-ui — Google Fonts CDN is reachable
- [ ] Metric numbers on dashboards render in a consistent weight

### Sidebar (Authenticated Portal Layout)
- [ ] Sidebar width is approximately 280px (not 256px)
- [ ] Sidebar background is very dark navy (#0B0F19) — not black, not slate-900
- [ ] GVOS logo area: small blue filled square with hub icon + "GVOS Platform" in light blue + "Enterprise Ops" in muted blue below
- [ ] **Active nav item:** has a left blue border, light blue text, and a semi-transparent white/blue background
- [ ] **Inactive nav items:** muted blue-gray text; hovering reveals light blue text + faint background
- [ ] Nav items present: Dashboard, Workspaces, My Profile — all links functional
- [ ] User footer at bottom: user initials in a blue avatar circle, display name, role label, Sign Out link
- [ ] Sign Out fires POST to `/logout` (not a plain anchor `href`)

### Top Bar
- [ ] Top bar is 64px tall (h-16), white/off-white background, subtle bottom border
- [ ] Top bar sticks to the top of the viewport when page content scrolls
- [ ] Notification bell icon visible on right side (Material Symbols: `notifications`)
- [ ] Security/shield icon visible (Material Symbols: `security`)
- [ ] User avatar (colored circle with initial) and display name visible
- [ ] User avatar shows correct role label below name

### Color Tokens — No Indigo Anywhere
- [ ] No indigo (#4F46E5 or `indigo-*`) colors visible anywhere in the portal
- [ ] Primary action buttons use GVOS blue (`#0058be`)
- [ ] Active/success status badges are green (`#10B981`)
- [ ] Payment-due/warning status badges are amber (`#F59E0B`)
- [ ] Blocked/error status badges are red (`#EF4444`)
- [ ] Trial type badge uses purple (`#8B5CF6`)
- [ ] Page background is off-white (`#f7f9fb`), not pure white and not gray-100

### Cards and Components
- [ ] All card components: white background, rounded-xl corners, subtle border (#E2E8F0), soft drop shadow
- [ ] No rounded-2xl corners visible (maximum is rounded-xl)
- [ ] Input focus states show a secondary blue ring (`focus:ring-2 focus:ring-secondary/20`)
- [ ] Input focus state changes border to secondary blue
- [ ] Error states show red border (`border-status-blocked`)
- [ ] Error messages render in red (`text-status-blocked`)

### Icons
- [ ] All icons are Material Symbols Outlined glyphs (outlined style, not filled)
- [ ] No SVG `<path>` icon fallbacks rendering alongside or in place of symbol font glyphs
- [ ] Icons align vertically with adjacent text (`vertical-align: middle`)
- [ ] Icon sizes are consistent: 16px inline, 18–20px card headers, 24px large feature icons

### Auth Pages
- [ ] Login page: dark navy background, GVOS logo at top, blue accent line at top of card
- [ ] Login: email icon (Material Symbols: `mail`) and lock icon (`lock`) in input prefix
- [ ] Login: "Security Notice" box with shield icon at bottom of card
- [ ] Forgot password page: light dot-pattern background, dark visual header panel with lock_reset icon
- [ ] Account status page: red left-border alert banner, blue accent bar on card, correct conditional text (suspended vs inactive)

### Dashboard Views
- [ ] All 8 dashboards load without errors for their respective roles
- [ ] Welcome heading uses Manrope-style bold weight (visually distinct from body text)
- [ ] Metric/stat cards use GVOS card pattern (white, rounded-xl, border, shadow)
- [ ] Icon containers use `bg-secondary/5` background with `text-secondary` icon
- [ ] Phase notice banner uses `bg-secondary/5 border-secondary/20` styling with `info` icon

### Lead Request Form
- [ ] `/request-service` progress header: GVOS secondary blue background (not indigo)
- [ ] Submit button: GVOS secondary blue (not indigo)
- [ ] Focus rings on inputs: secondary blue ring (not indigo)
- [ ] GVOS logo in form header (hub icon + "GVOS" in secondary-fixed) — no "GetVirtual" text

### Workspace Views
- [ ] `/workspaces` card grid: status badges use GVOS status-* color tokens
- [ ] `/workspaces` type badges: trial=purple, ongoing/project=secondary blue
- [ ] `/workspaces/{workspace}` status banners: amber for pending, green for active, green/gray for completed
- [ ] Workspace members list: role badges use correct GVOS tokens (manager=secondary, talent=green, client=amber)
- [ ] `divide-border-subtle` renders a visible but subtle dividing line between member rows

### Profile Page
- [ ] `/profile` inputs: border-border-subtle, secondary focus ring
- [ ] Save Changes button: GVOS secondary blue with save icon
- [ ] Change Password button: dark on-surface color (distinct from blue Save button)
- [ ] Error messages: text-status-blocked (red)
- [ ] Success banners: green bg-status-active/10 with check_circle icon

---

## Phase 5 — Task Board

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations
- [ ] `php artisan migrate` runs without error
- [ ] `workspace_tasks` table exists with all columns
- [ ] `workspace_task_comments` table exists with all columns
- [ ] `php artisan route:list` shows 8 workspace task routes

### Task Board — Filament Admin
- [ ] `/admin/workspace-tasks` loads — nav group "Workspace", nav item "Tasks" visible
- [ ] Navigation badge shows count of open tasks (pending + in_progress + revision_requested) in amber
- [ ] Can create a task from Filament — task_code auto-generated (TASK-00001 format)
- [ ] created_by_user_id set to the logged-in admin
- [ ] Status and priority dropdowns show correct options
- [ ] Editing a task fires `workspace_task.updated` audit log with before/after diff
- [ ] Changing status in edit fires `workspace_task.status_changed` audit log entry
- [ ] Changing assigned_to fires `workspace_task.assigned` audit log entry
- [ ] Archive action (soft delete) visible in table — no hard delete button
- [ ] Archived tasks do not appear in main list

### Task Board — Workspace Portal

#### Index (Kanban Board — Drag & Drop)
- [ ] `/workspaces/{workspace}/tasks` loads for a workspace member
- [ ] Page shows 7 status columns: Pending, In Progress, Blocked, Submitted, Revision Req., Approved, Closed
- [ ] Each column has a distinct colored header with icon, label, and count badge
- [ ] Tasks appear in the correct column for their status
- [ ] Task cards show: priority badge, task code, title (truncated), assignee avatar/name, comment count, due date
- [ ] Overdue tasks show due date in red; due-soon tasks show in amber
- [ ] Empty columns show "No tasks here" message (below the droppable zone)
- [ ] Global empty state shown when workspace has no tasks at all; has "Create First Task" button
- [ ] "New Task" button visible to admin/manager/talent/client (not observer)
- [ ] "Drag cards between columns to change status" hint visible to authorized roles
- [ ] Non-member accessing task index → 403

#### Drag and Drop
- [ ] Task cards for admin/manager/talent/client show `drag_indicator` handle icon
- [ ] Observer role cards do not show drag handle; SortableJS not initialized
- [ ] Dragging a card shows: ghost placeholder (dashed blue border), elevated dragged clone (shadow + rotation)
- [ ] Drop target column shows blue dashed outline while hovering (highlight removed on drop)
- [ ] Dropping a card in a new column sends AJAX POST to `/workspaces/{ws}/tasks/{task}/status`
- [ ] Successful move: card stays in new column, count badges update, green toast shown
- [ ] Failed move (permission denied): card reverts to original column, error toast shown
- [ ] Failed move (network error): card reverts to original column, error toast shown
- [ ] Column empty-state message appears/disappears correctly after moves
- [ ] Clicking a card (not dragging) navigates to task detail page
- [ ] Valid talent move: pending → in_progress succeeds (green toast)
- [ ] Valid talent move: in_progress → submitted succeeds (green toast)
- [ ] Invalid move as talent: submitted → approved → 403 → card reverts, error toast
- [ ] Valid client move: submitted → approved succeeds (green toast)
- [ ] Audit log `workspace_task.status_changed` fires on every successful drag move
- [ ] Mobile horizontal scroll still works at narrow viewport
- [ ] SortableJS CDN not loaded on other pages (only task board index)

#### Create Task
- [ ] `/workspaces/{workspace}/tasks/create` accessible to admin/manager
- [ ] Talent/client accessing create → 403
- [ ] Form: title (required), description, assign-to (dropdown of workspace members), priority, due date
- [ ] Internal notes field shown only to admin/manager
- [ ] Submitting valid form creates task; task appears in "Pending" column on index
- [ ] task_code auto-generated (TASK-XXXXX format)
- [ ] `workspace_task.created` audit log entry created
- [ ] Missing title → validation error displayed on form

#### Task Detail (Show)
- [ ] `/workspaces/{workspace}/tasks/{task}` loads
- [ ] Task code, status badge, priority badge, title, description visible
- [ ] Internal notes shown only to admin/manager (hidden for talent/client)
- [ ] Status action buttons shown based on allowed transitions for the user's role
- [ ] Clicking a status button shows a confirm() dialog; Cancel does not change status
- [ ] Status change succeeds → page reloads with updated status
- [ ] Status change fires `workspace_task.status_changed` audit log
- [ ] Wrong transition attempt (e.g. talent trying to approve) → 403 or flash error
- [ ] Comments section shows all public comments for all members
- [ ] Internal comments visible only to admin/manager
- [ ] Add comment form: submitting a public comment creates `workspace_task_comments` row (visibility=public)
- [ ] Internal comment checkbox shown only to admin/manager
- [ ] Talent/client submitting with internal checked → comment saved as public (server override)
- [ ] `workspace_task.comment_added` audit log fires on public comment
- [ ] `workspace_task.internal_comment_added` audit log fires on internal comment

#### Edit Task
- [ ] `/workspaces/{workspace}/tasks/{task}/edit` accessible to admin/manager
- [ ] Form pre-fills all fields from existing task (using old() fallback pattern)
- [ ] Due date input pre-filled in YYYY-MM-DD format
- [ ] Internal notes field shown only to admin/manager
- [ ] Saving updates the task; flash success shown
- [ ] `workspace_task.updated` audit log fires

### Workspace Show — Kanban Board Section
- [ ] `/workspaces/{workspace}` shows "Kanban Board" section with `view_kanban` icon
- [ ] "Open Kanban Board" button (blue, prominent) links to `/workspaces/{workspace}/tasks`
- [ ] "New Task" outline button visible to admin/manager/talent/client only
- [ ] When tasks exist: 4 metric cards visible (Total, Open, Blocked, Awaiting Review) with correct counts
- [ ] Status chips visible with count and color coding, linking to task board
- [ ] Up to 4 open tasks previewed with task code, title, assignee, due date
- [ ] "View all N open tasks on Kanban Board" link shown when open count > 4
- [ ] When no tasks: empty state shown with `view_kanban` icon and create button (if authorized)

### Dashboard Task Counts
- [ ] Super Admin dashboard: Task Overview section shows Total, Open, Blocked, Awaiting Review counts
- [ ] Operations Admin dashboard: same Task Overview section
- [ ] Talent dashboard: "My Tasks" section shows Active Tasks, Blocked, Due Soon (conditional — only if > 0)
- [ ] Line Manager dashboard: Workspace Tasks section shows Open, Submitted Awaiting Review
- [ ] Line Manager dashboard: Task Board quick-action card is now a clickable link (not disabled)
- [ ] Individual Client dashboard: Workspace Tasks section shown if tasks exist
- [ ] Business Client Admin dashboard: Workspace Tasks section shown if tasks exist
- [ ] Business Client Staff dashboard: Workspace Tasks section shown if tasks exist
- [ ] All 7 dashboards: notice updated to "Phase 5 — Task Board" (not Phase 4)

### Audit Log Verification
- [ ] `workspace_task.created` — fires when task created via portal or Filament
- [ ] `workspace_task.updated` — fires when task edited
- [ ] `workspace_task.status_changed` — fires when status changes, context has from/to values
- [ ] `workspace_task.assigned` — fires when assignee changes, context has from/to user IDs
- [ ] `workspace_task.comment_added` — fires when public comment submitted
- [ ] `workspace_task.internal_comment_added` — fires when internal comment submitted
- [ ] `workspace_task.deleted` — fires when task archived (soft deleted)

---

## Phase 5 Fix — Workspace Access for Primary Team Members

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Primary Manager Access

- [ ] Log in as the user set as `primary_manager_id` on a workspace (with no member row)
- [ ] `/workspaces` index shows that workspace in the list
- [ ] `/workspaces/{workspace}` loads (no 403)
- [ ] `/workspaces/{workspace}/tasks` (Kanban board) loads with manager-level drag permissions
- [ ] Can create a new task on the board
- [ ] Can drag tasks between columns (manager role transitions apply)
- [ ] Status action buttons on task detail work correctly

### Primary Talent Access

- [ ] Log in as the user set as `primary_talent_id` on a workspace (with no member row)
- [ ] `/workspaces` index shows that workspace in the list
- [ ] `/workspaces/{workspace}` loads (no 403)
- [ ] `/workspaces/{workspace}/tasks` (Kanban board) loads with talent-level drag permissions
- [ ] Can create a new task
- [ ] Talent-only transitions work (pending → in_progress, in_progress → submitted, etc.)
- [ ] Cannot approve/close tasks (client-only transitions blocked)

### Admin Access

- [ ] Log in as `super_admin` — `/workspaces` shows ALL workspaces (not just member ones)
- [ ] Log in as `operations_admin` — same
- [ ] `/workspaces/{workspace}` for any workspace loads without 403

### Active Member Access (regression check)

- [ ] User with active member row (any role) can still access workspace and task board
- [ ] Observer role: can view board; drag handle NOT shown; cannot create tasks

### Task-Assigned Fallback

- [ ] A user assigned to a task but with no workspace member row can view `/workspaces/{workspace}/tasks/{task}`
- [ ] They cannot access the Kanban index `/workspaces/{workspace}/tasks` (no workspace access = 403)
  - *(Note: `assigned_user` tier gives workspace-level access via `userHasAccess()` — so the index IS accessible to assigned users. Verify intended behaviour.)*
- [ ] Their status buttons use talent-level transitions

### Filament Sync Actions

- [ ] Open Workspace list in Filament `/admin/workspaces`
- [ ] "Sync Team" action is visible on rows where primary_manager_id or primary_talent_id is set
- [ ] Click "Sync Team" → confirm modal → notification appears: "Primary team synced | Added: N · Reactivated: N · Already active: N"
- [ ] After sync: member rows created/reactivated for primary manager and primary talent
- [ ] Audit log `workspace.primary_team_synced` fires with counts in context
- [ ] If member row already exists and is active with correct role: notification shows "Already active: 2" (no duplicate row)

### Auto-Sync on Workspace Save

- [ ] Edit a workspace in Filament, change `primary_manager_id` to a different user, Save
- [ ] New user automatically gets an active member row (role=manager)
- [ ] Previous primary manager member row is NOT automatically removed (manual action required)
- [ ] Audit log `workspace.primary_team_synced` + `workspace.member_added` fires

### Edit-Page Header Action

- [ ] Open Filament workspace edit page
- [ ] "Sync Primary Team" button appears in page header
- [ ] Click → confirm → success notification
- [ ] Member rows created/reactivated as expected

---

## Phase 5 Fix 2 — Task Detail and Kanban Drag-Drop

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Scenario 1: Task detail route (core fix)

- [ ] Login as talent assigned to workspace (primary talent or active member)
- [ ] Kanban board loads: `/workspaces/{id}/tasks`
- [ ] Click any task card
- [ ] Task detail page loads — no 404
- [ ] URL is `/workspaces/{id}/tasks/{task_id}` (integer IDs, not codes)

### Scenario 2: Talent — allowed drag move

- [ ] Login as talent assigned to a task (task assigned to their user account)
- [ ] Drag handle (⋮⋮) is visible on their assigned task cards
- [ ] Drag task from Pending → In Progress
- [ ] Card moves to In Progress column
- [ ] Toast shows "Task moved to "In Progress"."
- [ ] Audit log `workspace_task.status_changed` fires

### Scenario 3: Talent — submit

- [ ] Task in In Progress, assigned to talent
- [ ] Drag from In Progress → Submitted
- [ ] Move succeeds
- [ ] Toast shows success message

### Scenario 4: Talent — invalid move (not allowed transition)

- [ ] Task in Submitted status
- [ ] Talent tries to drag to Approved (not allowed for talent)
- [ ] No drag handle on Approved column drop attempt (JS blocks, or server blocks)
- [ ] Card reverts to Submitted column
- [ ] Toast shows: "This task cannot be moved from "Submitted" to "Approved". Allowed next statuses: ..."

### Scenario 5: Talent — cannot move someone else's task

- [ ] Talent logs in — task on board is assigned to a DIFFERENT user
- [ ] Drag handle is NOT visible on that other user's task card
- [ ] If somehow dragged (via dev tools), backend returns 403: "You can only update tasks assigned to you."
- [ ] Card reverts

### Scenario 6: Manager — broad moves

- [ ] Login as manager (primary manager or member manager)
- [ ] Drag handle visible on ALL task cards
- [ ] Drag Submitted → Approved → success
- [ ] Drag Submitted → Revision Requested → success
- [ ] Drag In Progress → Cancelled → success

### Scenario 7: Client — review flow

- [ ] Login as client member
- [ ] Drag Submitted → Approved → success
- [ ] Drag Submitted → Revision Requested → success
- [ ] Drag Pending → In Progress → fails (not allowed for client)
- [ ] Toast shows descriptive error for invalid move

### Scenario 8: Non-member access

- [ ] Login as user with no workspace access
- [ ] Directly visit `/workspaces/{id}/tasks/{id}` → 403 (not 404)
- [ ] Directly visit `/workspaces/{id}/tasks` → 403

### Scenario 9: Network/non-JSON error handling

- [ ] If server returns non-JSON response (e.g. Cloudflare HTML error), card reverts
- [ ] Toast shows: "The server returned an unexpected response. Please refresh the page and try again."

### Audit Log Verification

- [ ] `workspace_task.status_changed` fires for every successful drag move (check Laravel log / Filament audit)
- [ ] Failed moves log `workspace_task.status_update_denied` in Laravel log with user_id, task_id, reason

---

## Phase 5 Fix 3 — Talent Kanban Drag-Drop Permission Fix

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Scenario 1: Assigned talent — allowed drag (core scenario)

- [ ] Login as talent user who is assigned to a task in the workspace
- [ ] Kanban board loads — drag handles visible on their assigned task
- [ ] Drag task from Pending → In Progress
- [ ] Card lands in In Progress column
- [ ] Green toast: "Task moved to \"In Progress\"."
- [ ] Check Laravel log: `workspace_task.status_update_attempt` entry with `effective_role: talent`, `is_task_assignee: true`

### Scenario 2: Talent — submit task

- [ ] Task in In Progress, assigned to talent
- [ ] Drag In Progress → Submitted
- [ ] Move succeeds, green toast

### Scenario 3: Talent — invalid move blocked with descriptive message

- [ ] Task in Submitted status, assigned to talent
- [ ] Talent drags to Approved column (not allowed for talent)
- [ ] Card reverts to Submitted
- [ ] Red toast shows server message: "This task cannot be moved from \"Submitted\" to \"Approved\". Allowed next statuses: Approved, Revision Requested."
- [ ] Browser console shows `[GVOS Kanban] Drag rejected` with httpStatus 422 and response JSON

### Scenario 4: Primary talent — unassigned task

- [ ] Login as the user set as `primary_talent_id` on the workspace
- [ ] Board has an unassigned task (assigned_to_user_id is null)
- [ ] Drag handle is visible on that unassigned task
- [ ] Drag Pending → In Progress
- [ ] Move succeeds

### Scenario 5: Regular talent — cannot move unassigned task

- [ ] Login as a talent who is a workspace member but NOT the primary_talent_id
- [ ] Board has an unassigned task
- [ ] Drag handle IS visible (unassigned tasks show handle for all talent)
- [ ] Attempt to drag unassigned task to another column
- [ ] Card reverts
- [ ] Red toast: "Only the primary talent can move unassigned tasks."

### Scenario 6: Manager — broad access

- [ ] Login as manager (primary manager or member with role=manager)
- [ ] Drag handles visible on all task cards
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag In Progress → Cancelled → succeeds

### Scenario 7: assigned_user tier — SortableJS enabled

- [ ] If a user is assigned to a task but has NO workspace member row (assigned_user tier)
- [ ] Kanban board loads — SortableJS IS initialized (CAN_DRAG = true)
- [ ] Drag handle IS visible on their specific assigned task
- [ ] Drag Pending → In Progress → succeeds
- [ ] Check Laravel log: `workspace_role: assigned_user`, `effective_role: talent`, `is_task_assignee: true`

### Logging Verification

- [ ] Every drag attempt (success or fail) logs `workspace_task.status_update_attempt` with: user_id, user_email, user_roles, workspace_id, task_id, task_code, task_assigned_to, from_status, requested_status, workspace_role, effective_role, is_task_assignee, is_primary_talent, is_primary_manager, allowed_transitions
- [ ] Every failed drag logs `workspace_task.status_update_denied` with reason field
- [ ] Browser console shows `[GVOS Kanban] Drag rejected` for server-rejected drags with httpStatus and full response JSON

---

## Phase 5 Fix 4 — Workspace Role Expansion

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan view:clear`.

### Scenario 1: workspace_admin role

- [ ] In Filament, open a workspace and add a user as `workspace_admin` in the Members relation manager
- [ ] Log in as that user → Kanban board loads
- [ ] Drag handles visible on ALL task cards
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag Pending → Cancelled → succeeds
- [ ] Drag In Progress → Blocked → succeeds
- [ ] Debug role line under "Kanban Board" heading shows: `Task role: workspace_admin`

### Scenario 2: client_admin role

- [ ] Add a user as `client_admin` in workspace members
- [ ] Log in as that user → Kanban board loads
- [ ] Drag handles visible ONLY on cards in the Submitted and Approved columns
- [ ] No drag handles on Pending, In Progress, Blocked, Revision Req., Closed cards
- [ ] Drag Submitted → Approved → succeeds, green toast
- [ ] Drag Submitted → Revision Requested → succeeds, green toast
- [ ] Drag Approved → Closed → succeeds, green toast
- [ ] Drag Pending → In Progress → card reverts, red toast with 403/permission message
- [ ] Debug role line NOT shown (only shows for admin/workspace_admin/manager)

### Scenario 3: client_staff role

- [ ] Add a user as `client_staff` in workspace members
- [ ] Log in as that user → Kanban board loads
- [ ] NO drag handles visible on any card
- [ ] `CAN_DRAG` is false — SortableJS NOT initialized for this user
- [ ] Attempting status update via API (dev tools) → 403 "Client staff members cannot move task cards."

### Scenario 4: Legacy `client` member row still works

- [ ] A workspace member with old `role=client` (legacy) can still access the board
- [ ] They see drag handles on submitted/approved tasks (same as client_admin)
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag Pending → In Progress → fails with 403

### Scenario 5: Filament role dropdown default

- [ ] Open Filament → Workspace → edit page → Members relation manager
- [ ] Click "Add Member" → Role dropdown defaults to `Talent` (not `Client`)
- [ ] All 7 role options visible: Workspace Admin, Manager, Talent, Client Admin, Client Staff, Client, Observer
- [ ] workspace_admin badge shows in red/danger color
- [ ] client_admin and client_staff badges show in warning/amber color
- [ ] manager badge shows in info/blue color
- [ ] talent badge shows in success/green color

### Scenario 6: Role expansion migration

- [ ] `php artisan migrate` runs without error on a clean DB
- [ ] `workspace_members` table accepts `workspace_admin`, `client_admin`, `client_staff` values
- [ ] Existing rows with old role values (client/talent/manager/observer) are unaffected

### Scenario 7: Debug role line visibility

- [ ] Login as admin → board shows `Task role: admin` under heading
- [ ] Login as workspace_admin → shows `Task role: workspace_admin`
- [ ] Login as manager → shows `Task role: manager`
- [ ] Login as talent → NO debug line shown
- [ ] Login as client_admin → NO debug line shown
- [ ] Login as observer → NO debug line shown

---

---

## Phase 6 — Workspace Chat & Files

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan view:clear`.

### Migrations
- [ ] `php artisan migrate` runs without error
- [ ] `workspace_messages` table exists with all expected columns
- [ ] `workspace_files` table exists with all expected columns
- [ ] `php artisan route:list` shows chat routes (workspace.chat.index, workspace.chat.store, workspace.chat.destroy)
- [ ] `php artisan route:list` shows file routes (workspace.files.index, workspace.files.store, workspace.files.download, workspace.files.destroy)
- [ ] `php artisan route:list` shows task file route (workspace.tasks.files.store)

### Workspace Chat — Manager/Admin (full access)

- [ ] Login as admin or manager. Navigate to a workspace → click "Chat" card
- [ ] `/workspaces/{workspace}/chat` loads without error
- [ ] Breadcrumb shows workspace code + "Chat"
- [ ] Nav links to "Files" and "Task Board" present
- [ ] Empty state shown when no messages exist
- [ ] Post a public message — appears in list with correct name, timestamp, "you" indicator
- [ ] Post an internal message (check "Internal" checkbox) — appears with "Internal" badge
- [ ] Internal badge is visible to admin/manager
- [ ] Delete button appears on own message — clicking soft-deletes it; message removed from view
- [ ] `workspace_message.created` audit log entry fires
- [ ] `workspace_message.deleted` audit log entry fires on delete

### Workspace Chat — Talent (public only)

- [ ] Login as talent assigned to workspace. Navigate to chat page
- [ ] Chat page loads
- [ ] Only public messages shown — no internal messages visible
- [ ] "Internal" checkbox NOT shown on post form
- [ ] Post a message → saved as public (no internal option)
- [ ] Delete button shown only on own messages
- [ ] Cannot delete another user's message

### Workspace Chat — Client (public only)

- [ ] Login as client member. Navigate to chat page
- [ ] Only public messages shown
- [ ] Can post public messages
- [ ] No internal checkbox visible

### Workspace Chat — Observer

- [ ] Login as observer. Navigate to chat page
- [ ] Can view public messages (read-only)
- [ ] Post form IS shown but submitting → 403
- [ ] Or: "Your role allows viewing messages only" notice visible (view-only notice)

### Workspace Files — Manager/Admin (full access)

- [ ] Navigate to `/workspaces/{workspace}/files`
- [ ] Page loads with upload form (left) and file list (right)
- [ ] Upload a PDF file — appears in list with correct filename, category badge, size, uploaded-by
- [ ] Upload with `visibility=internal` — appears with "Internal" visibility badge
- [ ] Download a file — browser prompts file download (not a redirect to a public URL)
- [ ] File is served by the app (URL is `/workspaces/{workspace}/files/{file}/download`)
- [ ] `workspace_file.uploaded` audit log entry fires
- [ ] `workspace_file.downloaded` audit log entry fires; `downloads_count` increments in DB
- [ ] Delete button deletes the file record (soft delete); file disappears from list
- [ ] `workspace_file.deleted` audit log entry fires
- [ ] Physical file remains on disk after soft delete

### Workspace Files — Talent / Client (public files only)

- [ ] Login as talent or client. Navigate to files page
- [ ] Only public files visible — internal files NOT shown
- [ ] Can upload a file (saved as public even if user tries to set internal via POST manipulation)
- [ ] Can download public files
- [ ] Cannot see the "Internal" checkbox on the upload form
- [ ] Can delete own uploads; cannot delete others' files

### Workspace Files — Observer

- [ ] Login as observer. Navigate to files page
- [ ] Can view public file list
- [ ] Upload form NOT visible (or shows 403 on submit)

### Task File Attachments

- [ ] Navigate to a task detail page (`/workspaces/{ws}/tasks/{task}`)
- [ ] Sidebar shows "Task Files" section
- [ ] Upload a file from the task page — file appears in sidebar list with type icon, size, date
- [ ] File is linked to the task (stored with `workspace_task_id`)
- [ ] Download link for task attachment works
- [ ] Admin/manager sees "Internal" checkbox on task attachment upload form
- [ ] Talent/client do not see the Internal checkbox on task attachment upload

### Filament — WorkspaceFileResource

- [ ] `/admin/workspace-files` loads — nav group "Workspace", item "Files" visible (sort 4)
- [ ] Table shows workspace code, filename, title, category, visibility badge, uploaded by, size, downloads count, created at
- [ ] Visibility filter works (Public / Internal)
- [ ] Category filter works
- [ ] Archive action (soft delete) visible — clicking confirms and removes record from list
- [ ] No "New file" / Create button visible (canCreate = false)
- [ ] No edit link on rows (canEdit = false)

### Filament — WorkspaceMessageResource

- [ ] `/admin/workspace-messages` loads — nav group "Workspace", item "Messages" visible (sort 5)
- [ ] Table shows workspace code, author name, message (truncated to 60 chars), visibility badge, message type, created at
- [ ] Visibility filter works
- [ ] Type filter works (Text / System)
- [ ] Remove/Moderate action soft-deletes the message
- [ ] No create button visible

### Workspace Show — Chat & Files Cards

- [ ] Navigate to `/workspaces/{workspace}`
- [ ] "Chat" card visible with message count and link to chat page
- [ ] "Files" card visible with file count and link to files page
- [ ] "Time Tracking", "Billing", "Password Vault" placeholder cards visible with dashed border and opacity-50

### Admin Dashboard

- [ ] Super Admin dashboard: "Chat & Files" section shows Total Messages and Total Files count cards
- [ ] Total Messages card links to `/admin/workspace-messages`
- [ ] Total Files card links to `/admin/workspace-files`
- [ ] Phase notice updated to "Phase 6 — Chat & Files"
- [ ] Operations Admin dashboard: same section and notice

### Other Dashboards

- [ ] Talent dashboard: "Communication" section shows "Chat & Files" link (only if workspaces > 0)
- [ ] Line Manager dashboard: "Communication" section shows "Chat & Files" link (only if managed workspaces exist)
- [ ] Individual Client dashboard: "Workspace Chat" and "Workspace Files" links shown (only if workspaces > 0)
- [ ] Business Client Admin: same chat/files links
- [ ] Business Client Staff: same chat/files links
- [ ] All dashboards: Phase notice updated to "Phase 6"

### Access Control — Edge Cases

- [ ] Non-member accessing `/workspaces/{workspace}/chat` → 403
- [ ] Non-member accessing `/workspaces/{workspace}/files` → 403
- [ ] Downloading an internal file as client → 403
- [ ] Downloading a file for a workspace you're not a member of → 403
- [ ] Uploading a file exceeding 10 MB → validation error shown on form
- [ ] Uploading an unsupported file type (e.g. `.exe`) → validation error shown on form
- [ ] `workspace_file.downloaded` audit fires with correct file and user context

---

## Phase 7 — Time Tracking & Work Reports

### Migrations
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_time_logs` table exists with all columns and indexes
- [ ] `workspace_weekly_reports` table exists with all columns and indexes

### Time Log — Talent / Admin creation
- [ ] Talent can navigate to Workspace → Time Logs → Log Time
- [ ] Create form saves draft log with date, summary, duration
- [ ] Start/end time auto-calculates duration when duration_minutes is blank
- [ ] Talent can submit a draft log (status → submitted)
- [ ] Talent can only see own logs on index page
- [ ] Talent can edit own draft or rejected log; cannot edit submitted/approved
- [ ] Talent can delete own draft log only

### Time Log — Manager review
- [ ] Manager sees all logs on index (all statuses)
- [ ] Manager sees review form on show page (only when status = submitted)
- [ ] Manager can approve, mark reviewed, or reject a log
- [ ] Manager can set visibility = client_summary and write client_visible_summary
- [ ] Manager can write manager_notes (internal)

### Time Log — Client visibility
- [ ] Client index shows only approved + client_summary logs
- [ ] Client sees client_visible_summary, not work_summary
- [ ] Client cannot see manager_notes or work_details
- [ ] Client cannot access create/edit/delete routes (403)
- [ ] Observer gets 403 on time log index and show

### Task show page — time logs sidebar
- [ ] Talent/Manager see time log section in task show sidebar
- [ ] Log Time button pre-selects task via query param
- [ ] Client role does NOT see time logs sidebar
- [ ] Observer does NOT see time logs sidebar

### Weekly reports — creation and editing
- [ ] Manager creates a weekly report with suggested week dates
- [ ] total_minutes auto-filled from approved logs for that week
- [ ] Manager can save as draft or submit
- [ ] Manager can approve a submitted report (status → approved)
- [ ] Manager can publish an approved report (published_at populated)
- [ ] Manager cannot edit approved or published report (403)

### Weekly reports — visibility
- [ ] Client sees only published reports
- [ ] Talent sees submitted/approved/published (not draft)
- [ ] Manager/Admin sees all statuses
- [ ] Blockers/next_steps hidden from client on show page
- [ ] Observer gets 403 on reports index

### Workspace show page
- [ ] Time Logs active card links to time-logs.index with correct count
- [ ] Weekly Reports active card links to reports.index with correct count
- [ ] Client counts are filtered (approved+client_summary / published)
- [ ] Billing and Password Vault remain as dashed placeholder cards
- [ ] Old Time Tracking placeholder is removed

### Filament resources
- [ ] WorkspaceTimeLogResource appears under Workspace, sort 7
- [ ] WorkspaceWeeklyReportResource appears under Workspace, sort 8
- [ ] Status badge colours correct (approved=success, rejected=danger, etc.)
- [ ] Create buttons absent on both resources

### Dashboard Phase 7 notices
- [ ] All 7 portal dashboards show "Phase 7 — Time Tracking & Work Reports" notice

### Access control edge cases
- [ ] No workspace membership → 403 on all time log and report routes
- [ ] Talent cannot call review route (403)
- [ ] Client cannot call create/store/edit/update on logs or reports (403)

---

## Phase 8 — Billing Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `billing_plans` table exists with currency, amount, billing_cycle, status, and soft deletes
- [ ] `workspace_subscriptions` table exists with workspace, plan, billing dates, status, and grace fields
- [ ] `invoices` table exists with `invoice_number`, totals, payment fields, status, and soft deletes
- [ ] `invoice_items` table exists and links to invoices
- [ ] `payments` table exists with provider, provider_reference, status, confirmation fields, raw_payload, and soft deletes

### Filament Billing Resources
- [ ] `/admin/billing-plans` loads under Billing navigation
- [ ] Admin creates a billing plan with `bi_weekly` cycle
- [ ] Admin archives a billing plan; status changes to `archived`
- [ ] `/admin/workspace-subscriptions` loads
- [ ] Admin creates a subscription for a workspace and optional plan
- [ ] Subscription status, next billing date, and grace period save correctly
- [ ] `/admin/invoices` loads
- [ ] Admin creates an invoice with at least one line item
- [ ] Admin invoice create form shows sections in this order: Invoice Identity, Invoice Items, Totals and Payment Summary, Notes
- [ ] Admin invoice edit form uses the same section order as create
- [ ] Totals and payment fields appear below the invoice items repeater
- [ ] Totals section helper text says totals are calculated from invoice items and payment records where available
- [ ] Discount amount and tax amount remain editable
- [ ] Existing manual total behavior is preserved for invoices without line items
- [ ] Invoice number auto-generates as `GVOS-INV-YYYYMM-0001`
- [ ] Invoice totals and balance due recalculate from line items
- [ ] Issue invoice action changes status from `draft` to `issued`
- [ ] Mark Paid action marks invoice paid, sets `paid_at`, and clears balance
- [ ] Cancel action cancels draft/issued invoices
- [ ] `/admin/payments` loads
- [ ] Admin records a manual payment linked to an invoice
- [ ] Confirm payment action marks payment confirmed
- [ ] Confirmed payment increments invoice `amount_paid`
- [ ] Partial payment changes invoice to `partially_paid`
- [ ] Full payment changes invoice to `paid`, sets `paid_at`, and clears balance
- [ ] Linked subscription receives `last_paid_at`; payment_due/overdue/suspended returns to active

### Portal Billing Views
- [ ] Workspace detail page shows active Billing card for admin/manager/client roles
- [ ] Billing card shows subscription status if present
- [ ] Billing card shows next billing date if present
- [ ] Billing card shows outstanding balance from issued/partial/overdue invoices
- [ ] `/workspaces/{workspace}/billing` shows subscription summary, invoices, payments, and payment instructions placeholder
- [ ] `/workspaces/{workspace}/billing/invoices/{invoice}` shows invoice items, totals, notes, and payment history
- [ ] Invoice detail page header shows GVOS Invoice, invoice number, status badge, Back to Billing button, and Print button
- [ ] Invoice detail page shows bill-to/client details, workspace name, company when available, issue date, due date, subscription/billing cycle, and currency
- [ ] Invoice items table shows description, item type, quantity, unit amount, and total amount
- [ ] Totals section sits directly below the invoice items table
- [ ] Totals display in this order: subtotal, discount, tax, total amount, amount paid, balance due
- [ ] Total amount is emphasized and balance due is visually clear
- [ ] Money values are right-aligned on desktop and remain readable on mobile
- [ ] Payment history table shows payment reference, provider, amount, status, and paid-at date
- [ ] Print button calls `window.print()` and does not generate a PDF
- [ ] `/workspaces/{workspace}/billing/payments` shows payment history and empty state when none exist

### Access Control
- [ ] Client admin can view billing for their own workspace
- [ ] Individual client can view billing for their own workspace
- [ ] Business client staff can view billing if they have workspace access
- [ ] Client cannot see invoice `internal_notes`
- [ ] Client cannot see internal payment confirmation notes
- [ ] Talent receives 403 on billing routes
- [ ] Observer receives 403 on billing routes
- [ ] Non-member receives 403 on billing routes
- [ ] Invoice detail route rejects invoices from another workspace using integer FK comparison

### Dashboard Updates
- [ ] Super Admin dashboard shows total invoices, outstanding invoices, paid invoices, and confirmed payments total
- [ ] Operations Admin dashboard shows outstanding billing action item
- [ ] Individual Client dashboard shows billing quick link and outstanding balance
- [ ] Business Client Admin dashboard shows billing quick link and outstanding balance
- [ ] Talent dashboard does not show billing

### Audit Log Verification
- [ ] `billing_plan.created` fires on plan creation
- [ ] `billing_plan.updated` fires on plan edit/archive
- [ ] `workspace_subscription.created` fires on subscription creation
- [ ] `workspace_subscription.updated` fires on subscription edit
- [ ] `invoice.created`, `invoice.updated`, `invoice.issued`, `invoice.cancelled`, `invoice.marked_paid` fire as expected
- [ ] `payment.recorded`, `payment.confirmed`, `payment.failed_or_cancelled` fire as expected

### Regression Checks
- [ ] Existing workspace tasks still load
- [ ] Existing workspace chat still loads
- [ ] Existing workspace files still load
- [ ] Existing time logs still load
- [ ] Existing weekly reports still load
- [ ] No live gateway payment button appears anywhere
- [ ] No payroll UI appears anywhere
- [ ] Password vault exists only as the Phase 10 vault module after Phase 10 deployment; billing flows do not depend on it
- [ ] No visible UI contains `GetVirtual`

---

## Phase 9 — Semi Automated Time Tracking

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Routes
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_time_logs.status` accepts `running`
- [ ] `php artisan route:list` shows `time-tracker.current`
- [ ] `php artisan route:list` shows `workspace.time-tracker.start`
- [ ] `php artisan route:list` shows `workspace.time-tracker.stop`
- [ ] `php artisan route:list` shows `workspace.time-tracker.complete`

### Talent Timer Flow
- [ ] Talent dashboard shows Clock In when no timer is running
- [ ] Talent can select a workspace and optional task before starting
- [ ] Clock In creates a running time log with `started_at`, `status=running`, `ended_at=null`
- [ ] Dashboard shows a live elapsed timer after start
- [ ] Browser refresh still shows the running timer
- [ ] Clock Out stops the timer, sets `ended_at`, calculates `duration_minutes`, and saves status `draft`
- [ ] Complete Work Session requires a work summary and saves status `submitted`
- [ ] User cannot start a second running timer while one already exists

### Workspace Time Log Pages
- [ ] Workspace time logs index shows active timer controls above the table
- [ ] Workspace time logs index shows totals/table below the timer controls
- [ ] Time log detail page shows running session state for a running log
- [ ] Authorized users can stop or complete a running log from the detail page
- [ ] Running logs cannot be edited manually
- [ ] Running logs cannot be reviewed
- [ ] Running logs cannot be deleted

### Task Detail Integration
- [ ] Task detail page shows Start Timer for active task statuses
- [ ] Starting from a task links `workspace_task_id` to the created running log
- [ ] If the current user has a timer running elsewhere, task page links to the active timer instead of starting another

### Manager/Admin Visibility
- [ ] Manager/admin time log index shows running timers for the workspace
- [ ] Manager/admin can open a running timer record
- [ ] Manager/admin can stop or complete another user's running timer when needed
- [ ] Filament Workspace Time Logs table shows running status badge
- [ ] Filament Workspace Time Logs table shows started timestamp and duration display

### Client Protection
- [ ] Client cannot start, stop, or complete timers
- [ ] Client cannot see running time logs
- [ ] Client time log views still show only approved `client_summary` logs
- [ ] Client cannot see work_details, manager_notes, or internal summaries

### Audit and Regression
- [ ] `workspace_time_tracker.started` audit entry fires on Clock In
- [ ] `workspace_time_tracker.stopped` audit entry fires on Clock Out
- [ ] `workspace_time_tracker.completed` audit entry fires on Complete Work Session
- [ ] Existing manual time log create/edit/review still works
- [ ] Existing weekly reports still work
- [ ] Existing billing invoice/payment confirmation flows still work
- [ ] No billing database or payment logic changes are present
- [ ] No screenshots, keystrokes, screen monitoring, payroll, or billing automation appears; password vault appears only as the Phase 10 module after Phase 10 deployment
- [ ] No visible UI contains `GetVirtual`

---

## Phase 10 — Password Vault Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Models
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_vault_items` table exists with encrypted secret storage fields and soft deletes
- [ ] `workspace_vault_access_logs` table exists with action, IP, user agent, metadata, and created_at
- [ ] `WorkspaceVaultItem.secret_value` is encrypted at rest and not visible as plaintext in DB rows
- [ ] `WorkspaceVaultItem` array/JSON output does not include `secret_value`

### Portal Vault Routes
- [ ] `php artisan route:list` shows `workspace.vault.index`
- [ ] `php artisan route:list` shows `workspace.vault.create`
- [ ] `php artisan route:list` shows `workspace.vault.store`
- [ ] `php artisan route:list` shows `workspace.vault.show`
- [ ] `php artisan route:list` shows `workspace.vault.edit`
- [ ] `php artisan route:list` shows `workspace.vault.update`
- [ ] `php artisan route:list` shows `workspace.vault.reveal`
- [ ] `php artisan route:list` shows `workspace.vault.archive`
- [ ] `php artisan route:list` shows `workspace.vault.access-logs`

### Portal Vault UX
- [ ] Workspace detail page shows Password Vault card only when the user can create vault items or has visible assigned items
- [ ] Vault index shows metadata only: title, category, username, URL, visibility, status, last revealed
- [ ] Vault index does not show plaintext secret values
- [ ] Authorized creator/admin/manager/client admin can create a vault item
- [ ] Edit form does not prefill the existing secret
- [ ] Leaving secret blank on edit preserves the current encrypted secret
- [ ] Entering a new secret on edit rotates the stored secret
- [ ] Archive action changes status to `archived`; it does not hard-delete the record
- [ ] Secret detail page hides the secret by default
- [ ] Reveal button returns and displays the secret only for allowed users
- [ ] Copy button copies the secret only for allowed users
- [ ] Reveal and copy actions create `workspace_vault_access_logs` rows
- [ ] Access logs page shows metadata only and never shows plaintext secrets

### Access Control
- [ ] Super Admin / Operations Admin can view and manage all vault items through portal access as `admin`
- [ ] Workspace Admin can manage workspace vault items
- [ ] Manager can manage vault items and reveal only workspace-admin-visible, own-created, or explicitly assigned items
- [ ] Client Admin can create items and manage/reveal own-created or explicitly assigned items
- [ ] Client Staff cannot create, edit, archive, or view logs
- [ ] Talent and assigned users can only view/reveal explicitly assigned items
- [ ] Observer receives 403 on vault routes
- [ ] Non-member receives 403 on vault routes
- [ ] Allowed user assignment rejects users outside the workspace member/primary team/task-assignee set

### Filament Vault Resources
- [ ] `/admin/workspace-vault-items` loads under Workspace navigation
- [ ] Admin can create a vault item in Filament
- [ ] Filament vault item table does not show `secret_value`
- [ ] Filament edit form does not prefill the existing secret
- [ ] Blank secret on edit preserves the current value
- [ ] Filament Archive action changes status to `archived`
- [ ] Filament Restore action changes status to `active`
- [ ] `/admin/workspace-vault-access-logs` loads as read-only
- [ ] Filament access log table shows item, workspace, user, action, IP, and timestamp only
- [ ] No delete action appears on vault items or access logs

### Audit and Regression
- [ ] `workspace_vault_item.created` fires on vault item creation
- [ ] `workspace_vault_item.updated` fires on vault item update
- [ ] `workspace_vault_item.archived` fires on archive
- [ ] `workspace_vault_item.restored` fires on restore
- [ ] `workspace_vault_item.secret_revealed` fires on reveal/copy
- [ ] `workspace_vault_item.access_logs_viewed` fires when logs are viewed
- [ ] Audit log context does not include plaintext secrets
- [ ] Existing workspace tasks still load
- [ ] Existing workspace chat still loads
- [ ] Existing workspace files still load
- [ ] Existing time logs and timer controls still load
- [ ] Existing weekly reports still load
- [ ] Existing billing invoice/payment confirmation flows still work
- [ ] No payment gateway button, payroll UI, auto-login, browser extension, screenshot capture, keystroke capture, or screen-monitoring UI appears
- [ ] No visible UI contains `GetVirtual`

---

## Phase 11 - Notifications and Email System Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Routes
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `notifications` table exists with UUID id, type, notifiable morphs, data, read_at, timestamps
- [ ] `user_notification_preferences` table exists with user_id, notification_key, in_app_enabled, email_enabled
- [ ] `php artisan route:list` shows `notifications.index`
- [ ] `php artisan route:list` shows `notifications.read`
- [ ] `php artisan route:list` shows `notifications.read-all`
- [ ] `php artisan route:list` shows `settings.notifications`
- [ ] `php artisan route:list` shows `settings.notifications.update`

### Portal Notification UI
- [ ] Header notification bell links to `/notifications`
- [ ] Header notification bell shows unread count when unread notifications exist
- [ ] `/notifications` loads for authenticated users
- [ ] Notifications show unread first, title, message, date, action button, and mark-read action
- [ ] Mark one notification as read works
- [ ] Mark all as read works
- [ ] Empty notification state displays when no notifications exist
- [ ] User cannot mark another user's notification as read

### Preference UI
- [ ] `/settings/notifications` loads
- [ ] User can toggle in-app and email preferences for all 10 notification keys
- [ ] Saving preferences creates or updates `user_notification_preferences` rows
- [ ] `notification_preferences.updated` audit event fires
- [ ] Chat/message, task comment, task status, and file upload email are disabled by default
- [ ] Important notifications have email enabled by default when no preference row exists

### Trigger Tests
- [ ] Creating or assigning a task creates a notification for the assigned user
- [ ] Changing task status creates notifications for relevant recipients
- [ ] Adding a task comment creates a notification without including comment body in payload
- [ ] Uploading a file creates a notification without exposing raw storage path
- [ ] Posting a workspace chat message creates in-app notifications but does not spam email by default
- [ ] Submitting a time log notifies manager/workspace admin and does not notify clients
- [ ] Completing a timer as submitted notifies manager/workspace admin
- [ ] Publishing a weekly report notifies client-side workspace users
- [ ] Issuing an invoice notifies client-side workspace users
- [ ] Recording or confirming a payment notifies client-side users and relevant non-actor admins
- [ ] Approving a trial notifies the active lead user

### Safety and Regression
- [ ] Notification payloads do not contain vault secrets
- [ ] Notification payloads do not contain payment raw_payload
- [ ] Notification payloads do not contain internal invoice notes or manager notes
- [ ] Notification payloads do not contain raw file paths
- [ ] Existing tasks, chat, files, time logs, reports, billing, and vault routes still work
- [ ] Email notifications do not break the app when mail is not configured
- [ ] No payment gateway, payroll, real-time websocket chat, screenshot capture, keystroke capture, screen monitoring, auto-login, or browser extension UI appears
- [ ] No visible UI contains `GetVirtual`

## Phase 12 — Launch Readiness (planned)

## Phase 12 - Stabilization, QA, Access Audit and Bug Fix Pass

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Required Artisan Checks
- [ ] `php artisan migrate` runs without error
- [ ] `php artisan optimize:clear` runs without error
- [ ] `php artisan permission:cache-reset` runs without error
- [ ] `php artisan route:list` loads without route errors
- [ ] `php artisan route:list --name=vault` shows vault routes
- [ ] `php artisan route:list --name=notification` shows notification routes
- [ ] `php artisan route:list --name=time-tracker` shows timer routes
- [ ] `php artisan route:list --name=billing` shows billing routes

### Access and Permission Regression
- [ ] Portal task create rejects an assignee who is not an active workspace member or primary team user
- [ ] Portal task edit rejects changing assignee to a non-workspace user
- [ ] Existing valid task assignment to active workspace members still saves
- [ ] Talent cannot access workspace billing routes
- [ ] Clients cannot see running timers, internal time details, manager notes, invoice internal notes, or payment confirmation notes
- [ ] Non-members receive 403/404 on nested workspace routes
- [ ] Users can mark only their own notifications read
- [ ] Vault reveal is POST-only, workspace-scoped, permission-checked, and logs metadata only

### Module Regression
- [ ] Admin invoice create/edit form still shows totals below invoice items
- [ ] Invoice issue action still sends safe notifications
- [ ] Payment confirmation still updates invoice `amount_paid`, `balance_due`, and status
- [ ] One running timer per user is still enforced
- [ ] Completing a timer still requires `work_summary`
- [ ] Filament Time Logs table loads and has no view/edit/delete actions
- [ ] File library loads with pagination and downloads still work
- [ ] Chat loads the latest messages in readable order
- [ ] Notification mark-all-read marks the current user's unread notifications only

### Branding and Safety
- [ ] No visible UI contains `GetVirtual`
- [ ] No visible payment gateway, payroll, browser extension, auto-login, screenshot capture, keystroke tracking, or screen-monitoring UI appears
- [ ] Notification payloads do not contain vault secrets, raw file paths, payment raw payloads, internal invoice notes, manager notes, tokens, or API keys
- [ ] No new database migrations were added for Phase 12

---

## Phase 13 - Workspace Membership and Invitation Flow

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Required Artisan Checks
- [ ] `php artisan migrate` runs without error and creates `workspace_invitations`
- [ ] `php artisan optimize:clear` runs without error
- [ ] `php artisan permission:cache-reset` runs without error
- [ ] `php artisan route:list | grep members` shows workspace member routes
- [ ] `php artisan route:list | grep invitation` shows invitation review/accept routes

### Portal Member Management
- [ ] Admin can open `/workspaces/{workspace}/members`
- [ ] Workspace admin can open the member page for their workspace
- [ ] Manager can view members but cannot add, update, remove, invite, resend, or revoke
- [ ] Client admin can add/invite `client_staff` only
- [ ] Client staff can view only
- [ ] Talent can view the team list only
- [ ] Observer/non-member receives 403
- [ ] Admin can add an existing workspace member with role `manager`, `talent`, `client_admin`, `client_staff`, `workspace_admin`, or `observer`
- [ ] Duplicate active member add is rejected
- [ ] Removing a member sets `workspace_members.status=removed` and does not delete the user
- [ ] Updating a workspace role fires `workspace_member.role_changed`

### Invitation Flow
- [ ] Admin/workspace admin can create pending invitations
- [ ] Client admin can invite client staff only
- [ ] Invitation email failure does not break the portal action
- [ ] Existing invited users receive a database notification without token payload
- [ ] Pending invitations can be resent and revoked
- [ ] Public `/invitations/{token}` page shows invitation status
- [ ] Unauthenticated invitee is instructed to sign in or contact an admin if no account exists
- [ ] Authenticated user with matching email can accept invitation
- [ ] Authenticated user with different email cannot accept invitation
- [ ] Accepted invitation creates or reactivates an active workspace member row
- [ ] Accepted/revoked/expired invitations cannot be accepted again

### Filament
- [ ] Workspace edit page shows Members relation manager
- [ ] Workspace edit page shows Invitations relation manager
- [ ] Filament member removal remains a status change, not hard delete
- [ ] Filament invitation resend/revoke works for pending invitations

### Audit, Notifications, and Regression
- [ ] `workspace_member.added` fires on add/accept
- [ ] `workspace_member.role_changed` fires on role update
- [ ] `workspace_member.deactivated` fires on removal
- [ ] `workspace_invitation.created`, `.resent`, `.revoked`, `.accepted` fire as expected
- [ ] Audit logs do not include invitation tokens
- [ ] Workspace overview team card shows active, manager, talent, and client team counts
- [ ] Existing tasks, chat, files, billing, time logs, reports, vault, and notifications still work
- [ ] Existing invoice issue and payment confirmation flows still work
- [ ] No billing calculation, payment confirmation, invoice status, vault encryption, or timer core changes are present
- [ ] No visible UI contains `GetVirtual`

---

## Phase 14 - Invitation Account Activation and Onboarding

Run after `git pull origin main && php artisan optimize:clear && php artisan permission:cache-reset`.
No new migrations in Phase 14.

### Required Artisan Checks
- [ ] `php artisan optimize:clear` runs without error
- [ ] `php artisan permission:cache-reset` runs without error
- [ ] `php artisan route:list | grep invitation` shows `workspace.invitations.show`, `workspace.invitations.accept`, `workspace.invitations.register`

### New User (Scenario 4 — No Account)
- [ ] Invite a brand-new email that has no GVOS account
- [ ] Open invitation link — account setup form appears
- [ ] Email field is locked and cannot be changed
- [ ] First name, last name, and password fields are required
- [ ] Phone and timezone fields are optional
- [ ] Submit with valid data — user created, logged in, redirected to workspace
- [ ] User becomes active workspace member with the invited workspace role
- [ ] Invitation status becomes `accepted`
- [ ] `accepted_at` and `accepted_by` are set on the invitation record
- [ ] Duplicate acceptance of the same invitation is blocked
- [ ] Correct role-specific profile stub created (TalentProfile / ManagerProfile / ClientProfile)
- [ ] Correct platform role assigned based on workspace_role

### Existing Account Not Logged In (Scenario 3)
- [ ] Invite an email that already has a GVOS account
- [ ] Open invitation link without being signed in
- [ ] Page shows "A GVOS account already exists" and a Sign In button — no registration form

### Existing Account Logged In — Wrong Email (Scenario 2)
- [ ] Sign in as a different user, then open invitation link
- [ ] Page shows wrong-account warning with both email addresses
- [ ] Sign Out button shown; POST accept blocked by controller check

### Existing Account Logged In — Matching Email (Scenario 1)
- [ ] Sign in with the invited email, then open invitation link
- [ ] Accept button creates workspace membership
- [ ] Duplicate active membership is blocked

### Terminal States
- [ ] Accepted invitation shows correct state and optional workspace link
- [ ] Revoked/expired invitations show appropriate message, no action button

### Role Safety
- [ ] No path through invitation can create super_admin or operations_admin account
- [ ] Client admin invitation enforces workspace_role = client_staff only
- [ ] workspace_admin workspace role maps to line_manager platform role

### Audit
- [ ] `workspace_invitation.registered_and_accepted` fires on new-user path
- [ ] `workspace_invitation.accepted` fires on existing-user path
- [ ] `user.created` fires on new-user path
- [ ] No audit log entry contains token or password

### Regression
- [ ] Existing tasks, billing, payment confirmation, vault, timer, and notifications still work
- [ ] No billing calculation, payment confirmation, invoice status, vault encryption, or timer core changes
- [ ] No visible UI contains `GetVirtual`
