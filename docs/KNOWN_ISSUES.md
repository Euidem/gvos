# GVOS — Known Issues

## Format
Each issue: Date | Phase | Severity | Description | Status | Resolution

Severity levels: Critical | High | Medium | Low | Info

---

## Open Issues

*No blocking issues at this time.*

---

## Resolved Issues

### 2026-05-27 | Phase 0 | Info | PHP/Composer/Node.js not installed on build machine

**Description:** PHP 8.2+, Composer, and Node.js were not installed on the development machine.

**Resolution:** All source files were written by hand. cPanel used as the execution environment. Node/npm not required for Phase 0/1 (Blade + Tailwind CDN).

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | High | Missing auth controllers caused composer install failure

**Description:** `routes/auth.php` referenced 9 auth controllers that did not exist. Laravel validates invokable controllers at `package:discover`, causing cPanel install to crash with `Invalid route action`.

**Resolution:** Created all 9 auth controllers as source files. Commit `54112db`.

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | High | Empty migrations directory caused seeder failure

**Description:** `database/migrations/` was completely empty. Seeder failed with `Table 'roles' doesn't exist`. `config/permission.php` was also missing.

**Resolution:** Created 4 migration files and `config/permission.php`. Commit `299dd7a`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Blade component resolution path incorrect

**Description:** `php artisan view:cache` failed with "Unable to locate component [layouts.auth]". Files were in `resources/views/layouts/` but `<x-layouts.auth>` resolves to `resources/views/components/layouts/`.

**Resolution:** Created component files at correct location. Commit `afd12c7`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Filament 403 after login

**Description:** After successful login, `/admin` returned 403. User model did not implement `FilamentUser` contract.

**Resolution:** Added `implements FilamentUser` and `canAccessPanel()` to User model. Commit `a476da2`.

**Status:** Resolved.

---

### 2026-05-29 | Phase 1 | Critical | Target class [role] does not exist

**Description:**
Logging in as a non-admin user (e.g. Talent) and visiting `/talent/dashboard` threw:

```
Illuminate\Contracts\Container\BindingResolutionException
Target class [role] does not exist.
```

**Root cause:** In Laravel 11, `app/Http/Kernel.php` no longer exists. Spatie Laravel Permission v6 middleware aliases (`role`, `permission`, `role_or_permission`) are **not auto-registered** in Laravel 11 — they must be declared explicitly in `bootstrap/app.php` using `$middleware->alias([...])`. The Phase 1 implementation only registered `check.status` and forgot the Spatie aliases.

**Resolution:** Added all three Spatie middleware aliases to `bootstrap/app.php`:
```php
'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
```

**Status:** Resolved — commit `[Fix Phase 1 role middleware and user form UX]`.

---

## Warnings / Notes

### Phase 1 | Low | Tailwind CDN in production

Using `https://cdn.tailwindcss.com` is acceptable for Phase 0/1/2/3 staging. Should be replaced with a compiled Vite build before production launch.

---

### Phase 1 | Low | Inertia middleware active but no React pages

`HandleInertiaRequests` runs on every web request as a no-op (Blade-only responses pass through). Harmless until React pages are introduced in a later phase.

---

### Phase 3 | Medium | Active lead password reset required after trial approval

When "Approve Trial" creates a new user for the lead, the account is created with a random 20-char password. The lead cannot log in until they complete a password reset via the "Forgot Password" flow. The admin notification message in Filament advises this. A future improvement could be to send an automated welcome/reset email on trial approval.

---

### Phase 3 | Low | Trial countdown does not auto-refresh

The trial countdown displayed on the active-lead dashboard (`/lead/dashboard`) is rendered at page load time and does not update in real time. The user must refresh the page to see the updated hours remaining. A JavaScript countdown timer should be added in a future phase for a better UX.

---

### Phase 3 | Low | Price estimate currency display on active-lead dashboard

The price estimate card on the active lead dashboard shows the latest accepted estimate. If no estimate is accepted, it falls back to `latestAcceptedEstimate()` from the lead request. If neither exists, no estimate card is shown. This is intentional but worth noting for UX.

---

### Phase 3 UX | Low | Multi-step form does not preserve step position on browser back-button

If the user navigates away and then presses the browser's back button, the page reloads and returns to Step 1 (or the last Laravel-restored step). This is expected behaviour for a server-rendered form. A future improvement could use `sessionStorage` to persist the current step across soft navigations.

---

### Phase 3 UX | Low | Timezone "Other" value is stored as free text

When a user selects "Other" and types a custom timezone (e.g. `Asia/Kolkata`), that string is stored verbatim in `lead_requests.timezone`. The GVOS admin will see this in the Filament Lead Requests edit form. There is no validation that the value is a real IANA timezone. This is intentional for flexibility — a low-stakes field at the lead stage.

---

### Phase 3 UX | Info | Side panel illustration is CSS-only

The "Inside GVOS" illustration panel on the `/request-service` page is built entirely with HTML/CSS and Tailwind classes. No external images or SVG files are used. It represents a simplified preview of the GVOS workflow. Replace with real brand imagery when assets are available.

---

### Phase 3 | Info | Trial workspace features are placeholders

The active lead dashboard now shows a live workspace link when a workspace exists, or a "being prepared" placeholder when it does not. Tasks, files, and communication within the workspace are Phase 5+ features. The placeholder correctly communicates this to the lead.

---

### Phase 4 | Low | Country dropdown is a fixed list (not a full ISO 3166 list)

The `CountryList::options()` helper contains 21 common countries. Users whose country is not listed cannot select it — they must contact the GVOS team. Expanding to a full ISO country list is a future improvement.

---

### Phase 4 | Low | Workspace members are not de-duplicated on trial workspace creation

The "Create Trial Workspace" action creates members for active_lead, talent, and manager. If any of these users are the same person (edge case in testing), a database unique constraint error on `(workspace_id, user_id)` will fire. This is intentional — the unique constraint is correct business logic.

---

### Phase 4 | Info | Workspace task board is a placeholder

The workspace detail page (`/workspaces/{workspace}`) displays a "Tasks, files, and chat coming soon" placeholder panel. These features are Phase 5+.

---

### UI Audit | Low | Filament admin panel uses its own CSS — GVOS Stitch tokens not applied

The Filament admin panel (`/admin`) compiles its own stylesheet. GVOS Stitch design tokens (sidebar-bg, secondary, status-* colors, Manrope/Inter fonts) are **not applied** to Filament views. The Filament panel has a distinct visual identity from the GVOS portal.

This is intentional for Phase 0–4. Aligning Filament's visual identity requires either a Filament theme class with compiled CSS, or a full Filament panel theme. Treat this as a future improvement post-Phase 5.

---

### UI Audit | Info | Tailwind CDN dynamic class safeguard must not be removed

The GVOS portal layout uses Tailwind CDN (JIT mode). Dynamic PHP-conditional classes — for example, the active nav state `bg-white/10 border-l-4 border-secondary-fixed` — must appear in a rendered HTML element so the JIT engine scans and generates them.

A hidden safeguard `<div class="hidden ...">` was added to `resources/views/components/layouts/gvos.blade.php` containing all dynamically-rendered nav and status classes. **Do not remove this div.** Removing it will cause active nav highlighting and dynamic status badge colors to stop rendering.

---

### UI Audit v2 — Resolved | Tailwind CDN config loaded after CDN script (root cause of no visible UI change)

**Description:** After the first UI Fidelity Audit commit (`c472ebb`), the live app showed no visible change. The root cause was that `tailwind.config = {...}` was declared in a `<script>` block that appeared **after** `<script src="https://cdn.tailwindcss.com">` in all three component layout files. Per Tailwind CDN documentation, the config must be defined before the CDN script executes. The CDN was compiling with default settings only, so all custom GVOS tokens were never generated.

**Resolution:** Moved `tailwind.config` script block before the CDN `<script>` tag in all three component layouts. Added a comprehensive CSS fallback `<style>` block as a secondary safety net. Remaining indigo/slate/violet classes in 6 auth/lead views were also removed.

**Status:** Resolved — commit [Fix GVOS UI token rendering and visible styling].

---

### UI Audit | Low | `card-lift` class has no defined styles

The workspace index view (`resources/views/workspace/index.blade.php`) applies a `card-lift` class on workspace cards for a hover-lift effect. This class is defined in the gvos.blade.php CSS fallback block (transition + translateY + box-shadow). This was resolved as part of UI Repair v3 documentation pass.

---

### UI Repair v3 | Info | Custom spacing tokens are unreliable with Tailwind CDN JIT — use standard utilities

Custom spacing tokens defined in `tailwind.config extend.spacing` (e.g. `card-padding`, `input-gap`) are not reliably generated by the Tailwind CDN JIT scanner when used only as Tailwind class names. When these tokens fail to generate, zero spacing is applied, causing headings to touch card borders and form fields to collapse.

**Rule established:** Use standard Tailwind spacing utilities (`p-8`, `space-y-5`, `gap-5`, `px-8 pb-8`) for structural spacing. Custom spacing tokens may be kept in config for documentation but must not be relied upon for visible layout.

**Status:** Resolved in UI Visual Repair v3.

---

### UI Repair v3 | Info | Structural background colors must use inline styles, not custom token classes

Custom color token classes (`bg-sidebar-bg`, `bg-background`) on structural elements (`<body>`, `<aside>`, `<main>`) are unreliable even with both a tailwind.config definition and a CSS fallback block. The Tailwind CDN `@base` layer or browser default stylesheets can override them in some environments.

**Rule established:** `<body>`, `<aside>` (sidebar), and `<main>` (content area) backgrounds must use inline `style="background-color:#..."`. The CSS fallback block remains for non-structural token classes (badges, text, borders). Arbitrary values (`bg-[#hex]`) are also reliable and acceptable for structural elements.

**Status:** Resolved in UI Visual Repair v3.
