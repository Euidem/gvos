# GVOS — Portal UX QA Record (Phase 28)

**Date:** 2026-08-01
**Build under test:** Phase 28 working tree, served locally at `http://127.0.0.1:8123`
(`php artisan serve`) against an isolated MySQL 8.4 instance seeded with the controlled
demo environment (`php artisan gvos:demo-setup`).
**Baseline for comparison:** the live deployment at `https://gvos.afbs.ng`, measured during
Part A and recorded in `docs/PORTAL_UX_AUDIT.md`.

---

## 1. How this QA was performed — and its one limitation

**Pixel screenshots were not available.** The browser pane in this environment does not
composite frames, so `screenshot` returns a timeout. Rather than claim a visual comparison
that did not happen, QA was performed by **measuring the rendered DOM and computed layout**
in a real browser, plus full-stack HTTP checks. For the specific criteria the brief lists —
horizontal overflow, clipped controls, dead links, unauthorised links, duplicate links,
banned strings, uppercase usage, above-the-fold action counts, mobile scroll depth — measured
values are stronger evidence than eyeballing a screenshot.

What this does **not** cover: subjective aesthetic judgement (does it *look* premium),
font rendering, and colour harmony. **The owner should do a visual pass** — see
"Pages to inspect first" in the final report.

Every number below was read from the running application.

---

## 2. Automated coverage

### 2.1 Full-page smoke test — all roles, all pages

Logged in as each of the 8 demo accounts and requested every portal page plus all nine
workspace module pages for a workspace that account can access.

```
TALENT       login -> 200  /talent/dashboard
MANAGER      login -> 200  /manager/dashboard
CLIENT       login -> 200  /client/dashboard
BIZADMIN     login -> 200  /client/dashboard
BIZSTAFF     login -> 200  /client/dashboard
OBSERVER     login -> 200  /client/dashboard
LEAD         login -> 200  /lead/dashboard
RESTRICTED   login -> 200  /client/dashboard

RESULT: 143 ok, 1 failed
```

The single failure was `HTTP 0` — a client-side curl timeout on the very first request
after login while Blade was still compiling, not an application error. Re-requested
individually: `/talent/dashboard -> 200`.

**This also verifies the login redirect rules:** all eight non-admin accounts landed on
their own role dashboard and none reached `/admin`.

### 2.2 Navigation reachability — no link may lead to a 403

For each role: scraped every sidebar href and every workspace tab href from the rendered
HTML, then requested each one.

```
TALENT      12 nav links  all 200
MANAGER     13 nav links  all 200
CLIENT      13 nav links  all 200
BIZADMIN    13 nav links  all 200
BIZSTAFF    12 nav links  all 200
OBSERVER     8 nav links  all 200
LEAD        12 nav links  all 200
RESTRICTED  13 nav links  all 200

OK: no navigation link leads to a non-200 for any role
```

96 navigation destinations checked. Note the observer's deliberately smaller set (8): Time,
Reports, Vault, Billing and Team are all 403 for that role and are therefore absent from
navigation rather than rendered and blocked.

### 2.3 Blade compilation

`php artisan view:cache` compiles every template in the project; it succeeded after each
change set, proving no syntax or component-resolution errors across all rebuilt views.

---

## 3. Measured before/after

All figures from the same probe run against the old live build (Part A) and the new local
build (Part J).

### 3.1 Duplicate links

| Page | Before | After |
|------|--------|-------|
| Shell contribution (every page, every role) | **5×** `/workspaces`, 2× `/profile` | 1× `/workspaces`; each nav item a distinct URL |
| Talent dashboard | 7× `/workspaces`, 3× `/profile`, 3× `/notifications` | 2× dashboard (brand + Home), 2× `/notifications` (bell + nav) |
| Manager dashboard | **11×** `/workspaces`, plus 4 more duplicate groups | 2 groups, both conventional |
| Workspace overview | **8×** `/workspaces/{id}/tasks` | 3× tasks (tab + board link + header action) |
| Business admin dashboard | 3 differently-labelled "Quick Actions" all → `/workspaces` | panel removed |

Two duplicate pairs remain by design and are standard web conventions: the sidebar **brand
logo → home** (alongside the Home item) and the header **notification bell → /notifications**
(alongside the Notifications nav item). Neither is a mislabelled link.

### 3.2 Page weight and noise

| Metric | Talent dash | Manager dash | Workspace overview |
|--------|-------------|--------------|--------------------|
| Total links before → after | 33 → **24** | 38 → **28** | 30 → **33*** |
| Card containers before → after | — → 6 | 14 → **4** | 12 → **6** |
| Uppercase text elements before → after | many → **0** | 7 labels → **0** | several → **0** |
| `<h1>` count | 1 | 1 | 1 |
| Primary buttons above the fold | 1 | 0† | 1 |

\* The workspace overview link count rises because the nine-tab workspace navigation is now
present on the page; the *page body* itself lost the duplicated module cards.
† The manager dashboard is queue-driven: each queue row is itself the action, so there is no
separate primary button. Recorded as an intentional deviation from "exactly one primary".

### 3.3 Mobile (390 × 844) — manager dashboard scroll depth

| Element | Before | After |
|---------|--------|-------|
| First actionable review item | 1,804 px (**2.14 screens**) | **187 px (0.22 screens)** — above the fold |
| Vanity "Manager Profile / load bar" card | 442 px — above the fold | **removed** |
| Page height | 2,287 px | 2,168 px |
| Header content on mobile | menu, bell, **irrelevant "Clock In"** | menu, bell |

**9.6× improvement** in distance to the first actionable item.

Workspace page on mobile (390 px):

| Check | Result |
|-------|--------|
| Page horizontal overflow | 0 px |
| Workspace tab bar | 9 tabs, scrolls horizontally (839 px of content in a 358 px rail) — no wrap, no page overflow |
| Tab tap-target height | 46 px (≥ 44 px minimum) |
| Elements wider than viewport | none |
| Sidebar | off-canvas (`translateX(-100%)`), menu button visible |

### 3.4 Banned content and rendering bugs

| Check | Before | After |
|-------|--------|-------|
| Literal `&amp;` visible | yes (business admin + staff dashboards) | **0 occurrences** |
| Money without a currency | `1,980.00` | `USD 1,980.00` |
| Names truncated mid-word | 4 instances found | **0** |
| `GetVirtual` in UI | none | none |
| Rendered "Phase N" labels | none | none |
| Horizontal overflow (both viewports) | none | none |

---

## 4. Defects found *in the new build* during QA, and fixed

QA caught three regressions I had introduced. All were fixed and re-verified.

1. **Five sidebar items resolved to the same URL.** For a user with more than one
   workspace, Time / Messages / Files / Vault / My Work all fell back to `/workspaces` —
   reproducing the exact mislabelled-duplicate-link fault the phase set out to remove
   (measured: `6x /workspaces`). Fixed: workspace-scoped items now always resolve to the
   user's primary workspace, so every item has a distinct destination.
2. **An observer was offered a link that returns 403.** The sidebar gated on the *platform*
   role (`business_client_staff` → shows Reports), but the user is an `observer` in their
   workspace and `WorkspaceWeeklyReportController` aborts for observers. Verified live:
   `/workspaces/7/reports -> HTTP 403`. Fixed: `PortalNav` now also gates on the user's
   **workspace** role, mirroring the controller rules.
3. **Two primary buttons above the fold, and a duplicated task link** on the talent
   dashboard (card title and "Open Task" both linked to the same task). Fixed: the focus
   card title is no longer a link, and "Open Task" was demoted to secondary so "Clock In"
   is the single primary action.

---

## 5. Command verification (Part M)

Run in the order the brief specifies:

| Command | Result |
|---------|--------|
| `php artisan optimize:clear` | DONE |
| `php artisan view:clear` | Compiled views cleared successfully |
| `php artisan route:list` | **167 routes** — unchanged from Phase 27; no routes added, removed or renamed |
| `php artisan config:cache` | Configuration cached successfully |
| `php artisan route:cache` | Routes cached successfully |
| `php artisan view:cache` | Blade templates cached successfully |
| `php artisan gvos:demo-verify` | **16/16 PASS** |

The navigation reachability sweep was then **re-run with `config:cache`, `route:cache` and
`view:cache` all active** and returned the same clean result, confirming the redesign is safe
under production caching.

---

## 6. Accounts and states actually tested

| Account | Role | States exercised |
|---------|------|------------------|
| `talent.one.demo@gvos.test` | Talent | Populated dashboard, focus task, timer idle, multi-workspace nav, all 8 permitted workspace tabs |
| `manager.demo@gvos.test` | Line manager | Populated review queue (9 items), blocked work (3), no running timers, 4 workspaces, desktop + **mobile 390×844** |
| `individual.client.demo@gvos.test` | Individual client | Approvals pending, published report, billing with nothing outstanding |
| `business.admin.demo@gvos.test` | Business account admin | Company framing, outstanding balance with currency, team access |
| `business.staff.demo@gvos.test` | Business client staff | Simplified nav, no billing/vault/team |
| `observer.demo@gvos.test` | Observer (staff role, observer membership) | **Read-only**: no approval CTA, 8 nav links only, Time/Reports/Vault/Billing/Team correctly absent |
| `lead.demo@gvos.test` | Active lead | Trial active, "what happens next", narrow nav (no workspace modules) |
| `restricted.client.demo@gvos.test` | Restricted billing client | Billing restriction banner, restricted page reachable |
| `suspended.demo@gvos.test` | Suspended | Blocked at the account status page (verified in Part A) |

Viewports: **1440 × 900** and **390 × 844**.

---

## 7. What still needs a human eye

1. **Aesthetic judgement.** Whether the portal now reads as "premium" is subjective and was
   not measured. Please review the pages listed in the final report.
2. **Font rendering and colour harmony** under the CDN Tailwind build on the real domain.
3. **Interactive flows end to end** — clock in/out, Kanban drag, file upload/download, report
   publish, vault reveal. These were verified as *rendering* and as *route-reachable* here;
   the full click-through belongs on the deployed staging site with real sessions
   (see the Phase 28 section of `docs/TESTING_CHECKLIST.md`, group G).
