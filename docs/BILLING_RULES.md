# GVOS — Billing Rules

## Overview
This document defines all billing rules for GVOS. These rules must be implemented exactly. Do not deviate without explicit instruction from the product owner.

---

## Core Billing Model

### Subscription Type
- **Bi-weekly (every 14 days)** fixed subscription
- Clients pay a fixed amount per billing cycle, not per hour
- Pricing is determined during the lead/quote phase

### Billing Start
- Billing starts **immediately after trial ends** and the client converts
- There is no grace period before first invoice
- The first invoice is due at the start of the active subscription

### Payment Recording
- **MVP:** Payments are recorded manually by Operations Admin
- **Phase 8+:** Payment provider integration (Stripe or similar)
- Manual recording: Admin marks invoice as paid, records reference number

---

## Invoice Rules

### Invoice Generation
- Generated automatically at the start of each billing cycle
- Invoice includes: period start, period end, amount, workspace name
- Status: `draft` → `sent` → `paid` or `overdue`

### Invoice Due Date
- Due within 5 business days of generation (configurable by admin)

### Invoice History
- Clients can view their own invoice history
- Admins can view all invoices

---

## Non-Payment and Suspension

### Grace Period
- After invoice due date passes: **3-day warning period**
- System sends notification to client and admin

### Suspension Trigger
- If unpaid after grace period: workspace is automatically suspended
- Status: `workspace.status = suspended`
- Client sees "Account Suspended" screen

### Reactivation
- Admin records payment manually
- Invoice marked as paid
- Workspace status restored to `active`
- Client notified

### During Suspension
- Client **cannot access** workspace
- Talent **cannot clock in** or submit work
- Chat and files are read-only for admin
- Data is **preserved** — not deleted

---

## Trial to Billing Transition

### Steps
1. Trial ends (scheduled or admin-triggered)
2. Operations Admin reviews trial outcome
3. Admin converts lead to client or marks as lost
4. On conversion: subscription created, first invoice generated
5. Billing cycle begins immediately

### Trial Period
- Default: 1 business day
- Custom: negotiated with client and set by admin
- No payment taken during trial

---

## Pricing

### Price Estimate
- Generated during lead phase by Operations Admin
- Quote includes: service type, talent hours, billing cycle amount
- Client accepts or rejects quote

### Price Changes
- Price changes require admin action
- New plan created, old plan closes at end of current cycle
- Client notified of price change

---

## Business Clients

### Multiple Workspaces
- Business clients may have multiple workspaces (per department)
- Each workspace has its own subscription
- Consolidated invoice view for Business Client Admin

---

## Payroll
- **Out of scope for MVP**
- GVOS does not handle talent payroll in V1
- Payroll module to be designed post-MVP

---

## Fines and Sanctions (Billing Implications)
- Clients or talents found attempting to bypass platform communication rules may be fined or have services suspended
- Fine amounts are manual, recorded by admin
- Sanctions can include temporary or permanent access suspension

---

## Implementation Notes for Phase 8
- Create `subscription_plans` config table
- `subscriptions` table links workspace to plan
- `invoices` table with status workflow
- Queue-driven: check for overdue invoices daily
- Admin Filament resource for manual payment recording
- Client portal: read-only billing section
- Webhook support for payment provider (Phase 8+)
