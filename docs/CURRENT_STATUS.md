# GVOS — Current Status

**Last Updated:** 2026-06-06
**Current Phase:** Phase 11 - Notifications and Email System Foundation - Complete
**Current Activity:** Phase 11 implemented; billing, payment confirmation, and password vault encryption remain unchanged

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

## Phase 4 Status — Complete ✅ (2026-05-29)

### PART A — Country dropdown cleanup
- [x] `app/Support/CountryList.php` created with 21 country options
- [x] `CompanyResource.php` — country TextInput → searchable Select
- [x] `resources/views/profile/edit.blade.php` — country text input → select dropdown
- [x] `resources/views/lead/request-service.blade.php` — country text input → select dropdown

### PART B — `workspaces` table
- [x] Migration: workspace_code, lead_request/trial/company/client_profile FKs, primary_manager_id, primary_talent_id
- [x] name, description, status enum (pending/active/paused/completed/cancelled), type enum (trial/ongoing/project)
- [x] starts_at, ends_at, task_limit, file_limit_mb, notes, soft deletes

### PART C — `workspace_members` table
- [x] Migration: workspace_id FK, user_id FK, role enum (client/talent/manager/observer)
- [x] status enum (active/removed), joined_at, removed_at, notes
- [x] Unique constraint on (workspace_id, user_id)

### PART D — Models
- [x] `Workspace` model: SoftDeletes, all fillable, statusLabels/typeLabels, generateCode(), isActive()
- [x] `WorkspaceMember` model: roleLabels(), workspace/user relationships
- [x] `User` model: workspaceMemberships(), managedWorkspaces(), talentWorkspaces()
- [x] `Trial` model: workspace() HasOne added
- [x] `LeadRequest` model: workspaces() HasMany added
- [x] `Company` model: workspaces() HasMany added

### PART E — WorkspaceResource (Filament)
- [x] Nav group: "Workspace", sort 1
- [x] Full form: identity, linked records, team assignment, dates & limits, notes
- [x] Table with status/type badges, manager/talent columns
- [x] 3 table actions: Activate, Pause, Complete (with audit logging)
- [x] Pages: ListWorkspaces, CreateWorkspace (auto-generates code), EditWorkspace (before-snapshot audit)

### PART F — WorkspaceMembersRelationManager
- [x] Attached to WorkspaceResource Edit page
- [x] Add Member action → audit log workspace.member_added
- [x] Edit member action → audit log workspace.member_updated
- [x] Remove action (soft, sets status=removed, removed_at=now) → audit log workspace.member_removed

### PART G — "Create Workspace" action in TrialResource
- [x] Visible on approved/active/completed trials without existing workspace
- [x] Creates Workspace with auto-generated code, copies trial team/limits
- [x] Auto-adds active lead, talent, manager as workspace members with correct roles
- [x] Fires `trial.workspace_created` audit log entry

### PART H — Workspace Blade pages + Controller + Routes
- [x] `WorkspaceController` (index + show; 403 if not member or primary team)
- [x] `workspace/index.blade.php` — card grid with status/type badges, empty state
- [x] `workspace/show.blade.php` — status banner, team card, schedule card, members list, placeholder
- [x] Routes: GET `/workspaces`, GET `/workspaces/{workspace}` (auth + check.status)

### PART I — Dashboard updates (8 dashboards)
- [x] Super Admin: workspace active/total count card; Phase 4 notice
- [x] Operations Admin: workspace active/total count card; Phase 4 notice
- [x] Talent: "My Workspaces" card with count link; Phase 4 notice
- [x] Line Manager: "My Workspaces" card with count link; Phase 4 notice
- [x] Individual Client: "My Workspace" card with count link; Phase 4 notice
- [x] Business Client Admin: "My Workspace" card with count link; Phase 4 notice
- [x] Business Client Staff: "Workspace Access" card with count link; Phase 4 notice
- [x] Active Lead: live workspace link card (when workspace exists) or placeholder (when not)

### PART J — AuditLogger (7 new wrappers)
- [x] workspaceCreated, workspaceUpdated, workspaceStatusChanged
- [x] workspaceMemberAdded, workspaceMemberUpdated, workspaceMemberRemoved
- [x] trialWorkspaceCreated

### PART K — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md reviewed

---

---

## UI Fidelity Audit — Complete ✅ (2026-05-29)

### Overview
A complete audit of all implemented Blade views against the GVOS Stitch design reference was conducted. 18 files were updated to align typography, colour tokens, icon system, spacing, and component patterns with the Stitch design system. No new features, no backend changes, no database migrations.

### Files Updated (18 total)

| File | Change |
|------|--------|
| `resources/views/components/layouts/auth.blade.php` | Full rewrite — GVOS tokens, Google Fonts, Material Symbols, variant prop |
| `resources/views/components/layouts/gvos.blade.php` | Full rewrite — 280px sidebar, GVOS tokens, nav icons, user footer, top bar |
| `resources/views/components/layouts/public.blade.php` | Full GVOS Tailwind config, Google Fonts, Material Symbols added |
| `resources/views/auth/login.blade.php` | Redesigned with GVOS card pattern, secondary scheme, Material Symbols |
| `resources/views/auth/forgot-password.blade.php` | Redesigned with GVOS tokens, dark visual header |
| `resources/views/account/status.blade.php` | Redesigned with GVOS alert/card patterns |
| `resources/views/dashboard/super-admin.blade.php` | Full token alignment |
| `resources/views/dashboard/operations-admin.blade.php` | Full token alignment |
| `resources/views/dashboard/talent.blade.php` | Full token alignment |
| `resources/views/dashboard/line-manager.blade.php` | Full token alignment |
| `resources/views/dashboard/individual-client.blade.php` | Full token alignment |
| `resources/views/dashboard/business-client-admin.blade.php` | Full token alignment |
| `resources/views/dashboard/business-client-staff.blade.php` | Full token alignment |
| `resources/views/dashboard/active-lead.blade.php` | Full token alignment |
| `resources/views/workspace/index.blade.php` | Full token alignment |
| `resources/views/workspace/show.blade.php` | Full token alignment |
| `resources/views/profile/edit.blade.php` | Full token alignment |
| `resources/views/lead/request-service.blade.php` | indigo → secondary token replacement throughout |

### Design System Changes Applied

- **Fonts:** All 4 layouts now load Manrope (600/700/800) + Inter (400–700) + JetBrains Mono (500) from Google Fonts
- **Icons:** All SVG icon paths replaced with Material Symbols Outlined font
- **Color tokens:** indigo-600 (#4F46E5) → secondary (#0058be); emerald/amber/red/violet/sky → status-* tokens
- **Sidebar:** 256px → 280px, bg-slate-900 → bg-sidebar-bg (#0B0F19), GVOS logo with hub icon and "GVOS Platform" / "Enterprise Ops" branding
- **Active nav state:** bg-white/10 + border-l-4 border-secondary-fixed + text-secondary-fixed font-bold
- **Top bar:** h-16 sticky, bg-surface-container-lowest border-b border-border-subtle, notification bell + security icon + user avatar
- **Cards:** `bg-white rounded-xl border border-border-subtle shadow-card` (0px 4px 20px rgba(0,0,0,0.04))
- **Border radius:** rounded-2xl removed throughout; Stitch maximum is rounded-xl (0.75rem)
- **Primary buttons:** `bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary`
- **Status badges:** `bg-status-*/10 text-status-* border border-status-*/20`
- **Tailwind CDN safeguard:** Hidden `<div>` added to gvos.blade.php — ensures dynamic PHP-conditional classes always compile

### Commit
`c472ebb` — "UI Fidelity Audit: align all Blade views with Stitch GVOS design system"
Pushed: `0480f26..c472ebb main -> main`

---

## UI Fidelity Audit v2 — Complete ✅ (2026-05-29)

### Root Cause Identified and Fixed
The Tailwind CDN script was loading **before** `tailwind.config` was defined in all three component layout files. Per Tailwind CDN documentation, the config must be set **before** the CDN `<script>` tag loads. As a result, all custom GVOS tokens (`bg-sidebar-bg`, `text-secondary`, `border-border-subtle`, `shadow-card`, etc.) were not generated — the CDN compiled with default settings only.

### What Changed

| Area | Fix |
|------|-----|
| All 3 component layouts | `tailwind.config` moved to BEFORE `<script src="cdn.tailwindcss.com">` |
| All 3 component layouts | Comprehensive GVOS CSS token fallback block added to `<style>` |
| All 3 component layouts | `<!-- GVOS UI Fidelity v2 active -->` HTML comment added |
| `layouts/gvos.blade.php` (legacy) | Replaced with component redirect wrapper |
| `layouts/auth.blade.php` (legacy) | Replaced with component redirect wrapper |
| `auth/confirm-password.blade.php` | Full rewrite — indigo/slate removed, GVOS tokens applied |
| `auth/reset-password.blade.php` | Full rewrite — indigo removed, GVOS tokens applied |
| `auth/verify-email.blade.php` | Full rewrite — indigo removed, GVOS tokens applied |
| `auth/register.blade.php` | Full rewrite — indigo removed, GVOS tokens applied |
| `lead/request-service-success.blade.php` | Full rewrite — indigo/slate/emerald removed, GVOS tokens applied |
| `lead/request-service.blade.php` | All remaining indigo/violet replaced with GVOS tokens (PHP + JS) |

### CSS Fallback Coverage
Added hardcoded CSS rules (`.bg-sidebar-bg { ... }` etc.) as a safety net to all 3 layouts. Covers all GVOS custom tokens including opacity variants (`bg-secondary/5`, `bg-status-active/10`, etc.), hover/focus/active utilities, and border/shadow tokens.

### How to Verify from Browser Source
`View Source` any page and search for `GVOS UI Fidelity v2 active` — if found, the updated layout is rendering.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
```
No migrations needed.

---

---

## UI Visual Repair v3 — Complete ✅ (2026-05-30)

### Root Cause Identified and Fixed
After v2 made tokens visible, the login page still looked broken: heading touching the card edge, cramped form fields, sidebar color gone. Root cause: custom Tailwind **spacing tokens** (`p-card-padding`, `space-y-input-gap`, `px-card-padding`, etc.) and **color tokens** (`bg-sidebar-bg`, `bg-background`) on critical layout elements were failing to render despite the CDN config and CSS fallback. The CDN JIT can miss spacing tokens; CSS fallback rules can lose to Tailwind's specificity. Inline styles and standard Tailwind utilities are the only 100% reliable approach.

### What Changed

| File | Fix |
|------|-----|
| `components/layouts/auth.blade.php` | Body: `bg-sidebar-bg` → `style="background-color:#0B0F19"` on dark variant; version marker → v3 |
| `components/layouts/gvos.blade.php` | Body: `bg-background` → `style="background-color:#f7f9fb"`; Sidebar: `bg-sidebar-bg` → `style="background-color:#0B0F19"`; Main: `bg-background` → `style="background-color:#F8FAFC"`; version marker → v3 |
| `components/layouts/public.blade.php` | Body: `bg-sidebar-bg` → `style="background-color:#0B0F19"`; version marker → v3 |
| `auth/login.blade.php` | `p-card-padding` → `p-8`; `space-y-input-gap` → `space-y-5`; `px-card-padding pb-card-padding` → `px-8 pb-8` |
| `auth/forgot-password.blade.php` | `bg-sidebar-bg` on visual header → `style="background-color:#0B0F19"`; `p-card-padding` → `p-8`; `gap-input-gap` → `gap-5` |
| `account/status.blade.php` | `p-card-padding` → `p-8`; `px-card-padding pb-card-padding` → `px-8 pb-8` |

### Key Rule Going Forward
- Use `style="background-color:..."` or `bg-[#hex]` for all critical page/sidebar/section backgrounds — never `bg-{custom-token}` alone on structural elements
- Use standard Tailwind spacing utilities (`p-8`, `space-y-5`, `gap-5`) for card padding and form spacing — never custom spacing tokens on structural elements
- Custom tokens (via `tailwind.config` or CSS fallback) are fine for non-structural visual accents (badges, status pills, text colours)

### How to Verify from Browser Source
`View Source` any page and search for `GVOS UI Visual Repair v3 active` — if found, the updated layout is rendering. Sidebar and page background should be visibly dark navy (#0B0F19) on all auth and lead form pages.

### cPanel Commands
```bash
git pull origin main
php artisan optimize:clear
php artisan view:clear
```
No migrations needed.

---

---

## Phase 5 Status — Complete ✅ (2026-05-30)

### PART A — Migrations (2 new)
- [x] `2024_01_06_000001_create_workspace_tasks_table.php` — task_code, workspace_id, created_by_user_id, assigned_to_user_id, title, description, priority enum (low/normal/high/urgent), status enum (8 values), due_date, lifecycle timestamps, sort_order, internal_notes, soft deletes, indexes
- [x] `2024_01_06_000002_create_workspace_task_comments_table.php` — workspace_task_id, user_id, comment, visibility enum (public/internal), soft deletes, index

### PART B — Models (2 new, 2 updated)
- [x] `app/Models/WorkspaceTask.php` — SoftDeletes; statusLabels(), priorityLabels(), allowedTransitions(fromStatus, role), generateCode(), isOpen(), isDueSoon(), isOverdue(); relationships: workspace, createdBy, assignedTo, comments
- [x] `app/Models/WorkspaceTaskComment.php` — SoftDeletes; isInternal(), isPublic() helpers; relationships: task, user
- [x] `app/Models/Workspace.php` — added tasks() and openTasks() HasMany relationships
- [x] `app/Models/User.php` — added createdWorkspaceTasks(), assignedWorkspaceTasks(), workspaceTaskComments() HasMany relationships

### PART C–D — Task Access and Status Flow
- [x] Role-based access enforced in WorkspaceTaskController via private helper `getUserWorkspaceRole()`
- [x] 8 statuses: pending, in_progress, blocked, submitted, revision_requested, approved, closed, cancelled
- [x] `allowedTransitions()`: admin/manager have broad freedom; talent can self-advance and submit; client can approve/request revision/close; observer/none: none

### PART E — workspace/show.blade.php updated
- [x] Replaced "coming soon" placeholder with real task board summary
- [x] Shows open task count, status count chips linking to board, preview of 4 open tasks, "New Task" + "View All" links (role-gated)

### PART F–G — Routes and Controller and Blade Views
- [x] 8 nested routes under `workspaces/{workspace}/tasks` (all auth + check.status)
- [x] `app/Http/Controllers/WorkspaceTaskController.php` — index, create, store, show, edit, update, storeComment, updateStatus
- [x] `resources/views/workspace/tasks/index.blade.php` — horizontal scrollable 7-column kanban board, status columns with task cards, priority badges, assignee avatars, comment counts
- [x] `resources/views/workspace/tasks/create.blade.php` — task creation form with internal notes for admin/manager
- [x] `resources/views/workspace/tasks/show.blade.php` — task detail with status action buttons (confirm dialog), comment thread, sidebar meta
- [x] `resources/views/workspace/tasks/edit.blade.php` — task edit form, pre-filled

### PART H — Filament WorkspaceTaskResource
- [x] Nav group: "Workspace", sort 2; navigation badge showing open task count (warning color)
- [x] Full form + table with status/priority badges
- [x] Archive table action (soft delete); no hard delete
- [x] CreateWorkspaceTask page: auto-sets created_by_user_id + task_code
- [x] EditWorkspaceTask page: before-snapshot audit, logs status change + assignment change events

### PART I — Dashboard Updates (all 7 dashboards)
- [x] Super Admin: taskTotal, taskOpen, taskBlocked, taskSubmitted count grid; Phase 5 notice
- [x] Operations Admin: same task count grid; Phase 5 notice
- [x] Talent: myAssignedTasks, myBlockedTasks, myDueSoonTasks; conditional "My Tasks" section; Phase 5 notice
- [x] Line Manager: managerTasksOpen, managerTasksSubmitted; Task Board card made active; task summary grid; Phase 5 notice
- [x] Individual Client: clientOpenTasks, clientSubmittedTasks; conditional task summary; Phase 5 notice
- [x] Business Client Admin: same as individual client; Phase 5 notice
- [x] Business Client Staff: same as individual client; Phase 5 notice

### PART J — AuditLogger (7 new wrappers)
- [x] workspaceTaskCreated, workspaceTaskUpdated, workspaceTaskStatusChanged
- [x] workspaceTaskAssigned, workspaceTaskCommentAdded, workspaceTaskInternalCommentAdded, workspaceTaskDeleted

### PART K — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] DATABASE_SCHEMA.md updated (Phase 4 actual schema, Phase 5 tables documented, planned tables updated)
- [x] PERMISSION_MATRIX.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md updated

---

---

## Phase 5 Improvement — Kanban Drag & Drop ✅ (2026-05-30)

### What was improved
- [x] Task board index (`workspace/tasks/index.blade.php`) fully redesigned as an interactive Kanban board
- [x] SortableJS CDN added to task board page only (not globally)
- [x] Drag handle (`drag_indicator` icon) on each task card — only authorized roles see it
- [x] Cards draggable between columns; dropped card triggers AJAX POST to existing status route
- [x] `WorkspaceTaskController@updateStatus` now returns JSON when request expects JSON; form behavior unchanged
- [x] Backend role permissions enforced on every drag — invalid moves return 403 JSON and card reverts
- [x] Visual feedback: ghost placeholder, lifted dragging card, column drop highlight, toast notifications
- [x] Optimistic UI: column counts update immediately; reverted on failure
- [x] `workspace/show.blade.php` updated: "Open Kanban Board" button, 4 task metric cards (Total/Open/Blocked/Awaiting Review), improved status chips with color coding
- [x] No database changes made

---

---

## Phase 5 Fix — Workspace Access Bug ✅ (2026-05-30)

### Root Cause

Primary managers and primary talent could not access the Kanban board or workspace detail page despite being assigned as `primary_manager_id` / `primary_talent_id`. Two bugs were found:

1. **Strict `===` type mismatch** — Eloquent returns `primary_manager_id` and `primary_talent_id` as strings from the database (no integer cast defined on the model). `$user->id` is an integer. PHP's strict `===` comparison between a string and an integer always returns `false`. So the `===` checks in `WorkspaceTaskController::getUserWorkspaceRole()` silently failed even when the IDs matched.

2. **Missing admin check in `WorkspaceController::show()`** — Super admins and operations admins would get a 403 on the workspace detail page because only member/primary checks were performed.

3. **Missing task-assignment fallback** — A user assigned to a task but without a workspace member row could not view that task.

### What Was Fixed

| File | Change |
|------|--------|
| `app/Models/Workspace.php` | Added `resolveUserWorkspaceRole(User $user): string` — 5-tier role resolver using `(int)` casts to fix the type mismatch. Added `userHasAccess()`, `userCanCreateTasks()`, `userCanManageTasks()`, `userCanViewInternalTaskNotes()` helper methods. Added `syncPrimaryTeamToMembers()` — creates or reactivates member rows for primary manager and primary talent. |
| `app/Http/Controllers/WorkspaceController.php` | `index()` rewritten: admins see all workspaces; non-admins see member + primary + task-assigned workspaces. `show()` now delegates to `$workspace->userHasAccess($user)`. |
| `app/Http/Controllers/WorkspaceTaskController.php` | Removed broken private `getUserWorkspaceRole()`. Uses `$workspace->resolveUserWorkspaceRole($user)`. Added `transitionRole()` to map `assigned_user` → `talent`. Task `show()` now allows task-assigned users without workspace access to view their specific task. |
| `app/Filament/Resources/WorkspaceResource.php` | Added "Sync Team" table action — creates/reactivates member rows for primary manager and primary talent with audit logging and Filament success notification. |
| `app/Filament/Resources/WorkspaceResource/Pages/EditWorkspace.php` | Added "Sync Primary Team" header action. `afterSave()` now auto-syncs primary team to member rows whenever primary_manager_id or primary_talent_id is set. |
| `app/Services/AuditLogger.php` | Added `workspacePrimaryTeamSynced()` wrapper. |

### Role Resolution Priority (new — `Workspace::resolveUserWorkspaceRole()`)

| Priority | Condition | Role returned |
|----------|-----------|---------------|
| 1 | User has `super_admin` or `operations_admin` system role | `admin` |
| 2 | `primary_manager_id` matches user (int-cast comparison) | `manager` |
| 3 | `primary_talent_id` matches user (int-cast comparison) | `talent` |
| 4a | Active workspace member row with `role=manager` | `manager` |
| 4b | Active workspace member row with `role=talent` | `talent` |
| 4c | Active workspace member row with `role=client` | `client` |
| 4d | Active workspace member row with `role=observer` | `observer` |
| 5 | Assigned to a task in this workspace | `assigned_user` |
| — | None of the above | `none` → 403 |

### Access Paths Now Working

- [x] Super admin / operations admin can view any workspace and task board
- [x] Primary manager can view workspace and task board without a member row
- [x] Primary talent can view workspace and task board without a member row
- [x] Active member (any role) can view workspace and task board
- [x] User assigned to a task can view that specific task (even with no member row)
- [x] Observer-role members can view board but cannot drag/create/edit
- [x] `assigned_user` tier maps to `talent` for status transition purposes
- [x] Saving a workspace in Filament auto-syncs primary team to member rows
- [x] "Sync Team" action available in Workspace table + edit page header
- [x] Full audit trail for all sync events

---

---

## Phase 5 Fix 2 — Task Detail 404 and Kanban Drag-Drop Failure ✅ (2026-05-30)

### Root Causes

| # | Bug | Cause |
|---|-----|-------|
| 1 | Task detail page returns 404 | `authorizeTaskBelongsToWorkspace()` used strict `!==` comparison. `$task->workspace_id` is returned as a PHP **string** by PDO (no integer cast on the model). `$workspace->id` is an integer (primary key, auto-cast). `"1" !== 1` is always `true` in PHP → `abort(404)` fired on every task request. |
| 2 | Drag-drop shows "Could not move this task" | Same `abort(404)` in `authorizeTaskBelongsToWorkspace()` was called before the AJAX status update logic. Laravel returned a 404 response → JS could not find a useful `message` field → fallback toast text shown. |
| 3 | `canEdit` and `created_by_user_id` comparisons | `$task->created_by_user_id === $user->id` also had the string/int mismatch, preventing task creators from editing their own pending tasks. |
| 4 | Talent could move tasks not assigned to them | No assignee ownership check existed in `updateStatus()`. |
| 5 | Generic fallback error messages | Frontend only showed "Could not move this task." for all failure types with no useful detail. |

### What Was Fixed

| File | Change |
|------|--------|
| `app/Models/WorkspaceTask.php` | Added integer casts for `workspace_id`, `created_by_user_id`, `assigned_to_user_id`, `sort_order` — fixes ALL type-comparison bugs at the model level. Made `allowedTransitions()` comments more explicit. |
| `app/Http/Controllers/WorkspaceTaskController.php` | `authorizeTaskBelongsToWorkspace()` now uses `(int)` casts AND returns a JSON 404 response when the request expects JSON (so the Kanban board gets a useful message). `updateStatus()` adds talent-assignee restriction (talent can only update tasks assigned to themselves), descriptive transition error messages ("cannot move from X to Y"), `Log::info` entries for all failed transitions. `show()`, `edit()`, `update()` all use explicit `(int)` casts for creator comparison. |
| `resources/views/workspace/tasks/index.blade.php` | Drag handle now only shown to talent on their own assigned tasks or unassigned tasks (admin/manager/client see all handles). Added `X-Requested-With: XMLHttpRequest` header to fetch. Refactored revert logic into `revertCard()` helper. Error handling now shows status-code-aware fallback messages (403/422/404 each get specific text). `.catch()` shows a descriptive "unexpected response" message. Toast max-width increased for longer server messages. |

### Transition Rules (confirmed)

| Role | Can update tasks | Status moves allowed |
|------|-----------------|---------------------|
| admin/manager | Any task in workspace | Any operationally-sensible move + cancel |
| talent | Only tasks assigned to themselves (or unassigned) | pending→in_progress, in_progress→blocked/submitted, blocked→in_progress, revision_requested→in_progress |
| client | Any task in workspace | submitted→approved/revision_requested, approved→closed |
| observer | None | None |

---

---

---

## Phase 5 Fix 3 — Talent Kanban Drag-Drop Permission Fix ✅ (2026-05-30)

### Root Cause

After Fix 2, task detail pages worked and drag handles appeared correctly for talent. But dragging a card still failed. The root causes were:

1. **`updateStatus()` relied solely on `resolveUserWorkspaceRole()`** — In edge cases where the talent user was the primary talent but without a synced member row, or where `resolveUserWorkspaceRole()` returned `'assigned_user'` rather than `'talent'`, the single-signal role check could incorrectly deny access or map to the wrong effective role.

2. **`assigned_user` not in `CAN_DRAG` list** — The Kanban view used `in_array($role, ['admin','manager','talent','client'])` to decide whether to initialise SortableJS. Users resolved as `assigned_user` (no member row, but assigned to a task) saw no drag handles and had SortableJS disabled, even though they should have talent-level drag rights.

3. **No comprehensive logging** — Without server-side logging at every decision point, diagnosing the exact rejection reason required guessing.

### What Was Fixed

| File | Change |
|------|--------|
| `app/Http/Controllers/WorkspaceTaskController.php` | `updateStatus()` completely rewritten with multi-signal role determination (Steps 1–8). Now checks `$isTaskAssignee`, `$isPrimaryTalent`, `$isPrimaryManager` **in addition to** `resolveUserWorkspaceRole()`. Added comprehensive `Log::info('workspace_task.status_update_attempt', [...])` at Step 5 logging all context on every attempt. All rejection paths log to `workspace_task.status_update_denied` with full context: user_id, email, roles, workspace_id, task_id, assigned_to, from/to status, resolved role, effective role, allowed transitions, rejection reason. |
| `resources/views/workspace/tasks/index.blade.php` | Added `'assigned_user'` to `$draggableRoles` and to the `CAN_DRAG` JS expression. Added `'assigned_user'` case to `showDragHandle` match (only shows handle on their own assigned tasks). Added `console.warn('[GVOS Kanban] Drag rejected', {...})` on AJAX failure and `console.warn('[GVOS Kanban] Drag network/parse error', {...})` on `.catch()` — logs taskId, fromStatus, toStatus, httpStatus, response JSON for debugging. |

### Role Determination Logic (new — `updateStatus()` Step 3)

```
if workspaceRole === 'admin'                                              → admin
elif isPrimaryManager OR workspaceRole === 'manager'                     → manager
elif isTaskAssignee OR isPrimaryTalent OR workspaceRole in [talent, assigned_user] → talent
elif workspaceRole === 'client'                                           → client
else                                                                      → 403 + log
```

### Talent Move Rules (Step 6)

| Condition | Can move? |
|-----------|-----------|
| Task is explicitly assigned to this talent | ✅ Yes |
| Task is unassigned AND user is primary talent | ✅ Yes |
| Task is unassigned AND user is NOT primary talent | ❌ No — "Only the primary talent can move unassigned tasks." |
| Task is assigned to a different user | ❌ No — "You can only move tasks assigned to you. This task is assigned to [name]." |

### Transition Rules (unchanged)

| Role | Allowed moves |
|------|--------------|
| admin/manager | Any operationally-sensible move + cancel |
| talent | pending→in_progress, in_progress→blocked/submitted, blocked→in_progress, revision_requested→in_progress |
| client | submitted→approved/revision_requested, approved→closed |
| observer | None |

### Test Scenarios (PART J)

1. **Assigned talent drag** — Talent drags their own task pending→in_progress → succeeds, green toast
2. **Talent submit** — Talent drags in_progress→submitted → succeeds, green toast
3. **Talent invalid move** — Talent drags submitted→approved → 422, red toast with "cannot move from Submitted to Approved. Allowed next statuses: Approved, Revision Requested" (server message)
4. **Primary talent + unassigned task** — Primary talent drags unassigned task → succeeds
5. **Manager approve** — Manager drags submitted→approved → succeeds
6. **Client review** — Client drags submitted→approved or submitted→revision_requested → succeeds

---

## Phase 5 Fix 4 — Workspace Role Expansion ✅ (2026-05-30)

### What Changed

Expanded the workspace role model from 4 values (client/talent/manager/observer) to a full 7-role hierarchy matching the GVOS product spec. Added `workspace_admin`, `client_admin`, and `client_staff` throughout the stack — DB enum, models, controller, Filament relation manager, Kanban view.

| File | Change |
|------|--------|
| `database/migrations/2026_05_30_000001_expand_workspace_members_role_enum.php` | ALTER TABLE to expand `workspace_members.role` ENUM: adds `workspace_admin`, `client_admin`, `client_staff`. Legacy `client`, `manager`, `talent`, `observer` preserved. |
| `app/Models/WorkspaceMember.php` | `roleLabels()` expanded with all 7 roles + labels. `roleLabel()` uses `str_replace` fallback for unrecognised values. |
| `app/Models/Workspace.php` | `resolveUserWorkspaceRole()` rewritten: 7-tier resolution — admin > workspace_admin > primary_manager > member_manager > primary_talent > member_talent/client_admin/client_staff/observer > assigned_user > none. Legacy `client` member row maps to `client_admin`. `userCanCreateTasks()`, `userCanManageTasks()`, `userCanViewInternalTaskNotes()` updated to include `workspace_admin`. |
| `app/Models/WorkspaceTask.php` | `allowedTransitions()` updated: `workspace_admin` gets same broad transitions as admin/manager. `client_admin` (and legacy `client`) can approve/request revision/close. `client_staff`/`observer`/unrecognised → no transitions. |
| `app/Http/Controllers/WorkspaceTaskController.php` | `isAdminOrManager()` includes `workspace_admin`. `transitionRole()` maps `assigned_user`→`talent` and `client`→`client_admin`. `updateStatus()` Step 3 handles all new roles: `workspace_admin`→`workspace_admin` effective; `client_admin`/`client`→`client_admin` effective; `client_staff`/`observer`→403 with specific messages. `index()` passes `$effectiveRole` and `$showDebugRole` to view. `show()` passes `$effectiveRole`. |
| `resources/views/workspace/tasks/index.blade.php` | `$draggableRoles` expanded to include `workspace_admin` and `client_admin`. `showDragHandle` match updated: `workspace_admin` always shows handle; `client_admin`/`client` shows only on submitted/approved tasks; `client_staff`/`observer` never show. `CAN_DRAG` JS expression uses `$draggableRoles`. Debug role line added under board heading for admin/workspace_admin/manager. |
| `app/Filament/Resources/WorkspaceResource/RelationManagers/WorkspaceMembersRelationManager.php` | Role Select default changed from `client` to `talent`. Badge colors added for `workspace_admin` (danger), `client_admin` (warning), `client_staff` (warning). |

### Role Resolution Priority (updated)

| Priority | Condition | Role returned |
|----------|-----------|---------------|
| 1 | User has `super_admin` or `operations_admin` system role | `admin` |
| 2 | Active member row with `role=workspace_admin` | `workspace_admin` |
| 3 | `primary_manager_id` matches user (int-cast) | `manager` |
| 4 | Active member row with `role=manager` | `manager` |
| 5 | `primary_talent_id` matches user (int-cast) | `talent` |
| 6a | Active member row with `role=talent` | `talent` |
| 6b | Active member row with `role=client_admin` | `client_admin` |
| 6c | Active member row with `role=client_staff` | `client_staff` |
| 6d | Active member row with `role=client` (legacy) | `client_admin` |
| 6e | Active member row with `role=observer` | `observer` |
| 7 | Assigned to a task in this workspace | `assigned_user` |
| — | None of the above | `none` → 403 |

### Transition Rights (updated)

| Effective Role | Allowed Moves |
|----------------|---------------|
| admin / workspace_admin / manager | Any operationally-sensible move + cancel |
| talent / assigned_user | pending→in_progress, in_progress→blocked/submitted, blocked→in_progress, revision_requested→in_progress |
| client_admin / client (legacy) | submitted→approved/revision_requested, approved→closed |
| client_staff / observer | None |

### Drag Handle Visibility

| Role | When drag handle shows |
|------|------------------------|
| admin / workspace_admin / manager | Always (all tasks) |
| talent | Tasks assigned to self OR unassigned tasks |
| assigned_user | Only their explicitly assigned task |
| client_admin / client | Only on submitted or approved tasks |
| client_staff / observer | Never |

---

---

## Phase 6 Status — Complete ✅ (2026-05-30)

### PART A — `workspace_messages` table
- [x] Migration: id, workspace_id FK, user_id FK, parent_id nullable self-FK, message longText, visibility enum(public/internal), message_type enum(text/system), edited_at nullable, soft deletes, timestamps
- [x] Indexes: (workspace_id, visibility), parent_id

### PART B — `workspace_files` table
- [x] Migration: id, workspace_id FK, uploaded_by_user_id FK, workspace_task_id nullable FK, title nullable, original_filename, stored_filename, storage_path, mime_type nullable, file_size, visibility enum(public/internal), category nullable, description nullable, downloads_count, soft deletes, timestamps
- [x] Indexes: (workspace_id, visibility), workspace_task_id, uploaded_by_user_id

### PART C — Models (2 new, 3 updated)
- [x] `app/Models/WorkspaceMessage.php` — SoftDeletes; isInternal(), isPublic(), isSystemMessage(), isReply(); relationships: workspace, user, parent, replies
- [x] `app/Models/WorkspaceFile.php` — SoftDeletes; categoryLabels(), allowedMimes(), typeIcon(), formattedSize(), isInternal(), isPublic(); relationships: workspace, uploadedBy (FK: uploaded_by_user_id), task (FK: workspace_task_id)
- [x] `app/Models/Workspace.php` — added messages() and files() HasMany relationships (Phase 6 section)
- [x] `app/Models/WorkspaceTask.php` — added files() HasMany relationship (workspace_task_id FK)
- [x] `app/Models/User.php` — added workspaceMessages() and workspaceFiles() HasMany relationships

### PART D — Access Rules
- [x] `canViewInternal()` → admin, workspace_admin, manager roles only
- [x] `canPost()` → any role except observer/none
- [x] `canUpload()` → any role except observer/none
- [x] `canDelete()` → uploader OR manager/admin tier
- [x] File download: always verifies workspace membership + visibility access before streaming

### PART E & F — Routes and Controllers
- [x] 3 chat routes under `workspaces/{workspace}/chat` (index, store, destroy)
- [x] 4 file routes under `workspaces/{workspace}/files` (index, store, download, destroy)
- [x] 1 task-file route under `workspaces/{workspace}/tasks/{task}/files` (store)
- [x] `app/Http/Controllers/WorkspaceMessageController.php` — index, store, destroy; loads last 100 messages oldest-first; visibility-filtered
- [x] `app/Http/Controllers/WorkspaceFileController.php` — index, store, storeForTask, download, destroy; UUID stored filename; local disk storage; access-verified downloads; increment downloads_count

### PART G — Blade Views (2 new)
- [x] `resources/views/workspace/chat/index.blade.php` — breadcrumb, message list (avatar, name, Internal badge, timestamp, delete button), empty state, post form, observer notice
- [x] `resources/views/workspace/files/index.blade.php` — breadcrumb, upload form (left), file list (right), category/visibility badges, download/delete buttons, empty state

### PART H — workspace/show.blade.php updated
- [x] Chat card with message count (links to workspace.chat.index)
- [x] Files card with file count (links to workspace.files.index)
- [x] Three placeholder cards (Time Tracking, Billing, Password Vault) with dashed borders

### PART I — Task file attachments
- [x] Task show page sidebar: file list with typeIcon, title/filename, size, date, internal badge, download/delete buttons
- [x] Upload form with file input, optional internal checkbox (admin/manager only)

### PART J — Filament Resources (2 new)
- [x] `WorkspaceFileResource` — nav group "Workspace", sort 4; read-only (no create/edit); archive action; filters: visibility, category
- [x] `WorkspaceMessageResource` — nav group "Workspace", sort 5; read-only; moderate/remove action; filters: visibility, message_type
- [x] `ListWorkspaceFiles` and `ListWorkspaceMessages` pages — no header actions

### PART K — AuditLogger (6 new wrappers)
- [x] workspaceMessageCreated, workspaceMessageUpdated, workspaceMessageDeleted
- [x] workspaceFileUploaded (logs original_filename, category, workspace_task_id, uploaded_by_user_id), workspaceFileDownloaded, workspaceFileDeleted

### PART L — Dashboard Updates (all 8 dashboards)
- [x] Super Admin: messageTotal + fileTotal count cards; Phase 6 notice
- [x] Operations Admin: messageTotal + fileTotal count cards; Phase 6 notice
- [x] Talent: "Chat & Files" communication link card (when workspaces > 0); Phase 6 notice
- [x] Line Manager: "Chat & Files" communication link (when managed workspaces exist); Phase 6 notice
- [x] Individual Client: Workspace Chat + Workspace Files link cards (when workspaces > 0); Phase 6 notice
- [x] Business Client Admin: Workspace Chat + Workspace Files link cards; Phase 6 notice
- [x] Business Client Staff: Workspace Chat + Workspace Files link cards; Phase 6 notice
- [x] Active Lead: not updated (lead not yet converted to client/workspace member in Phase 6 scope)

### PART M — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] DATABASE_SCHEMA.md updated (Phase 6 tables documented)
- [x] PERMISSION_MATRIX.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md updated

---

## Architecture Notes (Phase 6 additions)

- **File storage:** `Storage::disk('local')` — files stored at `storage/app/workspaces/{workspace_id}/{uuid}.{ext}`. NOT publicly accessible via URL.
- **File downloads:** All downloads route through `WorkspaceFileController@download` which verifies workspace access + visibility before streaming via `Storage::disk('local')->download()`.
- **Soft delete (files):** Physical file on disk preserved; DB record soft-deleted. Future maintenance command can clean orphaned files.
- **Chat architecture:** Blade form submission only — no AJAX, no WebSockets. Last 100 messages loaded oldest-first.
- **MIME validation:** `WorkspaceFile::allowedMimes()` returns PDF, image (jpg/jpeg/png/gif/webp), Office (doc/docx/xls/xlsx/ppt/pptx), text/csv, zip — max 10 MB.
- **Message visibility:** `canViewInternal()` is true for admin/workspace_admin/manager roles only; clients/talent/observer see public messages only.
- **Task file attachments:** `workspace_task_id` nullable FK on workspace_files; storeForTask() verifies the task belongs to the workspace using int-cast comparison.

---

## Phase 7 Status — Complete ✅ (2026-05-31)

### PART A — Database Migrations (2 new tables)
- [x] `workspace_time_logs` — id, workspace_id FK, user_id FK, workspace_task_id nullable FK, log_date, started_at nullable, ended_at nullable, duration_minutes nullable, work_summary, work_details nullable, status enum(draft/submitted/reviewed/approved/rejected), reviewed_by_user_id nullable FK, reviewed_at nullable, manager_notes nullable, client_visible_summary nullable, visibility enum(internal/client_summary), timestamps, softDeletes
- [x] `workspace_weekly_reports` — id, workspace_id FK, week_start_date, week_end_date, prepared_by_user_id nullable FK, reviewed_by_user_id nullable FK, total_minutes, summary, achievements nullable, blockers nullable, next_steps nullable, client_notes nullable, status enum(draft/submitted/approved/published), published_at nullable, timestamps, softDeletes

### PART B — Models (2 new, 3 updated)
- [x] `app/Models/WorkspaceTimeLog.php` — SoftDeletes; statusLabels(), visibilityLabels(), resolvedDurationMinutes(), durationForHumans(), isClientVisible(); access helpers: canCreate(), canReview(), canViewAll(), isClientRole(); relationships: workspace, user, task, reviewedBy
- [x] `app/Models/WorkspaceWeeklyReport.php` — SoftDeletes; statusLabels(), totalDurationForHumans(), weekLabel(), isPublishedToClients(); visibleStatusesFor(), canCreate(), canApprove(); relationships: workspace, preparedBy, reviewedBy
- [x] `app/Models/Workspace.php` — added timeLogs() and weeklyReports() HasMany (Phase 7 section)
- [x] `app/Models/WorkspaceTask.php` — added timeLogs() HasMany (workspace_task_id FK)
- [x] `app/Models/User.php` — added workspaceTimeLogs(), reviewedWorkspaceTimeLogs(), preparedWeeklyReports(), reviewedWeeklyReports() HasMany

### PART C — Access Rules (reuse resolveUserWorkspaceRole())
- [x] canCreate: admin, workspace_admin, manager, talent, assigned_user
- [x] canReview: admin, workspace_admin, manager
- [x] canViewAll: admin, workspace_admin, manager
- [x] isClientRole: client_admin, client_staff, client (legacy)
- [x] Clients: approved + client_summary logs only; published reports only
- [x] Talent: own logs only; sees submitted/approved/published reports (not draft)
- [x] Observer: no access to time logs or reports

### PART D — Routes (15 new)
- [x] 8 time log routes under `workspaces/{workspace}/time-logs` (index, create, store, show, edit, update, review, destroy)
- [x] 7 weekly report routes under `workspaces/{workspace}/reports` (index, create, store, show, edit, update, destroy)

### PART E — Controllers (2 new)
- [x] `app/Http/Controllers/WorkspaceTimeLogController.php` — index, create, store, show, edit, update, review, destroy; role-filtered query; int-cast FK comparisons
- [x] `app/Http/Controllers/WorkspaceWeeklyReportController.php` — index, create, store, show, edit, update, destroy; suggests last-week dates and auto-computes total_minutes from approved logs

### PART F — Time Log Blade Views (4 new)
- [x] `resources/views/workspace/time-logs/index.blade.php` — filtered table (admin sees all, client sees approved+client_summary, talent sees own); Log Time button
- [x] `resources/views/workspace/time-logs/create.blade.php` — log_date, work_summary, optional task, start/end time, duration override, work_details, draft/submit status
- [x] `resources/views/workspace/time-logs/show.blade.php` — 2-col layout; review form (manager/admin, submitted only); client-visible summary toggle; manager notes; sidebar details
- [x] `resources/views/workspace/time-logs/edit.blade.php` — same fields as create; manager-only visibility and client_visible_summary fields

### PART G — Weekly Report Blade Views (4 new)
- [x] `resources/views/workspace/reports/index.blade.php` — status-filtered list; status badges; New Report button
- [x] `resources/views/workspace/reports/create.blade.php` — auto-suggested week; auto-filled total_minutes from approved logs; summary, achievements, blockers, next_steps, client_notes
- [x] `resources/views/workspace/reports/show.blade.php` — full report display; inline approve/publish quick actions; blockers/next_steps hidden from clients; sidebar metadata
- [x] `resources/views/workspace/reports/edit.blade.php` — all fields editable; manager sees approved/published status options

### PART H — workspace/show.blade.php updated
- [x] Time Logs active card (count filtered by role) links to workspace.time-logs.index
- [x] Weekly Reports active card (count filtered by role) links to workspace.reports.index
- [x] Time Tracking placeholder removed; Billing and Password Vault remain as placeholders (2-col)

### PART I — workspace/tasks/show.blade.php updated
- [x] Sidebar time log section: last 5 logs for the task (date, user, summary, duration, view link)
- [x] "Log Time" button (pre-selects task via query param) — talent/manager/admin only
- [x] Hidden from clients; hidden from observers

### PART J — Filament Resources (2 new)
- [x] `WorkspaceTimeLogResource` — nav group "Workspace", sort 7; read-only from Filament; filters: status, visibility; badge colours per status
- [x] `WorkspaceWeeklyReportResource` — nav group "Workspace", sort 8; read-only from Filament; filter: status; badge colours per status

### PART K — AuditLogger (9 new wrappers)
- [x] timeLogCreated, timeLogUpdated, timeLogReviewed, timeLogDeleted
- [x] weeklyReportCreated, weeklyReportUpdated, weeklyReportDeleted, weeklyReportPublished, weeklyReportStatusChanged

### PART L — Dashboard Updates (all 7 portals)
- [x] All 7 dashboards: Phase notice updated to "Phase 7 — Time Tracking & Work Reports"
- [x] Super Admin / Operations Admin: notice describes talent→manager→client flow
- [x] Talent: notice describes logging work sessions and weekly progress
- [x] Line Manager: notice describes reviewing/approving and publishing reports
- [x] Client portals (individual, business admin, business staff): notice describes published reports

### PART M — Documentation
- [x] CURRENT_STATUS.md updated
- [x] IMPLEMENTATION_LOG.md updated
- [x] DATABASE_SCHEMA.md updated
- [x] PERMISSION_MATRIX.md updated
- [x] TESTING_CHECKLIST.md updated
- [x] KNOWN_ISSUES.md updated

---

## Architecture Notes (Phase 7 additions)

- **Duration resolution:** `resolvedDurationMinutes()` prefers explicit `duration_minutes`; falls back to `started_at` → `ended_at` diff. Stored as integer minutes.
- **Client visibility:** Clients only see time logs where `status=approved AND visibility=client_summary`. Weekly reports: `status=published` only.
- **Talent visibility:** Sees own time logs of any status; sees weekly reports with status submitted/approved/published (not draft).
- **Weekly report total_minutes:** Auto-suggested from approved time logs in the selected week date range during create. Manager can manually override.
- **No surveillance:** No automated screenshots, no keystroke logging, no screen time monitoring. Time logging is entirely manual/self-reported.
- **No billing yet:** Duration data is captured but not wired to any billing or payroll system.
- **Int casts on all FK columns:** workspace_id, user_id, workspace_task_id, reviewed_by_user_id etc. cast to integer in both new models — consistent with Phase 5/6 pattern.

---

## Next Steps

1. cPanel: `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan view:clear`
2. Verify time log creation: talent logs time, submits; manager reviews and approves
3. Verify client visibility: client cannot see internal logs; can see approved+client_summary only
4. Verify weekly report flow: create → submit → approve → publish; client sees only published
5. Verify task show page time log sidebar renders correctly
6. Verify Filament: WorkspaceTimeLogResource and WorkspaceWeeklyReportResource appear under Workspace nav group, sort 7 and 8
7. Phase 8 billing foundation is implemented; run the Phase 8 checklist before starting Phase 9

---

## UI Source of Truth Alignment (2026-05-31)

Feature development is paused. The Stitch UI export has been designated as the frontend source of truth.

**What was done:**
- Stitch zip extracted to `design-reference/stitch_gvos_operations_platform/` (67 screen folders)
- `docs/UI_SOURCE_OF_TRUTH.md` created — maps every route to its Stitch screen folder
- `docs/UI_CORRECTION_PLAN.md` created — 7 correction batches with file lists, risk levels, test checklists
- `docs/FRONTEND_IMPLEMENTATION_RULES.md` created — 15 frontend rules; Stitch is the source of truth
- `docs/SEMI_AUTOMATED_TIME_TRACKING_PLAN.md` created — timer plan documented (not yet implemented)

**Summary of UI drift found:**
- Login: Major drift — current is single-column; Stitch is 2-col split-screen with decorative panel
- Dashboards: Major drift — phase banners don't exist in Stitch; talent needs Clock-In widget; manager needs richer metrics
- Sidebar: Moderate drift — missing Quick Action button, user profile card, workspace switcher for talent
- Header: Moderate drift — missing Clock In button
- Workspace show: Major drift — Stitch is a rich monitoring screen; current is a static card grid
- Chat/files/tasks: Moderate drift — layout differs but structure is close
- Time tracking: Major drift — Stitch shows a timer widget; current is a plain form table
- Phase banners: All 7 dashboards have banners that don't exist in any Stitch screen

**No code was changed. No database was changed. Documentation only.**

**Next step:** Phase 9 (not started). UI Batch 3 also available when instructed.

---

## Phase 8 Status — Complete ✅ (2026-05-31)

### PART A — Database Migrations (5 new tables)
- [x] `billing_plans` — name, code, currency, amount, billing_cycle, included_talents, status, softDeletes
- [x] `workspace_subscriptions` — workspace_id FK, billing_plan_id FK, currency, amount, billing_cycle, status (trial/active/payment_due/overdue/suspended/cancelled/ended), starts_at, next_billing_date, grace_ends_at, softDeletes
- [x] `invoices` — invoice_number unique, workspace_id FK, workspace_subscription_id FK nullable, currency, subtotal/discount/tax/total/amount_paid/balance_due, status (draft/issued/partially_paid/paid/overdue/cancelled/void), issue_date, due_date, internal_notes, softDeletes
- [x] `invoice_items` — invoice_id FK, description, quantity, unit_amount, total_amount, item_type enum
- [x] `payments` — payment_reference unique, invoice_id FK nullable, workspace_id FK nullable, provider enum (manual/bank_transfer/fincra/flutterwave/paystack/stripe/other), currency, amount, status, paid_at, confirmed_by_user_id FK, raw_payload json, softDeletes

### PART B — Models (5 new, 3 updated)
- [x] `BillingPlan` — SoftDeletes; cycleLabels(), formattedAmount(); hasMany subscriptions
- [x] `WorkspaceSubscription` — SoftDeletes; statusLabel(), formattedAmount(), isActive(), requiresPayment(); relationships: workspace, billingPlan, clientProfile, company, invoices, payments
- [x] `Invoice` — SoftDeletes; auto-generate invoice_number on create (GVOS-INV-YYYYMM-XXXX); recalculateTotals(), applyPayment(); hasMany items, payments
- [x] `InvoiceItem` — auto-calculate total_amount from quantity × unit_amount; belongsTo invoice; refreshes parent invoice totals after item changes
- [x] `Payment` — SoftDeletes; auto-generate payment_reference; idempotent confirm() method: sets confirmed, updates invoice amount_paid/balance_due/status, updates subscription; relationships: invoice, workspace, subscription, confirmedBy
- [x] `Workspace.php` — added subscriptions(), activeSubscription(), invoices(), payments()
- [x] `ClientProfile.php` — added subscriptions(), invoices()
- [x] `Company.php` — added subscriptions(), invoices()

### PART C — Billing Permissions
- Admin/Ops Admin: manage all billing resources in Filament; destructive deletes disabled in favour of archive/cancel/confirm status actions
- Workspace Admin/Manager: view subscription + invoice status for their workspace (portal only)
- Client Admin/Individual Client: view invoices + payment status (portal); cannot edit
- Client Staff: read-only view if workspace accessible
- Talent: CANNOT access billing (403 in controller; hidden in workspace/show)

### PART D/E — Routes + Controller (3 new routes)
- `GET /workspaces/{workspace}/billing` → `workspace.billing.index`
- `GET /workspaces/{workspace}/billing/invoices/{invoice}` → `workspace.billing.invoice`
- `GET /workspaces/{workspace}/billing/payments` → `workspace.billing.payments`
- `WorkspaceBillingController` — index, showInvoice, payments; canViewBilling() helper excludes talent/assigned_user/observer

### PART F — Portal Billing Views (3 new)
- `workspace/billing/index.blade.php` — subscription status card (with payment_due warning), invoice table with status badges, recent payments, payment instructions placeholder
- `workspace/billing/show-invoice.blade.php` — professional invoice detail layout with invoice header, bill-to/metadata section, item table, right-aligned totals directly below items, payment history, notes, protected internal_notes, and payment instructions placeholder
- `workspace/billing/payments.blade.php` — paginated payment history with status badges; confirmation notes visible only to internal roles

### PART G — workspace/show.blade.php updated
- Billing card now ACTIVE for non-talent roles — shows subscription status badge, amount/cycle, next billing date, outstanding balance
- Talent/assigned_user/observer see a disabled placeholder
- Password Vault remains as dashed placeholder

### PART H — Filament Resources (4 new, nav group "Billing")
- `BillingPlanResource` (sort 1) — create/edit/archive; status badge; cycle/currency filters
- `WorkspaceSubscriptionResource` (sort 2) — create/edit; status/currency/workspace filters; next_billing_date column
- `InvoiceResource` (sort 3) — create/edit + Issue / Mark Paid / Cancel inline actions; sectioned create/edit form with invoice identity, invoice items, totals below items, notes; status/workspace/currency/due_date filters
- `PaymentResource` (sort 4) — record/edit pending + Confirm / Cancel inline actions with confirmation notes form; provider/status/currency/workspace filters

### PART I — Invoice Number Format
`GVOS-INV-YYYYMM-0001` — auto-generated in `Invoice::booted()` if blank; increments per-month from existing records

### PART J — Payment/Invoice Status Flow
1. `Payment::confirm(userId, notes)` → sets status=confirmed, paid_at=now()
2. → calls `Invoice::applyPayment(amount)` → increments amount_paid, recalculates balance_due
3. → if balance_due ≤ 0: status=paid, paid_at=now(); else: status=partially_paid
4. → if subscription linked: last_paid_at=now(); if payment_due/overdue/suspended → status=active

### PART K — AuditLogger (12 new wrappers)
billing_plan.created/updated, workspace_subscription.created/updated, invoice.created/updated/issued/cancelled/marked_paid, payment.recorded/confirmed/failed_or_cancelled

### PART L — Dashboard Updates
- Super Admin: 4 billing count cards (total invoices, outstanding, paid, payments confirmed)
- Operations Admin: outstanding invoices action item added
- Individual Client: Billing quick link card with outstanding balance
- Business Client Admin: Billing quick link card with outstanding balance

### Phase 8 Invoice Detail Layout Correction (2026-06-06)
- [x] `resources/views/workspace/billing/show-invoice.blade.php` reorganized to read like a standard professional invoice
- [x] Totals now appear directly below the invoice items table in this order: subtotal, discount, tax, total amount, amount paid, balance due
- [x] Total amount and balance due are visually emphasized; money values are right-aligned
- [x] Payment history now uses a clean table with payment reference, provider, amount, status, and paid-at date
- [x] Client-visible notes and internal notes are separated; internal notes remain protected by the existing `$canViewInternal` gate
- [x] A simple print button was added with `window.print()`
- [x] No billing database, migration, calculation, payment confirmation, status, or permission logic changed
- [x] Stitch reference used: `billing_invoices_gvos`

### Phase 8 Admin Invoice Form Layout Correction (2026-06-06)
- [x] `app/Filament/Resources/InvoiceResource.php` form reorganized into Invoice Identity, Invoice Items, Totals and Payment Summary, and Notes sections
- [x] Totals now appear below invoice items in the admin create/edit form
- [x] Totals section includes helper text: totals are calculated from invoice items and payment records where available
- [x] Discount and tax remain editable; manual invoice total behavior is preserved
- [x] No database, migration, billing calculation, payment confirmation, invoice status, permission, or portal changes

---

## Phase 9 Status — Complete (2026-06-06)

### Semi Automated Time Tracking
- [x] `workspace_time_logs.status` enum extended with `running`
- [x] Server-side start/stop/complete timer controller added
- [x] One active running timer enforced per user globally
- [x] Talent dashboard clock-in widget now starts, stops, resumes, and completes running sessions
- [x] Workspace time log index shows active session controls and manager/admin running timer visibility
- [x] Time log detail page shows running state and stop/complete actions when authorized
- [x] Task detail page can start a timer linked to the current task
- [x] Filament time log table shows running status and live-duration-compatible duration column
- [x] Audit events added for timer started, stopped, and completed
- [x] Client visibility remains protected: clients still only see approved client_summary logs
- [x] No screenshots, keystrokes, screen monitoring, payroll, password vault, billing automation, or Phase 10 work
- [x] No billing database, payment confirmation, or invoice status logic changed

---

## Phase 10 Status — Complete (2026-06-06)

### Password Vault Foundation
- [x] `workspace_vault_items` migration created for workspace-scoped encrypted credentials
- [x] `workspace_vault_access_logs` migration created for metadata-only vault activity logs
- [x] `WorkspaceVaultItem` model added with encrypted `secret_value` cast and hidden secret serialization
- [x] `WorkspaceVaultAccessLog` model added with create/update/archive/reveal/copy/log-view action labels
- [x] Workspace relationships added for vault items and vault access logs
- [x] Portal vault routes added under `/workspaces/{workspace}/vault`
- [x] Portal vault list, create, edit, detail, reveal/copy, archive, and access log pages added
- [x] Workspace detail password vault card is active only when the user can create vault items or has visible assigned vault items
- [x] Filament `WorkspaceVaultItemResource` added for admin create/edit/archive/restore without exposing stored secrets
- [x] Filament `WorkspaceVaultAccessLogResource` added as read-only oversight for vault access metadata
- [x] Audit wrappers added for vault create/update/archive/restore/reveal/access-log viewing
- [x] Secrets are not shown in workspace cards, vault tables, Filament tables, audit logs, or access logs
- [x] No billing database, payment confirmation, invoice status, payment gateway, payroll, browser extension, auto-login, screenshot, keystroke, or screen-monitoring logic added
- [x] Stitch reference used: `password_vault_gvos`, adapted to keep lists metadata-only

---

## Phase 11 Status - Complete (2026-06-06)

### Notifications and Email System Foundation
- [x] Laravel database notification table added using the standard UUID notification schema
- [x] `user_notification_preferences` table added for per-user in-app and email preferences
- [x] `UserNotificationPreference` model added with Phase 11 notification keys and email defaults
- [x] 10 Laravel notification classes added for tasks, comments, files, messages, time logs, reports, invoices, payments, and trial approval
- [x] `NotificationService` added to resolve safe recipients, apply preferences, avoid duplicate recipients, and catch delivery failures
- [x] Portal `/notifications` inbox added with unread-first list, mark-read, mark-all-read, action links, and empty state
- [x] Portal `/settings/notifications` preferences page added with in-app and email toggles
- [x] Shared GVOS layout notification bell now links to the inbox and shows unread count
- [x] Task assignment, task status changes, task comments, file uploads, workspace messages, submitted time logs, published weekly reports, issued invoices, recorded/confirmed payments, and approved trials trigger notifications
- [x] Email is preference controlled and uses Laravel mail configuration; chat/message, task comment, task status, and file upload email are disabled by default
- [x] Filament read-only `UserNotificationPreferenceResource` added for admin visibility into user preferences
- [x] Audit event `notification_preferences.updated` added; notification creation itself is not audit-spammed
- [x] Notification payloads exclude vault secrets, raw storage paths, private payment payloads, internal admin notes, internal invoice notes, manager notes, tokens, and API keys
- [x] No Phase 12 work, payroll, payment gateway integration, real-time websocket chat, billing calculation changes, payment confirmation logic changes, or vault encryption changes

---

## UI Correction — Batch 1a Complete (2026-05-31)

**File modified:** `resources/views/components/layouts/gvos.blade.php`

**Stitch reference used:** `manager_command_center_gvos`, `admin_overview_gvos`, `talent_dashboard_gvos_1`, `client_dashboard_gvos`, `business_admin_dashboard_gvos`

**Changes made:**

### Sidebar
- Logo section: removed `border-b border-white/10` separator (Stitch has no border here); fixed font tokens to `font-headline-md text-headline-md font-bold text-secondary-fixed leading-none`; changed padding from `px-6 pt-6 pb-4` to `mb-8 px-2` pattern matching Stitch
- Nav items: active state updated to exact Stitch class: `bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold active:scale-95`; inactive state corrected from `text-on-primary-container` to `text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5`; nav text changed from `text-xs font-semibold` to `font-label-md text-label-md`
- Footer: added `border-t border-white/10` + `pt-gutter`; added **Quick Action button** (`bg-secondary text-on-secondary py-3 rounded-xl font-label-md hover:brightness-110`) linking to workspace.index
- Footer: added **Settings link** (profile.show route) matching Stitch `settings` icon + label
- Footer: added **Support placeholder** (disabled, opacity-40, cursor-not-allowed, "coming soon" tooltip)
- Profile card: improved to exact Stitch `bg-white/5 rounded-xl` with avatar initial, name `font-label-md text-label-md text-white`, role `text-[10px] uppercase tracking-wider`
- Sign out: moved into footer space, now uses `font-label-md text-label-md` with `logout` icon

### Header
- **Left**: replaced page title `h1` with `GVOS` bold brand text (`font-headline-md font-black text-secondary`) + search input (`rounded-full w-64 bg-surface-container-low`)
- **Center**: added **Workspace / Messages / Files** nav links — Workspace active when `$workspaceActive`, Messages and Files link to workspace.index (universal workspace hub; no per-workspace context in shared layout)
- **Right**: replaced user info display with **notifications bell** icon + vertical divider + **Clock In button** (`bg-secondary text-on-secondary px-4 py-2 rounded-lg`) linking to workspace.index

### Clock In placeholder
- Styled as Stitch blue button (matching `manager_command_center_gvos` header exactly)
- Links to `/workspaces` with `title="Go to your workspace to log time"`
- No timer logic — UI only as specified
- Semi-automated timer is documented in `SEMI_AUTOMATED_TIME_TRACKING_PLAN.md` but not implemented

### Preserved
- `<!-- GVOS UI Visual Repair v3 active -->` marker comment ✅
- Full CSS fallback block (all `.bg-sidebar-bg`, `.text-secondary-fixed`, etc.) ✅ (added `text-secondary-fixed-dim` and `active:scale-95`)
- Hidden Tailwind safeguard div (updated to include all new dynamic classes) ✅
- Tailwind CDN script order (config before CDN) ✅
- `@csrf` in logout form ✅
- All dashboard routes unaffected (layout only) ✅
- No database changes ✅
- No route changes ✅
- `GetVirtual` does not appear anywhere ✅

---

## UI Correction — Batch 1b Complete (2026-05-31)

**Files modified:**
- `resources/views/auth/login.blade.php` — full rewrite to 2-col Stitch split-screen
- `resources/views/components/layouts/auth.blade.php` — added `split` variant, CSS tokens for login decorative panel

**Stitch reference:** `login_gvos_1/code.html`

**Changes:**
- Layout: single centered card → 2-col split screen (left 45% white form, right 55% dark `#0B0F19` decorative panel)
- Email label: "Email Address" → "Business Email"
- Password label: "Password" → "Security Key"
- Forgot password link: "Forgot password?" → "Reset Access"
- Remember checkbox: "Keep me signed in" → "Persistent session for 24 hours"
- Submit button: "Sign in" → "Initialize Session" (with `arrow_forward` icon)
- Added password show/hide toggle (inline JS, `visibility` / `visibility_off` icon)
- Security footer: updated to "Security Protocol Active" with `verified_user` icon + AES-256 note
- Right panel: dark bg with dot-grid overlay, two floating glass cards (Node Distribution + Security Audit), bottom decoration with team indicator — all CSS-only, no external images
- Subtle slide-in animation for form panel (matches Stitch micro-interaction)
- `auth.blade.php`: added `split` variant branch (full-screen body, no centering); all other auth pages unchanged; added CSS tokens for decorative elements

**Preserved:**
- POST action `route('login')` ✅
- `@csrf` token ✅
- `name="email"` with `old('email')`, `name="password"`, `name="remember"` ✅
- `@error('email')` and `@error('password')` validation error display ✅
- `session('status')` for password reset success ✅
- `route('password.request')` forgot password link ✅
- `autocomplete`, `autofocus`, `required` attributes ✅
- Visual Repair v3 comment in auth layout ✅
- All other auth pages unaffected by `split` variant addition ✅
- `GetVirtual` does not appear anywhere ✅
- No database changes, no route changes ✅
