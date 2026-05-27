# GVOS — Testing Checklist

## Overview
Run the relevant checklist at the end of each phase before requesting approval to proceed.

---

## Phase 0 — Foundation Setup

### Environment
- [ ] PHP 8.2+ installed and accessible in terminal
- [ ] Composer installed
- [ ] Node.js 18+ installed
- [ ] MySQL or PostgreSQL running
- [ ] `.env` configured with correct DB credentials

### Installation
- [ ] `composer install` completes with no errors
- [ ] `php artisan key:generate` runs successfully
- [ ] `php artisan migrate` runs all migrations with no errors
- [ ] `php artisan db:seed` runs with no errors
- [ ] `npm install` completes with no errors
- [ ] `npm run build` or `npm run dev` starts with no errors

### Functionality
- [ ] Laravel app loads at http://localhost:8000
- [ ] Login page displays correctly
- [ ] Admin can login with admin@gvos.local / password
- [ ] After login, admin is redirected to Filament /admin
- [ ] Filament admin panel loads without errors
- [ ] All 8 role seeded in DB (check: `php artisan tinker` → `Role::all()`)
- [ ] Admin user has role `super_admin` (check in Filament or tinker)
- [ ] /docs folder exists with 16 files
- [ ] /design-reference/screens exists with Stitch screens
- [ ] .gitignore is present and correct
- [ ] Git has initial commit

### Security Basics
- [ ] `.env` is not committed to git (check `git status`)
- [ ] `vendor/` is not committed to git
- [ ] `node_modules/` is not committed to git

---

## Phase 1 — Identity and Access

### Authentication
- [ ] Login with valid credentials → correct dashboard
- [ ] Login with invalid credentials → error shown
- [ ] Password reset email sends (requires mail configured)
- [ ] Password reset link works once, fails on reuse
- [ ] Logged-out user redirected to login on protected routes

### Role Redirect
- [ ] super_admin → Filament /admin
- [ ] operations_admin → Filament /admin
- [ ] line_manager → /manager/dashboard
- [ ] talent → /talent/dashboard
- [ ] individual_client → /client/dashboard
- [ ] business_client_admin → /client/dashboard
- [ ] business_client_staff → /client/dashboard
- [ ] active_lead → /lead/dashboard

### Access Control
- [ ] Non-admin user cannot access /admin
- [ ] Talent cannot access /manager routes
- [ ] Client cannot access /talent or /manager routes
- [ ] Manager can access /manager but not /admin

### User Profile
- [ ] User can update their name
- [ ] User can update their email (requires reverification)
- [ ] User can change password
- [ ] User can update timezone

---

## Phase 2 — People and Organizations

- [ ] Admin can create a company with email domain
- [ ] Admin can create departments within a company
- [ ] Business admin can invite staff only with matching company domain
- [ ] Staff with wrong domain email cannot be invited
- [ ] Talent profile created and linked to user
- [ ] Manager profile created and linked to user
- [ ] Talent can be assigned to workspace (test in Phase 4)
- [ ] Manager can be assigned to workspace (test in Phase 4)

---

## Phase 3 — Leads and Trial Flow

- [ ] Admin can create a lead
- [ ] Lead status changes: New → Contacted → Quoted
- [ ] Price estimate created and visible to lead
- [ ] Lead can accept price estimate
- [ ] Admin can approve trial → trial workspace created
- [ ] Trial workspace accessible to lead (active_lead role)
- [ ] Trial expires at configured time
- [ ] Admin converts trial to client → role updated, billing starts
- [ ] Admin marks lead as Lost → access revoked

---

## Phase 4 — Workspace Engine

- [ ] Workspace created with correct type and status
- [ ] Members assigned to workspace
- [ ] Each role sees the correct workspace view:
  - Client: sees summary, NOT exact time logs
  - Talent: sees work area with task list
  - Manager: sees oversight with monitoring
  - Admin: sees full management view
- [ ] Workspace suspension removes client access
- [ ] Suspended workspace shows correct screen to client

---

## Phase 5 — Task Board

- [ ] Task created with title, description, status, priority, assignee, due date
- [ ] Task status changes via drag-and-drop or button
- [ ] Comments can be added to tasks
- [ ] Files can be attached to tasks
- [ ] Client can approve a completed task
- [ ] Client can reject a completed task with comment
- [ ] Rejected task moves to Rejected status
- [ ] Talent sees only assigned tasks
- [ ] Manager sees all workspace tasks

---

## Phase 6 — Chat and Files

- [ ] Messages send and display in workspace chat
- [ ] File can be attached in chat
- [ ] Manager can view all chat history
- [ ] Manager can flag a message
- [ ] Flagged messages highlighted in manager view
- [ ] Files can be uploaded to file library
- [ ] Files download correctly
- [ ] Files are NOT publicly accessible via direct URL
- [ ] Roles without file upload permission cannot upload

---

## Phase 7 — Time Tracking and Reports

- [ ] Talent clocks in → time log created
- [ ] Talent clocks out → time log closed with duration
- [ ] Talent cannot see other talents' time logs
- [ ] Client CANNOT see exact time log times
- [ ] Manager CAN see exact times
- [ ] Talent submits daily report
- [ ] Manager reviews and signs off on daily report
- [ ] Weekly summary generated correctly
- [ ] Client can view weekly summary only

---

## Phase 8 — Billing

- [ ] Subscription created on workspace activation
- [ ] Invoice generated at billing cycle start
- [ ] Admin can record manual payment
- [ ] Invoice marked as paid after recording
- [ ] Unpaid invoice after due date → warning triggered
- [ ] After grace period → workspace suspended
- [ ] Admin records payment → workspace reactivated
- [ ] Client can view their invoices
- [ ] Client cannot see other workspaces' invoices

---

## Phase 9 — Complaints and Satisfaction

- [ ] Client can raise a complaint with evidence
- [ ] Talent can raise a complaint
- [ ] Complaint assigned to manager
- [ ] Manager responds to complaint
- [ ] Manager can escalate to admin
- [ ] Admin resolves complaint with notes
- [ ] Resolution recorded and visible to raiser
- [ ] Satisfaction survey triggered after resolution
- [ ] Survey response captured

---

## Phase 10 — Vault and Security

- [ ] Client can add a credential to vault
- [ ] Credential fields encrypted in database (verify directly)
- [ ] Client can grant access to a specific talent
- [ ] Talent can see credentials granted to them (masked)
- [ ] Talent reveals credential → reveal logged
- [ ] Reveal log shows talent name, timestamp, IP
- [ ] Admin can view reveal log
- [ ] Talent cannot reveal credential not granted to them
- [ ] Audit log export works

---

## Phase 11 — Calls

- [ ] Call room opens within workspace
- [ ] Participants logged at call start
- [ ] Call metadata recorded (no recording)
- [ ] Call history visible in workspace
- [ ] Non-member cannot join call

---

## Phase 12 — Launch Readiness

- [ ] All Phase 1–11 checklists pass
- [ ] No cross-workspace data leak (isolation test)
- [ ] Mobile views look correct on 375px and 768px
- [ ] Dashboard load times under 2 seconds
- [ ] All /docs files are accurate and up to date
- [ ] KNOWN_ISSUES.md reviewed — no blocking issues
- [ ] .env.example has all required variables documented
- [ ] Deployment pipeline documented
- [ ] Database backup procedure documented
- [ ] Rollback procedure documented
