# GVOS — Project Brief

## Product Name
**GVOS** (GetVirtual Operations System)

## Company
**GetVirtual** — a virtual assistant agency that places skilled talents with clients and business clients to perform remote work.

## Product Description
GVOS is a secure, managed operations platform purpose-built for GetVirtual. It enables the company to oversee every aspect of the client-talent relationship within a controlled digital environment — from the first lead inquiry through billing, time tracking, task management, file sharing, and performance oversight.

GVOS is **not a marketplace**. GetVirtual controls all relationships. Clients do not hire directly. Talents do not apply independently. Every assignment is managed by GetVirtual operations staff.

## Problem Being Solved
Virtual assistant agencies typically operate across a patchwork of disconnected tools (email, Slack, Trello, invoicing software, spreadsheets). This creates:
- Poor visibility for supervisors
- Uncontrolled communication channels
- Inconsistent billing
- No audit trail for compliance
- Difficulty scaling operations safely

GVOS solves this by bringing all operations into a single, role-aware, monitored platform.

## Target Users
| Portal | Users |
|--------|-------|
| GVOS Ops Console | Super Admin, Operations Admin |
| GVOS Manager Console | Line Managers |
| GVOS Talent Portal | Talents |
| GVOS Client Portal | Individual Clients, Business Client Admins, Business Client Staff |

Active Leads access a limited pre-signup area until they convert to clients.

## Business Context
- GetVirtual is the initial operator of GVOS.
- The platform is structured so that it can become a SaaS product for other virtual assistant agencies in future.
- Phase 0–12 cover the full MVP build. Post-MVP phases will address SaaS multi-tenancy, advanced reporting, and API integrations.

## High-Level Goals
1. Give GetVirtual complete operational control over client-talent workspaces.
2. Provide clients a professional, clean portal for task oversight and reporting.
3. Provide talents a structured workspace for doing their work under supervision.
4. Give managers tools to supervise, report, and escalate.
5. Ensure all actions are audited, secured, and compliant.
6. Support billing and subscription management with minimal manual effort.
7. Create a foundation for future SaaS expansion.

## Stack
- **Backend:** Laravel (PHP)
- **Admin:** Filament (GVOS Ops Console)
- **Frontend portals:** Inertia + React + Tailwind CSS
- **Authorization:** Spatie Laravel Permission + Laravel Policies
- **Database:** MySQL or PostgreSQL
- **Queue/Notifications:** Laravel Queues + Notifications (Phase 5+)
- **Payments:** Abstracted provider integration (Phase 8+)

## Design Source of Truth
Stitch UI export is the approved visual direction. See `/design-reference/README.md`.

## Status
Phase 0 — Foundation and Documentation
