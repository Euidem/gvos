# GVOS — Portal UX Redesign Specification

**Created:** 2026-07-31 (Phase 28, Parts B & C)
**Status:** **Active — this document supersedes earlier portal layout decisions where they conflict.**

> **Authority note.** Where this spec conflicts with `docs/UI_SOURCE_OF_TRUTH.md` or
> `docs/FRONTEND_IMPLEMENTATION_RULES.md` (Rules 1, 3, 6, 9), **this spec wins** for the
> non-admin portal. The Stitch export remains a useful visual reference for colour,
> typography and card styling, but it is no longer the layout authority: Stitch has no
> role-aware navigation, no workspace sub-navigation and no attention-first dashboards,
> which is precisely what the owner asked for. Filament admin is out of scope and unchanged.

---

## 1. Design principles

1. **Answer the user's question first.** Every page opens with what the user came for, not
   with a summary of the system.
2. **One primary action per screen.** Exactly one filled blue button above the fold.
   Everything else is secondary (outline), tertiary (text) or in an overflow menu.
3. **Count only what drives a decision.** A number earns its place only if a different
   value would change what the user does next. Otherwise it becomes a caption, not a card.
4. **Name the thing.** Never say "1 item needs review" without linking to *that item*.
5. **Group with space, not borders.** Use whitespace, a section label and alignment before
   reaching for another white rectangle.
6. **Progressive disclosure.** Primary → secondary → rare. Rare actions live in menus or
   below the fold.
7. **Never show what the role cannot use.** Hide it; do not render a disabled placeholder.

---

## 2. Role journeys (Part B)

### 2.1 Talent — "What am I doing right now?"
| Question | Answered by |
|----------|-------------|
| What should I work on now? | **Focus block**: the single highest-priority open task, named, with a direct link |
| Am I clocked in? | **Timer bar** — persistent in the shell header when running; primary card on the dashboard |
| What is assigned to me? | "My work" list, grouped Blocked → Needs revision → Due soon → In progress |
| What is urgent/blocked? | Same list, ordered by urgency; blocked items first with reason |
| Where do I submit work? | Task row → task detail; the submit action lives on the task |
| Files / credentials? | Workspace sub-nav → Files / Vault |
| What time logs need action? | "Needs your attention" row: rejected or draft logs |
| What did my manager say? | Latest workspace message preview with link to chat |

**Sidebar:** Home · My Work · Workspaces · Time · Messages · Files · Notifications

### 2.2 Manager — "What needs me?"
| Question | Answered by |
|----------|-------------|
| What needs my attention now? | **Review queue** listing *actual items* (time logs, submitted tasks, draft reports) with deep links |
| Who is working now? | "Currently working" strip — running timers across supervised workspaces |
| Which logs need review? | Review queue, time-log group |
| Blocked / awaiting review tasks? | Review queue, task group |
| Reports to prepare or publish? | Review queue, report group |
| What is happening across workspaces? | Workspace list with per-workspace attention badges |
| What to communicate to clients? | Report drafts group + workspace chat links |

**Sidebar:** Command Center · Review Queue · Workspaces · Time · Reports · Messages · Files · Notifications
*(Review Queue is an anchor into the Command Center — no new route.)*

### 2.3 Individual client — "How is my work going?"
| Question | Answered by |
|----------|-------------|
| What progress has been made? | Latest published report summary, inline |
| What is being worked on now? | "In progress" task list (titles, not counts) |
| What needs my approval? | **Awaiting your approval** block, each item linked directly |
| Reports and deliverables? | Reports + Files in sub-nav |
| Can I contact my team? | Messages, with the team named |
| Billing status? | Single billing line; expands only when action is needed |

**Sidebar:** Overview · Progress · Tasks · Reports · Files · Messages · Billing · Notifications

### 2.4 Business client admin — "How is my account doing?"
Adds company-level framing: Company Overview, Workspaces, Reports, Tasks, Files, Messages,
Team Access, Billing, Vault, Notifications.

### 2.5 Business client staff — simplified
Overview · Workspaces · Tasks · Reports · Files · Messages · Notifications.
**No** billing management, **no** vault administration, **no** member administration.

### 2.6 Observer — read-only
Overview · Workspaces · Tasks · Messages · Files · Notifications.
Never render approve/submit/upload affordances. Time, Reports, Billing and Vault are
403 for this role and are therefore **absent from navigation**.

### 2.7 Active lead — narrow funnel
Shows: request status, estimate/trial state, **the next step**, what information is needed,
and how to proceed. Does **not** get the full client workspace navigation.
**Sidebar:** My Request · My Trial Workspace · Profile · Notifications.

---

## 3. Information architecture (Part C)

### 3.1 Global navigation structure

Two levels only:

```
Sidebar (role-aware, persistent)
└── Workspace sub-navigation (tabs, only inside a workspace)
```

The old third level (header quick-links) is **removed** — it duplicated the sidebar and
was mislabelled.

**Sidebar groups**

| Group | Contents |
|-------|----------|
| (ungrouped, top) | Home / Command Center / Overview — the role dashboard |
| **Work** | My Work · Review Queue · Workspaces · Time · Reports |
| **Communication** | Messages · Files · Notifications |
| **Account** | Billing · Team Access · Vault · Profile |

Groups render only if they contain at least one permitted item. Group labels are sentence
case, 11 px, muted — not uppercase blocks.

### 3.2 Role → sidebar matrix

Derived from the real gates in the workspace controllers (verified in code).

Two gates apply. First the **platform role** decides which items are offered at all;
then the user's **workspace role in their primary workspace** removes anything that role
cannot open (e.g. a `business_client_staff` user who is an `observer` in their workspace).

| Item | Talent | Manager | Ind. client | Biz admin | Biz staff | Lead |
|------|:------:|:-------:|:-----------:|:---------:|:---------:|:----:|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| My Work | ✓ | — | — | — | — | — |
| Review Queue | — | ✓ | — | — | — | — |
| Workspaces | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| My Trial Workspace | — | — | — | — | — | ✓ |
| Time | ✓ | ✓ | — | — | — | — |
| Reports | — | ✓ | ✓ | ✓ | ✓ | — |
| Messages | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| Files | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| Billing | — | — | ✓ | ✓ | — | — |
| Team Access | — | — | — | ✓ | — | — |
| Vault | ✓ | ✓ | ✓ | ✓ | — | — |
| Notifications | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Profile | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

**Second gate — workspace role.** A workspace-scoped item is dropped when the primary
workspace role cannot open it: `observer` loses Time, Reports and Vault; `talent` loses
Billing. This is what keeps an observer from being shown a Reports link that returns 403.

Workspace-scoped items always resolve to the user's **primary workspace**, even when they
belong to several — pointing them at the workspace list instead would give Time, Messages,
Files and Vault the *same* href as "Workspaces", recreating the duplicate-link problem this
phase exists to remove. The Workspaces item switches project; the workspace tab bar switches
module. Items are hidden entirely when the user has no workspace.

**Removed from the shell:** "Quick Action", "Settings" (alias of Profile), "Support"
(permanently disabled), the decorative search box, the universal "Clock In" button, and the
header "Workspace / Messages / Files" links.

### 3.3 Workspace sub-navigation

Every page inside `/workspaces/{id}/…` renders the same horizontal tab bar directly under
the workspace title:

```
Overview · Tasks · Messages · Files · Time · Reports · Team · Billing · Vault
```

Visibility per workspace role (verified against controllers):

| Tab | admin | workspace_admin | manager | talent | client_admin | client_staff | observer |
|-----|:-----:|:---------------:|:-------:|:------:|:------------:|:------------:|:--------:|
| Overview | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Tasks | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Messages | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Files | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Time | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | **✗ 403** |
| Reports | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | **✗ 403** |
| Team | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Billing | ✓ | ✓ | ✓ | **✗ 403** | ✓ | ✓ | **✗ 403** |
| Vault | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | **✗ 403** |

A tab is rendered **only** where the controller would allow the page. No disabled tabs.

### 3.4 Dashboard structure (all roles)

```
1  Context bar      role eyebrow + greeting + one-sentence state    (no card)
2  Attention block  the work — named items, each deep-linked        (primary)
3  Primary action   exactly one filled button, inside block 2
4  Workspace list   one row per workspace, attention badges
5  Supporting       at most 3 compact metrics, as a caption strip   (no cards)
6  Secondary        latest report / billing line / profile status
```

Blocks 5 and 6 never appear above block 2.

### 3.5 Page header convention

```
[eyebrow — parent context, links back]
H1  Page title            [status badge]        [primary action] [secondary]
    One-line subtitle explaining the page
────────────────────────────────────────────────────────  (hairline rule)
```

- Exactly one `<h1>` per page (previously `<h2>` was used for both page and section titles).
- Section headings are `<h2>`, 15 px semibold — not `headline-md`.
- The eyebrow replaces the ad-hoc `arrow_back` links; **one** back affordance per page.

### 3.6 Breadcrumb convention

Breadcrumbs are used **only** inside a workspace, as the header eyebrow:

```
Workspaces / Executive Support Operations
```

Max two levels. The current page is *not* repeated in the breadcrumb (it is the `<h1>`).
This supersedes Frontend Rule 9 ("no breadcrumbs") for workspace-scoped pages.

### 3.7 Primary / secondary action rules

| Level | Style | Count per page |
|-------|-------|----------------|
| Primary | filled `#0058be`, white text | **at most 1** above the fold |
| Secondary | 1 px border, transparent bg | ≤ 3 |
| Tertiary | text + chevron | unlimited, but never in the header |
| Destructive | red text, outline on hover | in overflow only |

**Zero primary buttons is correct on queue-driven pages.** Where the page *is* a list of
actionable items (the manager Command Center), each row carries its own action and adding a
separate primary button would invent an action the user did not ask for.

### 3.8 Alert hierarchy

| Level | Use | Placement |
|-------|-----|-----------|
| **Blocking** | Account suspended, workspace restricted | Replaces page content |
| **Critical** | Overdue invoice | Top of page, once |
| **Warning** | Due soon, blocked task | Inline, next to the thing |
| **Info / success** | Flash confirmations | Top of content, auto-styled |

A condition is stated **once per page**. The restricted-client dashboard currently states
it four times; the rebuild states it once, at Critical level.

### 3.9 Empty-state convention

Three parts: what this is, why it is empty, and one action (or an honest "nothing to do").
Role-aware. Never hedge ("…if this page is empty").

### 3.10 Mobile behaviour (390 px)

- Sidebar → left drawer with backdrop (kept; already works).
- Header keeps: menu, workspace/page title, notification bell, running-timer chip.
  The "Clock In" button is removed.
- Content order is the **same** as desktop — attention block first. Metrics move below.
- Workspace tabs become a horizontally scrollable strip with momentum, no wrap.
- Tap targets ≥ 44 px.
- Tables become stacked rows below `md`.

### 3.11 Width and spacing

| Token | Value | Use |
|-------|-------|-----|
| Page max width | `1280px` | reduced from 1440 — long lines hurt scanning |
| Reading column | `720px` | reports, task descriptions, messages |
| Page padding | 16 / 24 / 32 px at sm / md / lg | |
| Block gap | 32 px | between numbered blocks in §3.4 |
| Card padding | 20 px | reduced from 24 |
| Row height | 56 px min | list rows |

### 3.12 Card and table usage rules

- **Card** — only when the content is an independent object the user can act on.
- **List row** — default for collections (tasks, logs, files, members, notifications).
- **Table** — only for ≥ 3 comparable numeric/date columns (invoices, time logs on desktop).
- **Never** wrap a single number in a card. Use the caption strip.
- Maximum **6** card containers per page (workspace overview currently has 12).

### 3.13 Status badge rules

Keep the existing `status-badge` colour map. Rules: one badge per row; badge states the
*object's* status only; urgency (overdue, blocked) is a separate inline chip so it cannot
be confused with lifecycle state.

### 3.14 Form layout rules

Single column, max 560 px. Label above input. Help text below. Errors inline, red, below
the field. Primary submit bottom-left of the form, not floating in the page header.

### 3.15 Typography hierarchy

| Role | Spec |
|------|------|
| Page title (`h1`) | Manrope 700, 26 px / 32 — down from 32 px |
| Section (`h2`) | Inter 600, 15 px, `on-surface` |
| Group label | Inter 600, 11 px, `outline`, **sentence case** |
| Body | Inter 400, 14 px |
| Metric value | Manrope 700, 22 px |
| Caption | Inter 400, 12 px, `outline` |

**Uppercase is restricted to status badges only.** All other uppercase labelling is removed.

### 3.16 Colour usage

Existing tokens are kept. New rules:
- Blue `#0058be` = interactive only. Never decorative backgrounds.
- Gradients removed from dashboards.
- Status colours only on status objects.
- Page background `#f7f9fb`; content surfaces `#ffffff`; hairlines `#E2E8F0`.

### 3.17 Icon usage

Material Symbols Outlined, 20 px in nav, 18 px inline, 16 px in chips. An icon never
appears without a text label except in the notification bell and overflow menus. Decorative
icon tiles inside cards are removed.

### 3.18 Loading and no-data states

No client-side loading states exist (server-rendered). Rules: never render a card whose
value would be "—"; hide the block. Zero states use the empty-state component, and a zero
metric renders as muted, not red.

---

## 4. Implementation constraints (unchanged)

Preserved verbatim: route names, form actions, CSRF tokens, method spoofing, every
authorization condition, billing restriction gates, vault reveal flow and rate limits,
timer start/stop/complete, file validation and download authorization, notification
scoping, invitation token handling, client data-visibility rules.

Visual Repair v3 (CDN Tailwind + CSS token fallbacks + safeguard div) is **retained** —
no compiled build is introduced in this phase.
