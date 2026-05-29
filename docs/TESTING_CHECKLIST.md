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

### Public Lead Form (UX upgrade — multi-step)

Run after `git pull && php artisan optimize:clear` (no new migrations for UX update).

#### Load and Branding
- [ ] `GET /request-service` loads — GVOS header, gradient progress bar, step 1 visible
- [ ] No "GetVirtual" text visible anywhere on the page
- [ ] Page is scrollable; side panel visible on desktop (≥1024px), stacked on mobile
- [ ] "No payment required" badge visible in header on desktop

#### Step Navigation
- [ ] Step 1 shows "Your details" — Back button hidden, Next button visible
- [ ] Clicking Next with blank required fields: first_name, last_name, or email highlighted in red — does NOT advance
- [ ] Filling required fields and clicking Next advances to Step 2
- [ ] Progress bar fills to 50% on Step 2, 75% on Step 3, 100% on Step 4
- [ ] Step labels update: completed steps dim, current step bold white
- [ ] Back button appears from Step 2 onward
- [ ] Back correctly returns to previous step without losing entered data
- [ ] Submit button appears only on Step 4 — not on Steps 1–3

#### Step 1 — Your Details
- [ ] First name, last name, email fields present and required
- [ ] Phone, country, city marked optional — form advances without them
- [ ] Timezone dropdown has 11 options + "Other (specify below)"
- [ ] Selecting "Other" reveals the custom timezone free-text field
- [ ] Selecting a named timezone hides the custom field
- [ ] Entering a custom timezone and submitting: `lead_requests.timezone` stores the custom value

#### Step 2 — Support Needed
- [ ] Individual card selected by default (border highlighted)
- [ ] Clicking Business card highlights it and shows company fields (name, website, email domain)
- [ ] Clicking Individual hides company fields again
- [ ] 8 role icon cards visible: Virtual Assistant, Executive Assistant, Social Media Manager, Video Editor, Developer, Designer, Motion Graphics, Other
- [ ] Clicking a role card highlights it (coloured border)
- [ ] Clicking "Other (please specify)" shows free-text role field
- [ ] Clicking a different role hides the other field

#### Step 3 — Work Details
- [ ] Hours per week, start date, schedule, skills, description all present
- [ ] All fields optional — form advances without them
- [ ] textarea for work description is resizable
- [ ] Start date in the past shows Laravel validation error on submit

#### Step 4 — Final Details
- [ ] 6 budget range radio cards with sub-labels visible
- [ ] Clicking a budget card highlights it
- [ ] Source field present
- [ ] Privacy note visible ("Your information is only used...")
- [ ] "No payment required at this stage" text present
- [ ] Submit button present with arrow icon

#### Form Submission
- [ ] Valid form submission creates a `lead_requests` row with status = 'new'
- [ ] Redirects to `/request-service/success`
- [ ] `audit_logs` has `lead_request.created` entry with `actor_id = null`
- [ ] Non-authenticated users can access the form (no login required)
- [ ] Client type defaults to 'individual' even if user didn't interact with Step 2

#### Validation Errors (server-side)
- [ ] Submitting with invalid start date: Laravel returns error, form restores to Step 3 with error displayed
- [ ] Error message box visible with red border at top of the form
- [ ] All previously entered values restored via `old()` across all steps

#### Success Page
- [ ] `/request-service/success` shows emerald accent stripe and double-ring checkmark icon
- [ ] Heading "We've got your request!" visible
- [ ] 4-step "What happens next" card visible with correct copy
- [ ] "Sign In to GVOS" button links to login
- [ ] "Submit Another Request" links back to `/request-service`
- [ ] No "GetVirtual" text anywhere

#### Mobile Layout
- [ ] On mobile (< 1024px): form card appears above side panel
- [ ] Progress bar and step counter visible on mobile
- [ ] Step labels ("Your details" etc.) hidden on xs — step inline label visible instead
- [ ] Buttons are full-width or tap-friendly
- [ ] No horizontal scroll at 375px viewport width
- [ ] Role cards and budget cards wrap correctly on small screens

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

## Phase 4 — Workspace Engine

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Country Dropdown Cleanup
- [ ] Profile page `/profile` — Country is a dropdown (not a text input), has ≥17 options
- [ ] Selecting a country and saving updates `user_profiles.country`
- [ ] Filament `/admin/companies` create form — Country is a searchable Select
- [ ] Filament `/admin/companies` edit form — saved country pre-selects correctly
- [ ] Lead request form `/request-service` Step 1 — Country is a dropdown (not text input)
- [ ] Selecting a country on lead form and submitting stores value in `lead_requests.country`
- [ ] "Other" option at bottom of country list present on all dropdowns

### Workspaces — Filament Admin
- [ ] `/admin/workspaces` loads — nav group "Workspace" visible
- [ ] Can create a workspace manually — workspace_code auto-generated if blank
- [ ] Status badges: pending=amber, active=green, paused=blue, completed=gray, cancelled=red
- [ ] Type badges: trial=violet, ongoing=green, project=teal
- [ ] Activate action: visible on pending workspaces → status changes to active
- [ ] Pause action: visible on active workspaces → status changes to paused
- [ ] Complete action: visible on active/paused → status changes to completed, ends_at set to now
- [ ] No delete button present
- [ ] Workspace creation fires `workspace.created` audit log entry
- [ ] Workspace edit fires `workspace.updated` with before/after diff

### Workspace Members — Relation Manager
- [ ] Edit page of a workspace shows "Members" tab
- [ ] "Add Member" action opens modal — user select, role, status
- [ ] Adding a member creates workspace_members row, fires `workspace.member_added` audit log
- [ ] Editing a member fires `workspace.member_updated` audit log
- [ ] "Remove" action sets status=removed, removed_at=now, fires `workspace.member_removed`
- [ ] Removed member no longer shows in active members list (status filter)

### Create Workspace from Trial
- [ ] TrialResource table: "Create Workspace" action visible on approved/active/completed trial with no workspace
- [ ] "Create Workspace" action NOT visible if workspace already exists for the trial
- [ ] Clicking: creates `workspaces` row with auto-generated code
- [ ] Lead name used in workspace name (e.g. "Jane Smith Trial Workspace")
- [ ] Primary manager and primary talent copied from trial
- [ ] Active lead, talent, and manager added as workspace members with correct roles
- [ ] Audit log: `trial.workspace_created` entry created
- [ ] Filament notification confirms success with workspace code

### Workspace Portal — Blade Pages
- [ ] `/workspaces` loads for authenticated users
- [ ] Shows list of workspaces where user is a member (or primary team)
- [ ] Empty state shown when user has no workspaces
- [ ] Workspace cards show name, code, type, status badges, team, dates
- [ ] Clicking a workspace card navigates to `/workspaces/{workspace}`
- [ ] `/workspaces/{workspace}` detail page loads — status banner, team card, schedule card, members list
- [ ] Active workspace: green status banner with hours remaining (if ends_at set)
- [ ] Pending workspace: amber banner "Workspace pending activation"
- [ ] Completed workspace: gray banner
- [ ] Non-member accessing workspace detail → 403 Forbidden

### Dashboard Updates
- [ ] Super Admin: workspace count card shows active/total (e.g. "2/5")
- [ ] Operations Admin: same workspace count card
- [ ] Talent: "My Workspaces" card links to `/workspaces`; count correct
- [ ] Line Manager: "My Workspaces" card links to `/workspaces`; count correct
- [ ] Individual Client: "My Workspace" card links to `/workspaces`
- [ ] Business Client Admin: "My Workspace" card links to `/workspaces`
- [ ] Business Client Staff: "Workspace Access" card links to `/workspaces`
- [ ] Active Lead: if workspace exists, shows clickable workspace card linking to workspace detail
- [ ] Active Lead: if no workspace yet, shows "Your workspace is being prepared" placeholder
- [ ] All 8 dashboards: Phase 4 notice replaces Phase 3/2 notice

## UI Fidelity Audit — Design System Alignment

Run after `git pull && php artisan optimize:clear` (no migrations needed).

### Typography and Fonts
- [ ] Login page: GVOS heading rendered in Manrope (bold, wider letterforms than Inter)
- [ ] All body text and labels use Inter font (not browser default system-ui)
- [ ] No visible fallback to system-ui — Google Fonts CDN is reachable
- [ ] Metric numbers on dashboards render in a consistent weight

### Sidebar (Authenticated Portal Layout)
- [ ] Sidebar width is approximately 280px (not 256px)
- [ ] Sidebar background is very dark navy (#0B0F19) — not black, not slate-900
- [ ] GVOS logo area: small blue filled square with hub icon + "GVOS Platform" in light blue + "Enterprise Ops" in muted blue below
- [ ] **Active nav item:** has a left blue border, light blue text, and a semi-transparent white/blue background
- [ ] **Inactive nav items:** muted blue-gray text; hovering reveals light blue text + faint background
- [ ] Nav items present: Dashboard, Workspaces, My Profile — all links functional
- [ ] User footer at bottom: user initials in a blue avatar circle, display name, role label, Sign Out link
- [ ] Sign Out fires POST to `/logout` (not a plain anchor `href`)

### Top Bar
- [ ] Top bar is 64px tall (h-16), white/off-white background, subtle bottom border
- [ ] Top bar sticks to the top of the viewport when page content scrolls
- [ ] Notification bell icon visible on right side (Material Symbols: `notifications`)
- [ ] Security/shield icon visible (Material Symbols: `security`)
- [ ] User avatar (colored circle with initial) and display name visible
- [ ] User avatar shows correct role label below name

### Color Tokens — No Indigo Anywhere
- [ ] No indigo (#4F46E5 or `indigo-*`) colors visible anywhere in the portal
- [ ] Primary action buttons use GVOS blue (`#0058be`)
- [ ] Active/success status badges are green (`#10B981`)
- [ ] Payment-due/warning status badges are amber (`#F59E0B`)
- [ ] Blocked/error status badges are red (`#EF4444`)
- [ ] Trial type badge uses purple (`#8B5CF6`)
- [ ] Page background is off-white (`#f7f9fb`), not pure white and not gray-100

### Cards and Components
- [ ] All card components: white background, rounded-xl corners, subtle border (#E2E8F0), soft drop shadow
- [ ] No rounded-2xl corners visible (maximum is rounded-xl)
- [ ] Input focus states show a secondary blue ring (`focus:ring-2 focus:ring-secondary/20`)
- [ ] Input focus state changes border to secondary blue
- [ ] Error states show red border (`border-status-blocked`)
- [ ] Error messages render in red (`text-status-blocked`)

### Icons
- [ ] All icons are Material Symbols Outlined glyphs (outlined style, not filled)
- [ ] No SVG `<path>` icon fallbacks rendering alongside or in place of symbol font glyphs
- [ ] Icons align vertically with adjacent text (`vertical-align: middle`)
- [ ] Icon sizes are consistent: 16px inline, 18–20px card headers, 24px large feature icons

### Auth Pages
- [ ] Login page: dark navy background, GVOS logo at top, blue accent line at top of card
- [ ] Login: email icon (Material Symbols: `mail`) and lock icon (`lock`) in input prefix
- [ ] Login: "Security Notice" box with shield icon at bottom of card
- [ ] Forgot password page: light dot-pattern background, dark visual header panel with lock_reset icon
- [ ] Account status page: red left-border alert banner, blue accent bar on card, correct conditional text (suspended vs inactive)

### Dashboard Views
- [ ] All 8 dashboards load without errors for their respective roles
- [ ] Welcome heading uses Manrope-style bold weight (visually distinct from body text)
- [ ] Metric/stat cards use GVOS card pattern (white, rounded-xl, border, shadow)
- [ ] Icon containers use `bg-secondary/5` background with `text-secondary` icon
- [ ] Phase notice banner uses `bg-secondary/5 border-secondary/20` styling with `info` icon

### Lead Request Form
- [ ] `/request-service` progress header: GVOS secondary blue background (not indigo)
- [ ] Submit button: GVOS secondary blue (not indigo)
- [ ] Focus rings on inputs: secondary blue ring (not indigo)
- [ ] GVOS logo in form header (hub icon + "GVOS" in secondary-fixed) — no "GetVirtual" text

### Workspace Views
- [ ] `/workspaces` card grid: status badges use GVOS status-* color tokens
- [ ] `/workspaces` type badges: trial=purple, ongoing/project=secondary blue
- [ ] `/workspaces/{workspace}` status banners: amber for pending, green for active, green/gray for completed
- [ ] Workspace members list: role badges use correct GVOS tokens (manager=secondary, talent=green, client=amber)
- [ ] `divide-border-subtle` renders a visible but subtle dividing line between member rows

### Profile Page
- [ ] `/profile` inputs: border-border-subtle, secondary focus ring
- [ ] Save Changes button: GVOS secondary blue with save icon
- [ ] Change Password button: dark on-surface color (distinct from blue Save button)
- [ ] Error messages: text-status-blocked (red)
- [ ] Success banners: green bg-status-active/10 with check_circle icon

---

## Phase 5 — Task Board (planned)
## Phase 6 — Chat and Files (planned)
## Phase 7 — Time Tracking and Reports (planned)
## Phase 8 — Billing (planned)
## Phase 12 — Launch Readiness (planned)
