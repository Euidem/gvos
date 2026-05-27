# GVOS — Product Requirements Document (PRD)

## Version: 0.1 — Phase 0 Draft
## Status: Baseline — to be expanded per phase

---

## 1. Purpose
This document defines the functional and non-functional requirements for GVOS. It is the single source of truth for what the platform must do. Claude Code uses this document to guide implementation decisions.

---

## 2. Core Principles
- **Control first** — GetVirtual controls all relationships. The platform enforces this.
- **Role isolation** — Each user role sees only what they are entitled to see.
- **Auditability** — Every sensitive action is logged and reviewable.
- **Security by default** — All data is access-controlled. No public data by default.
- **Clean UX** — The Stitch UI direction is premium, clean, and workspace-centered.
- **SaaS-ready structure** — Though built for GetVirtual first, the architecture should support multi-tenancy later.

---

## 3. User Roles

### 3.1 Super Admin
- Full access to all platform features.
- Can manage all roles, settings, billing, audit logs.
- Access via: GVOS Ops Console (Filament).

### 3.2 Operations Admin
- Can manage leads, clients, companies, talents, managers, workspaces.
- Cannot manage platform settings or billing configuration.
- Access via: GVOS Ops Console (Filament).

### 3.3 Line Manager
- Supervises assigned talents and workspaces.
- Can view exact time logs, raise flags, write reports, manage tasks.
- Access via: GVOS Manager Console.

### 3.4 Talent
- Works within assigned workspace(s).
- Can clock in/out, manage tasks, upload files, chat, submit reports.
- Access via: GVOS Talent Portal.

### 3.5 Individual Client
- Personal client with one workspace.
- Can view weekly summaries, raise concerns, download files, view tasks.
- Cannot see exact time logs.
- Access via: GVOS Client Portal.

### 3.6 Business Client Admin
- Admin of a business account.
- Can manage their company's staff access, view workspaces, and see consolidated reports.
- Access via: GVOS Client Portal.

### 3.7 Business Client Staff
- Employee of a business client company.
- Invited by Business Client Admin using a company email address.
- Limited workspace access defined by the Business Client Admin.
- Access via: GVOS Client Portal.

### 3.8 Active Lead
- A person who has submitted a lead and is in pre-trial.
- Limited access — can view their lead status and pricing.
- Converts to client after trial approval.

---

## 4. Core Modules

### 4.1 Authentication and Access
- Login with email and password.
- Role-based redirection on login.
- Session management.
- Password reset.
- Email verification (Phase 1).
- 2FA (Phase 2+).

### 4.2 Lead Management
- Leads submitted via form or manually by admin.
- Lead status: New → Contacted → Quoted → Trial Approved → Converted → Lost.
- Price estimate generation.
- Trial approval workflow.
- Admin sees all leads. Leads see only their own status.

### 4.3 Trial Workspace
- Temporary workspace created on trial approval.
- Has limited chat, task board, and file area.
- Expires after trial period.
- On trial end: admin can convert to full workspace or mark as lost.

### 4.4 Client Management
- Individual client profile with personal details, workspace, billing info.
- Business client profile with company, departments, staff.
- Client status: Active, Suspended, Churned.

### 4.5 Company and Staff Management
- Business companies with name, address, departments.
- Staff invited via company email domain verification.
- Permissions per staff member managed by Business Client Admin.

### 4.6 Talent Management
- Talent profiles with skills, timezone, shift schedule, assigned workspaces.
- Onboarding checklist (Phase 2).
- Performance tracking linked to time logs and task completion.

### 4.7 Manager Management
- Manager profiles linked to assigned talents and workspaces.
- Manager can view monitoring data, write reports, raise escalations.

### 4.8 Workspace Engine
- One workspace per client (or per department for business clients).
- Workspace has: members, tasks, chat, files, time logs, reports.
- Workspace statuses: Trial, Active, Suspended, Closed.
- Role-aware views: client sees summary, talent sees work area, manager sees oversight view.

### 4.9 Task Board
- Tasks with title, description, status, priority, assignee, due date, attachments.
- Statuses: Backlog → To Do → In Progress → In Review → Done → Rejected.
- Comments and revisions on tasks.
- Client can approve or reject completed tasks.

### 4.10 Chat and Communication
- Workspace chat between client, talent, and manager.
- Voice note support (Phase 6).
- Monitored by manager. Flagging mechanism.
- File attachments in chat.
- No external communication allowed through the platform.

### 4.11 File Management
- Shared file library per workspace.
- Upload/download for clients and talents.
- File categories, versioning (Phase 6+).
- Admin can audit file activity.

### 4.12 Time Tracking
- Talent clocks in/out per workspace or per task.
- Daily time log entry.
- Clients see weekly summary only (not exact times).
- Managers and admins see exact daily time logs.
- Reports: daily, weekly.

### 4.13 Reports
- Talent: daily report submission.
- Manager: weekly summary review and sign-off.
- Client: receives weekly summary view.
- Admin: access to all reports.

### 4.14 Billing Foundation
- Plan types: subscription plans (bi-weekly).
- Billing starts after trial.
- Non-payment → automatic workspace suspension.
- Manual payment recording by admin.
- Invoice generation.
- Payment provider abstraction (Stripe, etc.) — Phase 8+.

### 4.15 Complaints and Satisfaction
- Client or talent can raise a concern.
- Complaints have: category, evidence, status, resolution notes.
- Manager handles first line, escalates to admin.
- Satisfaction surveys sent after key events.

### 4.16 Password Vault
- Encrypted credential storage per workspace.
- Client shares credentials with talent via vault only (not chat).
- Access log for every credential reveal.
- AES-256 encryption at rest.

### 4.17 Audit Logs
- Every sensitive action logged: who, what, when, context.
- Accessible by Super Admin and Operations Admin.
- Cannot be deleted or modified.
- Export capability (Phase 10).

### 4.18 Asset Tracking
- Track physical or digital assets issued to talents.
- Asset record: name, serial, assigned to, date, status.
- Admin managed.

---

## 5. Non-Functional Requirements

### 5.1 Security
- All user data is role-scoped.
- Sensitive fields encrypted at rest.
- CSRF protection (Laravel default).
- Rate limiting on auth endpoints.
- Activity monitoring visible to managers and admins.
- Users informed that activity is tracked on login and platform.

### 5.2 Performance
- Dashboard loads under 2 seconds.
- File uploads up to 50MB (Phase 6).
- Queued jobs for notifications, reports, heavy processing.

### 5.3 Reliability
- Zero-downtime deployments target (Phase 12).
- Database migrations must be reversible.
- Seeders must be idempotent.

### 5.4 Accessibility
- Tailwind utility classes with semantic HTML.
- Mobile-responsive (primary portals).
- WCAG 2.1 AA target for client portal (Phase 12).

---

## 6. Out of Scope for MVP
- Video/call recording.
- Payroll processing.
- External API marketplace integrations.
- Mobile native apps.
- Multi-agency SaaS mode.
- Advanced analytics/BI dashboards.
