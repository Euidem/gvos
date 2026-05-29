# GVOS — Testing Checklist

## Overview
Run the relevant checklist at the end of each phase before requesting approval to proceed.

---

## Phase 0 — Foundation Setup ✅ PASSED

### Environment
- [x] cPanel PHP 8.2+ available
- [x] cPanel MySQL available
- [x] `.env` configured with DB credentials on cPanel

### Installation
- [x] `composer install` completes with no errors (cPanel)
- [x] `php artisan key:generate` ran successfully (cPanel)
- [x] `php artisan migrate` runs all migrations with no errors (cPanel)
- [x] `php artisan db:seed` runs with no errors (cPanel)

### Functionality
- [x] Laravel app loads on cPanel staging URL
- [x] Login page displays correctly (Blade)
- [x] `admin@gvos.local / password` logs in successfully
- [x] After admin login, redirected to Filament `/admin`
- [x] Filament admin panel loads without errors
- [x] All 8 roles seeded in DB
- [x] Admin user has `super_admin` role
- [x] `/docs` folder has 16 files
- [x] `.gitignore` is correct (no .env, no vendor/, no node_modules/)

### Security
- [x] `.env` is not committed to Git
- [x] `vendor/` is not committed to Git

---

## Phase 1 — Identity and Access Foundation

Run these after `git pull && php artisan migrate && php artisan optimize:clear` on cPanel.

### Migrations
- [ ] `php artisan migrate` runs without errors
- [ ] `user_profiles` table exists in DB
- [ ] `audit_logs` table exists in DB
- [ ] `users.status` ENUM includes 'pending'

### Authentication
- [ ] Login with valid credentials → correct dashboard
- [ ] Login with invalid credentials → error shown, no crash
- [ ] Logged-out user redirected to login on all protected routes
- [ ] After login, `audit_logs` has a `user.login` entry

### Role Redirect
- [ ] `super_admin` → `/admin` (Filament)
- [ ] `operations_admin` → `/admin` (Filament)
- [ ] `line_manager` → `/manager/dashboard`
- [ ] `talent` → `/talent/dashboard`
- [ ] `individual_client` → `/client/dashboard`
- [ ] `business_client_admin` → `/client/dashboard`
- [ ] `business_client_staff` → `/client/dashboard`
- [ ] `active_lead` → `/lead/dashboard`

### Access Control
- [ ] Non-admin user cannot access `/admin` (gets 403)
- [ ] Talent cannot access `/manager/dashboard` (gets 403 or redirect)
- [ ] Client cannot access `/talent/dashboard` or `/manager/dashboard`
- [ ] Suspended user is redirected to `/account/status` on any dashboard route
- [ ] Inactive user is redirected to `/account/status` on any dashboard route
- [ ] Pending user CAN access their dashboard

### Filament User Management
- [ ] `/admin/users` loads without error
- [ ] Users list shows name, email, role badge, status badge
- [ ] Status filter works (filter by active/suspended/etc.)
- [ ] Role filter works
- [ ] Search by name and email works
- [ ] Super Admin can click "New User" and create a user
- [ ] Created user appears in list with correct role and status
- [ ] Super Admin can edit a user's status from active → suspended
- [ ] Suspended user loses portal access (test by logging in as that user)
- [ ] `audit_logs` has `user.created` entry after creating a user
- [ ] Operations Admin can VIEW users but cannot create or edit
- [ ] Delete button is NOT present in the UI

### Profile Editing
- [ ] `/profile` page loads for all authenticated roles
- [ ] Profile page shows current first_name, last_name, phone, country, city, timezone, bio
- [ ] Saving profile updates the `user_profiles` table
- [ ] Saving with first_name + last_name sets `onboarding_status` to 'complete'
- [ ] Role field is read-only (cannot be changed from profile page)
- [ ] `audit_logs` has `user.profile_updated` entry after saving

### Password Change
- [ ] Password change form on `/profile` page works
- [ ] Wrong `current_password` → validation error
- [ ] Non-matching confirmation → validation error
- [ ] Correct inputs → password updated
- [ ] After password change, old password no longer works for login
- [ ] `audit_logs` has `user.password_changed` entry

### Dashboards
- [ ] Super Admin dashboard shows welcome message, quick-action cards
- [ ] Operations Admin dashboard shows welcome and user management link
- [ ] Line Manager dashboard shows welcome, role badge, profile link
- [ ] Talent dashboard shows welcome, role badge, profile link
- [ ] Individual Client dashboard shows welcome, role badge, profile link
- [ ] Business Client Admin dashboard shows welcome, role badge, profile link
- [ ] Business Client Staff dashboard shows welcome, role badge, profile link
- [ ] Active Lead dashboard shows welcome and "Complete My Profile" button

### Audit Log
- [ ] `audit_logs` table is not empty after login + profile update + password change
- [ ] Audit rows have correct `action`, `user_id`, `subject_type`, `subject_id`
- [ ] Audit rows cannot be deleted (AuditLog model blocks deletes)

### Security
- [ ] No debug routes are exposed
- [ ] `.env` is NOT in GitHub
- [ ] Clients, talents, leads cannot access `/admin`
- [ ] Direct URL access to `/manager/dashboard` as a talent returns 403 or redirect

---

## Phase 2 — People and Organizations (planned)

- [ ] Admin can create a company with email domain
- [ ] Admin can create departments within a company
- [ ] Business admin can invite staff only with matching company domain
- [ ] Talent profile created and linked to user
- [ ] Manager profile created and linked to user

---

## Phase 3 — Leads and Trial Flow (planned)

- [ ] Admin can create a lead
- [ ] Lead status flow: New → Contacted → Quoted → Trial → Converted / Lost
- [ ] Admin converts trial to client → role updated, billing starts
- [ ] Lead access revoked on Lost status

---

## Phase 4 — Workspace Engine (planned)

- [ ] Workspace created with correct type and status
- [ ] Members assigned to workspace
- [ ] Each role sees the correct workspace view
- [ ] Workspace suspension removes client access

---

## Phase 5 — Task Board (planned)

- [ ] Task CRUD with status, priority, assignee, due date
- [ ] Client can approve / reject completed tasks
- [ ] Talent sees only assigned tasks

---

## Phase 6 — Chat and Files (planned)

- [ ] Messages send and display in workspace chat
- [ ] Manager can flag messages
- [ ] File upload and download work
- [ ] Files are NOT publicly accessible via direct URL

---

## Phase 7 — Time Tracking and Reports (planned)

- [ ] Talent clocks in / out
- [ ] Client CANNOT see exact time log times
- [ ] Talent submits daily report
- [ ] Manager reviews and signs off

---

## Phase 8 — Billing (planned)

- [ ] Subscription and invoice lifecycle
- [ ] Admin records manual payment
- [ ] Unpaid invoice after grace period → workspace suspended

---

## Phase 12 — Launch Readiness (planned)

- [ ] All Phase 1–11 checklists pass
- [ ] No cross-workspace data leak
- [ ] Mobile views correct at 375px and 768px
- [ ] All `/docs` files accurate
- [ ] `.env.example` has all required variables
- [ ] Database backup procedure documented
