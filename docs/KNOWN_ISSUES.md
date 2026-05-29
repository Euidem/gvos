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

**Status:** Resolved — Blade fallback deployed to cPanel successfully.

---

### 2026-05-27 | Phase 0 | High | Missing auth controllers caused composer install failure

**Description:** `routes/auth.php` referenced 9 auth controllers that did not exist as files. Laravel validates invokable controllers during `package:discover`, causing cPanel `composer install` to crash with `Invalid route action`.

**Resolution:** Created all 9 auth controllers as hand-written source files. Commit `54112db`.

**Status:** Resolved.

---

### 2026-05-27 | Phase 0 | High | Empty migrations directory caused seeder to fail

**Description:** `database/migrations/` was completely empty. `php artisan db:seed` failed with `Table 'roles' doesn't exist`. Also `config/permission.php` was missing.

**Resolution:** Created 4 migration files and `config/permission.php`. Commit `299dd7a`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Blade component resolution path incorrect

**Description:** `php artisan view:cache` failed with "Unable to locate component [layouts.auth]". Files were in `resources/views/layouts/` but `<x-layouts.auth>` resolves to `resources/views/components/layouts/`.

**Resolution:** Created component files at correct location with `@props` declarations. Commit `afd12c7`.

**Status:** Resolved.

---

### 2026-05-28 | Phase 0 | High | Filament 403 after login

**Description:** After successful login, `/admin` returned 403. User model did not implement `FilamentUser` contract. Filament v3 denies all users without this interface.

**Resolution:** Added `implements FilamentUser` and `canAccessPanel()` to User model. Commit `a476da2`.

**Status:** Resolved.

---

## Warnings / Notes

### Phase 1 | Low | Tailwind CDN in production

Using `https://cdn.tailwindcss.com` in production is acceptable for Phase 0/1 staging but should be replaced with a compiled Vite build in Phase 2+. The CDN version is larger and generates styles at runtime.

**Planned resolution:** Phase 2 — set up Node/npm on local machine or CI, run `npm run build`, commit `public/build/`.

---

### Phase 1 | Low | Inertia middleware active but no React pages used

`HandleInertiaRequests` middleware is registered in `bootstrap/app.php` and executes on every web request. Currently harmless (Blade-only responses are passed through). When React pages are introduced in Phase 2+, the middleware will handle shared data injection.

**No action needed** until React migration begins.
