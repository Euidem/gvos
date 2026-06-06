# GVOS — Semi-Automated Time Tracking Plan

**Created:** 2026-05-31
**Status:** Implemented in Phase 9 on 2026-06-06.

> **Important:** Phase 9 implements the semi-automated timer without screenshots, keystrokes, screen monitoring, payroll, password vault, billing automation, or Phase 10 work.

---

## Current State (Phase 7 Manual Logs)

Phase 7 introduced:
- `workspace_time_logs` table with `started_at`, `ended_at`, `duration_minutes`
- Manual form: talent enters log_date, work_summary, start time, end time, duration
- Status flow: `draft` → `submitted` → `reviewed` → `approved` → `rejected`
- Manager review + client-visible summary

This is functional but doesn't match the timer-first design in `time_tracking_daily_reports_gvos/code.html` which shows a Clock-In/Out widget with a live timer.

---

## Implemented Behavior

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
- **One active timer per user globally.** A user cannot run overlapping timers across workspaces. They must stop or complete the active session before starting another one.

---

## Proposed Data Model Review

### Existing `workspace_time_logs` table

| Column | Current | Needed for Timer | Compatible? |
|--------|---------|------------------|-------------|
| `started_at` | timestamp nullable | Timer start server timestamp | ✅ Already exists |
| `ended_at` | timestamp nullable | Timer stop server timestamp | ✅ Already exists |
| `duration_minutes` | integer nullable | Computed from started_at → ended_at | ✅ Already exists |
| `status` | enum (running/draft/submitted/reviewed/approved/rejected) | Running timer state | ✅ Implemented in Phase 9 |
| `work_summary` | text | Entered after stop (brief) | ✅ Already exists |
| `work_details` | longText nullable | Full notes | ✅ Already exists |

### Status Extension Required

The `status` enum now includes `running`.

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

### Migration Added in Phase 9

```php
// 2026_06_06_000001_add_running_status_to_workspace_time_logs_table.php
$table->enum('status', ['running', 'draft', 'submitted', 'reviewed', 'approved', 'rejected'])
      ->default('draft')
      ->change();
```

**No other column changes needed.** The existing `started_at`, `ended_at`, `duration_minutes`, and other fields fully support the semi-automated timer.

---

## Backend Endpoints Implemented in Phase 9

### Timer Routes

```
GET   /time-tracker/current                         → WorkspaceTimeTrackerController@current
POST  /workspaces/{workspace}/time-tracker/start    → WorkspaceTimeTrackerController@start
POST  /workspaces/{workspace}/time-tracker/stop     → WorkspaceTimeTrackerController@stop
POST  /workspaces/{workspace}/time-tracker/complete → WorkspaceTimeTrackerController@complete
```

### `start` action logic

```php
// 1. Check no other running log exists for this user globally
// 2. Create time log with:
//    status = 'running'
//    started_at = now()
//    workspace_task_id = $request->task_id (optional)
//    log_date = today()
// 3. Redirect back or return JSON with the timer id
```

### `stop` action logic

```php
// 1. Verify this log belongs to this user or the actor can manage time logs
// 2. Set ended_at = now()
// 3. Calculate duration_minutes = started_at->diffInMinutes(ended_at)
// 4. Set status = 'draft'
// 5. Return the saved duration for display
```

### `active` query logic

```php
// Find any running log for auth()->user()
// Returns: log_id, workspace, task, started_at, duration_minutes, show_url
```

---

## Frontend Timer Behavior Implemented

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
// Page renders the active timer from server data; JSON endpoint also exposes current state
// If running log exists: calculate elapsed from started_at, resume display countdown
// On Clock In: POST /start → store log ID, start JS interval
// On Clock Out: POST /stop → clear interval, show duration
// Timer runs: setInterval every second, formats HH:MM:SS from server-calculated start
// If tab closes: server still has started_at — on next load, timer resumes from server timestamp
```

---

## Dashboard Integration

The talent dashboard shows:
- Active timer widget (pulsing green if running, neutral if idle)
- Workspace and optional task selection before clock-in
- Clock Out action that saves a draft stopped log
- Complete Work Session action that submits the log for review

The workspace time-logs index page should show:
- Active running session at the top (with elapsed time display)
- Completed sessions below in table

---

## What Phase 7 Currently Does vs What's Planned

| Feature | Phase 7 Manual | Phase 9 Semi-Auto |
|---------|-------------------|---------------------|
| Time entry method | Manual form | Clock In/Out widget |
| Timer | None | Server-stamped start/stop/complete |
| Duration | Manual input or start/end time form | Auto-calculated from server timestamps |
| Browser close | N/A | Timer continues on server |
| Status: running | Not in enum | Added by Phase 9 migration |
| Dashboard widget | None | Clock-In/Out with live display |
| Task linking | Optional on form | Optional before Clock In |

---

## Implemented Dependencies

Phase 9 implemented:
1. A migration adding `running` to the `status` enum.
2. `WorkspaceTimeTrackerController` with `current`, `start`, `stop`, and `complete`.
3. Header Clock In entry point in `gvos.blade.php`.
4. Talent dashboard timer widget with minimal JS interval display.
5. Time-logs index active-session controls and manager/admin running timer list.
6. A server guard preventing overlapping running timers for the same user.

---

## Notes

- No screenshots, no keystroke tracking, no screen time monitoring — ever.
- Duration is always computed from server-side timestamps.
- The frontend JS timer is purely cosmetic (display purposes) — never trusted for billing.
- The `duration_minutes` column already exists and will be auto-filled on `stop`.
- No payroll or billing integration is planned at this stage.
