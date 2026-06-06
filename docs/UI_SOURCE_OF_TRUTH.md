# GVOS — UI Source of Truth Map

**Status:** Active — Stitch export is the frontend source of truth as of 2026-05-31.

**Stitch Export Location:**
`design-reference/stitch_gvos_operations_platform/` (extracted from `stitch_gvos_operations_platform (1).zip`)

Each screen folder contains:
- `code.html` — full Tailwind/HTML source used as layout authority
- `screen.png` — visual reference screenshot

---

## How to use this map

Before editing any Blade view:
1. Open the matching Stitch folder listed in the "Stitch Source Folder" column.
2. Read `code.html` — do not invent new layouts.
3. Match the sidebar, header, card structure, spacing, icons and typography to the Stitch HTML.
4. Document the Stitch folder used in any commit message.

---

## Route → Stitch Source Map

### Auth

| App Route | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|-----------|-------------------|----------------------|-----------------|----------------|-------|
| `/login` | `resources/views/auth/login.blade.php` | `login_gvos_1` | Exact | **Major drift** | Stitch: 2-col split-screen, "Initialize Session" button, decorative right panel with floating data cards. Current: standard single-column auth layout. |
| `/login` (alt) | same | `login_gvos_2` | Reference | Not assessed | Secondary login variant — compare and pick most appropriate. |
| `/forgot-password` | `resources/views/auth/forgot-password.blade.php` | `forgot_password_gvos` | Exact | Not assessed | Must match Stitch forgot password layout. |
| `/account/status` | `resources/views/account/status.blade.php` | `suspended_account_gvos` | Exact | Not assessed | Suspended/inactive account holding page. |

### Dashboards

| App Route | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|-----------|-------------------|----------------------|-----------------|----------------|-------|
| Filament `/admin` | Filament (custom) | `admin_overview_gvos` | Reference | **Moderate drift** | Admin uses Filament; Stitch admin_overview is reference for layout patterns. |
| `/manager/dashboard` | `resources/views/dashboard/line-manager.blade.php` | `manager_command_center_gvos` | Exact | **Major drift** | Stitch: rich metric cards, talent monitoring, task summary. Current: simpler card layout + Phase notice banner. |
| `/talent/dashboard` | `resources/views/dashboard/talent.blade.php` | `talent_dashboard_gvos_1` | Exact | **Major drift** | Stitch: Clock-In widget with timer, workspace switcher in sidebar, task priority grid. Current: no clock-in, no timer, no workspace switcher. |
| `/talent/dashboard` (alt) | same | `talent_dashboard_gvos_2` | Reference | Not assessed | Second talent dashboard state. |
| `/client/dashboard` | `resources/views/dashboard/individual-client.blade.php` | `client_dashboard_gvos` | Exact | **Moderate drift** | Stitch: project status, deliverable timeline. Current: simpler. |
| `/client/dashboard` (business admin) | `resources/views/dashboard/business-client-admin.blade.php` | `business_admin_dashboard_gvos` | Exact | **Moderate drift** | Business admin specific dashboard. |
| `/client/dashboard` (business staff) | `resources/views/dashboard/business-client-staff.blade.php` | `client_dashboard_gvos` | Reference | **Moderate drift** | Use client dashboard as reference. |
| `/lead/dashboard` | `resources/views/dashboard/lead.blade.php` | `lead_dashboard_gvos_1` + `lead_dashboard_gvos_2` | Exact | Not assessed | Two-state lead dashboard. |

### Workspace

| App Route | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|-----------|-------------------|----------------------|-----------------|----------------|-------|
| `/workspaces` | `resources/views/workspace/index.blade.php` | `workspaces_management_gvos` + `talent_workspace_overview_gvos` | Exact | **Moderate drift** | Admin sees management view; talent sees overview. |
| `/workspaces/{workspace}` | `resources/views/workspace/show.blade.php` | `workspace_monitoring_gvos` | Exact | **Major drift** | Stitch: rich workspace overview with live metrics, activity feed. Current: card grid with placeholder sections. |
| `/workspaces/{workspace}/tasks` | `resources/views/workspace/tasks/index.blade.php` | `task_board_gvos` | Exact | **Moderate drift** | Stitch: proper kanban columns with filter bar, rich task cards. Current: functional but different card styling. |
| `/workspaces/{workspace}/tasks/{task}` | `resources/views/workspace/tasks/show.blade.php` | `task_detail_gvos` | Exact | **Moderate drift** | Stitch: detailed task card with progress, comments, attachments panel. |
| `/workspaces/{workspace}/tasks/create` | `resources/views/workspace/tasks/create.blade.php` | `create_task_gvos` | Exact | Not assessed | Must match Stitch create task form layout. |
| `/workspaces/{workspace}/chat` | `resources/views/workspace/chat/index.blade.php` | `workspace_chat_gvos` | Exact | **Moderate drift** | Stitch: chat with message bubbles, thread support, attachment icons. |
| `/workspaces/{workspace}/files` | `resources/views/workspace/files/index.blade.php` | `file_library_gvos` | Exact | **Moderate drift** | Stitch: file grid/list with category filters, preview thumbnails. |
| `/workspaces/{workspace}/time-logs` | `resources/views/workspace/time-logs/index.blade.php` | `time_tracking_daily_reports_gvos` | Exact | **Major drift** | Stitch: timer widget at top, daily log list below. Current: table-only, no timer UI. |
| `/workspaces/{workspace}/time-logs/{log}` | `resources/views/workspace/time-logs/show.blade.php` | `exact_time_logs_review_gvos` | Exact | **Moderate drift** | Stitch: detailed review panel for managers. |
| `/workspaces/{workspace}/reports` | `resources/views/workspace/reports/index.blade.php` | `weekly_report_gvos` | Exact | **Moderate drift** | Stitch: report list with summary cards. |
| `/workspaces/{workspace}/reports/{report}` | `resources/views/workspace/reports/show.blade.php` | `weekly_report_gvos` | Reference | **Moderate drift** | Use weekly_report_gvos as reference for show view. |

### Workspace Settings (Future)

| App Route | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|-----------|-------------------|----------------------|-----------------|----------------|-------|
| Workspace settings | Not yet implemented | `workspace_settings_gvos` | Exact | Not implemented | Placeholder. |

### Notifications and Settings

| App Route | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|-----------|-------------------|----------------------|-----------------|----------------|-------|
| `/notifications` | `resources/views/notifications/index.blade.php` | `workspace_monitoring_gvos` + `client_dashboard_gvos` | Reference | Phase 11 foundation built | No dedicated Stitch notification screen; uses GVOS shell, card list, badges, and action-button patterns. |
| `/settings/notifications` | `resources/views/settings/notifications.blade.php` | `workspace_settings_gvos` + `profile/edit` pattern | Reference | Phase 11 foundation built | No dedicated Stitch notification settings screen; uses GVOS shell and compact settings rows. |

### Admin / Operations Management Screens (Filament)

| Feature | Current Implementation | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|---------|------------------------|----------------------|-----------------|----------------|-------|
| Lead management | Filament `LeadRequestResource` | `lead_management_gvos` / `leads_management_gvos` | Reference | Not assessed | Filament UI differs; use Stitch as reference for portal-side views. |
| Company management | Filament `CompanyResource` | `companies_management_gvos` | Reference | Not assessed | |
| Department management | Filament `DepartmentResource` | `departments_management_gvos` | Reference | Not assessed | |
| Workspace management | Filament `WorkspaceResource` | `workspaces_management_gvos` | Reference | Not assessed | |
| Talent onboarding | Filament `UserResource` | `talent_onboarding_gvos` | Reference | Not assessed | |
| Exact time log review | Filament `WorkspaceTimeLogResource` | `exact_time_logs_review_gvos` | Reference | Not assessed | May need portal-side review UI. |
| Reports review | Filament resource | `manager_reports_review_gvos` | Reference | Not assessed | |
| Audit logs | Filament `AuditLogResource` | `audit_logs_gvos` | Reference | Not assessed | |

### Monitoring / Analytics

| Feature | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|---------|-------------------|----------------------|-----------------|----------------|-------|
| Workspace monitoring | — | `workspace_monitoring_gvos` | Exact | Not implemented | Real-time workspace activity view. |
| Talent performance | — | `talent_performance_monitoring_gvos` | Reference | Not implemented | |
| Manager talent/client monitoring | — | `manager_talent_client_monitoring_gvos` | Reference | Not implemented | |

### Future Modules (Do Not Build Yet)

| Feature | Current Blade View | Stitch Source Folder | Fidelity Target | Current Status | Notes |
|---------|-------------------|----------------------|-----------------|----------------|-------|
| Billing | Not built | `billing_invoices_gvos` + `global_billing_overview_gvos` | Exact | Not started | Do not build yet. |
| Password vault | `resources/views/workspace/vault/*.blade.php` | `password_vault_gvos` | Reference | Phase 10 foundation built | Adapted to keep vault tables metadata-only; plaintext reveal happens only on the item detail page after access logging. |

### Mobile Variants (Reference only — not priority)

| Screen | Stitch Source Folder | Notes |
|--------|----------------------|-------|
| Client dashboard mobile | `client_dashboard_mobile_gvos` | Responsive reference. |
| Manager dashboard mobile | `manager_command_center_mobile_gvos` | Responsive reference. |
| Task board mobile | `task_board_mobile_gvos` | Responsive reference. |
| Workspace chat mobile | `workspace_chat_mobile_gvos` | Responsive reference. |
| Workspace monitoring mobile | `workspace_monitoring_mobile_gvos` | Responsive reference. |
| Talent dashboard mobile | `talent_dashboard_mobile_gvos` | Responsive reference. |
| Audit logs mobile | `audit_logs_mobile_gvos` | Responsive reference. |

---

## Shared Design System (from Stitch code.html — source of truth)

### Colors
All screens share these exact tokens:
- `secondary` = `#0058be` (primary brand blue)
- `sidebar-bg` = `#0B0F19` (dark sidebar background)
- `secondary-fixed` = `#d8e2ff`
- `secondary-fixed-dim` = `#adc6ff`
- `secondary-container` = `#2170e4`
- `on-secondary` = `#ffffff`
- `surface` = `background` = `surface-bright` = `#f7f9fb`
- `surface-container-lowest` = `#ffffff`
- `border-subtle` = `#E2E8F0`
- `outline` = `#76777d`
- `on-surface` = `#191c1e`
- `on-surface-variant` = `#45464d`

### Typography
- Headlines: Manrope (wght 600, 700, 800)
- Body + Labels: Inter (wght 400, 500, 600, 700)
- Mono: JetBrains Mono (wght 500)

### Shared Shell Structure
Every screen follows this exact HTML structure:
```
<aside class="w-[280px] h-screen fixed left-0 top-0 bg-sidebar-bg">
  <!-- Logo: GVOS Platform + Enterprise Ops -->
  <!-- Nav items: active = bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold -->
  <!-- Nav items: inactive = text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5 -->
  <!-- Footer: Quick Action button (bg-secondary) + Settings + Help + User profile card -->
</aside>

<header class="fixed/sticky h-16 bg-surface-container-lowest border-b border-border-subtle z-40 ml-[280px]">
  <!-- GVOS bold text OR search bar + nav links (Workspace, Messages, Files) + icons + Clock In button -->
</header>

<main class="ml-[280px] [mt-16]">
  <!-- page content -->
</main>
```

---

## Notes

- Visual Repair v3 CSS fallbacks in `gvos.blade.php` MUST NOT be removed until fully replaced by compiled CSS.
- `GetVirtual` must never appear in any visible UI element.
- All screen folders contain both `code.html` (layout authority) and `screen.png` (visual reference).
