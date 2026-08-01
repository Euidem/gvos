# GVOS — Non-Admin Portal UX Audit

**Created:** 2026-07-31 (Phase 28, Part A)
**Method:** Live inspection of `https://gvos.afbs.ng` using the controlled demo accounts,
combined with source review of every portal Blade view.
**Viewports tested:** 1440 × 900 (desktop) and 390 × 844 (mobile).
**Instrumentation:** DOM + computed-layout probe measuring horizontal overflow, duplicate
link targets, above-the-fold CTA counts, scroll depth to primary content, dead links,
literal-entity leakage and truncated strings.

> **Note on screenshots.** Pixel screenshots were not available in this environment (the
> browser pane does not composite frames headlessly). All findings below are therefore
> backed by *measured* DOM/geometry values and page text rather than visual impression.
> Every number in this document was read off the live deployment.

---

## 1. Executive summary

The portal is functionally complete but is not yet one coherent product. Three structural
faults explain most of the owner's feedback:

1. **The shell is identical for all eight roles.** The sidebar has three links regardless of
   who is signed in. Role-specific work (review queues, billing, reports, vault, members)
   has no navigation entry anywhere, so it is only reachable by drilling through a workspace.
2. **Navigation is duplicated instead of structured.** The shell alone emits five links to
   `/workspaces` and two to `/profile` on every page. Individual pages then add more: the
   manager dashboard carries **11** links to `/workspaces`; the workspace overview carries
   **8** links to the same task board.
3. **Dashboards lead with passive totals, not decisions.** Every dashboard opens with a
   four-card statistics strip. On mobile a manager scrolls **2.14 screens (1,804 px)** past a
   decorative capacity meter before reaching the actual review queue.

Secondary faults: no workspace sub-navigation (module pages are dead ends), heavy uppercase
labelling, alarmist and speculative alert copy, and several concrete rendering bugs
(literal `&amp;`, currency amounts printed with no currency, aggressive name truncation).

---

## 2. Answers to the 20 audit questions

| # | Question | Verdict | Evidence |
|---|----------|---------|----------|
| 1 | Is it obvious where the user is? | **No** | Sidebar active state only distinguishes Dashboard / Workspaces / Profile. Inside `/workspaces/2/files` the sidebar highlights "Workspaces" and nothing indicates which workspace or which module. |
| 2 | Is role and context clear? | **Partly** | Role appears only as tiny uppercase text in the sidebar profile card. Nothing else adapts. |
| 3 | Is the next action obvious? | **No** | Talent dashboard renders **18 CTAs above the fold**, all styled with similar weight. |
| 4 | Is navigation understandable? | **No** | Header offers "Workspace", "Messages", "Files" — all three resolve to `/workspaces` (measured). |
| 5 | Are related features grouped? | **No** | Billing, Reports, Vault, Members, Time have no navigation grouping; they exist only inside a workspace. |
| 6 | Too many equal-priority actions? | **Yes** | Manager dashboard: 16 above-fold CTAs. Workspace overview: 12. |
| 7 | Dashboards overloaded with statistics? | **Yes** | All six dashboards open with a 4-card stat strip. Several cards restate a number shown metres away. |
| 8 | Cards that inform but don't help act? | **Yes** | "ACTIVE WORKSPACES 1" above a list containing exactly one workspace. "Manager Profile / Load 40%". |
| 9 | Duplicate links / repeated content? | **Yes — severe** | See §3. Shell contributes 5 duplicates per page before any content renders. |
| 10 | Titles / breadcrumbs / back links consistent? | **No** | Page titles use `headline-lg` on some pages and `headline-md` on others. Each module page hand-rolls its own `arrow_back` link; the workspace overview has two separate "back" links. |
| 11 | Billing / reports / tasks / messages / files easy to find? | **No** | None are in the sidebar for any role. |
| 12 | Restricted actions hidden appropriately? | **No** | A talent is shown a **"Billing — Not available for this role"** card (`workspace/show.blade.php:707`). An observer is shown a **"FOR APPROVAL"** card despite being read-only. |
| 13 | Empty states helpful? | **Mostly, one broken** | Observer dashboard: "Access documents and deliverables shared with your team. *No files have been shared yet if this page is empty.*" |
| 14 | Alerts too technical? | **Yes** | Suspended page: "Your workspace has been deactivated by the **GVOS Security Layer**. Core operations are currently offline." Also two speculative blocks: "There **may be** a pending payment issue", "**may be** undergoing a compliance review". |
| 15 | Does mobile make sense? | **Partly** | No horizontal overflow (good), sidebar drawer works. But content order is wrong — see §6. |
| 16 | Tables where cards/timelines are easier? | **Yes** | Time logs and members are dense tables; chat is a flat list with no day grouping. |
| 17 | Does every page feel like one product? | **No** | Dashboards are gradient hero panels; module pages are plain white cards with a small heading. Two different visual languages. |
| 18 | Decoration replacing usability? | **Yes** | Gradient hero panels and the manager "load bar" occupy the most valuable screen area and carry no action. |
| 19 | Pages with too much blank space? | **Yes** | Every section is a bordered white rectangle; the workspace overview stacks 12 of them. |
| 20 | Too much content above the primary action? | **Yes** | Manager: review queue at 1,804 px on mobile; vanity profile card at 442 px. |

---

## 3. Duplicate navigation — measured

Counted on the live deployment (`querySelectorAll('a[href]')`, grouped by href).

### Contributed by the shell on **every** page, for **every** role

| Target | Count | Sources |
|--------|-------|---------|
| `/workspaces` | **5** | sidebar "Workspaces", sidebar "Quick Action", header "Workspace", header "Messages", header "Files" |
| `/profile` | **2** | sidebar "My Profile", sidebar "Settings" |

"Quick Action" (styled as the sidebar's primary blue button) and "Settings" are simply
aliases of links directly above them. Header "Messages" and "Files" are mislabelled — they
do not go to messages or files.

### Contributed by pages, on top of the above

| Page | Role | Duplicate targets |
|------|------|-------------------|
| `/manager/dashboard` | Manager | **11×** `/workspaces`, 3× `/profile`, 3× `/notifications`, 2× each of four workspace URLs |
| `/talent/dashboard` | Talent | 7× `/workspaces`, 3× `/profile`, 3× `/notifications`, 3× `/workspaces/2/time-logs` |
| `/workspaces/2` | Talent | **8×** `/workspaces/2/tasks`, 7× `/workspaces` |
| `/client/dashboard` | Business admin | 4 separate routes to billing; 3 "Quick Action" cards ("Workspace Messages", "Shared Files", "Progress Reports") **all resolve to `/workspaces`** |

The business-admin case is the clearest failure: the workspace card directly above those
quick actions already links correctly to `/workspaces/3/files` and `/workspaces/3/reports`,
so the "Quick Actions" panel is strictly worse than the content it sits under.

---

## 4. Dead, mislabelled and role-inappropriate controls

| Control | Location | Problem |
|---------|----------|---------|
| **"Clock In"** button | Header, **all roles** | Shown to clients, business staff, observers and leads, none of whom can log time. It is not a clock-in control at all — it is a link to `/workspaces`. |
| **Search box** | Header, all pages | Not wrapped in a form, no handler. Purely decorative. |
| **"Support"** | Sidebar, all pages | A `<div>` with `cursor-not-allowed`, permanently disabled. |
| **"Quick Action"** | Sidebar, all pages | Primary-styled button that duplicates "Workspaces". |
| **"GVOS Support"** card | Active-lead dashboard | Styled as a card with a chevron but has **no `href`** — unclickable. |
| **"Contact Support"** | Suspended page | `mailto:support@gvos.io` — a domain unrelated to the deployment (`gvos.afbs.ng`). |
| **"Billing — Not available for this role"** | Workspace overview | Renders a disabled module card to talent instead of omitting it. |
| **"FOR APPROVAL"** stat | Observer dashboard | Observers are read-only and cannot approve. |

---

## 5. Concrete rendering bugs

| Bug | Evidence | Source |
|-----|----------|--------|
| Literal `&amp;` rendered as visible text | Business-admin and observer dashboards display "Active **&amp;** pending" | `business-client-admin.blade.php:150`, `business-client-staff.blade.php:68`, `individual-client.blade.php:269` — `&amp;` written inside a Blade echo, which escapes it again |
| Money printed with **no currency** | "Outstanding balance: **1,980.00**"; probe found zero currency symbols on the page | `business-client-admin.blade.php:97,263`, `individual-client.blade.php:93,273` — `number_format()` with no currency prefix |
| Names truncated mid-word in cards | "Executive Support Operat…", "Finance Operations Suppo…", "Demo Logistics Sta…", "Market Research Spri…" | `Str::limit(..., 16–24)` in `individual-client:184,204`, `business-client-admin:207`, `active-lead:116,124,209` |
| Two competing "back" links | Workspace overview has "All Workspaces" (top) and "Back to Workspaces" (bottom) | `workspace/show.blade.php:104` and `:741` |
| Inconsistent page-title scale | Time logs and reports use `font-headline-md`; workspaces index and workspace overview use `font-headline-lg` | `time-logs/index:12`, `reports/index:12` vs `workspace/index:6` |

---

## 6. Mobile (390 × 844) — measured

No horizontal overflow was found on any page tested, and the sidebar drawer + backdrop
work correctly. The problem is **content order**, not breakage.

Manager dashboard scroll offsets:

| Element | Offset | Screens down |
|---------|--------|--------------|
| "Manager Profile" vanity card (capacity, load bar) | 442 px | 0.5 — **above the fold** |
| "TASKS FOR REVIEW" stat | 733 px | 0.9 |
| "PENDING REVIEW" stat | 909 px | 1.1 |
| "Supervised Workspaces" list | 1,042 px | 1.2 |
| **"ACTION QUEUE"** (the actual work) | **1,804 px** | **2.14** |
| "QUICK LINKS" (duplicates the sidebar) | 2,070 px | 2.5 |
| Total page height | 2,287 px | — |

The header on mobile drops the nav links but **keeps the irrelevant "Clock In" button**.

---

## 7. Per-page findings

### 7.1 Talent dashboard (`/talent/dashboard`)
- 18 above-fold CTAs; 33 links total; 7 point to `/workspaces`.
- Hero gradient panel occupies the first ~330 px and contains three secondary buttons
  ("Open Workspace", "Notifications", "Time Logs") competing with the timer.
- The timer widget — genuinely the talent's most-used control — is pushed to the right
  half of the hero and drops *below* the welcome text on mobile.
- Stat strip: "Active Tasks / Due Soon / Blocked / Weekly Time". "Due Soon" and "Blocked"
  are subsets of "Active Tasks", so the same tasks are counted up to three times.
- Per-workspace chips repeat Tasks / Time Logs / Files / Chat for every workspace, then a
  "Quick Actions" column repeats All Workspaces / Time Logs / Notifications / My Profile.
- No answer to "what should I work on now?" — no task is ever named on the dashboard.

### 7.2 Manager dashboard (`/manager/dashboard`)
- Headline claims "9 items require your review across 4 workspaces" but **there is no
  cross-workspace review destination**; the manager must open each workspace separately.
- The same review counts are rendered **three times**: hero button "Review Time Logs 4",
  stat cards "TASKS FOR REVIEW 4" + "PENDING REVIEW 5", and the "ACTION QUEUE" panel.
- Seven uppercase section labels on one page.
- "QUICK LINKS" repeats three sidebar destinations.

### 7.3 Individual client dashboard
- "1 task is awaiting your review" — but no link to that task; the client must guess the path.
- Reports referenced four times (hero button, stat card, workspace chip, bottom card).
- "ACTIVE WORKSPACES 1" sits directly above a list of one workspace.
- Workspace name truncated to "Executive Support Operat…".

### 7.4 Business client admin dashboard
- Billing referenced four times; outstanding balance printed twice, both without currency.
- "Active workspaces 1" (account card) and "WORKSPACES 1" (stat card) ~200 px apart.
- Literal `&amp;` visible.
- No entry point for **Team Access (members)** or **Vault**, both of which this role can use.

### 7.5 Business client staff / Observer dashboard
- Shares one Blade file, so the read-only observer sees a "FOR APPROVAL" call to action.
- Literal `&amp;` visible.
- Broken empty-state sentence ("…No files have been shared yet if this page is empty.").
- Four bottom action cards duplicate the sidebar.

### 7.6 Active lead dashboard
- Two truncated strings ("Demo Logistics Sta…", "Market Research Spri…").
- "GVOS Support" card is unclickable.
- Shows the full client shell (Workspaces, Messages, Files, Clock In) although the brief
  requires the lead experience to stay narrow.
- States status but never states **the next step** or **what information is required**.

### 7.7 Workspaces index
- Clean and the least problematic page in the portal, but each card exposes only the
  workspace name — no indication of what needs attention inside it.

### 7.8 Workspace overview (`/workspaces/{id}`)
- 12 card containers; 8 links to the same task board.
- Metric strip ("Tasks 6 / Team 3 / Time Logs 5 / Reports 2") is passive counting.
- Kanban preview repeats the task counts a second time as status chips.
- Four near-identical module cards (Chat / Files / Time Logs / Reports), each "count +
  title + description + Open X →".
- Renders a disabled Billing card to roles without billing access.
- Copy uses "5 msgs" and "Approved workspace credentials with logged reveal activity".

### 7.9 Module pages (tasks, chat, files, time logs, reports, billing, vault, members)
- **There is no workspace sub-navigation.** Every module page hand-rolls its own
  `arrow_back` link to the workspace overview. Moving from Tasks to Files requires going
  back to the overview first — measured across all eight module pages.
- Page-title typography is inconsistent (`headline-md` vs `headline-lg`).
- Each page re-establishes workspace context in its own way.

### 7.10 Notifications, notification settings, profile, onboarding
- Notifications: flat list, no grouping by date or by workspace.
- Notification settings: reachable only from the profile page — not from the notifications
  page it configures.
- Profile: three-column layout whose sidebar duplicates sidebar navigation again.
- Onboarding: four `<h2>` elements used as *section* headings, conflicting with the rest of
  the portal where `<h2>` is the *page* title. Renders "Your Workspace" twice.

---

## 8. What is already good (keep)

- Design tokens are coherent and the colour palette is professional.
- No `GetVirtual` string anywhere in the rendered portal (verified on every page tested).
- No rendered "Phase N" labels (verified).
- No horizontal overflow at either viewport.
- Mobile drawer + backdrop behaviour is correct.
- The `portal` component library (page-header, stat-card, section-card, alert, empty-state,
  status-badge) is a sound foundation and should be extended rather than replaced.
- Billing restriction, suspension and permission gating all behave correctly — the problems
  are presentational, not authorisation faults.

---

## 9. Priorities for the rebuild

1. Make the shell role-aware; delete the dead/duplicate controls (Quick Action, Settings
   alias, Support, search, universal Clock In, mislabelled header links).
2. Add workspace sub-navigation so modules stop being dead ends.
3. Replace opening stat strips with a single "what needs attention" block that links to
   the specific item.
4. Give managers a real cross-workspace review queue destination.
5. Delete duplicated quick-link panels that repeat the sidebar.
6. Fix the five concrete rendering bugs in §5.
7. Rewrite alarmist/speculative copy and reduce uppercase.
8. Re-order mobile content so the primary action is first.
