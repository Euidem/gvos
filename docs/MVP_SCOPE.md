# GVOS — MVP Scope

## What is the MVP?
The MVP is the first complete version of GVOS that GetVirtual can use in live operations. It covers all 12 build phases (Phase 1–12) on top of the Phase 0 foundation.

## MVP Is Included
| # | Module | Key Deliverable |
|---|--------|----------------|
| 1 | Authentication & Roles | Login, roles, permissions, dashboards |
| 2 | People & Organizations | Clients, companies, talents, managers |
| 3 | Leads & Trial Flow | Lead form, price estimate, trial workspace |
| 4 | Workspace Engine | Workspace CRUD, member management, status |
| 5 | Task Board | Task CRUD, board UI, comments, client approval |
| 6 | Chat & Files | Workspace chat, file library, attachments |
| 7 | Time Tracking & Reports | Clock in/out, daily report, weekly summary |
| 8 | Billing Foundation | Plans, subscriptions, invoices, manual payment |
| 9 | Complaints & Satisfaction | Complaint workflow, satisfaction surveys |
| 10 | Vault & Security | Encrypted vault, reveal logs |
| 11 | Calls | Embedded call room (no recording) |
| 12 | QA & Launch Readiness | Full permission audit, deployment prep |

## MVP Is Excluded
| Feature | Reason | Future Phase |
|---------|--------|-------------|
| Call recording | Legal/consent complexity | Post-MVP |
| Payroll processing | Separate system | Post-MVP |
| Mobile native apps | Responsive web first | Post-MVP |
| SaaS multi-tenancy | GetVirtual-only first | Post-MVP |
| Advanced BI dashboards | Too complex for V1 | Post-MVP |
| External integrations (Slack, etc.) | Scope control | Post-MVP |
| AI-assisted task matching | V2 feature | Post-MVP |

## Phase Breakdown

### Phase 0 — Documentation and Project Setup ✅
Foundation, docs, Laravel setup, Filament, Inertia React, roles, seeders.

### Phase 1 — Identity and Access Foundation
- Auth with email/password
- Role-based login redirect
- User profile pages
- Admin user management in Filament
- Password reset flow

### Phase 2 — People and Organizations
- Company model: name, domain, departments
- Business client admin account
- Staff invitation flow
- Talent profile model
- Manager profile model
- Assignment linking: talent → workspace, manager → workspace

### Phase 3 — Leads and Trial Flow
- Lead submission form (public or admin-only)
- Lead status workflow
- Price estimate builder
- Trial workspace creation
- Trial to paid conversion prompt

### Phase 4 — Workspace Engine
- Workspace model with type (trial/active), status, members
- Role-aware workspace dashboards
- Workspace settings management
- Admin workspace management in Filament

### Phase 5 — Task Board
- Task model with status, priority, assignee, due date
- Kanban board UI (React)
- Task comments
- File attachments on tasks
- Client task approval flow

### Phase 6 — Chat and Files
- Real-time chat per workspace (broadcasting)
- File library with categories
- Manager monitoring view
- Voice note upload support

### Phase 7 — Time Tracking and Reports
- Clock in/out per workspace
- Task-level timer
- Daily report submission form
- Weekly summary generation
- Manager report sign-off
- Client weekly summary view

### Phase 8 — Billing Foundation
- Subscription plans table
- Client subscription assignment
- Invoice generation
- Manual payment recording
- Non-payment → suspension workflow
- Payment provider abstraction layer

### Phase 9 — Complaints and Satisfaction
- Complaint form with categories and evidence
- Complaint status workflow
- Manager escalation to admin
- Satisfaction survey trigger on key events

### Phase 10 — Vault and Security Hardening
- Encrypted password vault per workspace
- Access grant system
- Reveal log per credential
- Audit export
- Security configuration review

### Phase 11 — Calls
- Embedded call room (third-party provider)
- Call log recording (metadata only, no recording)
- Participant log

### Phase 12 — QA and Launch Readiness
- Full permission matrix audit
- Workspace isolation testing
- Billing cycle testing
- Mobile viewport testing
- Deployment pipeline setup
- Final documentation pass
