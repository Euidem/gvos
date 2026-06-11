# GVOS — Production Readiness Checklist

**Created:** 2026-06-11 (Phase 24)
**Purpose:** Step-by-step launch checklist for deploying GVOS to production (cPanel/MySQL).

> This checklist assumes a Laravel 11 + Filament 3 deployment on shared cPanel hosting
> with MySQL. PHP is **not** available in the local dev sandbox, so all `php artisan`
> commands below MUST be run on the cPanel server (via SSH or the cPanel Terminal).

---

## 1. Pre-Deployment (local / git)

- [ ] All work committed and pushed to `main`
- [ ] `.env.example` reviewed — contains **no real secrets** (verified Phase 24)
- [ ] Database backup taken of the current production DB
- [ ] File storage backup taken of `storage/app/private/` (workspace files + uploads)
- [ ] Note the current `APP_KEY` value — see the **APP_KEY warning** below

---

## 2. Deployment Commands (run on cPanel, in order)

```bash
# 1. Pull latest code
git pull origin main

# 2. Install PHP dependencies (only if composer.lock changed)
composer install --no-dev --optimize-autoloader

# 3. Run migrations (safe — additive only; see Phase 24 migration audit)
php artisan migrate --force

# 4. Clear all caches
php artisan optimize:clear

# 5. Rebuild caches for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Reset the Spatie permission cache (roles/permissions)
php artisan permission:cache-reset

# 7. Storage health check (disk config, writability, symlink, private root)
php artisan gvos:storage-check

# 8. Billing status refresh — DRY RUN first (no DB writes)
php artisan gvos:billing-refresh-statuses --dry-run

# 9. Billing status refresh — ACTUAL run (writes status changes)
php artisan gvos:billing-refresh-statuses
```

> **Note on `config:cache`:** once config is cached, `.env` changes require re-running
> `php artisan config:cache` to take effect. If you change `.env`, re-run step 5.

---

## 3. Production Environment Variables (`.env`)

| Variable | Production value | Why |
|----------|------------------|-----|
| `APP_ENV` | `production` | Disables dev affordances |
| `APP_DEBUG` | `false` | **Critical** — debug output leaks stack traces, env, queries |
| `APP_URL` | `https://yourdomain.com` | Correct links, storage URLs |
| `SESSION_SECURE_COOKIE` | `true` | **Critical** — prevents session cookie theft over HTTP (HTTPS only) |
| `SESSION_ENCRYPT` | `true` (recommended) | Encrypts session payloads at rest |
| `DB_*` | cPanel MySQL credentials | Database connection |
| `MAIL_MAILER` | `smtp` | Real email delivery (see cPanel SMTP block in `.env.example`) |
| `MAIL_HOST` / `MAIL_PORT` / `MAIL_USERNAME` / `MAIL_PASSWORD` | cPanel mail account | SMTP delivery |
| `MAIL_FROM_ADDRESS` | `noreply@yourdomain.com` | Branded sender |
| `LOG_LEVEL` | `warning` or `error` | Reduce log noise/sensitive volume |

### APP_KEY warning (READ THIS)

- `APP_KEY` must be generated **once** (`php artisan key:generate`) and then kept **STABLE**.
- The **password vault** encrypts `secret_value` via Laravel's `encrypted` cast, which
  derives its key from `APP_KEY`.
- **If `APP_KEY` is changed or regenerated in production, ALL existing vault secrets
  become permanently undecryptable.** Back up `APP_KEY` alongside your database backup.
- `VAULT_ENCRYPTION_KEY` in `.env.example` is **reserved for future use** and is not
  consumed by any current code. Leave it blank.

---

## 4. Post-Deployment Smoke Tests (manual)

### Admin (Filament `/admin`)
- [ ] Super Admin can log in at `/admin`
- [ ] Operations Admin can log in at `/admin`
- [ ] Command Center dashboard loads with all 9 widgets (no Filament property errors)
- [ ] Quick Actions links resolve (no "route not found")
- [ ] Operational Alerts render
- [ ] Audit Logs resource is read-only (no create/edit/delete)
- [ ] Vault widgets show counts only (no secret values)
- [ ] Email Delivery Logs show no secrets
- [ ] A non-admin user is denied access to `/admin`

### Mail
- [ ] `/admin/mail-test` (super_admin / operations_admin only) sends a test email
- [ ] Test email arrives and is GVOS-branded

### Role-based portal smoke tests
- [ ] **Talent**: dashboard loads, clock-in/out works, time log submits
- [ ] **Line Manager**: dashboard loads, can review submitted time logs, generate report
- [ ] **Individual Client**: dashboard loads, sees published reports only, billing visible
- [ ] **Business Client Admin**: dashboard loads, can invite staff, sees billing
- [ ] **Business Client Staff**: dashboard loads, limited access correct
- [ ] **Active Lead**: lead dashboard loads
- [ ] Suspended/inactive user is redirected to `/account/status`
- [ ] Restricted-billing client is redirected to the restricted page on gated routes

### Security spot checks
- [ ] Vault reveal is POST-only and rate-limited (direct GET 405)
- [ ] File download requires workspace membership; internal files hidden from clients
- [ ] One user cannot read another user's notifications
- [ ] Invitation link cannot grant super_admin / operations_admin

---

## 5. Backups & Monitoring

- [ ] Database backup scheduled (daily recommended)
- [ ] File storage backup scheduled (`storage/app/private/`)
- [ ] `APP_KEY` stored securely with backups
- [ ] Error log location confirmed (`storage/logs/laravel.log`)
- [ ] Scheduler/cron configured if `gvos:billing-refresh-statuses` should run daily:
      `php artisan schedule:run` via cPanel cron (every minute) — or call the command
      directly on a daily cron.

---

## 6. Rollback Plan

- [ ] Previous git commit hash recorded before deploy
- [ ] DB backup available to restore if a migration causes issues
- [ ] To roll back code: `git reset --hard <previous-hash>` then re-run cache commands
- [ ] Migrations in this release are additive (no destructive `down()` needed for rollback)

---

## Quick Reference — GVOS Artisan Commands

| Command | Purpose |
|---------|---------|
| `php artisan gvos:storage-check` | Verify file storage health (private root, writability, symlink) |
| `php artisan gvos:billing-refresh-statuses --dry-run` | Preview billing status transitions |
| `php artisan gvos:billing-refresh-statuses` | Apply billing status transitions (payment_due → overdue → restricted) |
| `php artisan permission:cache-reset` | Clear Spatie role/permission cache after role changes |
