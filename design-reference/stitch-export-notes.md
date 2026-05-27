# GVOS — Stitch Export Notes

## Overview
This file documents each Stitch screen, its purpose, which role uses it, and notes for implementation.

All screens are located in: `/design-reference/screens/stitch_gvos_operations_platform/`

---

## Authentication Screens

### login_gvos_1 / login_gvos_2
- **Purpose:** Main login screen
- **Users:** All roles
- **Implementation Phase:** Phase 1
- **Notes:** Two variants. login_gvos_1 is the primary. Clean centered card with GVOS branding. Email + password fields. "Forgot password" link. Monitoring notice at bottom.

### forgot_password_gvos
- **Purpose:** Password reset request page
- **Users:** All roles
- **Implementation Phase:** Phase 1
- **Notes:** Simple email input. Submit triggers reset email.

### accept_invitation_gvos
- **Purpose:** Staff accepts email invitation to join company workspace
- **Users:** business_client_staff, talent
- **Implementation Phase:** Phase 2
- **Notes:** Token-based. User sets password on first access.

---

## Lead Screens

### lead_dashboard_gvos
- **Purpose:** Active lead's view of their inquiry status and price estimate
- **Users:** active_lead
- **Implementation Phase:** Phase 3
- **Notes:** Shows lead status progress, price estimate details, accept/reject actions.

### lead_management_gvos / leads_management_gvos
- **Purpose:** Admin view of all leads
- **Users:** super_admin, operations_admin (Filament)
- **Implementation Phase:** Phase 3
- **Notes:** Table with lead status, filters, actions. May be Filament native resource.

### price_estimate_accepted_gvos
- **Purpose:** Confirmation screen after lead accepts price estimate
- **Users:** active_lead
- **Implementation Phase:** Phase 3
- **Notes:** Success state. Informs lead that trial is being prepared.

---

## Trial Screens

### trial_workspace_gvos
- **Purpose:** Trial workspace view for lead/new client
- **Users:** active_lead (trial)
- **Implementation Phase:** Phase 3
- **Notes:** Limited workspace with task area and chat. Trial expiry notice shown.

### trial_chat_gvos
- **Purpose:** Chat within trial workspace
- **Users:** active_lead, talent (trial)
- **Implementation Phase:** Phase 3
- **Notes:** Basic chat without full features.

### trial_task_board_gvos
- **Purpose:** Task board within trial workspace
- **Users:** active_lead, talent (trial)
- **Implementation Phase:** Phase 3
- **Notes:** Simplified task board. Limited to trial features.

### trial_expired_gvos
- **Purpose:** Screen shown when trial has expired without conversion
- **Users:** active_lead
- **Implementation Phase:** Phase 3
- **Notes:** Informs user trial ended. CTA to contact GetVirtual.

---

## Client Portal Screens

### client_dashboard_gvos / client_dashboard_mobile_gvos
- **Purpose:** Individual client's main workspace overview
- **Users:** individual_client, business_client_admin, business_client_staff
- **Implementation Phase:** Phase 4 (basic), Phase 7 (with time summary)
- **Notes:** Shows workspace activity summary, recent tasks, weekly summary card, quick actions.

### business_admin_dashboard_gvos
- **Purpose:** Business client admin dashboard
- **Users:** business_client_admin
- **Implementation Phase:** Phase 2/4
- **Notes:** Consolidates multiple workspaces, shows company-wide summary.

### billing_invoices_gvos
- **Purpose:** Client billing and invoice view
- **Users:** individual_client, business_client_admin
- **Implementation Phase:** Phase 8
- **Notes:** Invoice list with status badges. Subscription plan details.

### invite_staff_gvos
- **Purpose:** Business admin invites company staff
- **Users:** business_client_admin
- **Implementation Phase:** Phase 2
- **Notes:** Email input with domain validation. Invitation sent flow.

### staff_permissions_gvos
- **Purpose:** Business admin manages staff permissions
- **Users:** business_client_admin
- **Implementation Phase:** Phase 2
- **Notes:** Per-staff permission toggles for workspace access.

### client_profile_details_gvos
- **Purpose:** Client profile view and edit
- **Users:** individual_client, business_client_admin
- **Implementation Phase:** Phase 1
- **Notes:** Name, email, timezone, company info (if business).

### raise_concern_gvos
- **Purpose:** Client raises a complaint or concern
- **Users:** individual_client, business_client_admin, business_client_staff
- **Implementation Phase:** Phase 9
- **Notes:** Category dropdown, description, file evidence upload.

### suspended_account_gvos
- **Purpose:** Shown when account is suspended (non-payment)
- **Users:** All client roles
- **Implementation Phase:** Phase 8
- **Notes:** Clear suspension message. Contact GetVirtual CTA. No workspace access.

### unauthorized_access_gvos
- **Purpose:** 403 / unauthorized access screen
- **Users:** All roles
- **Implementation Phase:** Phase 1
- **Notes:** Clean error state. Back/home button.

---

## Talent Portal Screens

### talent_dashboard_gvos_1 / talent_dashboard_gvos_2 / talent_dashboard_mobile_gvos
- **Purpose:** Talent's main workspace view
- **Users:** talent
- **Implementation Phase:** Phase 4 (basic), Phase 7 (with time tracking)
- **Notes:** Task list, clock in/out button, daily report link, workspace summary.

### talent_workspace_overview_gvos
- **Purpose:** Talent overview of their workspace(s)
- **Users:** talent
- **Implementation Phase:** Phase 4
- **Notes:** If talent has multiple workspaces, shows summary of each.

### talent_profile_gvos
- **Purpose:** Talent profile page
- **Users:** talent (own), manager (view), admin (full)
- **Implementation Phase:** Phase 1
- **Notes:** Skills, schedule, status, timezone. Admin can edit; talent can view/edit own.

### talent_onboarding_gvos
- **Purpose:** Talent onboarding checklist
- **Users:** talent (new)
- **Implementation Phase:** Phase 2
- **Notes:** Step-by-step onboarding tasks.

### talent_performance_monitoring_gvos
- **Purpose:** Talent performance data view
- **Users:** line_manager, super_admin, operations_admin
- **Implementation Phase:** Phase 7
- **Notes:** Task completion rate, hours, report consistency.

### submit_daily_report_gvos
- **Purpose:** Talent submits end-of-day report
- **Users:** talent
- **Implementation Phase:** Phase 7
- **Notes:** Tasks completed list, notes, submit button.

### submission_success_gvos
- **Purpose:** Confirmation after successful form submission
- **Users:** All roles
- **Implementation Phase:** Phase 7
- **Notes:** Generic success state used across multiple flows.

---

## Manager Console Screens

### manager_command_center_gvos / manager_command_center_mobile_gvos
- **Purpose:** Manager's main oversight dashboard
- **Users:** line_manager
- **Implementation Phase:** Phase 4
- **Notes:** Shows assigned workspaces, talent activity, active clock-ins, flags.

### manager_talent_client_monitoring_gvos
- **Purpose:** Manager monitors specific talent or client activity
- **Users:** line_manager
- **Implementation Phase:** Phase 6/7
- **Notes:** Real-time view of talent clock status, chat activity, task progress.

### manager_talents_list_gvos
- **Purpose:** Manager's list of all assigned talents
- **Users:** line_manager
- **Implementation Phase:** Phase 2
- **Notes:** Table with talent name, status, active workspace, today's hours.

### manager_clients_list_gvos
- **Purpose:** Manager's list of all assigned clients
- **Users:** line_manager
- **Implementation Phase:** Phase 2
- **Notes:** Table with client name, workspace, subscription status.

### manager_reports_complaints_gvos
- **Purpose:** Manager reviews reports and complaints
- **Users:** line_manager
- **Implementation Phase:** Phase 7/9
- **Notes:** Combined view of pending daily reports and open complaints.

### manager_reports_review_gvos
- **Purpose:** Manager reviews and signs off on reports
- **Users:** line_manager
- **Implementation Phase:** Phase 7
- **Notes:** Report detail with sign-off button.

### manager_escalation_center_gvos
- **Purpose:** Manager escalates complaints to admin
- **Users:** line_manager
- **Implementation Phase:** Phase 9
- **Notes:** Escalation form with notes and evidence.

---

## Admin / Ops Console Screens (Filament)

### admin_overview_gvos
- **Purpose:** Admin high-level dashboard
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 1 (Filament dashboard customization)
- **Notes:** KPIs: total clients, active workspaces, overdue invoices, open complaints.

### workspaces_management_gvos
- **Purpose:** Admin workspace management table
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 4
- **Notes:** Filament resource. Filter by status, type, client.

### companies_management_gvos
- **Purpose:** Admin company management
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 2
- **Notes:** Filament resource. Company CRUD with departments.

### departments_management_gvos
- **Purpose:** Admin department management within companies
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 2
- **Notes:** Linked to company resource.

### account_management_gvos
- **Purpose:** Admin user account management
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 1
- **Notes:** All user accounts. Filter by role.

### audit_logs_gvos / audit_logs_mobile_gvos
- **Purpose:** Admin audit log viewer
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 10 (full), Phase 1 (basic)
- **Notes:** Table with user, action, timestamp, context. Filters by action type and date.

### global_billing_overview_gvos
- **Purpose:** Admin view of all subscriptions and invoices
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 8
- **Notes:** KPIs: MRR, overdue invoices, suspended workspaces.

### asset_tracking_gvos
- **Purpose:** Admin asset management
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 12 (or earlier if needed)
- **Notes:** Table of assets with assignment status.

### leads_management_gvos
- **Purpose:** Admin leads pipeline view
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 3
- **Notes:** Kanban or table with lead status. Filament resource.

### workspace_settings_gvos
- **Purpose:** Per-workspace settings management
- **Users:** super_admin, operations_admin
- **Implementation Phase:** Phase 4
- **Notes:** Members, billing start date, status, type.

### workspace_monitoring_gvos / workspace_monitoring_mobile_gvos
- **Purpose:** Real-time workspace activity view
- **Users:** line_manager, super_admin, operations_admin
- **Implementation Phase:** Phase 6
- **Notes:** Active users, current tasks in progress, chat activity indicators.

---

## Workspace Feature Screens

### task_board_gvos / task_board_mobile_gvos
- **Purpose:** Main task kanban board
- **Users:** talent (manage), client (view/approve), manager (oversee)
- **Implementation Phase:** Phase 5
- **Notes:** Columns: Backlog, To Do, In Progress, In Review, Done, Rejected. Drag-and-drop.

### create_task_gvos
- **Purpose:** New task creation form
- **Users:** talent, manager, admin
- **Implementation Phase:** Phase 5
- **Notes:** Title, description, assignee, priority, due date, attachments.

### task_detail_gvos
- **Purpose:** Task detail page with comments and history
- **Users:** All workspace members
- **Implementation Phase:** Phase 5
- **Notes:** Full task detail. Comment thread. Status change buttons. Attachment list.

### workspace_chat_gvos / workspace_chat_mobile_gvos
- **Purpose:** Real-time workspace chat
- **Users:** All workspace members
- **Implementation Phase:** Phase 6
- **Notes:** Message thread with file attachment support. Voice note playback.

### file_library_gvos
- **Purpose:** Shared file storage for workspace
- **Users:** All workspace members (role-gated upload/download)
- **Implementation Phase:** Phase 6
- **Notes:** Grid or list view with categories. Upload button. Download button.

### password_vault_gvos
- **Purpose:** Encrypted credential vault
- **Users:** individual_client, business_client_admin (manage), talent (view granted)
- **Implementation Phase:** Phase 10
- **Notes:** Masked credentials. Reveal button. Access grant management.

### time_daily_reports_gvos / time_tracking_daily_reports_gvos
- **Purpose:** Talent's time log and daily report view
- **Users:** talent
- **Implementation Phase:** Phase 7
- **Notes:** Today's log, weekly total, daily report submission.

### exact_time_logs_review_gvos
- **Purpose:** Manager/admin exact time log review
- **Users:** line_manager, super_admin, operations_admin
- **Implementation Phase:** Phase 7
- **Notes:** Full time log with clock in/out times. Editable by admin.

### weekly_report_gvos
- **Purpose:** Weekly summary report view
- **Users:** line_manager (create/sign off), individual_client (view)
- **Implementation Phase:** Phase 7
- **Notes:** Summary stats, task list, hours worked (no exact times for client).

### empty_states_gvos
- **Purpose:** Empty state illustrations for various sections
- **Users:** All roles
- **Implementation Phase:** Phase 4+
- **Notes:** Use these illustrations for empty lists, no data states.

---

## Additional Design Notes

- The `gvos_stitch_ui_prompt_pack.md` file in the screens folder contains AI design prompts and additional design context from the designer.
- Mobile screens (suffixed `_mobile_gvos`) should be used as reference for responsive breakpoints.
- Dark sidebar is consistent across all portals except the Active Lead pre-access area.
- Status badges color coding must be consistent: green (active), amber (pending/warning), red (error/suspended), blue (info/trial).
