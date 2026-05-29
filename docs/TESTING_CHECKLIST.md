# GVOS — Testing Checklist

## Overview
Run the relevant checklist at the end of each phase before requesting approval to proceed.

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

## Phase 2 — People and Organizations (planned)

- [ ] Admin can create a company with email domain
- [ ] Admin can create departments
- [ ] Business admin can invite staff with matching email domain
- [ ] Talent profile linked to user
- [ ] Manager profile linked to user

---

## Phase 3 — Leads and Trial Flow (planned)
## Phase 4 — Workspace Engine (planned)
## Phase 5 — Task Board (planned)
## Phase 6 — Chat and Files (planned)
## Phase 7 — Time Tracking and Reports (planned)
## Phase 8 — Billing (planned)
## Phase 12 — Launch Readiness (planned)

*(See previous checklist versions for full Phase 3–12 detail)*
