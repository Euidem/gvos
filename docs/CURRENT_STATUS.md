# GVOS — Current Status

**Last Updated:** 2026-05-29
**Current Phase:** Phase 2 — People and Organization Foundation ✅ Complete

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
- **Blade CDN:** Tailwind CDN — Phase 0/1/2 staging only
- **Node/npm:** Not required for Phase 0/1/2
- **Filament panel:** `/admin` — `canAccessPanel()` restricts to super_admin + operations_admin
- **Status middleware:** `check.status` blocks suspended/inactive users from dashboards
- **Timezones:** 11-option dropdown list, default `Africa/Lagos`
- **Role labels:** Friendly labels in UI; slug values stored in DB
- **Filament nav groups:** "User Management" (Users), "People & Organizations" (Companies, Departments, Profiles)
- **Stub profiles:** Creating a user via Filament auto-creates a stub profile row for talent/manager/client roles
- **GetVirtual:** Removed from all visible app UI (Blade views, layouts, dashboards). Internal docs only.

---

## Next Steps

1. cPanel: run `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`
2. Test: create a Company and Department in Filament
3. Test: create a Talent user — confirm TalentProfile stub created automatically
4. Test: talent dashboard shows talent profile status card
5. Test: all dashboards show Phase 2 notice
6. Test: no "GetVirtual" text visible in app UI
7. Get Phase 2 approval
8. Begin Phase 3: Leads and Trial Flow
