# GVOS — Build Phases

## Overview
GVOS is built in 13 phases (Phase 0–12). Each phase has a clear deliverable and must be approved before the next phase starts. Do not build ahead. Do not skip phases.

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

## Phase Approval Process
1. Complete all deliverables for the phase
2. Run the phase-specific testing checklist
3. Share the completion report with product owner
4. Product owner approves before proceeding
5. Update CURRENT_STATUS.md with approved status
6. Log in IMPLEMENTATION_LOG.md
