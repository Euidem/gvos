# GVOS — User Flows

## Overview
This document describes the key user journeys through GVOS. These flows guide UI implementation and route planning.

---

## Flow 1: Lead to Trial to Client

```
Lead submits inquiry (form or admin creates lead)
    ↓
Operations Admin reviews lead
    ↓
Admin contacts lead (external, phone/email)
    ↓
Admin creates price estimate in GVOS
    ↓
Lead receives price estimate → Accepts or Rejects
    ↓ (Accepted)
Operations Admin approves trial
    ↓
Trial workspace created automatically
    ↓
Lead is given active_lead portal access
    ↓
Trial period begins (1 day or negotiated)
    ↓
Trial ends:
    ├─ Convert → Client role assigned, billing starts, full workspace activated
    └─ Mark Lost → Lead access revoked
```

---

## Flow 2: New Client Onboarding

```
Operations Admin creates client account
    ↓
Client receives invitation email
    ↓
Client sets password and logs in
    ↓
Client sees GVOS Client Portal dashboard
    ↓
Client's workspace is already prepared by admin
    ↓
Talent and Manager assigned to workspace by admin
    ↓
Client gets workspace overview with talent intro
```

---

## Flow 3: Business Client Staff Invitation

```
Business Client Admin logs in
    ↓
Goes to Company Settings → Staff
    ↓
Enters staff email (must match company domain)
    ↓
Staff receives invitation email
    ↓
Staff sets password and logs in
    ↓
Sees GVOS Client Portal with limited access
    ↓
Business Client Admin configures their permissions
```

---

## Flow 4: Talent Daily Work

```
Talent logs in to GVOS Talent Portal
    ↓
Sees workspace dashboard
    ↓
Reviews task board and opens assigned tasks
    ↓
Clocks in (time tracking starts)
    ↓
Works on tasks (updates status, adds comments)
    ↓
Uses password vault to access client credentials if needed
    ↓
Communicates in workspace chat
    ↓
Uploads completed files to file library
    ↓
Clocks out (time tracking stops)
    ↓
Submits daily report
```

---

## Flow 5: Manager Daily Oversight

```
Manager logs in to GVOS Manager Console
    ↓
Sees command center with all assigned workspaces
    ↓
Reviews talent time logs for today
    ↓
Checks task board activity
    ↓
Reviews workspace chat for any issues
    ↓
Reviews daily reports submitted by talents
    ↓
Signs off on approved daily reports
    ↓
Raises concern if any issues noted
    ↓
At end of week: generates and submits weekly summary
```

---

## Flow 6: Client Weekly Review

```
Client logs in to GVOS Client Portal
    ↓
Sees workspace dashboard with summary stats
    ↓
Reviews weekly summary report
    ↓
Reviews task board (sees status of tasks)
    ↓
Approves or requests revision on completed tasks
    ↓
Downloads files from file library
    ↓
Raises concern if dissatisfied (complaint flow)
    ↓
Views invoice and subscription status in Billing section
```

---

## Flow 7: Complaint and Escalation

```
Client or Talent raises a concern (complaint form)
    ↓
Complaint created with: type, description, evidence attached
    ↓
Assigned to Line Manager
    ↓
Manager reviews and responds
    ↓
Manager can:
    ├─ Resolve → Resolution recorded, case closed
    └─ Escalate → Complaint passed to Operations Admin
        ↓
        Admin reviews and takes action
        ↓
        Resolution recorded
        ↓
        Satisfaction survey sent to raiser
```

---

## Flow 8: Password Vault Access

```
Client adds credentials to vault (locked field)
    ↓
Client grants access to specific talent(s)
    ↓
Talent opens vault in their workspace
    ↓
Talent requests to reveal credential
    ↓
System logs: talent name, timestamp, credential accessed
    ↓
Credential shown for limited time (masked again after)
    ↓
Admin/Manager can view reveal log at any time
```

---

## Flow 9: Billing and Suspension

```
Subscription created for client (by Operations Admin)
    ↓
Invoice generated at billing cycle start
    ↓
Client informed via notification
    ↓
Payment recorded (manual or automatic):
    ├─ Paid → Invoice marked paid, subscription continues
    └─ Not paid (past due date):
        ↓
        Warning notification sent
        ↓
        Grace period expires
        ↓
        Workspace automatically suspended
        ↓
        Client sees "Account Suspended" screen
        ↓
        Operations Admin records payment → workspace reactivated
```

---

## Flow 10: Admin Audit Review

```
Super Admin or Operations Admin opens Audit Logs in Ops Console
    ↓
Filters by: user, date range, action type, workspace
    ↓
Reviews log entries
    ↓
Drills into individual entries for full context
    ↓
Exports if required (Phase 10+)
```

---

## Authentication Flow

```
User navigates to GVOS login page
    ↓
Enters email and password
    ↓
Laravel authenticates credentials
    ↓
Middleware checks user role
    ↓
User redirected to role-specific dashboard:
    ├─ super_admin / operations_admin → Filament Ops Console
    ├─ line_manager → Manager Console dashboard
    ├─ talent → Talent Portal dashboard
    ├─ individual_client / business_client_admin / business_client_staff → Client Portal dashboard
    └─ active_lead → Lead status page
```
