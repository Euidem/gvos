# GVOS — Semi-Automated Time Tracking Plan

**Created:** 2026-05-31
**Status:** Planning only — NOT YET IMPLEMENTED.

> **Important:** Phase 7 currently covers manual time logging only (talent fills a form with date, summary, duration). The timer-based semi-automated system described in this document is a planned enhancement. Do not implement until explicitly instructed.

---

## Current State (Phase 7 Manual Logs)

Phase 7 introduced:
- `workspace_time_logs` table with `started_at`, `ended_at`, `duration_minutes`
- Manual form: talent enters log_date, work_summary, start time, end time, duration
- Status flow: `draft` → `submitted` → `reviewed` → `approved` → `rejected`
- Manager review + client-visible summary

This is functional but doesn't match the timer-first design in `time_tracking_daily_reports_gvos/code.html` which shows a Clock-In/Out widget with a live timer.

---

## Desired Future Behavior

### Talent Experience

1. Talent opens their dashboard or workspace time log page.
2. A visible **timer widget** is displayed prominently.
3. Talent clicks **"Clock In"** / **"Start Timer"** when beginning work.
4. The timer counts up (displayed in `HH:MM:SS` format).
5. Talent can optionally select a workspace and task before starting.
6. Talent clicks **"Clock Out"** / **"Stop Timer"** when finished.
7. On stop, a work summary prompt appears (short description of what was done).
8. The completed session is saved as a time log with status `submitted` (or `draft` if summary not yet added).
9. Talent can edit and add details to the log after stopping.

### Manager Experience

10. Manager sees detailed logs per talent including start/stop times.
11. Manager can review, approve, or reject logs.
12. Manager can set client-visible summary before marking as approved.

### Client Experience

13. Client sees only approved logs where `visibility = client_summary` — the `client_visible_summary` text, not raw timing.

---

## Core Design Principles

- **No surveillance.** No screenshots, keystrokes, screen time monitoring, or activity tracking. Time is purely self-reported via a simple start/stop action.
- **Server-side truth.** Timer start and stop are recorded as server-side timestamps (`started_at`, `stopped_at` / `ended_at`). The frontend timer is display-only — duration is calculated server-side from the timestamps, not trusted from the browser.
- **Browser close resilience.** Because `started_at` is saved on the server at timer start, if the browser closes, the session is still "running" on the server. On next login, the UI shows the timer still running (calculated from server `started_at` to now). Talent can stop it manually.
- **One active timer per user per workspace.** A talent should not have two running timers in the same workspace simultaneously.

---

## Proposed Data Model Review

### Existing `workspace_time_logs` table

| Column | Current | Needed for Timer | Compatible? |
|--------|---------|------------------|-------------|
| `started_at` | timestamp nullable | Timer start server timestamp | ✅ Already exists |
| `ended_at` | timestamp nullable | Timer stop server timestamp | ✅ Already exists |
| `duration_minutes` | integer nullable | Computed from started_at → ended_at | ✅ Already exists |
| `status` | enum (draft/submitted/reviewed/approved/rejected) | Needs `running` status | ⚠️ Migration needed |
| `work_summary` | text | Entered after stop (brief) | ✅ Already exists |
| `work_details` | longText nullable | Full notes | ✅ Already exists |

### Status Extension Required

The `status` enum needs a new value: `running`.

Proposed full status set:
```
running   — timer actively running (started_at set, ended_at null)
draft     — timer stopped, summary not yet submitted (or manually created log)
submitted — talent has submitted for review
reviewed  — manager has reviewed (intermediate state)
approved  — manager approved; may be client-visible
rejected  — manager rejected; talent can re-edit
```

Optionally: `paused` — if pause functionality is needed.

### Migration Needed (Future — Do Not Create Yet)

```php
// Proposed future migration: modify status enum to add 'running'
$table->enum('status', ['running', 'draft', 'submitted', 'reviewed', 'approved', 'rejected'])
      ->default('draft')
      ->change();
```

**No other column changes needed.** The existing `started_at`, `ended_at`, `duration_minutes`, and other fields fully support the semi-automated timer.

---

## Proposed Backend Endpoints (Future — Do Not Build Yet)

### Timer Routes (to be added when implementing)

```
POST  /workspaces/{workspace}/time-logs/start   → WorkspaceTimeLogController@start
POST  /workspaces/{workspace}/time-logs/{log}/stop  → WorkspaceTimeLogController@stop
GET   /workspaces/{workspace}/time-logs/active   → WorkspaceTimeLogController@active
```

### `start` action logic

```php
// 1. Check no other running log exists for this user in this workspace
// 2. Create time log with:
//    status = 'running'
//    started_at = now()
//    workspace_task_id = $request->task_id (optional)
//    log_date = today()
// 3. Return the time log ID to frontend for display
```

### `stop` action logic

```php
// 1. Verify this log belongs to this user
// 2. Set ended_at = now()
// 3. Calculate duration_minutes = started_at->diffInMinutes(ended_at)
// 4. Set status = 'draft'
// 5. Return the saved duration for display
```

### `active` query logic

```php
// Find any running log for auth()->user() in this workspace
// Returns: log_id, started_at, workspace_task_id (for timer resume on page load)
```

---

## Frontend Timer Behavior (Future — Do Not Build Yet)

### Timer Widget HTML sketch (based on `talent_dashboard_gvos_1/code.html`):

```html
<!-- Clock-In/Out Widget -->
<div class="bg-white p-2 pl-4 rounded-2xl border-2 border-border-subtle shadow-lg flex items-center gap-8">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-status-active/10 flex items-center justify-center text-status-active animate-pulse">
      <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">timer</span>
    </div>
    <div>
      <p class="font-label-md text-label-md text-outline uppercase">Today's Time</p>
      <p id="timer-display" class="font-headline-md text-headline-md font-bold text-primary">00:00:00</p>
    </div>
  </div>
  <button id="timer-btn" class="bg-secondary text-white px-6 py-3 rounded-xl font-label-md">
    Clock In
  </button>
</div>
```

### JavaScript timer (display only — not source of truth):

```javascript
// On page load: fetch /time-logs/active
// If running log exists: calculate elapsed from started_at, resume display countdown
// On Clock In: POST /start → store log ID, start JS interval
// On Clock Out: POST /stop → clear interval, show duration
// Timer runs: setInterval every second, formats HH:MM:SS from server-calculated start
// If tab closes: server still has started_at — on next load, timer resumes from server timestamp
```

---

## Dashboard Integration (Future)

When implemented, the talent dashboard should show:
- Active timer widget (pulsing green if running, neutral if idle)
- Today's total logged time
- This week's total logged time
- Quick "Log Time" shortcut if no timer running

The workspace time-logs index page should show:
- Active running session at the top (with elapsed time display)
- Completed sessions below in table

---

## What Phase 7 Currently Does vs What's Planned

| Feature | Phase 7 (Current) | Semi-Auto (Planned) |
|---------|-------------------|---------------------|
| Time entry method | Manual form | Clock In/Out widget |
| Timer | None | Server-stamped start/stop |
| Duration | Manual input or start/end time form | Auto-calculated from server timestamps |
| Browser close | N/A | Timer continues on server |
| Status: running | Not in enum | Needs migration |
| Dashboard widget | None | Clock-In/Out with live display |
| Task linking | Optional on form | Optional before Clock In |

---

## Implementation Dependencies

When the time comes to implement the semi-automated timer:
1. A migration to add `running` to the `status` enum is required.
2. The `WorkspaceTimeLogController` needs three new methods: `start`, `stop`, `active`.
3. The layout (`gvos.blade.php`) needs the Clock In button wired to AJAX/form.
4. The talent dashboard needs the timer widget (HTML + minimal JS interval display).
5. The time-logs index page needs an "active session" row at top.
6. A policy guard to prevent two running timers per user per workspace.

---

## Notes

- No screenshots, no keystroke tracking, no screen time monitoring — ever.
- Duration is always computed from server-side timestamps.
- The frontend JS timer is purely cosmetic (display purposes) — never trusted for billing.
- The `duration_minutes` column already exists and will be auto-filled on `stop`.
- No payroll or billing integration is planned at this stage.
