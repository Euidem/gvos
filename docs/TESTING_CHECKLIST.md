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

## Phase 5 — Task Board

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations
- [ ] `php artisan migrate` runs without error
- [ ] `workspace_tasks` table exists with all columns
- [ ] `workspace_task_comments` table exists with all columns
- [ ] `php artisan route:list` shows 8 workspace task routes

### Task Board — Filament Admin
- [ ] `/admin/workspace-tasks` loads — nav group "Workspace", nav item "Tasks" visible
- [ ] Navigation badge shows count of open tasks (pending + in_progress + revision_requested) in amber
- [ ] Can create a task from Filament — task_code auto-generated (TASK-00001 format)
- [ ] created_by_user_id set to the logged-in admin
- [ ] Status and priority dropdowns show correct options
- [ ] Editing a task fires `workspace_task.updated` audit log with before/after diff
- [ ] Changing status in edit fires `workspace_task.status_changed` audit log entry
- [ ] Changing assigned_to fires `workspace_task.assigned` audit log entry
- [ ] Archive action (soft delete) visible in table — no hard delete button
- [ ] Archived tasks do not appear in main list

### Task Board — Workspace Portal

#### Index (Kanban Board — Drag & Drop)
- [ ] `/workspaces/{workspace}/tasks` loads for a workspace member
- [ ] Page shows 7 status columns: Pending, In Progress, Blocked, Submitted, Revision Req., Approved, Closed
- [ ] Each column has a distinct colored header with icon, label, and count badge
- [ ] Tasks appear in the correct column for their status
- [ ] Task cards show: priority badge, task code, title (truncated), assignee avatar/name, comment count, due date
- [ ] Overdue tasks show due date in red; due-soon tasks show in amber
- [ ] Empty columns show "No tasks here" message (below the droppable zone)
- [ ] Global empty state shown when workspace has no tasks at all; has "Create First Task" button
- [ ] "New Task" button visible to admin/manager/talent/client (not observer)
- [ ] "Drag cards between columns to change status" hint visible to authorized roles
- [ ] Non-member accessing task index → 403

#### Drag and Drop
- [ ] Task cards for admin/manager/talent/client show `drag_indicator` handle icon
- [ ] Observer role cards do not show drag handle; SortableJS not initialized
- [ ] Dragging a card shows: ghost placeholder (dashed blue border), elevated dragged clone (shadow + rotation)
- [ ] Drop target column shows blue dashed outline while hovering (highlight removed on drop)
- [ ] Dropping a card in a new column sends AJAX POST to `/workspaces/{ws}/tasks/{task}/status`
- [ ] Successful move: card stays in new column, count badges update, green toast shown
- [ ] Failed move (permission denied): card reverts to original column, error toast shown
- [ ] Failed move (network error): card reverts to original column, error toast shown
- [ ] Column empty-state message appears/disappears correctly after moves
- [ ] Clicking a card (not dragging) navigates to task detail page
- [ ] Valid talent move: pending → in_progress succeeds (green toast)
- [ ] Valid talent move: in_progress → submitted succeeds (green toast)
- [ ] Invalid move as talent: submitted → approved → 403 → card reverts, error toast
- [ ] Valid client move: submitted → approved succeeds (green toast)
- [ ] Audit log `workspace_task.status_changed` fires on every successful drag move
- [ ] Mobile horizontal scroll still works at narrow viewport
- [ ] SortableJS CDN not loaded on other pages (only task board index)

#### Create Task
- [ ] `/workspaces/{workspace}/tasks/create` accessible to admin/manager
- [ ] Talent/client accessing create → 403
- [ ] Form: title (required), description, assign-to (dropdown of workspace members), priority, due date
- [ ] Internal notes field shown only to admin/manager
- [ ] Submitting valid form creates task; task appears in "Pending" column on index
- [ ] task_code auto-generated (TASK-XXXXX format)
- [ ] `workspace_task.created` audit log entry created
- [ ] Missing title → validation error displayed on form

#### Task Detail (Show)
- [ ] `/workspaces/{workspace}/tasks/{task}` loads
- [ ] Task code, status badge, priority badge, title, description visible
- [ ] Internal notes shown only to admin/manager (hidden for talent/client)
- [ ] Status action buttons shown based on allowed transitions for the user's role
- [ ] Clicking a status button shows a confirm() dialog; Cancel does not change status
- [ ] Status change succeeds → page reloads with updated status
- [ ] Status change fires `workspace_task.status_changed` audit log
- [ ] Wrong transition attempt (e.g. talent trying to approve) → 403 or flash error
- [ ] Comments section shows all public comments for all members
- [ ] Internal comments visible only to admin/manager
- [ ] Add comment form: submitting a public comment creates `workspace_task_comments` row (visibility=public)
- [ ] Internal comment checkbox shown only to admin/manager
- [ ] Talent/client submitting with internal checked → comment saved as public (server override)
- [ ] `workspace_task.comment_added` audit log fires on public comment
- [ ] `workspace_task.internal_comment_added` audit log fires on internal comment

#### Edit Task
- [ ] `/workspaces/{workspace}/tasks/{task}/edit` accessible to admin/manager
- [ ] Form pre-fills all fields from existing task (using old() fallback pattern)
- [ ] Due date input pre-filled in YYYY-MM-DD format
- [ ] Internal notes field shown only to admin/manager
- [ ] Saving updates the task; flash success shown
- [ ] `workspace_task.updated` audit log fires

### Workspace Show — Kanban Board Section
- [ ] `/workspaces/{workspace}` shows "Kanban Board" section with `view_kanban` icon
- [ ] "Open Kanban Board" button (blue, prominent) links to `/workspaces/{workspace}/tasks`
- [ ] "New Task" outline button visible to admin/manager/talent/client only
- [ ] When tasks exist: 4 metric cards visible (Total, Open, Blocked, Awaiting Review) with correct counts
- [ ] Status chips visible with count and color coding, linking to task board
- [ ] Up to 4 open tasks previewed with task code, title, assignee, due date
- [ ] "View all N open tasks on Kanban Board" link shown when open count > 4
- [ ] When no tasks: empty state shown with `view_kanban` icon and create button (if authorized)

### Dashboard Task Counts
- [ ] Super Admin dashboard: Task Overview section shows Total, Open, Blocked, Awaiting Review counts
- [ ] Operations Admin dashboard: same Task Overview section
- [ ] Talent dashboard: "My Tasks" section shows Active Tasks, Blocked, Due Soon (conditional — only if > 0)
- [ ] Line Manager dashboard: Workspace Tasks section shows Open, Submitted Awaiting Review
- [ ] Line Manager dashboard: Task Board quick-action card is now a clickable link (not disabled)
- [ ] Individual Client dashboard: Workspace Tasks section shown if tasks exist
- [ ] Business Client Admin dashboard: Workspace Tasks section shown if tasks exist
- [ ] Business Client Staff dashboard: Workspace Tasks section shown if tasks exist
- [ ] All 7 dashboards: notice updated to "Phase 5 — Task Board" (not Phase 4)

### Audit Log Verification
- [ ] `workspace_task.created` — fires when task created via portal or Filament
- [ ] `workspace_task.updated` — fires when task edited
- [ ] `workspace_task.status_changed` — fires when status changes, context has from/to values
- [ ] `workspace_task.assigned` — fires when assignee changes, context has from/to user IDs
- [ ] `workspace_task.comment_added` — fires when public comment submitted
- [ ] `workspace_task.internal_comment_added` — fires when internal comment submitted
- [ ] `workspace_task.deleted` — fires when task archived (soft deleted)

---

## Phase 5 Fix — Workspace Access for Primary Team Members

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Primary Manager Access

- [ ] Log in as the user set as `primary_manager_id` on a workspace (with no member row)
- [ ] `/workspaces` index shows that workspace in the list
- [ ] `/workspaces/{workspace}` loads (no 403)
- [ ] `/workspaces/{workspace}/tasks` (Kanban board) loads with manager-level drag permissions
- [ ] Can create a new task on the board
- [ ] Can drag tasks between columns (manager role transitions apply)
- [ ] Status action buttons on task detail work correctly

### Primary Talent Access

- [ ] Log in as the user set as `primary_talent_id` on a workspace (with no member row)
- [ ] `/workspaces` index shows that workspace in the list
- [ ] `/workspaces/{workspace}` loads (no 403)
- [ ] `/workspaces/{workspace}/tasks` (Kanban board) loads with talent-level drag permissions
- [ ] Can create a new task
- [ ] Talent-only transitions work (pending → in_progress, in_progress → submitted, etc.)
- [ ] Cannot approve/close tasks (client-only transitions blocked)

### Admin Access

- [ ] Log in as `super_admin` — `/workspaces` shows ALL workspaces (not just member ones)
- [ ] Log in as `operations_admin` — same
- [ ] `/workspaces/{workspace}` for any workspace loads without 403

### Active Member Access (regression check)

- [ ] User with active member row (any role) can still access workspace and task board
- [ ] Observer role: can view board; drag handle NOT shown; cannot create tasks

### Task-Assigned Fallback

- [ ] A user assigned to a task but with no workspace member row can view `/workspaces/{workspace}/tasks/{task}`
- [ ] They cannot access the Kanban index `/workspaces/{workspace}/tasks` (no workspace access = 403)
  - *(Note: `assigned_user` tier gives workspace-level access via `userHasAccess()` — so the index IS accessible to assigned users. Verify intended behaviour.)*
- [ ] Their status buttons use talent-level transitions

### Filament Sync Actions

- [ ] Open Workspace list in Filament `/admin/workspaces`
- [ ] "Sync Team" action is visible on rows where primary_manager_id or primary_talent_id is set
- [ ] Click "Sync Team" → confirm modal → notification appears: "Primary team synced | Added: N · Reactivated: N · Already active: N"
- [ ] After sync: member rows created/reactivated for primary manager and primary talent
- [ ] Audit log `workspace.primary_team_synced` fires with counts in context
- [ ] If member row already exists and is active with correct role: notification shows "Already active: 2" (no duplicate row)

### Auto-Sync on Workspace Save

- [ ] Edit a workspace in Filament, change `primary_manager_id` to a different user, Save
- [ ] New user automatically gets an active member row (role=manager)
- [ ] Previous primary manager member row is NOT automatically removed (manual action required)
- [ ] Audit log `workspace.primary_team_synced` + `workspace.member_added` fires

### Edit-Page Header Action

- [ ] Open Filament workspace edit page
- [ ] "Sync Primary Team" button appears in page header
- [ ] Click → confirm → success notification
- [ ] Member rows created/reactivated as expected

---

## Phase 5 Fix 2 — Task Detail and Kanban Drag-Drop

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Scenario 1: Task detail route (core fix)

- [ ] Login as talent assigned to workspace (primary talent or active member)
- [ ] Kanban board loads: `/workspaces/{id}/tasks`
- [ ] Click any task card
- [ ] Task detail page loads — no 404
- [ ] URL is `/workspaces/{id}/tasks/{task_id}` (integer IDs, not codes)

### Scenario 2: Talent — allowed drag move

- [ ] Login as talent assigned to a task (task assigned to their user account)
- [ ] Drag handle (⋮⋮) is visible on their assigned task cards
- [ ] Drag task from Pending → In Progress
- [ ] Card moves to In Progress column
- [ ] Toast shows "Task moved to "In Progress"."
- [ ] Audit log `workspace_task.status_changed` fires

### Scenario 3: Talent — submit

- [ ] Task in In Progress, assigned to talent
- [ ] Drag from In Progress → Submitted
- [ ] Move succeeds
- [ ] Toast shows success message

### Scenario 4: Talent — invalid move (not allowed transition)

- [ ] Task in Submitted status
- [ ] Talent tries to drag to Approved (not allowed for talent)
- [ ] No drag handle on Approved column drop attempt (JS blocks, or server blocks)
- [ ] Card reverts to Submitted column
- [ ] Toast shows: "This task cannot be moved from "Submitted" to "Approved". Allowed next statuses: ..."

### Scenario 5: Talent — cannot move someone else's task

- [ ] Talent logs in — task on board is assigned to a DIFFERENT user
- [ ] Drag handle is NOT visible on that other user's task card
- [ ] If somehow dragged (via dev tools), backend returns 403: "You can only update tasks assigned to you."
- [ ] Card reverts

### Scenario 6: Manager — broad moves

- [ ] Login as manager (primary manager or member manager)
- [ ] Drag handle visible on ALL task cards
- [ ] Drag Submitted → Approved → success
- [ ] Drag Submitted → Revision Requested → success
- [ ] Drag In Progress → Cancelled → success

### Scenario 7: Client — review flow

- [ ] Login as client member
- [ ] Drag Submitted → Approved → success
- [ ] Drag Submitted → Revision Requested → success
- [ ] Drag Pending → In Progress → fails (not allowed for client)
- [ ] Toast shows descriptive error for invalid move

### Scenario 8: Non-member access

- [ ] Login as user with no workspace access
- [ ] Directly visit `/workspaces/{id}/tasks/{id}` → 403 (not 404)
- [ ] Directly visit `/workspaces/{id}/tasks` → 403

### Scenario 9: Network/non-JSON error handling

- [ ] If server returns non-JSON response (e.g. Cloudflare HTML error), card reverts
- [ ] Toast shows: "The server returned an unexpected response. Please refresh the page and try again."

### Audit Log Verification

- [ ] `workspace_task.status_changed` fires for every successful drag move (check Laravel log / Filament audit)
- [ ] Failed moves log `workspace_task.status_update_denied` in Laravel log with user_id, task_id, reason

---

## Phase 5 Fix 3 — Talent Kanban Drag-Drop Permission Fix

Run after `git pull && php artisan optimize:clear && php artisan view:clear` (no new migrations).

### Scenario 1: Assigned talent — allowed drag (core scenario)

- [ ] Login as talent user who is assigned to a task in the workspace
- [ ] Kanban board loads — drag handles visible on their assigned task
- [ ] Drag task from Pending → In Progress
- [ ] Card lands in In Progress column
- [ ] Green toast: "Task moved to \"In Progress\"."
- [ ] Check Laravel log: `workspace_task.status_update_attempt` entry with `effective_role: talent`, `is_task_assignee: true`

### Scenario 2: Talent — submit task

- [ ] Task in In Progress, assigned to talent
- [ ] Drag In Progress → Submitted
- [ ] Move succeeds, green toast

### Scenario 3: Talent — invalid move blocked with descriptive message

- [ ] Task in Submitted status, assigned to talent
- [ ] Talent drags to Approved column (not allowed for talent)
- [ ] Card reverts to Submitted
- [ ] Red toast shows server message: "This task cannot be moved from \"Submitted\" to \"Approved\". Allowed next statuses: Approved, Revision Requested."
- [ ] Browser console shows `[GVOS Kanban] Drag rejected` with httpStatus 422 and response JSON

### Scenario 4: Primary talent — unassigned task

- [ ] Login as the user set as `primary_talent_id` on the workspace
- [ ] Board has an unassigned task (assigned_to_user_id is null)
- [ ] Drag handle is visible on that unassigned task
- [ ] Drag Pending → In Progress
- [ ] Move succeeds

### Scenario 5: Regular talent — cannot move unassigned task

- [ ] Login as a talent who is a workspace member but NOT the primary_talent_id
- [ ] Board has an unassigned task
- [ ] Drag handle IS visible (unassigned tasks show handle for all talent)
- [ ] Attempt to drag unassigned task to another column
- [ ] Card reverts
- [ ] Red toast: "Only the primary talent can move unassigned tasks."

### Scenario 6: Manager — broad access

- [ ] Login as manager (primary manager or member with role=manager)
- [ ] Drag handles visible on all task cards
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag In Progress → Cancelled → succeeds

### Scenario 7: assigned_user tier — SortableJS enabled

- [ ] If a user is assigned to a task but has NO workspace member row (assigned_user tier)
- [ ] Kanban board loads — SortableJS IS initialized (CAN_DRAG = true)
- [ ] Drag handle IS visible on their specific assigned task
- [ ] Drag Pending → In Progress → succeeds
- [ ] Check Laravel log: `workspace_role: assigned_user`, `effective_role: talent`, `is_task_assignee: true`

### Logging Verification

- [ ] Every drag attempt (success or fail) logs `workspace_task.status_update_attempt` with: user_id, user_email, user_roles, workspace_id, task_id, task_code, task_assigned_to, from_status, requested_status, workspace_role, effective_role, is_task_assignee, is_primary_talent, is_primary_manager, allowed_transitions
- [ ] Every failed drag logs `workspace_task.status_update_denied` with reason field
- [ ] Browser console shows `[GVOS Kanban] Drag rejected` for server-rejected drags with httpStatus and full response JSON

---

## Phase 5 Fix 4 — Workspace Role Expansion

Run after `git pull && php artisan migrate && php artisan optimize:clear && php artisan view:clear`.

### Scenario 1: workspace_admin role

- [ ] In Filament, open a workspace and add a user as `workspace_admin` in the Members relation manager
- [ ] Log in as that user → Kanban board loads
- [ ] Drag handles visible on ALL task cards
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag Pending → Cancelled → succeeds
- [ ] Drag In Progress → Blocked → succeeds
- [ ] Debug role line under "Kanban Board" heading shows: `Task role: workspace_admin`

### Scenario 2: client_admin role

- [ ] Add a user as `client_admin` in workspace members
- [ ] Log in as that user → Kanban board loads
- [ ] Drag handles visible ONLY on cards in the Submitted and Approved columns
- [ ] No drag handles on Pending, In Progress, Blocked, Revision Req., Closed cards
- [ ] Drag Submitted → Approved → succeeds, green toast
- [ ] Drag Submitted → Revision Requested → succeeds, green toast
- [ ] Drag Approved → Closed → succeeds, green toast
- [ ] Drag Pending → In Progress → card reverts, red toast with 403/permission message
- [ ] Debug role line NOT shown (only shows for admin/workspace_admin/manager)

### Scenario 3: client_staff role

- [ ] Add a user as `client_staff` in workspace members
- [ ] Log in as that user → Kanban board loads
- [ ] NO drag handles visible on any card
- [ ] `CAN_DRAG` is false — SortableJS NOT initialized for this user
- [ ] Attempting status update via API (dev tools) → 403 "Client staff members cannot move task cards."

### Scenario 4: Legacy `client` member row still works

- [ ] A workspace member with old `role=client` (legacy) can still access the board
- [ ] They see drag handles on submitted/approved tasks (same as client_admin)
- [ ] Drag Submitted → Approved → succeeds
- [ ] Drag Pending → In Progress → fails with 403

### Scenario 5: Filament role dropdown default

- [ ] Open Filament → Workspace → edit page → Members relation manager
- [ ] Click "Add Member" → Role dropdown defaults to `Talent` (not `Client`)
- [ ] All 7 role options visible: Workspace Admin, Manager, Talent, Client Admin, Client Staff, Client, Observer
- [ ] workspace_admin badge shows in red/danger color
- [ ] client_admin and client_staff badges show in warning/amber color
- [ ] manager badge shows in info/blue color
- [ ] talent badge shows in success/green color

### Scenario 6: Role expansion migration

- [ ] `php artisan migrate` runs without error on a clean DB
- [ ] `workspace_members` table accepts `workspace_admin`, `client_admin`, `client_staff` values
- [ ] Existing rows with old role values (client/talent/manager/observer) are unaffected

### Scenario 7: Debug role line visibility

- [ ] Login as admin → board shows `Task role: admin` under heading
- [ ] Login as workspace_admin → shows `Task role: workspace_admin`
- [ ] Login as manager → shows `Task role: manager`
- [ ] Login as talent → NO debug line shown
- [ ] Login as client_admin → NO debug line shown
- [ ] Login as observer → NO debug line shown

---

---

## Phase 6 — Workspace Chat & Files

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan view:clear`.

### Migrations
- [ ] `php artisan migrate` runs without error
- [ ] `workspace_messages` table exists with all expected columns
- [ ] `workspace_files` table exists with all expected columns
- [ ] `php artisan route:list` shows chat routes (workspace.chat.index, workspace.chat.store, workspace.chat.destroy)
- [ ] `php artisan route:list` shows file routes (workspace.files.index, workspace.files.store, workspace.files.download, workspace.files.destroy)
- [ ] `php artisan route:list` shows task file route (workspace.tasks.files.store)

### Workspace Chat — Manager/Admin (full access)

- [ ] Login as admin or manager. Navigate to a workspace → click "Chat" card
- [ ] `/workspaces/{workspace}/chat` loads without error
- [ ] Breadcrumb shows workspace code + "Chat"
- [ ] Nav links to "Files" and "Task Board" present
- [ ] Empty state shown when no messages exist
- [ ] Post a public message — appears in list with correct name, timestamp, "you" indicator
- [ ] Post an internal message (check "Internal" checkbox) — appears with "Internal" badge
- [ ] Internal badge is visible to admin/manager
- [ ] Delete button appears on own message — clicking soft-deletes it; message removed from view
- [ ] `workspace_message.created` audit log entry fires
- [ ] `workspace_message.deleted` audit log entry fires on delete

### Workspace Chat — Talent (public only)

- [ ] Login as talent assigned to workspace. Navigate to chat page
- [ ] Chat page loads
- [ ] Only public messages shown — no internal messages visible
- [ ] "Internal" checkbox NOT shown on post form
- [ ] Post a message → saved as public (no internal option)
- [ ] Delete button shown only on own messages
- [ ] Cannot delete another user's message

### Workspace Chat — Client (public only)

- [ ] Login as client member. Navigate to chat page
- [ ] Only public messages shown
- [ ] Can post public messages
- [ ] No internal checkbox visible

### Workspace Chat — Observer

- [ ] Login as observer. Navigate to chat page
- [ ] Can view public messages (read-only)
- [ ] Post form IS shown but submitting → 403
- [ ] Or: "Your role allows viewing messages only" notice visible (view-only notice)

### Workspace Files — Manager/Admin (full access)

- [ ] Navigate to `/workspaces/{workspace}/files`
- [ ] Page loads with upload form (left) and file list (right)
- [ ] Upload a PDF file — appears in list with correct filename, category badge, size, uploaded-by
- [ ] Upload with `visibility=internal` — appears with "Internal" visibility badge
- [ ] Download a file — browser prompts file download (not a redirect to a public URL)
- [ ] File is served by the app (URL is `/workspaces/{workspace}/files/{file}/download`)
- [ ] `workspace_file.uploaded` audit log entry fires
- [ ] `workspace_file.downloaded` audit log entry fires; `downloads_count` increments in DB
- [ ] Delete button deletes the file record (soft delete); file disappears from list
- [ ] `workspace_file.deleted` audit log entry fires
- [ ] Physical file remains on disk after soft delete

### Workspace Files — Talent / Client (public files only)

- [ ] Login as talent or client. Navigate to files page
- [ ] Only public files visible — internal files NOT shown
- [ ] Can upload a file (saved as public even if user tries to set internal via POST manipulation)
- [ ] Can download public files
- [ ] Cannot see the "Internal" checkbox on the upload form
- [ ] Can delete own uploads; cannot delete others' files

### Workspace Files — Observer

- [ ] Login as observer. Navigate to files page
- [ ] Can view public file list
- [ ] Upload form NOT visible (or shows 403 on submit)

### Task File Attachments

- [ ] Navigate to a task detail page (`/workspaces/{ws}/tasks/{task}`)
- [ ] Sidebar shows "Task Files" section
- [ ] Upload a file from the task page — file appears in sidebar list with type icon, size, date
- [ ] File is linked to the task (stored with `workspace_task_id`)
- [ ] Download link for task attachment works
- [ ] Admin/manager sees "Internal" checkbox on task attachment upload form
- [ ] Talent/client do not see the Internal checkbox on task attachment upload

### Filament — WorkspaceFileResource

- [ ] `/admin/workspace-files` loads — nav group "Workspace", item "Files" visible (sort 4)
- [ ] Table shows workspace code, filename, title, category, visibility badge, uploaded by, size, downloads count, created at
- [ ] Visibility filter works (Public / Internal)
- [ ] Category filter works
- [ ] Archive action (soft delete) visible — clicking confirms and removes record from list
- [ ] No "New file" / Create button visible (canCreate = false)
- [ ] No edit link on rows (canEdit = false)

### Filament — WorkspaceMessageResource

- [ ] `/admin/workspace-messages` loads — nav group "Workspace", item "Messages" visible (sort 5)
- [ ] Table shows workspace code, author name, message (truncated to 60 chars), visibility badge, message type, created at
- [ ] Visibility filter works
- [ ] Type filter works (Text / System)
- [ ] Remove/Moderate action soft-deletes the message
- [ ] No create button visible

### Workspace Show — Chat & Files Cards

- [ ] Navigate to `/workspaces/{workspace}`
- [ ] "Chat" card visible with message count and link to chat page
- [ ] "Files" card visible with file count and link to files page
- [ ] "Time Tracking", "Billing", "Password Vault" placeholder cards visible with dashed border and opacity-50

### Admin Dashboard

- [ ] Super Admin dashboard: "Chat & Files" section shows Total Messages and Total Files count cards
- [ ] Total Messages card links to `/admin/workspace-messages`
- [ ] Total Files card links to `/admin/workspace-files`
- [ ] Phase notice updated to "Phase 6 — Chat & Files"
- [ ] Operations Admin dashboard: same section and notice

### Other Dashboards

- [ ] Talent dashboard: "Communication" section shows "Chat & Files" link (only if workspaces > 0)
- [ ] Line Manager dashboard: "Communication" section shows "Chat & Files" link (only if managed workspaces exist)
- [ ] Individual Client dashboard: "Workspace Chat" and "Workspace Files" links shown (only if workspaces > 0)
- [ ] Business Client Admin: same chat/files links
- [ ] Business Client Staff: same chat/files links
- [ ] All dashboards: Phase notice updated to "Phase 6"

### Access Control — Edge Cases

- [ ] Non-member accessing `/workspaces/{workspace}/chat` → 403
- [ ] Non-member accessing `/workspaces/{workspace}/files` → 403
- [ ] Downloading an internal file as client → 403
- [ ] Downloading a file for a workspace you're not a member of → 403
- [ ] Uploading a file exceeding 10 MB → validation error shown on form
- [ ] Uploading an unsupported file type (e.g. `.exe`) → validation error shown on form
- [ ] `workspace_file.downloaded` audit fires with correct file and user context

---

## Phase 7 — Time Tracking & Work Reports

### Migrations
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_time_logs` table exists with all columns and indexes
- [ ] `workspace_weekly_reports` table exists with all columns and indexes

### Time Log — Talent / Admin creation
- [ ] Talent can navigate to Workspace → Time Logs → Log Time
- [ ] Create form saves draft log with date, summary, duration
- [ ] Start/end time auto-calculates duration when duration_minutes is blank
- [ ] Talent can submit a draft log (status → submitted)
- [ ] Talent can only see own logs on index page
- [ ] Talent can edit own draft or rejected log; cannot edit submitted/approved
- [ ] Talent can delete own draft log only

### Time Log — Manager review
- [ ] Manager sees all logs on index (all statuses)
- [ ] Manager sees review form on show page (only when status = submitted)
- [ ] Manager can approve, mark reviewed, or reject a log
- [ ] Manager can set visibility = client_summary and write client_visible_summary
- [ ] Manager can write manager_notes (internal)

### Time Log — Client visibility
- [ ] Client index shows only approved + client_summary logs
- [ ] Client sees client_visible_summary, not work_summary
- [ ] Client cannot see manager_notes or work_details
- [ ] Client cannot access create/edit/delete routes (403)
- [ ] Observer gets 403 on time log index and show

### Task show page — time logs sidebar
- [ ] Talent/Manager see time log section in task show sidebar
- [ ] Log Time button pre-selects task via query param
- [ ] Client role does NOT see time logs sidebar
- [ ] Observer does NOT see time logs sidebar

### Weekly reports — creation and editing
- [ ] Manager creates a weekly report with suggested week dates
- [ ] total_minutes auto-filled from approved logs for that week
- [ ] Manager can save as draft or submit
- [ ] Manager can approve a submitted report (status → approved)
- [ ] Manager can publish an approved report (published_at populated)
- [ ] Manager cannot edit approved or published report (403)

### Weekly reports — visibility
- [ ] Client sees only published reports
- [ ] Talent sees submitted/approved/published (not draft)
- [ ] Manager/Admin sees all statuses
- [ ] Blockers/next_steps hidden from client on show page
- [ ] Observer gets 403 on reports index

### Workspace show page
- [ ] Time Logs active card links to time-logs.index with correct count
- [ ] Weekly Reports active card links to reports.index with correct count
- [ ] Client counts are filtered (approved+client_summary / published)
- [ ] Billing and Password Vault remain as dashed placeholder cards
- [ ] Old Time Tracking placeholder is removed

### Filament resources
- [ ] WorkspaceTimeLogResource appears under Workspace, sort 7
- [ ] WorkspaceWeeklyReportResource appears under Workspace, sort 8
- [ ] Status badge colours correct (approved=success, rejected=danger, etc.)
- [ ] Create buttons absent on both resources

### Dashboard Phase 7 notices
- [ ] All 7 portal dashboards show "Phase 7 — Time Tracking & Work Reports" notice

### Access control edge cases
- [ ] No workspace membership → 403 on all time log and report routes
- [ ] Talent cannot call review route (403)
- [ ] Client cannot call create/store/edit/update on logs or reports (403)

---

## Phase 8 — Billing Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `billing_plans` table exists with currency, amount, billing_cycle, status, and soft deletes
- [ ] `workspace_subscriptions` table exists with workspace, plan, billing dates, status, and grace fields
- [ ] `invoices` table exists with `invoice_number`, totals, payment fields, status, and soft deletes
- [ ] `invoice_items` table exists and links to invoices
- [ ] `payments` table exists with provider, provider_reference, status, confirmation fields, raw_payload, and soft deletes

### Filament Billing Resources
- [ ] `/admin/billing-plans` loads under Billing navigation
- [ ] Admin creates a billing plan with `bi_weekly` cycle
- [ ] Admin archives a billing plan; status changes to `archived`
- [ ] `/admin/workspace-subscriptions` loads
- [ ] Admin creates a subscription for a workspace and optional plan
- [ ] Subscription status, next billing date, and grace period save correctly
- [ ] `/admin/invoices` loads
- [ ] Admin creates an invoice with at least one line item
- [ ] Admin invoice create form shows sections in this order: Invoice Identity, Invoice Items, Totals and Payment Summary, Notes
- [ ] Admin invoice edit form uses the same section order as create
- [ ] Totals and payment fields appear below the invoice items repeater
- [ ] Totals section helper text says totals are calculated from invoice items and payment records where available
- [ ] Discount amount and tax amount remain editable
- [ ] Existing manual total behavior is preserved for invoices without line items
- [ ] Invoice number auto-generates as `GVOS-INV-YYYYMM-0001`
- [ ] Invoice totals and balance due recalculate from line items
- [ ] Issue invoice action changes status from `draft` to `issued`
- [ ] Mark Paid action marks invoice paid, sets `paid_at`, and clears balance
- [ ] Cancel action cancels draft/issued invoices
- [ ] `/admin/payments` loads
- [ ] Admin records a manual payment linked to an invoice
- [ ] Confirm payment action marks payment confirmed
- [ ] Confirmed payment increments invoice `amount_paid`
- [ ] Partial payment changes invoice to `partially_paid`
- [ ] Full payment changes invoice to `paid`, sets `paid_at`, and clears balance
- [ ] Linked subscription receives `last_paid_at`; payment_due/overdue/suspended returns to active

### Portal Billing Views
- [ ] Workspace detail page shows active Billing card for admin/manager/client roles
- [ ] Billing card shows subscription status if present
- [ ] Billing card shows next billing date if present
- [ ] Billing card shows outstanding balance from issued/partial/overdue invoices
- [ ] `/workspaces/{workspace}/billing` shows subscription summary, invoices, payments, and payment instructions placeholder
- [ ] `/workspaces/{workspace}/billing/invoices/{invoice}` shows invoice items, totals, notes, and payment history
- [ ] Invoice detail page header shows GVOS Invoice, invoice number, status badge, Back to Billing button, and Print button
- [ ] Invoice detail page shows bill-to/client details, workspace name, company when available, issue date, due date, subscription/billing cycle, and currency
- [ ] Invoice items table shows description, item type, quantity, unit amount, and total amount
- [ ] Totals section sits directly below the invoice items table
- [ ] Totals display in this order: subtotal, discount, tax, total amount, amount paid, balance due
- [ ] Total amount is emphasized and balance due is visually clear
- [ ] Money values are right-aligned on desktop and remain readable on mobile
- [ ] Payment history table shows payment reference, provider, amount, status, and paid-at date
- [ ] Print button calls `window.print()` and does not generate a PDF
- [ ] `/workspaces/{workspace}/billing/payments` shows payment history and empty state when none exist

### Access Control
- [ ] Client admin can view billing for their own workspace
- [ ] Individual client can view billing for their own workspace
- [ ] Business client staff can view billing if they have workspace access
- [ ] Client cannot see invoice `internal_notes`
- [ ] Client cannot see internal payment confirmation notes
- [ ] Talent receives 403 on billing routes
- [ ] Observer receives 403 on billing routes
- [ ] Non-member receives 403 on billing routes
- [ ] Invoice detail route rejects invoices from another workspace using integer FK comparison

### Dashboard Updates
- [ ] Super Admin dashboard shows total invoices, outstanding invoices, paid invoices, and confirmed payments total
- [ ] Operations Admin dashboard shows outstanding billing action item
- [ ] Individual Client dashboard shows billing quick link and outstanding balance
- [ ] Business Client Admin dashboard shows billing quick link and outstanding balance
- [ ] Talent dashboard does not show billing

### Audit Log Verification
- [ ] `billing_plan.created` fires on plan creation
- [ ] `billing_plan.updated` fires on plan edit/archive
- [ ] `workspace_subscription.created` fires on subscription creation
- [ ] `workspace_subscription.updated` fires on subscription edit
- [ ] `invoice.created`, `invoice.updated`, `invoice.issued`, `invoice.cancelled`, `invoice.marked_paid` fire as expected
- [ ] `payment.recorded`, `payment.confirmed`, `payment.failed_or_cancelled` fire as expected

### Regression Checks
- [ ] Existing workspace tasks still load
- [ ] Existing workspace chat still loads
- [ ] Existing workspace files still load
- [ ] Existing time logs still load
- [ ] Existing weekly reports still load
- [ ] No live gateway payment button appears anywhere
- [ ] No payroll UI appears anywhere
- [ ] Password vault exists only as the Phase 10 vault module after Phase 10 deployment; billing flows do not depend on it
- [ ] No visible UI contains `GetVirtual`

---

## Phase 9 — Semi Automated Time Tracking

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Routes
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_time_logs.status` accepts `running`
- [ ] `php artisan route:list` shows `time-tracker.current`
- [ ] `php artisan route:list` shows `workspace.time-tracker.start`
- [ ] `php artisan route:list` shows `workspace.time-tracker.stop`
- [ ] `php artisan route:list` shows `workspace.time-tracker.complete`

### Talent Timer Flow
- [ ] Talent dashboard shows Clock In when no timer is running
- [ ] Talent can select a workspace and optional task before starting
- [ ] Clock In creates a running time log with `started_at`, `status=running`, `ended_at=null`
- [ ] Dashboard shows a live elapsed timer after start
- [ ] Browser refresh still shows the running timer
- [ ] Clock Out stops the timer, sets `ended_at`, calculates `duration_minutes`, and saves status `draft`
- [ ] Complete Work Session requires a work summary and saves status `submitted`
- [ ] User cannot start a second running timer while one already exists

### Workspace Time Log Pages
- [ ] Workspace time logs index shows active timer controls above the table
- [ ] Workspace time logs index shows totals/table below the timer controls
- [ ] Time log detail page shows running session state for a running log
- [ ] Authorized users can stop or complete a running log from the detail page
- [ ] Running logs cannot be edited manually
- [ ] Running logs cannot be reviewed
- [ ] Running logs cannot be deleted

### Task Detail Integration
- [ ] Task detail page shows Start Timer for active task statuses
- [ ] Starting from a task links `workspace_task_id` to the created running log
- [ ] If the current user has a timer running elsewhere, task page links to the active timer instead of starting another

### Manager/Admin Visibility
- [ ] Manager/admin time log index shows running timers for the workspace
- [ ] Manager/admin can open a running timer record
- [ ] Manager/admin can stop or complete another user's running timer when needed
- [ ] Filament Workspace Time Logs table shows running status badge
- [ ] Filament Workspace Time Logs table shows started timestamp and duration display

### Client Protection
- [ ] Client cannot start, stop, or complete timers
- [ ] Client cannot see running time logs
- [ ] Client time log views still show only approved `client_summary` logs
- [ ] Client cannot see work_details, manager_notes, or internal summaries

### Audit and Regression
- [ ] `workspace_time_tracker.started` audit entry fires on Clock In
- [ ] `workspace_time_tracker.stopped` audit entry fires on Clock Out
- [ ] `workspace_time_tracker.completed` audit entry fires on Complete Work Session
- [ ] Existing manual time log create/edit/review still works
- [ ] Existing weekly reports still work
- [ ] Existing billing invoice/payment confirmation flows still work
- [ ] No billing database or payment logic changes are present
- [ ] No screenshots, keystrokes, screen monitoring, payroll, or billing automation appears; password vault appears only as the Phase 10 module after Phase 10 deployment
- [ ] No visible UI contains `GetVirtual`

---

## Phase 10 — Password Vault Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Models
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `workspace_vault_items` table exists with encrypted secret storage fields and soft deletes
- [ ] `workspace_vault_access_logs` table exists with action, IP, user agent, metadata, and created_at
- [ ] `WorkspaceVaultItem.secret_value` is encrypted at rest and not visible as plaintext in DB rows
- [ ] `WorkspaceVaultItem` array/JSON output does not include `secret_value`

### Portal Vault Routes
- [ ] `php artisan route:list` shows `workspace.vault.index`
- [ ] `php artisan route:list` shows `workspace.vault.create`
- [ ] `php artisan route:list` shows `workspace.vault.store`
- [ ] `php artisan route:list` shows `workspace.vault.show`
- [ ] `php artisan route:list` shows `workspace.vault.edit`
- [ ] `php artisan route:list` shows `workspace.vault.update`
- [ ] `php artisan route:list` shows `workspace.vault.reveal`
- [ ] `php artisan route:list` shows `workspace.vault.archive`
- [ ] `php artisan route:list` shows `workspace.vault.access-logs`

### Portal Vault UX
- [ ] Workspace detail page shows Password Vault card only when the user can create vault items or has visible assigned items
- [ ] Vault index shows metadata only: title, category, username, URL, visibility, status, last revealed
- [ ] Vault index does not show plaintext secret values
- [ ] Authorized creator/admin/manager/client admin can create a vault item
- [ ] Edit form does not prefill the existing secret
- [ ] Leaving secret blank on edit preserves the current encrypted secret
- [ ] Entering a new secret on edit rotates the stored secret
- [ ] Archive action changes status to `archived`; it does not hard-delete the record
- [ ] Secret detail page hides the secret by default
- [ ] Reveal button returns and displays the secret only for allowed users
- [ ] Copy button copies the secret only for allowed users
- [ ] Reveal and copy actions create `workspace_vault_access_logs` rows
- [ ] Access logs page shows metadata only and never shows plaintext secrets

### Access Control
- [ ] Super Admin / Operations Admin can view and manage all vault items through portal access as `admin`
- [ ] Workspace Admin can manage workspace vault items
- [ ] Manager can manage vault items and reveal only workspace-admin-visible, own-created, or explicitly assigned items
- [ ] Client Admin can create items and manage/reveal own-created or explicitly assigned items
- [ ] Client Staff cannot create, edit, archive, or view logs
- [ ] Talent and assigned users can only view/reveal explicitly assigned items
- [ ] Observer receives 403 on vault routes
- [ ] Non-member receives 403 on vault routes
- [ ] Allowed user assignment rejects users outside the workspace member/primary team/task-assignee set

### Filament Vault Resources
- [ ] `/admin/workspace-vault-items` loads under Workspace navigation
- [ ] Admin can create a vault item in Filament
- [ ] Filament vault item table does not show `secret_value`
- [ ] Filament edit form does not prefill the existing secret
- [ ] Blank secret on edit preserves the current value
- [ ] Filament Archive action changes status to `archived`
- [ ] Filament Restore action changes status to `active`
- [ ] `/admin/workspace-vault-access-logs` loads as read-only
- [ ] Filament access log table shows item, workspace, user, action, IP, and timestamp only
- [ ] No delete action appears on vault items or access logs

### Audit and Regression
- [ ] `workspace_vault_item.created` fires on vault item creation
- [ ] `workspace_vault_item.updated` fires on vault item update
- [ ] `workspace_vault_item.archived` fires on archive
- [ ] `workspace_vault_item.restored` fires on restore
- [ ] `workspace_vault_item.secret_revealed` fires on reveal/copy
- [ ] `workspace_vault_item.access_logs_viewed` fires when logs are viewed
- [ ] Audit log context does not include plaintext secrets
- [ ] Existing workspace tasks still load
- [ ] Existing workspace chat still loads
- [ ] Existing workspace files still load
- [ ] Existing time logs and timer controls still load
- [ ] Existing weekly reports still load
- [ ] Existing billing invoice/payment confirmation flows still work
- [ ] No payment gateway button, payroll UI, auto-login, browser extension, screenshot capture, keystroke capture, or screen-monitoring UI appears
- [ ] No visible UI contains `GetVirtual`

---

## Phase 11 - Notifications and Email System Foundation

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Migrations and Routes
- [ ] `php artisan migrate` runs without error on cPanel
- [ ] `notifications` table exists with UUID id, type, notifiable morphs, data, read_at, timestamps
- [ ] `user_notification_preferences` table exists with user_id, notification_key, in_app_enabled, email_enabled
- [ ] `php artisan route:list` shows `notifications.index`
- [ ] `php artisan route:list` shows `notifications.read`
- [ ] `php artisan route:list` shows `notifications.read-all`
- [ ] `php artisan route:list` shows `settings.notifications`
- [ ] `php artisan route:list` shows `settings.notifications.update`

### Portal Notification UI
- [ ] Header notification bell links to `/notifications`
- [ ] Header notification bell shows unread count when unread notifications exist
- [ ] `/notifications` loads for authenticated users
- [ ] Notifications show unread first, title, message, date, action button, and mark-read action
- [ ] Mark one notification as read works
- [ ] Mark all as read works
- [ ] Empty notification state displays when no notifications exist
- [ ] User cannot mark another user's notification as read

### Preference UI
- [ ] `/settings/notifications` loads
- [ ] User can toggle in-app and email preferences for all 10 notification keys
- [ ] Saving preferences creates or updates `user_notification_preferences` rows
- [ ] `notification_preferences.updated` audit event fires
- [ ] Chat/message, task comment, task status, and file upload email are disabled by default
- [ ] Important notifications have email enabled by default when no preference row exists

### Trigger Tests
- [ ] Creating or assigning a task creates a notification for the assigned user
- [ ] Changing task status creates notifications for relevant recipients
- [ ] Adding a task comment creates a notification without including comment body in payload
- [ ] Uploading a file creates a notification without exposing raw storage path
- [ ] Posting a workspace chat message creates in-app notifications but does not spam email by default
- [ ] Submitting a time log notifies manager/workspace admin and does not notify clients
- [ ] Completing a timer as submitted notifies manager/workspace admin
- [ ] Publishing a weekly report notifies client-side workspace users
- [ ] Issuing an invoice notifies client-side workspace users
- [ ] Recording or confirming a payment notifies client-side users and relevant non-actor admins
- [ ] Approving a trial notifies the active lead user

### Safety and Regression
- [ ] Notification payloads do not contain vault secrets
- [ ] Notification payloads do not contain payment raw_payload
- [ ] Notification payloads do not contain internal invoice notes or manager notes
- [ ] Notification payloads do not contain raw file paths
- [ ] Existing tasks, chat, files, time logs, reports, billing, and vault routes still work
- [ ] Email notifications do not break the app when mail is not configured
- [ ] No payment gateway, payroll, real-time websocket chat, screenshot capture, keystroke capture, screen monitoring, auto-login, or browser extension UI appears
- [ ] No visible UI contains `GetVirtual`

## Phase 12 — Launch Readiness (planned)

## Phase 12 - Stabilization, QA, Access Audit and Bug Fix Pass

Run after `git pull origin main && php artisan migrate && php artisan optimize:clear && php artisan permission:cache-reset`.

### Required Artisan Checks
- [ ] `php artisan migrate` runs without error
- [ ] `php artisan optimize:clear` runs without error
- [ ] `php artisan permission:cache-reset` runs without error
- [ ] `php artisan route:list` loads without route errors
- [ ] `php artisan route:list --name=vault` shows vault routes
- [ ] `php artisan route:list --name=notification` shows notification routes
- [ ] `php artisan route:list --name=time-tracker` shows timer routes
- [ ] `php artisan route:list --name=billing` shows billing routes

### Access and Permission Regression
- [ ] Portal task create rejects an assignee who is not an active workspace member or primary team user
- [ ] Portal task edit rejects changing assignee to a non-workspace user
- [ ] Existing valid task assignment to active workspace members still saves
- [ ] Talent cannot access workspace billing routes
- [ ] Clients cannot see running timers, internal time details, manager notes, invoice internal notes, or payment confirmation notes
- [ ] Non-members receive 403/404 on nested workspace routes
- [ ] Users can mark only their own notifications read
- [ ] Vault reveal is POST-only, workspace-scoped, permission-checked, and logs metadata only

### Module Regression
- [ ] Admin invoice create/edit form still shows totals below invoice items
- [ ] Invoice issue action still sends safe notifications
- [ ] Payment confirmation still updates invoice `amount_paid`, `balance_due`, and status
- [ ] One running timer per user is still enforced
- [ ] Completing a timer still requires `work_summary`
- [ ] Filament Time Logs table loads and has no view/edit/delete actions
- [ ] File library loads with pagination and downloads still work
- [ ] Chat loads the latest messages in readable order
- [ ] Notification mark-all-read marks the current user's unread notifications only

### Branding and Safety
- [ ] No visible UI contains `GetVirtual`
- [ ] No visible payment gateway, payroll, browser extension, auto-login, screenshot capture, keystroke tracking, or screen-monitoring UI appears
- [ ] Notification payloads do not contain vault secrets, raw file paths, payment raw payloads, internal invoice notes, manager notes, tokens, or API keys
- [ ] No new database migrations were added for Phase 12
