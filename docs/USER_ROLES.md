# GVOS — User Roles

## Overview
GVOS has 8 distinct user roles. Each role maps to a specific portal and access level. Role assignment is managed exclusively by GetVirtual administrators.

---

## Role: super_admin

**Display Name:** Super Admin
**Portal:** GVOS Ops Console (Filament)
**Managed by:** Self (first user seeded by GetVirtual)

**Description:**
Full platform access. Can configure all settings, manage all users and roles, access billing configuration, view all audit logs, and perform any administrative action.

**Key Permissions:**
- All permissions
- Manage roles and permissions
- Configure platform settings
- Access billing settings
- View full audit logs
- Suspend/reactivate any workspace or user

**Notes:**
- Only one or two Super Admins should exist.
- Super Admin actions are logged and are not excluded from audit.

---

## Role: operations_admin

**Display Name:** Operations Admin
**Portal:** GVOS Ops Console (Filament)
**Managed by:** Super Admin

**Description:**
Day-to-day operations management. Can manage leads, clients, companies, talents, managers, and workspaces. Cannot change platform settings or billing configuration.

**Key Permissions:**
- Create and manage leads
- Create and manage client accounts (individual and business)
- Create and manage company records
- Create and manage talent profiles
- Create and manage manager accounts
- Create and manage workspaces
- View all time logs and reports
- Record manual payments
- View audit logs (read-only)

---

## Role: line_manager

**Display Name:** Line Manager
**Portal:** GVOS Manager Console
**Managed by:** Operations Admin or Super Admin

**Description:**
Supervises assigned talents and workspaces. Has oversight tools to monitor activity, write reports, review time logs, manage task flow, and escalate issues.

**Key Permissions:**
- View assigned workspaces
- View exact time logs for assigned talents
- View all task details in assigned workspaces
- Write and submit weekly reports
- Review and sign off on talent daily reports
- Raise escalations and complaints
- Monitor workspace chat
- Cannot modify billing or platform settings

---

## Role: talent

**Display Name:** Talent
**Portal:** GVOS Talent Portal
**Managed by:** Operations Admin; supervised by Line Manager

**Description:**
The virtual assistant who performs work for the client within a structured workspace. Works under manager supervision.

**Key Permissions:**
- Access assigned workspace(s)
- View and manage assigned tasks
- Clock in and clock out
- Submit daily reports
- Upload and download workspace files
- Participate in workspace chat
- Access password vault (read credentials granted to them)
- View their own time logs

**Notes:**
- Talents cannot see billing information.
- Talents cannot see other talents' data.
- Activity is monitored and they must be informed of this.

---

## Role: individual_client

**Display Name:** Individual Client
**Portal:** GVOS Client Portal
**Managed by:** Operations Admin

**Description:**
A personal client who has a single workspace with one or more assigned talents.

**Key Permissions:**
- View their workspace dashboard
- View weekly summaries (not exact time logs)
- View and comment on tasks
- Approve or reject completed tasks
- Upload and download workspace files
- Participate in workspace chat
- Access password vault (add credentials for talent)
- Raise concerns/complaints
- View invoices and subscription status

**Notes:**
- Cannot see exact talent time logs.
- Cannot invite other users to their workspace.

---

## Role: business_client_admin

**Display Name:** Business Client Admin
**Portal:** GVOS Client Portal
**Managed by:** Operations Admin

**Description:**
Administrator of a business account. Can manage their company's staff and view consolidated workspace reports across all company workspaces.

**Key Permissions:**
- All individual_client permissions across company workspaces
- Invite company staff (must use company email domain)
- Manage staff permissions within the portal
- View consolidated company reports
- View all company invoices

---

## Role: business_client_staff

**Display Name:** Business Client Staff
**Portal:** GVOS Client Portal
**Managed by:** Business Client Admin

**Description:**
An employee of a business client company. Invited by the Business Client Admin. Access is limited to what the Business Client Admin grants them.

**Key Permissions:**
- Access workspaces granted by Business Client Admin
- View tasks (read-only unless granted more)
- View weekly summaries (if granted)
- Participate in chat (if granted)
- Upload/download files (if granted)

**Notes:**
- Must be invited using a verified company email address.
- Business Client Admin controls their access level.

---

## Role: active_lead

**Display Name:** Active Lead
**Portal:** Pre-signup area / GVOS Client Portal (limited)
**Managed by:** Operations Admin

**Description:**
A person or company that has submitted a lead inquiry. They have read-only access to their lead status and any price estimate generated for them. Once approved for trial, they transition to a client role.

**Key Permissions:**
- View their lead status
- View price estimate
- Accept or reject price estimate
- View trial workspace (once trial is approved)

**Notes:**
- Active leads are not yet paying clients.
- Their access is stripped if the lead is marked as Lost.
- On trial approval, an Operations Admin converts them to the appropriate client role.

---

## Role Assignment Summary

| Role | Can Be Created By |
|------|------------------|
| super_admin | Seeder only (or another Super Admin) |
| operations_admin | Super Admin |
| line_manager | Super Admin, Operations Admin |
| talent | Super Admin, Operations Admin |
| individual_client | Operations Admin (via lead conversion or direct) |
| business_client_admin | Operations Admin |
| business_client_staff | Business Client Admin (invitation) |
| active_lead | Self-registration or Operations Admin |
