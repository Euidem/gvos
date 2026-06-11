# GVOS — Build Phases

## Overview
GVOS is built in phases (Phase 0–18+). Each phase has a clear deliverable and must be approved before the next phase starts. Do not build ahead. Do not skip phases.

---

## Phase 24 — Final Production QA, Bug Bash and Launch Readiness ✅
**Status:** Complete (2026-06-11)
**Goal:** Validate the full product for real users via an end-to-end audit; fix confirmed bugs only.

### Deliverables
- [x] Route audit (auth/status/billing middleware, POST-only state changes, nested resource ownership) — PASS
- [x] Permission matrix audit across all roles and modules — PASS (no leaks/escalation)
- [x] Migration & schema-cast audit — PASS (additive, safe order)
- [x] Model helper null-safety/logic audit — PASS
- [x] Billing/invoice visibility audit — PASS
- [x] Timer & weekly report visibility audit — PASS
- [x] Password vault security audit — PASS
- [x] File upload/download security audit — PASS
- [x] Notifications & email audit — PASS
- [x] Invitation & onboarding anti-escalation audit — PASS
- [x] Portal UI audit (no GetVirtual, no rendered phase labels) — PASS
- [x] Admin panel audit (widgets, read-only audit logs) — PASS
- [x] Security/production config audit — PASS
- [x] Performance/query audit (pagination, eager loading) — PASS
- [x] `docs/PRODUCTION_READINESS_CHECKLIST.md` created
- [x] 1 confirmed bug fixed (`.env.example` vault key comment + APP_KEY warning)

### Constraints Respected
- No Phase 25, no new modules, no payment gateway, no payroll
- No invoice/payment/vault/timer/invitation/file logic changed
- No UI redesign; GVOS naming throughout; no migrations added

---

## Phase 23 — Portal Dashboard and Workspace Experience Polish ✅
**Status:** Complete (2026-06-11)
**Goal:** Make the non-admin GVOS portal feel like a premium product — mobile responsive, context-aware copy, practical empty states, and consistent visual patterns.

### Deliverables
- [x] Mobile sidebar: slide-in overlay on screens < 768px with hamburger button and backdrop
- [x] Talent dashboard: dynamic subtitle (tasks / all clear / no workspaces), Time Logs quick link fix, Notifications link
- [x] Line Manager dashboard: workspace list empty state with icon + guidance, Notifications link
- [x] Individual Client dashboard: dynamic 4-state subtitle (no workspace / balance / pending / clear)
- [x] Business Client Admin dashboard: Reports card zero-state copy improved
- [x] Business Client Staff dashboard: dynamic 3-state subtitle
- [x] Workspace show: widened content area max-w-4xl → max-w-5xl
- [x] Reports module: client-role empty state with practical guidance copy
- [x] Files module: talent + client role empty state copy
- [x] Vault module: title em dash consistency fix

### Constraints Respected
- No Phase 24 built
- No new backend modules
- No payment gateway, invoice, payment, vault, timer, invitation, or file logic changes
- No admin command center widget changes
- No payroll
- No GetVirtual in visible UI

---

## Phase 22 — Admin Dashboard and Operational Command Center Polish ✅
**Status:** Complete (2026-06-11)
**Goal:** Transform the Filament admin panel into a useful command center for Super Admin and Operations Admin roles.

### Deliverables
- [x] 9 Filament dashboard widgets: PlatformOverview, WorkspaceOperations, BillingHealth, TimeProductivity, Reports, SecurityVault, OperationalAlerts, RecentActivity, QuickActions
- [x] Custom Dashboard page override — heading "GVOS Command Center", subheading present
- [x] AuditLogResource — read-only Filament resource for browsing audit logs in the admin panel
- [x] Navigation re-grouping: Operations (6 resources), People (6), Billing (4), Security (3), Communications (3), Leads & Trials (3)
- [x] OperationalAlerts widget — 10 alert types with severity colors and direct admin links
- [x] RecentActivity widget — last 10 audit events with actor, action, workspace context
- [x] QuickActions widget — 10 quick action links to key admin pages
- [x] Removed `FilamentInfoWidget` from default dashboard widgets
- [x] No database migrations — all changes at Filament/widget layer
- [x] No billing calculations, payment confirmation, vault encryption, timer core, or invitation token logic changed
- [x] No GetVirtual in any visible UI

### Constraints
- No Phase 23 built
- No payment gateway
- No billing calculation changes
- No vault encryption changes
- No timer core changes
- No invitation token security changes
- No file storage security changes
- No payroll

---

## Phase 21 — Portal Security, Rate Limiting and CSRF Audit ✅
**Status:** Complete (2026-06-10)
**Goal:** Audit and harden GVOS portal security across forms, POST actions, sensitive routes, uploads, vault reveal, invitations, login, notifications, billing and workspace actions.

### Deliverables
- [x] Full route security audit: all state-changing routes on correct HTTP verbs, no unsafe GETs, middleware confirmed on all portal routes
- [x] CSRF audit: all 32 POST-form Blade views confirmed to have `@csrf`; vault reveal JS uses `X-CSRF-TOKEN` header
- [x] Rate limiting: 4 named limiters defined in `AppServiceProvider::boot()` (`vault-reveal`, `file-upload`, `chat-send`, `invitation`)
- [x] Rate limiting: `throttle:vault-reveal` on vault secret reveal (10/min per user)
- [x] Rate limiting: `throttle:file-upload` on workspace file upload and task file upload (20/min per user)
- [x] Rate limiting: `throttle:chat-send` on workspace chat send (30/min per user)
- [x] Rate limiting: `throttle:invitation` on invitation register and accept (10/min per IP)
- [x] Vault security audit: `canReveal()` confirmed to block archived items, secret never in URL/logs, `secret_value` encrypted+hidden
- [x] Invitation security audit: token not logged, email locked to invited address, role enforcement confirmed, DB transaction verified
- [x] Login rate limiting: confirmed pre-existing `LoginRequest` protection (5/min per email+IP)
- [x] Notification scoping: `markRead`/`markAllRead` confirmed scoped to `$request->user()`
- [x] Billing controller: confirmed portal routes are read-only
- [x] Session security: `SESSION_SECURE_COOKIE` added to `.env.example` with production guidance; `APP_DEBUG` production warning added
- [x] No migration required — all hardening at application/configuration layer

### Constraints
- No Phase 22 built
- No new product modules
- No billing calculation changes
- No payment confirmation changes (unless security bug)
- No vault encryption changes (unless security bug)
- No timer core logic changes (unless security bug)
- No invitation token behavior changes (unless security bug)
- No payment gateways
- No payroll
- No GetVirtual mentions in UI
- GVOS naming only

---

## Phase 20 — File Storage Security and Access Hardening ✅
**Status:** Complete (2026-06-10)
**Goal:** Audit and harden workspace file storage so files are private, authorized, and safe before production.

### Deliverables
- [x] Full audit of file storage design: private `local` disk confirmed, no raw storage URLs in Blade, all downloads via controller
- [x] Full audit of route and controller authorization: workspace membership, file-workspace ownership, visibility, billing restrictions
- [x] `config/filesystems.php`: local disk `serve: false` — prevents accidental Storage::url() on private files
- [x] `WorkspaceFile::allowedMimes()`: removed `gif`, added `mp4`, `mov` (per spec)
- [x] `WorkspaceFile::blockedMimeTypes()`: explicit MIME blocklist (PHP, HTML, JS, SVG, executables, shell scripts)
- [x] `WorkspaceFile::blockedExtensions()`: explicit extension blocklist (same types)
- [x] `WorkspaceFile::sanitizeFilename()`: strips path separators, null bytes, leading dots, limits length
- [x] `WorkspaceFileController::handleUpload()`: blocked MIME + extension validation closure; sanitized original_filename; extension safety net
- [x] `WorkspaceFileController::download()`: sanitized Content-Disposition filename
- [x] `php artisan gvos:storage-check`: storage health check command (disk config, writability, PHP limits, symlink, write/delete test)
- [x] No migration required — all hardening at application layer

### Constraints
- No Phase 21 built
- No new product modules
- No billing calculation changes
- No payment confirmation changes
- No vault encryption changes
- No timer core changes
- No invitation token changes
- No payment gateways
- No payroll
- No GetVirtual in visible UI

---

## Phase 19 — Billing Middleware QA and Production Readiness Pass ✅
**Status:** Complete (2026-06-10)
**Goal:** Audit, test, and harden Phase 18 billing enforcement middleware and subscription enforcement system before any new feature work.

### Deliverables
- [x] Full audit of `CheckWorkspaceBillingAccess` middleware — route grouping, role pass-through, always-allowed list confirmed correct
- [x] Full audit of `BillingRefreshStatuses` command — step logic, notification semantics, summary counters, error handling reviewed
- [x] Full audit of billing banner partial — all 4 states reviewed, null-safety confirmed
- [x] Full audit of `Payment::confirm()` manual suspension safety — confirmed `wasManuallySuspended()` check prevents auto-restore
- [x] Full audit of Filament actions (Restrict/Suspend/Reactivate) — visibility conditions, audit calls, notification wiring confirmed
- [x] Full audit of notification payloads — no sensitive data, correct recipient scoping confirmed
- [x] 4 bugs found and fixed (see IMPLEMENTATION_LOG.md)
- [x] All docs updated: CURRENT_STATUS, IMPLEMENTATION_LOG, BUILD_PHASES, PERMISSION_MATRIX, TESTING_CHECKLIST, KNOWN_ISSUES

### Constraints
- No Phase 20 built
- No new product features added
- No payment gateway integration
- No invoice calculation changes
- No vault encryption changes
- No timer core changes
- No invitation token changes
- No payroll built
- No GetVirtual in visible UI

---

## Phase 18 — Billing Subscription Enforcement and Workspace Access Restrictions ✅
**Status:** Complete (2026-06-10)
**Goal:** Billing enforcement layer — payment warning banners, overdue/grace-period tracking, workspace restriction for non-paying clients, admin suspend/reactivate actions, safe internal continuity for all internal roles.

### Deliverables
- [x] Migration: 6 enforcement columns on `workspace_subscriptions` (restricted_at, suspended_at, reactivated_at, restriction_reason, suspended_by FK, reactivated_by FK)
- [x] `WorkspaceSubscription` model: billing state helpers, grace period constants, restriction/suspension/reactivation methods
- [x] `Invoice` model: billing helper methods (isUnpaid, isOverdue, isDueSoon, remainingBalance, billingWarningLevel)
- [x] `Workspace` model: billing access helpers, fixed `activeSubscription` scope to include suspended status
- [x] `Payment::confirm()`: manual suspension safety — manually suspended workspaces are never auto-cleared by payment
- [x] `CheckWorkspaceBillingAccess` middleware (`check.billing`): client roles blocked when restricted/suspended; internal roles always pass; billing routes always allowed
- [x] All workspace routes use `check.billing` middleware
- [x] `workspace.billing.restricted` route and page — always accessible; shows outstanding balance, invoice, support instructions
- [x] `resources/views/partials/billing-banner.blade.php` — 4-state banner partial (due_soon/overdue/restricted/suspended)
- [x] Billing banner added to: billing/index, workspace/show, individual-client dashboard, business-client-admin dashboard
- [x] 5 notification classes: BillingDueSoon, BillingOverdue, WorkspaceRestricted, WorkspaceSuspended, WorkspaceReactivated
- [x] 5 NotificationService methods for billing events
- [x] 7 AuditLogger billing enforcement wrappers
- [x] `php artisan gvos:billing-refresh-statuses` command — idempotent, dry-run capable, sends notifications, writes audit log
- [x] Filament WorkspaceSubscriptionResource: restricted_at/suspended_at columns, Restrict/Suspend/Reactivate actions with confirmation modals

### Constraints
- No Phase 19 built
- No live payment gateway integration
- No invoice calculation changes
- No password vault changes
- No timer core changes
- No invitation token changes
- No payroll built
- No GetVirtual in visible UI

---

## Phase 0 — Documentation and Project Setup ✅
**Status:** Complete (current)
**Goal:** Create a clean, documented foundation that all future phases build on.

### Deliverables
- [x] Laravel project created
- [x] Filament installed (GVOS Ops Console)
- [x] Inertia + React installed (portal frontends)
- [x] Tailwind CSS configured
- [x] Spatie Laravel Permission installed
- [x] Base roles seeded (8 roles)
- [x] First Super Admin seeder
- [x] /docs folder with 16 documentation files
- [x] /design-reference folder with Stitch UI export
- [x] Git initialized with first commit
- [x] .gitignore configured
- [x] .env.example configured

---

## Phase 1 — Identity and Access Foundation
**Status:** Not started
**Depends on:** Phase 0 complete and approved

### Deliverables
- [ ] Login page (Stitch design implemented)
- [ ] Password reset flow
- [ ] Email verification scaffold
- [ ] Role-based dashboard redirect on login
- [ ] User profile page (edit name, email, password, avatar, timezone)
- [ ] Admin user management in Filament (CRUD for all roles)
- [ ] Filament panel locked to admin roles only
- [ ] Route middleware for all 8 roles
- [ ] Phase 0 placeholder dashboards replaced with real Stitch-inspired dashboards

### Test Checklist
- Login redirects each role to the correct dashboard
- Non-admin cannot access Filament
- Password reset email sends and works
- Profile updates save correctly

---

## Phase 2 — People and Organizations
**Status:** Not started
**Depends on:** Phase 1 approved

### Deliverables
- [ ] Company model: create, edit, list in Filament
- [ ] Department model: linked to company
- [ ] Email domain validation for business staff
- [ ] Talent profile model with skills, schedule, timezone
- [ ] Manager profile model
- [ ] Admin: assign talent → workspace, manager → workspace
- [ ] Staff invitation workflow (email)
- [ ] Business Client Admin can manage their own staff

### Test Checklist
- Company created with email domain
- Staff cannot be invited with wrong domain
- Talent profile linked to user
- Manager profile linked to user
- Assignments save and display correctly

---

## Phase 3 — Leads and Trial Flow
**Status:** Not started
**Depends on:** Phase 2 approved

### Deliverables
- [ ] Lead model and Filament resource
- [ ] Lead status workflow (New → Contacted → Quoted → Trial Approved → Converted → Lost)
- [ ] Price estimate builder in Filament
- [ ] Lead portal view (active_lead sees their status and estimate)
- [ ] Lead acceptance/rejection flow
- [ ] Trial workspace auto-creation on approval
- [ ] Trial expiry handling
- [ ] Trial to client conversion (role upgrade, billing start)

### Test Checklist
- Lead created and progresses through statuses
- Price estimate visible to lead
- Trial workspace creates on approval
- Conversion upgrades role and triggers billing start

---

## Phase 4 — Workspace Engine
**Status:** Not started
**Depends on:** Phase 3 approved

### Deliverables
- [ ] Workspace model: type, status, members
- [ ] Workspace member management
- [ ] Workspace settings page
- [ ] Role-aware workspace dashboard views:
  - Client: overview + summary only
  - Talent: work view with task list
  - Manager: oversight view with monitoring tools
  - Admin: full management view
- [ ] Workspace status flow (Trial → Active → Suspended → Closed)
- [ ] Admin workspace management in Filament

### Test Checklist
- Each role sees the correct workspace view
- Client cannot see exact time logs in workspace
- Manager can see all members and time logs
- Workspace suspension removes client access

---

## Phase 5 — Task Board
**Status:** Not started
**Depends on:** Phase 4 approved

### Deliverables
- [ ] Task model with status, priority, assignee, due date
- [ ] Kanban board UI (React, Stitch design)
- [ ] Create task form
- [ ] Task detail view
- [ ] Task comments
- [ ] File attachments on tasks
- [ ] Client approval flow (approve / request revision)
- [ ] Rejected status handling

### Test Checklist
- Tasks move through all statuses
- Client can approve or reject completed tasks
- Talent sees only assigned tasks
- Manager sees all tasks in workspace

---

## Phase 6 — Chat and Files
**Status:** Not started
**Depends on:** Phase 5 approved

### Deliverables
- [ ] Workspace chat (real-time or polling MVP)
- [ ] File attachments in chat
- [ ] Voice note upload and playback
- [ ] Manager monitoring view (flag messages)
- [ ] File library per workspace
- [ ] File categories
- [ ] Upload/download with role gates
- [ ] Admin file audit view

### Test Checklist
- Messages send and receive correctly
- Flagged messages visible to manager
- File upload restricted by role
- Files stored in private storage, not public

---

## Phase 7 — Time Tracking and Reports
**Status:** Not started
**Depends on:** Phase 6 approved

### Deliverables
- [ ] Clock in / clock out per workspace
- [ ] Task-level timer (optional)
- [ ] Daily time log view for talent
- [ ] Daily report submission form
- [ ] Manager report review and sign-off
- [ ] Weekly summary generation
- [ ] Client weekly summary view (no exact times)
- [ ] Admin exact time log view

### Test Checklist
- Talent clocks in, logs time
- Client cannot see exact times
- Manager sees exact time log
- Weekly summary generates correctly
- Daily report submission saves

---

## Phase 8 — Billing Foundation
**Status:** Not started
**Depends on:** Phase 7 approved

### Deliverables
- [ ] Subscription plans table
- [ ] Subscription creation on workspace conversion
- [ ] Invoice generation (scheduled)
- [ ] Manual payment recording in Filament
- [ ] Non-payment detection (scheduled check)
- [ ] Automatic workspace suspension
- [ ] Reactivation on payment
- [ ] Client portal billing view (invoices, subscription status)
- [ ] Payment provider abstraction layer (no live integration in MVP)

### Test Checklist
- Invoice generates at billing cycle
- Unpaid invoice triggers suspension after grace period
- Payment recorded → workspace reactivated
- Client can view their invoices

---

## Phase 9 — Complaints and Satisfaction
**Status:** Not started
**Depends on:** Phase 8 approved

### Deliverables
- [ ] Complaint form with categories and evidence
- [ ] Complaint status workflow
- [ ] Manager assignment and response
- [ ] Admin escalation handling
- [ ] Resolution notes
- [ ] Satisfaction survey trigger (post-resolution)
- [ ] Survey response collection
- [ ] Manager satisfaction report view

### Test Checklist
- Client raises complaint
- Manager sees and responds
- Escalation reaches admin
- Survey sent after resolution

---

## Phase 10 — Vault and Security Hardening
**Status:** Password vault foundation complete; security hardening items not started
**Depends on:** Phase 9 approved

### Deliverables
- [x] Encrypted vault per workspace
- [x] Client adds credentials to vault
- [x] Access grant system (grant to talent)
- [x] Talent can reveal granted credentials
- [x] Reveal log per credential
- [ ] AES-256 encryption implementation review (Laravel encrypted cast in use; formal review pending)
- [ ] 2FA implementation (admin accounts minimum)
- [ ] Audit log export
- [ ] Security header configuration
- [ ] Rate limiting review and hardening

### Test Checklist
- Vault credentials encrypted in database
- Talent can only reveal credentials granted to them
- Reveal logged with IP and timestamp
- Admin can view full reveal log

---

## Phase 11 — Calls
**Status:** Not started
**Depends on:** Phase 10 approved

### Deliverables
- [ ] Embedded call room (third-party: Daily.co, Whereby, or similar)
- [ ] Call initiated from workspace
- [ ] Call participants listed
- [ ] Call metadata logged (not recorded)
- [ ] Call history view in workspace

### Test Checklist
- Call room loads within workspace
- Participants logged correctly
- No recording occurs
- Non-workspace members cannot join

---

## Phase 12 — QA and Launch Readiness
**Status:** Stabilization audit complete for Phases 8-11; broader launch-readiness items pending
**Depends on:** Phase 11 approved

**Phase 12 audit update (2026-06-06):** Stabilization audit complete for Phases 8-11. Broader launch-readiness items remain pending.

### Deliverables
- [x] Full permission matrix audit for recent billing, timer, vault, and notification modules
- [x] Workspace route and nested-resource ownership audit for tasks, chat, files, billing, time logs, reports, vault, and notifications
- [x] Portal task assignment hardening so non-workspace users cannot be assigned through task forms
- [x] Filament time log resource stabilized as read-only
- [x] Notification mark-all-read optimized with current-user scoped update
- [x] Workspace chat and file-library query stabilization
- [x] Confirmation that no database, billing logic, payment confirmation, invoice status, or vault encryption changes were made
- [ ] Full permission matrix audit (all 8 roles × all resources)
- [ ] Workspace isolation testing (cross-workspace data leak test)
- [ ] Billing cycle end-to-end test
- [ ] Mobile viewport testing (client and talent portals)
- [ ] Performance audit (dashboards under 2s)
- [ ] Security header review
- [ ] Deployment pipeline configuration
- [ ] Environment variable documentation
- [ ] Final documentation pass (all /docs files updated)
- [ ] Backup and recovery procedure documented

### Test Checklist
- All permission scenarios pass
- No cross-workspace data visible
- Billing cycle test passes
- Mobile viewports look correct
- No critical security findings

---

## Phase 13 - Workspace Membership and Invitations
**Status:** Complete
**Depends on:** Phase 12 stabilization audit

### Deliverables
- [x] Portal workspace member management page
- [x] Add existing workspace users with workspace role validation
- [x] Workspace role update and safe deactivation
- [x] Workspace invitations table and model
- [x] Invitation create, resend, revoke, review, and accept routes
- [x] Existing-user database notifications and mail invitation attempts
- [x] Phase 13 audit events without token logging
- [x] Filament workspace invitations relation manager
- [x] Workspace overview team card count/link update
- [x] Documentation update for schema, permissions, testing, known issues, and status
- [x] No billing, payment, vault encryption, timer, payroll, gateway, screenshot, keystroke, or screen-monitoring changes

### Test Checklist
- Member page loads for authorized roles
- Client admin can manage client staff only
- Invitation token page renders and acceptance requires matching authenticated email
- Pending invitations can be resent/revoked
- Soft removal preserves users and sets member status to removed
- Audit and notification payloads exclude invitation tokens

---

## Phase 14 - Invitation Account Activation and Onboarding
**Status:** Complete
**Depends on:** Phase 13 workspace invitations

### Deliverables
- [x] `WorkspaceInvitationController` with `show`, `accept`, and `registerAndAccept` methods
- [x] Public `GET /invitations/{token}` updated to `WorkspaceInvitationController::show`
- [x] Public `POST /invitations/{token}/register` route for new-user self-registration
- [x] Auth-protected `POST /invitations/{token}/accept` updated to `WorkspaceInvitationController::accept`
- [x] Invitation page detects account existence and renders correct scenario
- [x] Scenario 1: logged-in matching email → Accept button
- [x] Scenario 2: logged-in wrong email → error + sign-out
- [x] Scenario 3: account exists, not logged in → Login prompt
- [x] Scenario 4: no account → registration form with locked email
- [x] Platform role safely inferred from workspace_role; super_admin / operations_admin blocked
- [x] Profile stubs created matching Filament CreateUser pattern
- [x] Database transaction for full register-and-accept flow
- [x] `workspace_invitation.registered_and_accepted` audit event; no token or password logged
- [x] Filament invitation relation manager updated with accepted_at and accepted_by columns
- [x] Documentation updates (status, log, permissions, testing, known issues, build phases)
- [x] No billing, payment, vault encryption, timer, payroll, gateway, screenshot, keystroke, or screen-monitoring changes

### Test Checklist
- New user opens invitation, fills registration form, is logged in and added to workspace
- Existing user without session sees login prompt, not registration form
- Logged-in wrong-email user cannot accept
- Logged-in matching-email user can accept
- Accepted/revoked/expired invitations show terminal state with no action button
- No audit entry contains token or password
- Role-safety: no super_admin or operations_admin created via invitation

---

## Phase 15 - Email Configuration, System Mail Testing and Branded Notification Templates
**Status:** Complete
**Depends on:** Phase 14 invitation flow

### Deliverables
- [x] GVOS branded mail CSS theme (`resources/views/vendor/mail/html/themes/gvos.css`)
- [x] Custom mail header and footer blade overrides for GVOS wordmark and privacy footer
- [x] `config/mail.php` updated with `markdown.theme = gvos`
- [x] `.env.example` updated with `MAIL_MARKDOWN_THEME`, cPanel SMTP block (SSL + TLS), mail test tool reference
- [x] `GvosNotification::toMail()` improved — `GVOS:` subject prefix, better footer, `The GVOS Team` salutation
- [x] `WorkspaceInvitationMailNotification` improved — inviter name, better subject, clearer expiry, ignore note
- [x] `email_delivery_logs` migration and model for mail delivery tracking
- [x] `NotificationService` logs mail success/failure to `email_delivery_logs`; error messages sanitized for SMTP credential safety
- [x] `EmailDeliveryLogResource` Filament read-only resource with filters
- [x] `MailTest` Filament page at `/admin/mail-test` — super_admin + operations_admin only; sanitized error display
- [x] Auth email branding verified (APP_NAME=GVOS controls password reset email)
- [x] Documentation updates (status, log, permissions, testing, known issues, build phases)
- [x] No billing, payment, vault encryption, timer, invitation token logic, payroll, gateway, screenshot, keystroke, or screen-monitoring changes

### Test Checklist
- Password reset email renders with GVOS branding (dark header, GVOS footer)
- Invitation email includes workspace name, inviter name, expiry, and GVOS salutation
- Mail test page at `/admin/mail-test` is accessible only to super_admin and operations_admin
- Mail test with log driver writes to laravel.log without error
- Mail test with cPanel SMTP delivers to recipient
- Failed mail test shows sanitized error — no credentials visible
- `email_delivery_logs` table creates success row on mail delivery and failed row on error
- Filament `/admin/email-delivery-logs` shows delivery log entries
- No raw email addresses in delivery log — only sha256 hash

---

## Phase 16 — User Onboarding Completion
**Status:** Complete
**Depends on:** Phase 15 email configuration

### Deliverables
- [x] Migration: `onboarding_completed_at` and `last_onboarding_step` added to `user_profiles`
- [x] `User` model onboarding helpers: `needsOnboarding()`, `hasCompletedRequiredProfile()`, `profileForRole()`, `primaryWorkspace()`, `onboardingChecklist()`, `onboardingCompletionPercentage()`
- [x] `AuditLogger` wrappers: `onboardingProfileUpdated()` and `onboardingCompleted()`
- [x] `OnboardingController` with `index`, `update`, `completeStep` methods
- [x] `/onboarding` page — welcome, role label, progress ring, checklist, profile form, workspace card, role-based action links
- [x] Onboarding banner partial included in all 5 role dashboards — auto-hides on completion
- [x] Workspace show: orientation card for new members or users with incomplete onboarding
- [x] Improved empty states: workspace index (role-aware + onboarding CTA), tasks (role-specific), time logs (role-specific)
- [x] Post-invitation redirect: new users go to `/onboarding` after registration; existing users with incomplete profile go to `/onboarding` after acceptance
- [x] Database notification on onboarding completion (silent fail-safe)
- [x] No billing, payment, vault, timer, invitation token, payroll, gateway, screenshot, or keystroke changes

### Test Checklist
- New user via invitation redirected to `/onboarding` after registration
- Existing user with incomplete profile redirected to `/onboarding` after accepting invitation
- Onboarding page shows correct role label, checklist, and workspace card
- Progress ring shows 0% with no profile, advances when required fields are filled
- "Mark Setup Complete" button only appears when required fields are done
- After completion: `onboarding_status = complete`, `onboarding_completed_at` set, redirect to workspace/dashboard
- Onboarding banners shown on all 5 role dashboards until setup complete
- Workspace orientation card shown for new/incomplete-profile members
- Role-specific empty states in workspace index, tasks, and time logs

---

---

## Phase 17 — Weekly Report Automation and Client Summary Workflow
**Status:** Complete (2026-06-10)
**Depends on:** Phase 16 onboarding

### Goal
Make GVOS weekly reports easier and more professional. Managers generate drafts from workspace activity (time logs and completed tasks), edit and approve the report, then publish to clients. Clients see only polished published summaries — never internal notes or raw logs.

### Deliverables
- [x] Migration: `generated_at` + `generated_by_user_id` on `workspace_weekly_reports`
- [x] `WorkspaceWeeklyReport` model: `wasGenerated()`, `generatedBy()` relation
- [x] `WeeklyReportGeneratorService::generate()` — builds report fields from approved/submitted time logs and tasks; no internal fields exposed
- [x] `WeeklyReportGeneratorService::preview()` — count-only preview for the generate form
- [x] Controller: `generate()` GET, `generateStore()` POST, `publish()` POST (dedicated publish action)
- [x] Generate view: date picker, preview count grid, "How it works" info box
- [x] Edit view: Client-Visible section (green) + Internal section (amber); auto-generated banner
- [x] Show view: client-polished view — hours block, friendly headings, notes box, published footer; internal sections locked with amber badge
- [x] Index view: "Generate Report" + "Write Manually" buttons; auto-generated badge on list items
- [x] Workspace show: enhanced Weekly Reports card with latest report status, generate button for managers, view latest for clients
- [x] Manager dashboard: report drafts count with amber link
- [x] Client dashboards: Published Reports card links to reports when > 0
- [x] `AuditLogger::weeklyReportGenerated()` wrapper
- [x] `NotificationService::notifyWeeklyReportGenerated()` — internal notification (managers/admins only)
- [x] `WeeklyReportGeneratedNotification` class
- [x] Filament resource: workspace filter, duration formatting, `generated_at` icon column
- [x] No billing, payment, vault, timer, invitation token, payroll, gateway changes

### Test Checklist
- Manager generates report from date range — draft created, redirected to edit
- Edit page clearly separates client-visible and internal sections
- Publishing sets status=published, fires client notification
- Client sees only summary, achievements, hours block, client notes — never blockers/next_steps/internal notes
- Manager dashboard shows amber report draft count when drafts exist
- Workspace show card shows latest report status + appropriate buttons per role
- Filament workspace filter and generated_at column work correctly

---

## Phase Approval Process
1. Complete all deliverables for the phase
2. Run the phase-specific testing checklist
3. Share the completion report with product owner
4. Product owner approves before proceeding
5. Update CURRENT_STATUS.md with approved status
6. Log in IMPLEMENTATION_LOG.md
