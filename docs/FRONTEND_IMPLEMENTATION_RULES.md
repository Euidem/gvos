# GVOS — Frontend Implementation Rules

**Created:** 2026-05-31
**Authority:** These rules apply to ALL frontend work from this point forward.
**Stitch Export Location:** `design-reference/stitch_gvos_operations_platform/`

---

## Rule 1 — Stitch is the Source of Truth

`code.html` in each Stitch folder is the authoritative frontend specification. Before writing or editing any Blade view, open the matching `code.html` file from the Stitch export. The mapping is defined in `docs/UI_SOURCE_OF_TRUTH.md`.

**Do not invent layouts.** If a layout question arises, the answer is in the Stitch HTML.

---

## Rule 2 — Match Structure First, Then Details

When replicating a Stitch screen:
1. Match the **HTML structure** and **flex/grid layout** first.
2. Then match **spacing tokens** (`p-gutter`, `space-y-section-gap`, card padding).
3. Then match **typography** (font families, size tokens, weights).
4. Then match **colors and borders**.
5. Then match **icon names** (Material Symbols).

Do not start with colors and work outward — start with the layout skeleton.

---

## Rule 3 — Use the Same Layout Shell

Every portal page uses the same `<x-layouts.gvos>` component. The sidebar structure is:

```html
<aside class="w-[280px] h-screen fixed left-0 top-0 bg-sidebar-bg flex flex-col py-gutter px-4 z-50">
  <!-- Logo: GVOS Platform + Enterprise Ops -->
  <!-- Optional: Workspace Switcher (talent only) -->
  <!-- Nav items (active / inactive) -->
  <!-- Footer: Quick Action button + Settings + Help + User profile card -->
</aside>

<main class="ml-[280px] min-h-screen">
  <header class="sticky top-0 z-40 h-16 bg-surface-container-lowest border-b border-border-subtle shadow-sm flex items-center justify-between px-gutter">
    <!-- Search + nav links (Workspace, Messages, Files) + icons + Clock In -->
  </header>
  <!-- Page content: max-w-[1440px] mx-auto p-gutter or p-8 -->
</main>
```

**Active nav item class:**
```
bg-white/10 border-l-4 border-secondary-fixed text-secondary-fixed-dim font-bold
```

**Inactive nav item class:**
```
text-on-surface-variant hover:text-secondary-fixed hover:bg-white/5 transition-colors
```

---

## Rule 4 — Design Token Compliance

Always use the established GVOS design tokens. Never use hardcoded hex values for tokens that already have names.

**Correct:**
```html
<div class="bg-secondary text-on-secondary px-4 py-2 rounded-lg">
```

**Wrong:**
```html
<div style="background:#0058be; color:#fff; padding:8px 16px; border-radius:8px;">
```

The exception: Visual Repair v3 inline styles (`style="background-color:#0B0F19"`) are kept as CSS fallbacks on structural elements ONLY until a compiled Tailwind build replaces them.

### Token Reference

| Token | Value | Use |
|-------|-------|-----|
| `secondary` | `#0058be` | Brand blue, primary actions, active states |
| `sidebar-bg` | `#0B0F19` | Sidebar background |
| `surface` / `background` | `#f7f9fb` | Page background |
| `surface-container-lowest` | `#ffffff` | Cards, header |
| `border-subtle` | `#E2E8F0` | Card borders, dividers |
| `on-surface` | `#191c1e` | Primary text |
| `on-surface-variant` | `#45464d` | Secondary text |
| `outline` | `#76777d` | Placeholder, muted text |
| `secondary-fixed` | `#d8e2ff` | Sidebar text (active item) |
| `on-primary-container` | `#7c839b` | Sidebar secondary text |

---

## Rule 5 — Typography Rules

Use the font tokens. Do not use plain `font-bold text-gray-800` — use the design-system tokens.

| Token | Family | Use |
|-------|--------|-----|
| `font-headline-lg text-headline-lg` | Manrope 700 | Page titles (h2) |
| `font-headline-md text-headline-md` | Manrope 600 | Section headings, sidebar logo |
| `font-label-md text-label-md` | Inter 600 12px | Labels, nav items, buttons |
| `font-body-md text-body-md` | Inter 400 16px | Body content |
| `font-body-sm text-body-sm` | Inter 400 14px | Secondary content, form hints |
| `font-mono-sm text-mono-sm` | JetBrains Mono | Code, task codes, IDs |

---

## Rule 6 — Card Structure

Standard card pattern from Stitch:
```html
<div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
  <!-- card content -->
</div>
```

Metric card (dashboard stats):
```html
<div class="bg-white rounded-xl border border-border-subtle p-6 shadow-card">
  <p class="font-label-md text-label-md text-outline uppercase tracking-wider mb-1">Label</p>
  <p class="font-headline-lg text-headline-lg text-primary">42</p>
  <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">+3 from last week</p>
</div>
```

---

## Rule 7 — Icon Usage

Use Material Symbols Outlined. Always apply the font-variation-settings for consistent rendering:
```html
<span class="material-symbols-outlined" style="font-size: 20px;">dashboard</span>
```

For filled icons (logo, featured):
```html
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1; font-size: 20px;">security</span>
```

---

## Rule 8 — No Phase Banners

Do not add "Phase X — Feature" status banners to any portal screen. These do not exist in any Stitch design. Use `docs/CURRENT_STATUS.md` to track phase completion internally.

---

## Rule 9 — No Breadcrumbs

Stitch screens do not use breadcrumbs. Use `h2` page titles with a subtitle line instead:
```html
<div class="mb-6">
  <h2 class="font-headline-lg text-headline-lg text-primary">Page Title</h2>
  <p class="font-body-md text-body-md text-on-surface-variant mt-1">Short description or workspace context.</p>
</div>
```

The exception: breadcrumbs may be retained temporarily while full Stitch conversion is in progress.

---

## Rule 10 — No GetVirtual in UI

The brand name `GetVirtual` must never appear in:
- Page titles or tab titles
- Headings, labels, or body text
- Login or signup screens
- Error pages
- Admin panels visible to non-system users

The visible product name is **GVOS** everywhere.

---

## Rule 11 — Preserve Visual Repair v3

Do not remove, comment out, or bypass:
1. The `<!-- GVOS UI Visual Repair v3 active -->` marker comment in `gvos.blade.php`
2. The CSS fallback block (`.bg-sidebar-bg`, `.text-secondary-fixed`, etc.)
3. The hidden safeguard div that forces Tailwind CDN to generate dynamic classes
4. The inline `style="background-color:#0B0F19"` on the sidebar `<aside>` element

These are intentional fallback mechanisms for the CDN Tailwind setup. They are removed ONLY when the project switches to a compiled Vite/npm build.

---

## Rule 12 — Document Stitch Source in Commits

Every commit that modifies a Blade view must reference the Stitch folder used. Format:
```
git commit -m "Update talent dashboard to match talent_dashboard_gvos_1 (Stitch)"
```

---

## Rule 13 — Placeholder Rule for Missing Screens

If a Blade view has no matching Stitch screen:
1. Use the structurally closest Stitch screen as reference.
2. Mark the view file with a comment: `{{-- No Stitch screen — based on: [closest folder] --}}`
3. Document it as "No Stitch source" in `docs/UI_SOURCE_OF_TRUTH.md`.

---

## Rule 14 — No New Feature Code During UI Correction

During the UI correction batches (Batches 1–6), do not add new routes, migrations, models, or controllers. Only Blade views, the layout component, and the auth layout may change.

---

## Rule 15 — Test After Every Batch

After completing each batch:
1. Load each modified page in a browser.
2. Compare side-by-side with the corresponding `screen.png`.
3. Verify responsive behaviour (mobile sidebar collapses).
4. Verify no `GetVirtual` text appears.
5. Verify Visual Repair v3 fallback styles are still present.
