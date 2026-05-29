# GVOS — Current Status

**Last Updated:** 2026-05-29
**Current Phase:** Phase 3 — Leads and Trial Flow ✅ Complete (UX upgrade applied)

---

## Phase 0 Status — Complete ✅

All Phase 0 objectives confirmed working on cPanel staging.

---

## Phase 1 Status — Complete ✅ (patch applied 2026-05-29)

### Phase 1 Core
- [x] user_profiles table — extended profile data
- [x] audit_logs table — immutable event trail
- [x] UserProfile and AuditLog models
- [x] AuditLogger service
- [x] CheckAccountStatus middleware
- [x] ProfileController (show + update)
- [x] PasswordController (change password + audit log)
- [x] Filament UserResource (view, create, edit, filters)
- [x] Profile editing page at /profile
- [x] Account status holding page
- [x] All 8 dashboards improved

### Phase 1 Patch
- [x] Spatie role middleware aliases registered in bootstrap/app.php
- [x] first_name / last_name in Filament user create/edit forms
- [x] Friendly role labels in Filament UI
- [x] Timezone 11-option dropdown (default: Africa/Lagos)
- [x] Improved audit logging with before-snapshot pattern

---

## Phase 2 Status — Complete ✅ (2026-05-29)

### PART A — GetVirtual removed from visible UI
- [x] Login page: "GetVirtual Operations System" → "Operations Management Platform"
- [x] Forgot password page: same replacement
- [x] Register page: same replacement + "GetVirtual administrators" → "administrators"
- [x] Account status page: same replacement
- [x] Sidebar layouts: "GetVirtual Operations" → "Managed Operations"
- [x] Login monitoring notice: references to GetVirtual removed
- [x] Active lead dashboard: GetVirtual email removed
- [x] UserResource timezone comment: updated

### PART B — Companies
- [x] Migration: `companies` table (soft deletes, status enum, timezone, email domain)
- [x] Model: `Company` with departments + clientProfiles relationships, SoftDeletes
- [x] Filament: `CompanyResource` (list, create, edit, filters, no delete)

### PART C — Departments
- [x] Migration: `departments` table (company_id FK, status enum)
- [x] Model: `Department` with company + clientProfiles relationships
- [x] Filament: `DepartmentResource` (list, create, edit, filters, no delete)

### PART D — Client Profiles
- [x] Migration: `client_profiles` table (user_id, company_id, department_id FKs)
- [x] Model: `ClientProfile` with user, company, department relationships
- [x] Filament: `ClientProfileResource` (list, create, edit, filters, no delete)

### PART E — Talent Profiles
- [x] Migration: `talent_profiles` table (training_status, equipment_status enums)
- [x] Model: `TalentProfile` with user relationship
- [x] Filament: `TalentProfileResource` (list, create, edit, filters, no delete)

### PART F — Manager Profiles
- [x] Migration: `manager_profiles` table (capacity_limit, current_load)
- [x] Model: `ManagerProfile` with user relationship
- [x] Filament: `ManagerProfileResource` (list, create, edit, filters, no delete)

### PART G — User Model Relationships
- [x] User::clientProfile() — hasOne ClientProfile
- [x] User::talentProfile() — hasOne TalentProfile
- [x] User::managerProfile() — hasOne ManagerProfile

### PART H — Filament Resources (all 5)
- [x] Navigation group: "People & Organizations"
- [x] Role-based access: super_admin + operations_admin can view/create/edit; no delete
- [x] Before-snapshot audit pattern on all Edit pages
- [x] Friendly status badges and labels throughout

### PART I — UserResource CreateUser Profile Stub
- [x] When creating a Talent user → stub TalentProfile created (pending / not_started)
- [x] When creating a Line Manager → stub ManagerProfile created (pending)
- [x] When creating any Client user → stub ClientProfile created (pending)

### PART J — Dashboard Updates
- [x] Super Admin + Ops Admin: count cards for companies, talent, managers, clients
- [x] Talent dashboard: talent profile status card (training, equipment, code)
- [x] Line Manager dashboard: manager profile status card (capacity, code)
- [x] Individual Client dashboard: client profile status card
- [x] Business Client Admin dashboard: company card with status
- [x] Business Client Staff dashboard: staff profile card
- [x] All dashboards: Phase 1 notice updated to Phase 2 notice

### PART K — Audit Logger
- [x] 10 new convenience wrappers: company.created/updated, department.created/updated,
      client_profile.created/updated, talent_profile.created/updated,
      manager_profile.created/updated

### PART L — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] DATABASE_SCHEMA.md updated (Phase 2 tables added, planned tables updated)
- [x] PERMISSION_MATRIX.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md reviewed (no new blocking issues)

---

## Phase 3 Status — Complete ✅ (2026-05-29)

### PART A — `lead_requests` table
- [x] Migration: id, lead_code, first/last name, email, phone, country, city, timezone
- [x] client_type enum (individual/business), company fields, role_needed enum, role_needed_other
- [x] estimated_hours_per_week, preferred_start_date, preferred_work_schedule
- [x] required_skills (text), work_description (longText), budget_range, source
- [x] status enum (11 values: new→converted/lost/disqualified), admin_notes, soft deletes

### PART B — `price_estimates` table
- [x] Migration: lead_request_id FK, currency enum (USD/GBP/EUR/NGN), estimated_amount decimal
- [x] billing_cycle enum (bi_weekly/monthly), estimated_hours_per_week, role_needed
- [x] status enum (draft/sent/accepted/rejected/expired), accepted_at, expires_at, notes

### PART C — `trials` table
- [x] Migration: trial_code, lead_request_id FK, three separate user FKs (active_lead/talent/manager)
- [x] price_estimate_id FK, status enum (pending/approved/active/completed/expired/cancelled/converted)
- [x] starts_at, ends_at, trial_duration_hours (default 24), trial_task_limit (default 3)
- [x] trial_file_limit_mb (default 100), notes

### PART D — Public Lead Form (UX-upgraded 2026-05-29)
- [x] `LeadRequestController` with TIMEZONES, ROLES, BUDGET_RANGES constants
- [x] Timezone validation updated: `nullable, string, max:100` (accepts custom / "Other" values)
- [x] GET/POST `/request-service` routes (no auth required)
- [x] GET `/request-service/success` route
- [x] `resources/views/lead/request-service.blade.php` — 4-step guided multi-step form
  - Step 1: Your Details (name, email, phone, country, city, timezone with Other option)
  - Step 2: Support Needed (client type cards, business fields, role cards with icons)
  - Step 3: Work Details (hours, start date, schedule, skills, description)
  - Step 4: Final Details (budget cards with sub-labels, source, privacy note, submit)
  - Progress bar and step indicator in gradient header
  - Trust side panel with hero copy, benefit bullets, "What happens next"
  - CSS-only illustration panel (client → talent → trial → tracked flow)
  - Vanilla JS multi-step logic, client-side validation on required fields
  - "Other" timezone: shows free-text input, JS copies value to hidden field on submit
  - Mobile responsive: two-column desktop, stacked mobile (side panel below form)
  - Server-side Laravel errors trigger correct step restoration on reload
- [x] `resources/views/lead/request-service-success.blade.php` — improved success page
  - Emerald accent stripe, double-ring success icon
  - "What happens next" 4-step card
  - Sign In + Submit Another actions
- [x] `resources/views/components/layouts/public.blade.php` — scrollable public layout

### PART E — `LeadRequestResource` (Filament)
- [x] Navigation group: "Leads & Trials", sort 1
- [x] Navigation badge: count of 'new' leads (warning color)
- [x] Global search: first_name, last_name, email, company_name
- [x] Full form (4 sections) + table with status badges and filters
- [x] 7 table actions: Edit, Under Review, Price Estimated, Price Accepted, Approve Trial, Lost, Disqualify
- [x] Approve Trial: creates/finds user, assigns active_lead role, creates ClientProfile stub, creates Trial record
- [x] Pages: ListLeadRequests, CreateLeadRequest, EditLeadRequest (with before-snapshot audit)

### PART F — `PriceEstimateResource` (Filament)
- [x] Navigation group: "Leads & Trials", sort 2
- [x] Form + table with status badges and filters
- [x] 4 table actions: Mark Sent, Mark Accepted (updates lead to price_accepted), Mark Rejected, Mark Expired
- [x] Creating an estimate auto-advances lead from new/under_review → price_estimated
- [x] Pages: ListPriceEstimates, CreatePriceEstimate, EditPriceEstimate

### PART G — `TrialResource` (Filament)
- [x] Navigation group: "Leads & Trials", sort 3
- [x] Full form + table with status badges
- [x] 5 table actions: Start Trial (sets starts_at/ends_at, lead→trial_active), Complete, Expire, Cancel, Payment Pending
- [x] Pages: ListTrials, CreateTrial, EditTrial

### PART H — Active Lead Dashboard
- [x] Shows lead request summary (role, hours, status)
- [x] Trial status card with countdown (hours remaining) for active trials
- [x] Approved/completed/expired/cancelled trial state messages
- [x] Team card: assigned talent and manager names
- [x] Price estimate card (amount, currency, billing cycle)
- [x] Payment pending CTA when trial complete or payment_pending
- [x] Trial workspace placeholder for future phases
- [x] Graceful fallback when no trial exists

### PART I — Super Admin + Ops Admin Dashboards
- [x] Lead pipeline section: Total, New, Under Review, Trial Approved, Trial Active, Payment Pending
- [x] Each card links to filtered admin lead list
- [x] Phase 2 notice updated to Phase 3 notice

### PART J — AuditLogger Wrappers (12 new)
- [x] leadRequestCreated, leadRequestUpdated, leadRequestStatusChanged
- [x] priceEstimateCreated, priceEstimateUpdated, priceEstimateAccepted
- [x] trialCreated, trialUpdated, trialStarted, trialCompleted, trialCancelled, trialPaymentPending

### PART K — Model Relationships
- [x] LeadRequest: hasMany priceEstimates, hasMany trials; helper methods
- [x] PriceEstimate: belongsTo leadRequest; formattedAmount()
- [x] Trial: belongsTo leadRequest/priceEstimate/activeLeadUser/assignedTalent/assignedManager
- [x] Trial: isActive(), hoursRemaining() helpers
- [x] User: hasMany activeLeadTrials, assignedTalentTrials, assignedManagerTrials

### PART L — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] DATABASE_SCHEMA.md updated (3 new Phase 3 tables, planned tables updated)
- [x] PERMISSION_MATRIX.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md reviewed

---

## Admin Credentials (Staging Only)

> ⚠️ Change these before any production use.

| Field | Value |
|-------|-------|
| Email | admin@gvos.local |
| Password | password |
| Role | super_admin |
| Portal | /admin (Filament Ops Console) |

---

## cPanel — Commands to Run After Each Pull

```bash
git pull origin main
php artisan migrate
php artisan optimize:clear
php artisan permission:cache-reset
```

---

## Architecture Notes

- **Role middleware:** `role:X` aliases registered via `bootstrap/app.php`
- **Auth guard:** `web` (session)
- **Blade CDN:** Tailwind CDN — Phase 0/1/2/3 staging only
- **Node/npm:** Not required for Phase 0/1/2/3
- **Filament panel:** `/admin` — `canAccessPanel()` restricts to super_admin + operations_admin
- **Status middleware:** `check.status` blocks suspended/inactive users from dashboards
- **Timezones:** 11-option dropdown + "Other" free-text on public form; any string accepted via controller; Filament user form still uses the 11-option list
- **Role labels:** Friendly labels in UI; slug values stored in DB
- **Filament nav groups:** "User Management" (Users), "People & Organizations" (Companies, Departments, Profiles), "Leads & Trials" (Lead Requests, Price Estimates, Trials)
- **Stub profiles:** Creating a user via Filament auto-creates a stub profile row for talent/manager/client roles
- **GetVirtual:** Removed from all visible app UI (Blade views, layouts, dashboards). Internal docs only.
- **Public form:** `/request-service` — no auth, CSRF protected, GVOS branding only
- **Active lead user creation:** Approve Trial action creates user with random password; they must use password reset to log in

---

## Next Steps

1. cPanel: `git pull origin main && php artisan optimize:clear` (no new migrations in this UX update)
2. Test `/request-service`: multi-step form loads, progress bar visible, GVOS branding only
3. Test step navigation: Next/Back, progress bar updates, step label updates
4. Test step 2 toggles: Business fields visible only for Business; role Other text field appears correctly
5. Test timezone Other: selecting Other shows custom field, submitted value stored in lead_requests.timezone
6. Test full form submission: lead_requests row created with all fields
7. Test `/request-service/success`: styled success page with "What happens next" card
8. Test with intentionally blank first_name/email: front-end highlights field, does not advance step
9. Test on mobile viewport: stacked layout, full-width buttons, no horizontal scroll
10. Test Filament lead pipeline still works after UX update (no functional changes to back-end)
11. Get Phase 3 (UX upgrade) sign-off
12. Begin Phase 4: Workspace Foundation
