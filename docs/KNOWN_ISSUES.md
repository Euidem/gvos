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
