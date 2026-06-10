# GVOS — Known Issues

## Format
Each issue: Date | Phase | Severity | Description | Status | Resolution

Severity levels: Critical | High | Medium | Low | Info

---

## Open Issues

### 2026-05-31 | UI Alignment | Medium | UI has drifted from Stitch source of truth

**Description:** Multiple portal screens have drifted from the Stitch UI export in layout, component composition, and page structure. Key deviations:
1. Login page is single-column; Stitch uses 2-col split-screen layout.
2. All 7 dashboards show "Phase X — Feature" banners not present in any Stitch screen.
3. Workspace show page is a static card grid; Stitch `workspace_monitoring_gvos` is a rich monitoring screen.
4. Breadcrumbs used on most pages; Stitch uses page headers without breadcrumbs.
5. Some legacy React/Inertia files remain visually older than the Blade Stitch-aligned views.

**Impact:** Visual and UX inconsistency between built product and design intent.

**Status:** Partially resolved. Header Clock In, sidebar shell, and Phase 9 timer UI are now implemented. Remaining drift is documented in `docs/UI_CORRECTION_PLAN.md`.

**Resolution plan:** Work through correction batches 1–6 as described in UI_CORRECTION_PLAN.md.

---

## Phase 20 Warnings / Notes

### Phase 20 | Info | 10 MB limit applies to mp4/mov — practical restriction for video attachments
The global file size limit is 10 MB (`max:10240` in KB). MP4 and MOV files are now in the allowed list per spec, but 10 MB is restrictive for video (1 min 720p ≈ 50–100 MB). For now this is intentional — GVOS is a workspace management platform, not a video host. If video needs grow, increase the limit in `handleUpload()` and update the upload form hint.

---

### Phase 20 | Info | Physical files are never auto-deleted on soft-delete
Soft-deleting a `WorkspaceFile` record removes it from the UI and makes it inaccessible via model binding, but the physical file remains at `storage_path('app/private/workspaces/{id}/{uuid}.ext')`. This is intentional (preserves files for potential admin restore). A future scheduled cleanup command should prune orphaned physical files where `deleted_at` is older than the retention window.

---

### Phase 20 | Info | `gif` removed from allowed MIME types
Previously `gif` was in `allowedMimes()`. Removed in Phase 20 to align with the MVP spec. Any existing GIF files already uploaded are unaffected (they remain on disk and in DB). New GIF uploads will be rejected with a validation error. If GIF support is needed, add `gif` back to `allowedMimes()`.

---

### Phase 20 | Info | `storage:link` is for public disk only — not needed for private files
`php artisan storage:link` creates `public/storage` → `storage_path('app/public')`. GVOS workspace files are on the `local` disk (`storage_path('app/private')`), which is **not** symlinked and should never be. The symlink is only needed if public assets (e.g. user avatars) are stored on the public disk. Run `php artisan gvos:storage-check` to verify the symlink is correct on cPanel.

---

## Phase 19 Warnings / Notes

### Phase 19 | Info | `payment_due` is an intermediate state — banner now visible
The `payment_due` status exists between `active/trial` and `overdue`. The artisan command marks a subscription `payment_due` when the billing date passes with unpaid invoices; it does not immediately advance to `overdue` on the same run (that happens on the next run once `payment_due` is confirmed). Phase 18 had a gap where `payment_due` subscriptions showed no billing banner because none of `isOverdue()`, `isDueSoon()`, `isRestricted()`, `isSuspended()` matched. Fixed in Phase 19: `billing-banner.blade.php` now checks `isPaymentDue()` explicitly and maps it to the `overdue` banner state.

---

### Phase 19 | Info | Step 3 notification semantic fix
The billing refresh command Step 3 (marking `active/trial → payment_due`) previously sent a `notifyBillingDueSoon` notification, which would reference the billing date as if it were upcoming when it had already passed. Fixed in Phase 19 to send `notifyBillingOverdue` — semantically correct because the billing date has elapsed.

---

## Phase 18 Warnings / Notes

### Phase 18 | Info | Migration must run before enforcement middleware is effective
`2026_06_10_000004_add_billing_enforcement_fields_to_workspace_subscriptions.php` adds 6 columns used by `isRestricted()`, `isSuspended()`, `wasManuallySuspended()`. Until the migration runs, all enforcement checks return `false` (no restriction). Run `php artisan migrate` immediately after deployment.

---

### Phase 18 | Info | `check.billing` middleware: no subscription = no restriction
If a workspace has no `activeSubscription`, `isRestricted()` and `isSuspended()` both return `false` and all clients pass through. This is intentional — unsubscribed/trial workspaces without a subscription record are not gated.

---

### Phase 18 | Info | `gvos:billing-refresh-statuses` sends duplicate notifications on repeated runs
The command is idempotent for **status changes** (it skips subscriptions already in the correct state). However, `isDueSoon()` can re-fire a `notifyBillingDueSoon` on each run if the subscription is in `active`/`trial` and approaching `next_billing_date`. For production, run the command once daily via the scheduler and wrap `isDueSoon` re-notifications with a "last notified at" guard if needed in a future phase.

---

### Phase 18 | Info | Manual suspension requires admin reactivation — not self-service
A workspace suspended via the Filament `Suspend` action (`suspended_by IS NOT NULL`) can only be restored by a super_admin or operations_admin via the `Reactivate` action. `Payment::confirm()` will NOT auto-restore it. Admins must be instructed to always use `Reactivate` after confirming payment for suspended workspaces.

---

## Phase 17 Warnings / Notes

### Phase 17 | Info | Migration must run before using Generate Report feature
`add_generation_fields_to_workspace_weekly_reports_table` adds `generated_at` and `generated_by_user_id`. Both columns are nullable so existing reports are unaffected. The generate form and service will work correctly only after running `php artisan migrate`. Without the migration, `generateStore()` will fail when attempting to fill `generated_at`.

---

### Phase 17 | Info | `WeeklyReportGeneratorService` uses `updated_at` as fallback for task completion date
Completed tasks are matched to the date range using `approved_at OR closed_at OR updated_at`. If a task was approved/closed before Phase 17 was deployed and neither `approved_at` nor `closed_at` is populated, `updated_at` is used as the fallback. This may include tasks updated (not completed) within the range in edge cases. Review generated drafts before publishing.

---

### Phase 17 | Info | `notifyWeeklyReportGenerated` sends to all workspace internal members
`NotificationService::workspaceInternalRecipients()` returns all managers and admins in the workspace. If a workspace has multiple managers, all will receive the draft-generated notification. This is intentional — all managers can see and edit reports.

---

## Phase 16 Warnings / Notes

### Phase 16 | Info | Onboarding migration must run before invitation acceptance
The `add_onboarding_fields_to_user_profiles_table` migration adds `onboarding_completed_at` and `last_onboarding_step` columns. These are nullable so the app will not crash if the migration has not run yet, but `OnboardingController::completeStep()` and `ProfileController::update()` will silently not set those fields. Run `php artisan migrate` after deploy.

---

### Phase 16 | Info | `primaryWorkspace()` depends on workspace membership data
`User::primaryWorkspace()` checks active workspace memberships first, then primary_manager_id/primary_talent_id. For a brand new user with no workspace yet, this returns null. The onboarding page handles null workspace gracefully (shows an empty card with a waiting message).

---

## Phase 15 Warnings / Notes

### Phase 15 | Info | email_delivery_logs migration must be run manually on cPanel

The `email_delivery_logs` table requires `php artisan migrate` after deploying. Until the migration runs, `NotificationService` mail delivery calls will throw a database error. **Mitigation:** deploy code and run migrate before testing any mail-enabled notification flows on staging/production.

---

### Phase 15 | Info | Mail delivery log does not capture database channel events

The `EmailDeliveryLog` is only written for the `mail` channel. Database notifications are not logged in this table — they are tracked via the standard Laravel `notifications` table. This is intentional to avoid double-logging.

---

### Phase 15 | Info | Vendor mail theme requires `php artisan view:clear` after deployment

Switching from the default Laravel mail theme to the `gvos` custom theme requires clearing the compiled view cache. Run `php artisan view:clear` after pull if invitation emails still render without GVOS branding.

---

## Phase 14 Warnings / Notes

### Phase 14 | Info | Login redirect after sign-in for Scenario 3 requires manual return

When an invited user is not logged in but has an existing account (Scenario 3), they are shown a "Sign In" button. The login page does not automatically redirect back to the invitation URL after authentication. The user must return to the original invitation link manually after signing in. A future improvement could pass the invitation URL as a `?redirect=` query param and handle it in the login controller.

---

### Phase 14 | Info | No email verification for invitation-registered users

Users created through the invitation registration path receive an `active` status immediately and are logged in without email verification. This is intentional for smooth onboarding. If email verification is required in future, the registration flow will need to set `status=pending` and dispatch a verification email before logging in.

---

## Phase 6 Warnings / Notes

### Phase 6 | Info | Chat has no real-time updates — page reload required

Workspace chat uses Blade + standard form submission only. Messages do not appear in real-time; the user must reload the page to see new messages posted by others. Real-time chat (WebSockets / Pusher / Laravel Reverb) is explicitly deferred to a later phase.

---

### Phase 6 | Info | Files stored in local disk — not publicly accessible

All uploaded workspace files are stored at `storage/app/workspaces/{workspace_id}/{uuid}.{ext}` via `Storage::disk('local')`. They are NOT accessible via a public URL. Downloads are streamed through `WorkspaceFileController@download` which verifies access before serving. This means files are not accessible even if someone guesses the storage path.

---

### Phase 6 | Low | Physical files are NOT deleted on soft delete

When a workspace file is soft-deleted, the DB record is marked with `deleted_at` but the physical file on disk is preserved. This is intentional — soft deletes allow restoration. A future maintenance command (`php artisan gvos:cleanup-orphaned-files`) should be written to purge physical files whose DB records have been soft-deleted beyond a retention window.

---

### Phase 6 | Low | File download route increments downloads_count regardless of file size

`WorkspaceFileController@download` calls `$file->increment('downloads_count')` before streaming. If the user cancels the download mid-stream, the count is still incremented. This is an accepted limitation for Phase 6 — accurate download completion tracking would require a more complex streaming approach.

---

### Phase 6 | Info | Chat messages limited to 5000 characters at UI layer (not DB layer)

The `message` column is `longText` (no DB constraint). The 5000-character limit is enforced in the `WorkspaceMessageController@store` validation rule (`max:5000`). If messages are inserted directly into the DB, there is no length constraint at the DB level. This is intentional for operational flexibility.

---

### Phase 6 | Low | Upload form visible to all authorized roles even when workspace file limit is not enforced

`WorkspaceFile::allowedMimes()` and `max:10240` validation enforce per-file limits. The `workspaces.file_limit_mb` column exists but is NOT currently enforced in Phase 6. A workspace can accumulate files beyond its configured limit. File quota enforcement is a future improvement.

---

## Phase 7 Warnings / Notes

### Phase 7 | Info | Time logging is fully manual — no automated time capture

All time logging is manual and self-reported by the talent. There is no automated screenshot capture, keystroke tracking, screen-time monitoring, or any surveillance mechanism. This is intentional by design for Phase 7. The `duration_minutes` field can be entered directly or auto-calculated from start/end times only when the user provides them.

---

### Phase 7 | Info | Duration auto-calculation is based on same-day times only

`resolvedDurationMinutes()` calculates the diff between `started_at` and `ended_at`. If a work session crosses midnight (e.g. 23:00 → 01:00), the controller stores both as datetimes on the same `log_date`. No midnight-crossing validation is applied in Phase 7 — the talent is expected to log cross-midnight sessions as two separate logs.

---

### Phase 7 | Info | Duration is stored in minutes — no sub-minute precision

The `duration_minutes` column is an integer. Sub-minute precision is not captured. All inputs are rounded to whole minutes.

---

### Phase 7 | Low | total_minutes on weekly reports is not auto-updated when time logs change

When a weekly report is created, `total_minutes` is auto-suggested from approved time logs in the date range. However, if additional time logs are approved after the report is saved, the `total_minutes` value does NOT auto-update. The manager must manually edit the report to reflect the corrected total. A future improvement would be a computed aggregate relationship.

---

### Phase 7 | Info | Time logs are not linked to billing or payroll

The `duration_minutes` and `status` data captured in Phase 7 are stored but not yet wired to any billing calculation, invoice generation, or payroll system. Those features are explicitly deferred.

---

### Phase 7 | Low | client_visible_summary is not required when visibility = client_summary

When a manager sets `visibility = client_summary`, the `client_visible_summary` field is recommended but not required. If it is blank, the client index/show pages will show an empty cell in that column. Validation could be added in a future iteration to require `client_visible_summary` when `visibility = client_summary`.

---

---

## Phase 8 Warnings / Notes

### Phase 8 | Info | Billing is provider agnostic, no live payment gateway yet

The `payments.provider` and `payments.raw_payload` fields are ready for future Fincra, Flutterwave, Paystack, Stripe, bank transfer, or other integrations. Phase 8 does not collect live payments, create checkout links, process webhooks, or verify gateway transactions.

---

### Phase 8 | Info | Payments are manual admin-confirmed records

Admins can record and confirm manual payments in Filament. Confirming a payment updates the linked invoice balance and can reactivate a linked subscription from payment_due/overdue/suspended. There is no automatic reconciliation against bank statements or gateway events yet.

---

### Phase 8 | Low | No automated recurring billing job

Subscriptions store billing cycle, next billing date, grace period, and status, but there is no scheduled job that automatically creates invoices, advances next_billing_date, or marks overdue accounts. Admins must create invoices and update subscription statuses manually for now.

---

### Phase 8 | Low | Subscription access control is not fully automated

Billing status is visible in the portal, but workspace access is not automatically suspended when a subscription becomes overdue or suspended. Account/workspace suspension logic should be added only when the business rules for grace periods and client exceptions are final.

---

## Phase 9 Warnings / Notes

### Phase 9 | Info | Timer is self-reported and display-only in the browser

The Clock In/Clock Out UI records server-side timestamps and calculates saved duration on the server. The JavaScript timer only formats elapsed time for display. There are no screenshots, keystrokes, screen monitoring, activity sampling, or surveillance signals.

---

### Phase 9 | Low | One active timer is enforced per user globally

The timer start action blocks any second running timer for the same user, even in a different workspace. This avoids overlapping sessions and ambiguous duration data. Users must stop or complete the active timer before starting another one.

---

### Phase 9 | Low | No automatic idle detection or overnight cutoff

If a user leaves a timer running overnight or closes the browser, the timer remains running on the server until stopped manually. This is intentional for Phase 9 server-truth resilience. Managers/admins can stop a running timer when needed.

---

### Phase 9 | Info | Timer data is not wired to payroll or billing automation

Running, stopped, submitted, reviewed, and approved time logs remain operational records. They are not used to auto-generate invoices, payroll, subscription changes, or payment events.

---

## Phase 10 Warnings / Notes

### Phase 10 | Info | Vault secrets depend on the Laravel APP_KEY

`workspace_vault_items.secret_value` uses Laravel encryption. If `APP_KEY` is changed after secrets are stored, existing secrets will not decrypt. Keep the production `APP_KEY` stable and backed up before storing live credentials.

---

### Phase 10 | Info | No auto-login, browser extension, or credential injection

The vault stores encrypted workspace credentials and supports logged reveal/copy actions only. It does not fill login forms, install browser extensions, perform auto-login, or inject credentials into third-party sites.

---

### Phase 10 | Info | Access logs are metadata-only

`workspace_vault_access_logs` record who viewed metadata, revealed, copied, archived, restored, or viewed logs. They intentionally do not store plaintext secrets or credential payloads.

---

### Phase 10 | Low | No built-in password generator yet

Phase 10 does not include a generated-password workflow. Admins and authorized workspace users must enter secret values manually. A generator can be added later without changing the core vault schema.

---

## Phase 11 Warnings / Notes

### Phase 11 | Info | Email delivery depends on Laravel mail configuration

Database notifications work without any mail provider. Email notifications are sent only when the user's preference enables email for that key and Laravel mail is configured. Production must configure:

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

No mail secrets should be committed to Git.

---

### Phase 11 | Info | No real-time notification transport

Phase 11 uses Laravel database notifications and standard page loads. There is no websocket, polling worker, push notification service, browser extension, or external paid notification provider.

---

### Phase 11 | Low | Notification delivery failures are logged and do not block business actions

`NotificationService` catches database/mail delivery exceptions and logs a warning. This protects task, file, chat, report, billing, and trial flows from being interrupted by mail configuration problems, but failed email deliveries need server log review.

---

### Phase 11 | Info | Sensitive payloads are intentionally minimal

Notification payloads store safe metadata only. They do not include vault secrets, raw file paths, payment raw payloads, internal invoice notes, manager notes, API keys, tokens, or internal admin-only notes.

---

## Phase 12 Warnings / Notes

### Phase 12 | Info | Local PHP validation is unavailable on the workstation

PHP is not installed on the local build workstation, so artisan checks must be run on cPanel after pulling the Phase 12 commit. Required commands are documented in `docs/TESTING_CHECKLIST.md`.

---

### Phase 12 | Info | No schema changes were required during stabilization

The Phase 12 audit did not add migrations or alter pushed migrations. Billing totals, payment confirmation, invoice status logic, and vault encryption remain unchanged.

---

## Phase 13 Warnings / Notes

### Phase 13 | Info | Invitation acceptance requires an existing GVOS account

Workspace invitations can be reviewed publicly, but accepting an invitation requires the invitee to sign in with the invited email address. If the email does not already belong to a GVOS user, the invitation page tells the invitee to contact a workspace admin to activate an account. Full self-service registration remains deferred.

---

### Phase 13 | Info | Invitation email delivery depends on Laravel mail configuration

Invitation email is attempted when a pending invitation is created or resent. Mail delivery failures are logged with a hashed email and do not block the portal or Filament action. Production must configure the standard Laravel `MAIL_*` values.

---

### Phase 13 | Low | Client staff company matching is strongest when company domain/profile data exists

Client admins are limited to client staff invitations. Existing users are checked against client profile company data where available; new invitees can also be checked against `companies.company_email_domain` when set. If no company domain is configured, admins should manually review staff invites.

---

## Resolved Issues

### 2026-06-06 | Phase 12 | High | Portal task assignment could grant workspace access to arbitrary users

**Description:** Portal task create/edit validation accepted any existing `users.id` as `assigned_to_user_id`. Because task assignment is one workspace access signal, a crafted request could assign a task to a non-workspace user and create an unintended access path.

**Resolution:** Portal task create/edit now requires the assignee to already be in the workspace active-member or primary-team set. Non-workspace assignees receive a validation error.

**Status:** Resolved.

---

### 2026-06-06 | Phase 12 | Medium | Filament Time Logs exposed actions without registered pages

**Description:** `WorkspaceTimeLogResource` registered only an index page but displayed view/edit/delete actions. This could produce broken admin actions and also conflicted with the requirement that running logs are not edited or deleted incorrectly from the admin list.

**Resolution:** The Filament Time Logs resource is read-only in Phase 12. View/edit/delete table actions and bulk delete were removed.

**Status:** Resolved.

---

### 2026-06-06 | Phase 12 | Low | Notification mark-all-read loaded all unread notifications

**Description:** `NotificationController::markAllRead()` loaded the current user's unread notification collection before marking it read, which was unnecessary work for accounts with many unread notifications.

**Resolution:** The action now performs one current-user-scoped database update on unread notifications.

**Status:** Resolved.

---

### 2026-06-06 | Phase 9 | Low | Timer status `running` not yet in schema

**Description:** The semi-automated timer plan required a `running` value in the `workspace_time_logs.status` enum.

**Resolution:** Added migration `2026_06_06_000001_add_running_status_to_workspace_time_logs_table.php` to extend the enum and implemented the Phase 9 timer flow.

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | Info | PHP/Composer/Node.js not installed on build machine

**Description:** PHP 8.2+, Composer, and Node.js were not installed on the development machine.

**Resolution:** All source files were written by hand. cPanel used as the execution environment. Node/npm not required for Phase 0/1 (Blade + Tailwind CDN).

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | High | Missing auth controllers caused composer install failure

**Description:** `routes/auth.php` referenced 9 auth controllers that did not exist. Laravel validates invokable controllers at `package:discover`, causing cPanel install to crash with `Invalid route action`.

**Resolution:** Created all 9 auth controllers as source files. Commit `54112db`.

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | High | Empty migrations directory caused seeder failure

**Description:** `database/migrations/` was completely empty. Seeder failed with `Table 'roles' doesn't exist`. `config/permission.php` was also missing.

**Resolution:** Created 4 migration files and `config/permission.php`. Commit `299dd7a`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Blade component resolution path incorrect

**Description:** `php artisan view:cache` failed with "Unable to locate component [layouts.auth]". Files were in `resources/views/layouts/` but `<x-layouts.auth>` resolves to `resources/views/components/layouts/`.

**Resolution:** Created component files at correct location. Commit `afd12c7`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Filament 403 after login

**Description:** After successful login, `/admin` returned 403. User model did not implement `FilamentUser` contract.

**Resolution:** Added `implements FilamentUser` and `canAccessPanel()` to User model. Commit `a476da2`.

**Status:** Resolved.

---

### 2026-05-29 | Phase 1 | Critical | Target class [role] does not exist

**Description:**
Logging in as a non-admin user (e.g. Talent) and visiting `/talent/dashboard` threw:

```
Illuminate\Contracts\Container\BindingResolutionException
Target class [role] does not exist.
```

**Root cause:** In Laravel 11, `app/Http/Kernel.php` no longer exists. Spatie Laravel Permission v6 middleware aliases (`role`, `permission`, `role_or_permission`) are **not auto-registered** in Laravel 11 — they must be declared explicitly in `bootstrap/app.php` using `$middleware->alias([...])`. The Phase 1 implementation only registered `check.status` and forgot the Spatie aliases.

**Resolution:** Added all three Spatie middleware aliases to `bootstrap/app.php`:
```php
'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
```

**Status:** Resolved — commit `[Fix Phase 1 role middleware and user form UX]`.

---

---

### 2026-05-30 | Phase 5 | Critical | Talent Kanban drag-drop still failing after Fix 2

**Description:** After Fix 2, task detail pages worked correctly. But dragging a Kanban card still failed silently for talent users. The card would move in the UI and then revert with an error toast, but no useful server message was surfaced to identify the cause.

**Root cause:**
1. `updateStatus()` relied on `resolveUserWorkspaceRole()` alone. In edge cases (primary talent with no member row, or `assigned_user` tier), this could produce a wrong effective role or deny access incorrectly.
2. The Kanban view's `CAN_DRAG` check excluded `'assigned_user'` from the draggable role list, disabling SortableJS for those users.
3. No server-side logging at drag-attempt time made diagnosis impossible without adding logging first.

**Resolution:**
- Rewrote `updateStatus()` with 8-step multi-signal role determination. Now checks `$isTaskAssignee`, `$isPrimaryTalent`, `$isPrimaryManager` alongside `resolveUserWorkspaceRole()`.
- Added comprehensive `Log::info` at every decision point (step 5: attempt log; all rejection paths: denied log with reason).
- Added `'assigned_user'` to `CAN_DRAG` and `showDragHandle` in the Kanban view.
- Added `console.warn` to JS fetch error handler for browser-side debugging.

**Commit:** "Fix talent Kanban task movement permissions"

**Status:** Resolved.

---

### 2026-05-30 | Phase 5 | Critical | Task detail 404 and Kanban drag-drop failure

**Description:** After the workspace access fix, talent and manager users could see the Kanban board but:
1. Clicking any task card returned a 404 page.
2. Dragging a card showed "Could not move this task" (or an uninformative error toast).

**Root cause:**
`WorkspaceTask` model had no integer casts on its foreign key columns (`workspace_id`, `created_by_user_id`, `assigned_to_user_id`). PHP PDO with `ATTR_EMULATE_PREPARES = true` (the Laravel MySQL default) returns integer DB columns as PHP **strings**. `authorizeTaskBelongsToWorkspace()` used strict `!==` to compare `$task->workspace_id` (string `"1"`) against `$workspace->id` (integer `1`). In PHP, `"1" !== 1` is always `true`, so `abort(404)` was triggered on every task request regardless of whether the task belonged to the workspace. The drag-drop AJAX path hit the same abort, producing a 404 JSON response whose `message` field did not clearly indicate the actual cause.

Additionally: no talent-assignee restriction existed — talent could update any task in the workspace.

**Resolution:**
- Added integer casts for FK columns to `WorkspaceTask` model.
- Fixed `authorizeTaskBelongsToWorkspace()` with explicit `(int)` casts + returns a JSON 404 for AJAX requests.
- Added talent-assignee check in `updateStatus()` with descriptive error message.
- Improved all error messages to include from/to status names.
- Added `Log::info` for all failed transition attempts.
- Updated Kanban frontend: per-card drag handle visibility for talent, `X-Requested-With` header, `revertCard()` helper, status-code-aware error messages.

**Commit:** "Fix task detail routes and Kanban status updates"

**Status:** Resolved.

---

### 2026-05-30 | Phase 5 | Critical | Primary manager / talent 403 on workspace and task board

**Description:** Users set as `primary_manager_id` or `primary_talent_id` on a workspace could not access the workspace detail page or the Kanban task board. They received a 403 "You do not have access to this workspace." error.

**Root cause:**
1. `WorkspaceTaskController::getUserWorkspaceRole()` used strict `===` to compare `$workspace->primary_manager_id` (string, returned by Eloquent without a cast) against `$user->id` (integer). PHP strict comparison between a string and an integer always returns `false`. Both primary-team checks silently failed.
2. `WorkspaceController::show()` did not check admin system roles — `super_admin` / `operations_admin` users would also get 403 on the workspace detail page.
3. Neither controller provided a task-assignment fallback (user assigned to a task but with no member row).

**Resolution:**
- Added centralised `resolveUserWorkspaceRole(User $user)` method to `Workspace` model using `(int)` casts to eliminate the type mismatch.
- Added `userHasAccess()`, `userCanCreateTasks()`, `userCanManageTasks()`, `userCanViewInternalTaskNotes()` helpers to `Workspace` model.
- Added `syncPrimaryTeamToMembers()` to `Workspace` model to create or reactivate member rows for primary team.
- Rewrote `WorkspaceController::index()` and `show()` to use the model helpers.
- Rewrote `WorkspaceTaskController` to delegate role resolution to the model; added `transitionRole()` mapping; added task-assigned fallback in `show()`.
- Added "Sync Team" Filament table action and "Sync Primary Team" header action to `WorkspaceResource`.
- `EditWorkspace::afterSave()` now auto-syncs primary team to member rows.
- Added `workspacePrimaryTeamSynced()` to `AuditLogger`.

**Commit:** "Fix workspace task access for primary team members"

**Status:** Resolved.

---

## Warnings / Notes

### Phase 1 | Low | Tailwind CDN in production

Using `https://cdn.tailwindcss.com` is acceptable for Phase 0/1/2/3 staging. Should be replaced with a compiled Vite build before production launch.

---

### Phase 1 | Low | Inertia middleware active but no React pages

`HandleInertiaRequests` runs on every web request as a no-op (Blade-only responses pass through). Harmless until React pages are introduced in a later phase.

---

### Phase 3 | Medium | Active lead password reset required after trial approval

When "Approve Trial" creates a new user for the lead, the account is created with a random 20-char password. The lead cannot log in until they complete a password reset via the "Forgot Password" flow. The admin notification message in Filament advises this. A future improvement could be to send an automated welcome/reset email on trial approval.

---

### Phase 3 | Low | Trial countdown does not auto-refresh

The trial countdown displayed on the active-lead dashboard (`/lead/dashboard`) is rendered at page load time and does not update in real time. The user must refresh the page to see the updated hours remaining. A JavaScript countdown timer should be added in a future phase for a better UX.

---

### Phase 3 | Low | Price estimate currency display on active-lead dashboard

The price estimate card on the active lead dashboard shows the latest accepted estimate. If no estimate is accepted, it falls back to `latestAcceptedEstimate()` from the lead request. If neither exists, no estimate card is shown. This is intentional but worth noting for UX.

---

### Phase 3 UX | Low | Multi-step form does not preserve step position on browser back-button

If the user navigates away and then presses the browser's back button, the page reloads and returns to Step 1 (or the last Laravel-restored step). This is expected behaviour for a server-rendered form. A future improvement could use `sessionStorage` to persist the current step across soft navigations.

---

### Phase 3 UX | Low | Timezone "Other" value is stored as free text

When a user selects "Other" and types a custom timezone (e.g. `Asia/Kolkata`), that string is stored verbatim in `lead_requests.timezone`. The GVOS admin will see this in the Filament Lead Requests edit form. There is no validation that the value is a real IANA timezone. This is intentional for flexibility — a low-stakes field at the lead stage.

---

### Phase 3 UX | Info | Side panel illustration is CSS-only

The "Inside GVOS" illustration panel on the `/request-service` page is built entirely with HTML/CSS and Tailwind classes. No external images or SVG files are used. It represents a simplified preview of the GVOS workflow. Replace with real brand imagery when assets are available.

---

### Phase 3 | Info | Trial workspace features are placeholders

The active lead dashboard now shows a live workspace link when a workspace exists, or a "being prepared" placeholder when it does not. Tasks, files, and communication within the workspace are Phase 5+ features. The placeholder correctly communicates this to the lead.

---

### Phase 4 | Low | Country dropdown is a fixed list (not a full ISO 3166 list)

The `CountryList::options()` helper contains 21 common countries. Users whose country is not listed cannot select it — they must contact the GVOS team. Expanding to a full ISO country list is a future improvement.

---

### Phase 4 | Low | Workspace members are not de-duplicated on trial workspace creation

The "Create Trial Workspace" action creates members for active_lead, talent, and manager. If any of these users are the same person (edge case in testing), a database unique constraint error on `(workspace_id, user_id)` will fire. This is intentional — the unique constraint is correct business logic.

---

### Phase 4 | Info | Workspace task board is a placeholder

The workspace detail page (`/workspaces/{workspace}`) displays a "Tasks, files, and chat coming soon" placeholder panel. These features are Phase 5+.

---

### UI Audit | Low | Filament admin panel uses its own CSS — GVOS Stitch tokens not applied

The Filament admin panel (`/admin`) compiles its own stylesheet. GVOS Stitch design tokens (sidebar-bg, secondary, status-* colors, Manrope/Inter fonts) are **not applied** to Filament views. The Filament panel has a distinct visual identity from the GVOS portal.

This is intentional for Phase 0–4. Aligning Filament's visual identity requires either a Filament theme class with compiled CSS, or a full Filament panel theme. Treat this as a future improvement post-Phase 5.

---

### UI Audit | Info | Tailwind CDN dynamic class safeguard must not be removed

The GVOS portal layout uses Tailwind CDN (JIT mode). Dynamic PHP-conditional classes — for example, the active nav state `bg-white/10 border-l-4 border-secondary-fixed` — must appear in a rendered HTML element so the JIT engine scans and generates them.

A hidden safeguard `<div class="hidden ...">` was added to `resources/views/components/layouts/gvos.blade.php` containing all dynamically-rendered nav and status classes. **Do not remove this div.** Removing it will cause active nav highlighting and dynamic status badge colors to stop rendering.

---

### UI Audit v2 — Resolved | Tailwind CDN config loaded after CDN script (root cause of no visible UI change)

**Description:** After the first UI Fidelity Audit commit (`c472ebb`), the live app showed no visible change. The root cause was that `tailwind.config = {...}` was declared in a `<script>` block that appeared **after** `<script src="https://cdn.tailwindcss.com">` in all three component layout files. Per Tailwind CDN documentation, the config must be defined before the CDN script executes. The CDN was compiling with default settings only, so all custom GVOS tokens were never generated.

**Resolution:** Moved `tailwind.config` script block before the CDN `<script>` tag in all three component layouts. Added a comprehensive CSS fallback `<style>` block as a secondary safety net. Remaining indigo/slate/violet classes in 6 auth/lead views were also removed.

**Status:** Resolved — commit [Fix GVOS UI token rendering and visible styling].

---

### UI Audit | Low | `card-lift` class has no defined styles

The workspace index view (`resources/views/workspace/index.blade.php`) applies a `card-lift` class on workspace cards for a hover-lift effect. This class is defined in the gvos.blade.php CSS fallback block (transition + translateY + box-shadow). This was resolved as part of UI Repair v3 documentation pass.

---

### UI Repair v3 | Info | Custom spacing tokens are unreliable with Tailwind CDN JIT — use standard utilities

Custom spacing tokens defined in `tailwind.config extend.spacing` (e.g. `card-padding`, `input-gap`) are not reliably generated by the Tailwind CDN JIT scanner when used only as Tailwind class names. When these tokens fail to generate, zero spacing is applied, causing headings to touch card borders and form fields to collapse.

**Rule established:** Use standard Tailwind spacing utilities (`p-8`, `space-y-5`, `gap-5`, `px-8 pb-8`) for structural spacing. Custom spacing tokens may be kept in config for documentation but must not be relied upon for visible layout.

**Status:** Resolved in UI Visual Repair v3.

---

### Phase 5 Kanban | Info | SortableJS loaded via CDN — no npm, no build step

SortableJS is loaded from `https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js` only on the task board index page. This is intentional for the current Blade+CDN phase. A future improvement would pin a specific SortableJS version and include it in a compiled bundle (Vite + npm). The `@latest` tag means the version could change on CDN update — if breakage occurs, pin to a specific version (e.g., `@1.15.3`).

---

### Phase 5 Kanban | Low | Empty column placeholder is static (not dynamically inserted)

The "No tasks here" message in empty columns is rendered by PHP at page load. If all tasks are dragged out of a column during a session (without page reload), the "No tasks here" message correctly appears (JS toggles it via `updateEmptyState()`). However, if all tasks are dragged INTO an empty column, the PHP-rendered "No tasks here" text disappears correctly. This is handled client-side in the `onAdd` callback.

---

### Phase 5 Kanban | Low | Drag-and-drop not available on touch devices in all browsers

SortableJS supports touch events (mobile drag-and-drop) but browser support varies. On iOS Safari, touch-drag may require the `forceFallback: true` option in SortableJS config. Currently set to default (`false`) which uses native HTML5 drag API. If mobile drag-and-drop is needed, add `forceFallback: true, fallbackTolerance: 3` to the SortableJS config.

---

### Phase 5 | Low | Filament task assignee dropdown shows all users, not filtered to workspace members

The `WorkspaceTaskResource` form shows all users in the `assigned_to_user_id` dropdown. Ideally it should be filtered to members of the selected workspace. This is a known limitation acknowledged in the Phase 5 spec: "if that is complex, use all users for now with a note to refine later." A future improvement would reactively filter the assignee dropdown based on the selected workspace_id.

---

### Phase 5 | Low | Task status buttons do not perform optimistic UI update

Clicking a status action button on the task detail page (`/workspaces/{workspace}/tasks/{task}`) submits a POST form. The page reloads after the server processes the update. There is no client-side optimistic update or AJAX. The JavaScript `confirm()` dialog prevents accidental submissions. A future improvement could use a Livewire component or fetch/XHR for a smoother experience.

---

### Phase 5 | Low | Task `sort_order` is not editable via the portal UI

The `workspace_tasks.sort_order` column exists and is used to order tasks within a column. However, there is no drag-and-drop or manual reordering UI on the kanban board — tasks are ordered by `sort_order` ascending then `created_at` ascending. Drag-to-reorder is a future Phase 6/7 enhancement.

---

### Phase 5 | Info | Internal notes and internal comments are server-enforced, not hidden at DB level

Internal notes on tasks and internal comments are stripped server-side for non-admin/non-manager users — the field is simply unsaved on store/update, and internal comments are overridden to 'public'. The Blade views additionally hide these fields from the UI. The underlying data is not encrypted; admin DB access can still read all internal fields.

---

### UI Repair v3 | Info | Structural background colors must use inline styles, not custom token classes

Custom color token classes (`bg-sidebar-bg`, `bg-background`) on structural elements (`<body>`, `<aside>`, `<main>`) are unreliable even with both a tailwind.config definition and a CSS fallback block. The Tailwind CDN `@base` layer or browser default stylesheets can override them in some environments.

**Rule established:** `<body>`, `<aside>` (sidebar), and `<main>` (content area) backgrounds must use inline `style="background-color:#..."`. The CSS fallback block remains for non-structural token classes (badges, text, borders). Arbitrary values (`bg-[#hex]`) are also reliable and acceptable for structural elements.

**Status:** Resolved in UI Visual Repair v3.
