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

### Public Lead Form
- [ ] `GET /request-service` loads — shows GVOS branding, 4-section form
- [ ] "Business" radio selection toggles company fields visible
- [ ] "Other" role selection shows free-text role field
- [ ] All required fields validated (first_name, last_name, email, client_type)
- [ ] Valid form submission creates a `lead_requests` row with status = 'new'
- [ ] Redirects to `/request-service/success` — shows GVOS branded confirmation
- [ ] `audit_logs` has `lead_request.created` entry with actor_id = null
- [ ] Non-authenticated users can access the form (no login required)

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

## Phase 4 — Workspace Engine (planned)
## Phase 5 — Task Board (planned)
## Phase 6 — Chat and Files (planned)
## Phase 7 — Time Tracking and Reports (planned)
## Phase 8 — Billing (planned)
## Phase 12 — Launch Readiness (planned)
