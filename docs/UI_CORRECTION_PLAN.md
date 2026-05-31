# GVOS — UI Correction Plan

**Created:** 2026-05-31
**Status:** Planning — no code changes made yet.
**Authority:** `design-reference/stitch_gvos_operations_platform/`

---

## Current State Summary

The GVOS platform has been built functionally with all Phase 1–7 features working. The design token system (colors, fonts, spacing) exactly matches the Stitch export. However, several layout patterns, page structures, and component compositions have drifted from the Stitch source:

1. **Phase notice banners** — all dashboards show "Phase X — Feature" status banners that don't exist in any Stitch screen.
2. **Login page** — current is a standard single-column form; Stitch is a 2-col split-screen with a decorated right panel.
3. **Sidebar** — current does not have the workspace switcher for talent, the user profile card at the bottom, or the Quick Action button.
4. **Header** — current header lacks Clock In button; some pages show header inconsistently.
5. **Dashboard richness** — Stitch dashboards have rich metric cards, activity feeds, and live widgets. Current dashboards are simpler summary pages.
6. **Talent dashboard** — Stitch has a prominent Clock-In/Out timer widget. Current has none.
7. **Breadcrumbs** — current uses breadcrumbs on most pages; Stitch does not use breadcrumbs (uses page headers with sub-labels instead).
8. **Workspace show page** — Stitch workspace_monitoring_gvos is a rich page with metrics, activity feed, workspace health. Current is a static summary card grid.
9. **Time tracking** — Stitch shows a live timer widget at the top of time_tracking_daily_reports_gvos. Current Phase 7 is a plain table list form.

---

## Correction Batches

---

### Batch 1 — Core Shell and Auth

**Priority:** HIGH — Affects every page and first impressions.

#### 1a. Shared Portal Layout (`x-layouts.gvos`) ✅ COMPLETE (2026-05-31)

**File to modify:** `resources/views/components/layouts/gvos.blade.php`

**Stitch source:** `admin_overview_gvos/code.html`, `manager_command_center_gvos/code.html`, `talent_dashboard_gvos_1/code.html`

**Changes required:**
- Add **Quick Action button** (`bg-secondary text-white py-3 rounded-xl`) to sidebar footer above Settings/Help — present in all Stitch screens
- Add **user profile card** to sidebar footer — `bg-white/5 rounded-xl px-3 py-4 flex items-center gap-3` with avatar, name, role — present in manager_command_center_gvos
- Add **Clock In button** (`bg-secondary text-on-secondary px-4 py-2 rounded-lg`) to header right side — present in all dashboard headers
- Remove `border-b border-white/10` separator after logo in sidebar (Stitch logo section has no border separator)
- Ensure header `GVOS` bold brand text displays in header left (`font-headline-md font-black text-secondary`) — some Stitch screens show this; others omit it
- Consider workspace switcher component slot for talent sidebar (workspace name + unfold_more icon)

**Data needed:** `auth()->user()->name`, `auth()->user()->getGvosRoleName()` (already available)

**Risk:** Low — visual only, no logic changes.

**Testing (✅ = done in code review):**
- [x] All portal pages still load correctly after layout change — layout is unchanged structurally
- [x] Active nav item has `border-l-4 border-secondary-fixed bg-white/10 text-secondary-fixed-dim font-bold`
- [x] Sidebar user profile card shows logged-in user name and role
- [x] Quick Action button visible in sidebar footer — links to workspace.index
- [x] Clock In button visible in header — UI placeholder, links to workspace.index
- [x] Visual Repair v3 comment preserved in HTML
- [x] `GetVirtual` does not appear anywhere
- [ ] Live browser verification pending on cPanel

#### 1b. Login Page ✅ COMPLETE (2026-05-31)

**Files modified:** `resources/views/auth/login.blade.php`, `resources/views/components/layouts/auth.blade.php`

**Stitch source:** `login_gvos_1/code.html`

**Changes required:**
- Full 2-column split-screen layout: form panel (45% width, `bg-surface-container-lowest`) + decorative panel (55% width, `bg-sidebar-bg`)
- Email field label: "Business Email" (not "Email")
- Password field label: "Security Key" (not "Password")
- Submit button: "Initialize Session" with arrow icon (not "Log in")
- Password field: show/hide toggle button (eye icon)
- Remember checkbox: "Persistent session for 24 hours"
- Security note footer: AES-256 encryption message
- Right panel: dark background with decorative floating data cards (can be simplified to CSS-only for now)
- GVOS logo: `w-10 h-10 bg-secondary rounded-lg` with `security` icon + "GVOS" bold headline text

**Data needed:** Standard Fortify fields (`email`, `password`)

**Risk:** Low — no backend logic changes.

**Testing:**
- [ ] Login form submits correctly to `/login`
- [ ] Validation errors display
- [ ] "Forgot password" link works
- [ ] Mobile layout collapses decorative panel (hidden on small screens)

#### 1c. Forgot Password

**File to modify:** `resources/views/auth/forgot-password.blade.php`

**Stitch source:** `forgot_password_gvos/code.html`

**Risk:** Low

#### 1d. Suspended Account

**File to modify:** `resources/views/account/status.blade.php`

**Stitch source:** `suspended_account_gvos/code.html`

**Risk:** Low

---

### Batch 2 — Dashboards ✅ COMPLETE (2026-05-31)

**Priority:** HIGH — First screen users see after login.

#### 2a. Admin Overview Dashboard

**Files to modify:** `resources/views/dashboard/super-admin.blade.php`, `resources/views/dashboard/operations-admin.blade.php`

**Stitch source:** `admin_overview_gvos/code.html`

**Changes required:**
- Replace Phase notice banner with Stitch metric cards grid (system health, workspace counts, talent counts, active sessions)
- Add activity feed section
- Match header: search bar, Workspace/Messages/Files links, notifications, Clock In button
- Remove breadcrumbs (Stitch uses page-level `h2` headers only)
- Sidebar: add Quick Action button + Settings/Help footer links

**Data needed:** Already available: workspace counts, user counts, audit log counts, message/file counts.

**Risk:** Medium — significant layout change but no logic change.

#### 2b. Talent Dashboard

**Files to modify:** `resources/views/dashboard/talent.blade.php`

**Stitch source:** `talent_dashboard_gvos_1/code.html`

**Changes required:**
- Remove Phase notice banner
- Add **Clock-In/Out widget** at top of page (timer icon with animate-pulse, start/stop timer button — placeholder UI only, no backend timer in this batch)
- Add workspace switcher in sidebar
- Add task priority grid (urgent/high tasks highlighted)
- Match "Welcome back, [Name]" greeting headline
- Add "You have N tasks remaining" subtitle

**Data needed:** Existing task count data already available.

**Risk:** Medium

#### 2c. Manager Dashboard

**Files to modify:** `resources/views/dashboard/line-manager.blade.php`

**Stitch source:** `manager_command_center_gvos/code.html`

**Changes required:**
- Remove Phase notice banner
- Add workspace metric cards
- Add "Your Talent" section with talent status
- Add "Your Clients" summary section
- Match header with Clock In button

**Risk:** Medium

#### 2d. Client Dashboard (Individual)

**Files to modify:** `resources/views/dashboard/individual-client.blade.php`

**Stitch source:** `client_dashboard_gvos/code.html`

**Changes required:**
- Remove Phase notice banner
- Match Stitch project status cards
- Add deliverable timeline section

**Risk:** Medium

#### 2e. Business Admin Dashboard

**Files to modify:** `resources/views/dashboard/business-client-admin.blade.php`

**Stitch source:** `business_admin_dashboard_gvos/code.html`

**Risk:** Medium

---

### Batch 3 — Workspace Core

**Priority:** HIGH — Core product experience.

#### 3a. Workspace Overview (Show Page)

**File to modify:** `resources/views/workspace/show.blade.php`

**Stitch source:** `workspace_monitoring_gvos/code.html`

**Changes required:**
- Replace current card grid with Stitch workspace monitoring layout
- Add live metrics section (task counts, team status, hours this week)
- Add activity feed
- Keep Time Logs and Reports active cards (Phase 7 links)
- Keep Billing and Password Vault as placeholders

**Risk:** Medium-High

#### 3b. Task Board

**File to modify:** `resources/views/workspace/tasks/index.blade.php`

**Stitch source:** `task_board_gvos/code.html`

**Changes required:**
- Match Stitch kanban column layout precisely
- Add filter bar (Status, Priority, Talent dropdowns)
- Match task card structure: priority badge, task code, title, assignee avatar, due date, attachment count
- Remove breadcrumbs; use page header `h2` + subtitle

**Risk:** Medium

#### 3c. Task Detail

**File to modify:** `resources/views/workspace/tasks/show.blade.php`

**Stitch source:** `task_detail_gvos/code.html`

**Risk:** Medium

#### 3d. Create Task

**File to modify:** `resources/views/workspace/tasks/create.blade.php`

**Stitch source:** `create_task_gvos/code.html`

**Risk:** Low

---

### Batch 4 — Communication and Files

**Priority:** MEDIUM

#### 4a. Workspace Chat

**File to modify:** `resources/views/workspace/chat/index.blade.php`

**Stitch source:** `workspace_chat_gvos/code.html`

**Changes required:**
- Match Stitch chat bubble layout
- Message bubbles (own messages right-aligned, others left-aligned)
- Attachment icons in messages
- Thread/reply indicators if present in Stitch

**Risk:** Medium

#### 4b. File Library

**File to modify:** `resources/views/workspace/files/index.blade.php`

**Stitch source:** `file_library_gvos/code.html`

**Changes required:**
- Match Stitch file grid or list layout
- Category filter tabs
- File type icons/previews
- Upload area styling

**Risk:** Medium

---

### Batch 5 — Time and Reports

**Priority:** MEDIUM — Phase 7 core, recently built but needs Stitch alignment.

#### 5a. Time Tracking Daily Reports

**File to modify:** `resources/views/workspace/time-logs/index.blade.php`

**Stitch source:** `time_tracking_daily_reports_gvos/code.html`

**Changes required:**
- Add timer widget at top of page (Clock In/Out area — placeholder UI only; full timer requires semi-automated plan)
- Match daily log list layout below
- Match log entry card structure

**Risk:** Medium — placeholder timer widget is low risk; actual timer logic is Batch 7.

#### 5b. Exact Time Log Review

**File to modify:** `resources/views/workspace/time-logs/show.blade.php`

**Stitch source:** `exact_time_logs_review_gvos/code.html`

**Risk:** Low-Medium

#### 5c. Weekly Report

**File to modify:** `resources/views/workspace/reports/index.blade.php`, `show.blade.php`

**Stitch source:** `weekly_report_gvos/code.html`

**Risk:** Low-Medium

---

### Batch 6 — Admin Management Screens

**Priority:** LOW — Filament handles most admin; portal-side admin screens are less critical.

#### 6a–6f. Leads, Companies, Departments, Workspaces, Talent Onboarding, Audit Logs

These are Filament resources. Stitch screens (`lead_management_gvos`, `companies_management_gvos`, etc.) are reference for future portal-side admin views. Not actionable until portal-side admin screens are built.

---

### Batch 7 — Future Modules (Do Not Start)

| Screen | Stitch Source | Notes |
|--------|---------------|-------|
| Billing | `billing_invoices_gvos`, `global_billing_overview_gvos` | Do not build yet. |
| Password vault | `password_vault_gvos` | Do not build yet. |
| Semi-automated timer | `time_tracking_daily_reports_gvos` + timer widget | See `SEMI_AUTOMATED_TIME_TRACKING_PLAN.md`. Do not implement yet. |

---

## Correction Order Recommendation

1. **Batch 1a** — Fix shared layout (sidebar + header) — affects all pages, highest ROI per file changed
2. **Batch 1b** — Fix login page — first impression
3. **Batch 2** — Fix dashboards (phase banners removal + Stitch card layouts)
4. **Batch 3** — Fix workspace core (show, task board, task detail)
5. **Batch 4** — Fix chat and files
6. **Batch 5** — Fix time and reports to match Stitch (with timer placeholder)
7. **Batch 1c, 1d** — Forgot password, suspended account

---

## Global Rules for All Batches

- Do NOT remove Visual Repair v3 CSS fallbacks until fully replaced by compiled CSS.
- Do NOT use `GetVirtual` anywhere in visible UI.
- Every commit that touches a Blade view must reference the Stitch source folder used.
- If a Stitch screen is ambiguous, open the `screen.png` and use it as the visual reference.
- Preserve all existing data bindings and Laravel route/model logic — only change presentation.
- Do not change routes, migrations, models, or controllers during UI correction.
