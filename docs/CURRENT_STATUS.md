# GVOS — Current Status

**Last Updated:** 2026-05-29
**Current Phase:** Phase 1 — Identity and Access Foundation ✅ Complete

---

## Phase 0 Status — Complete ✅

All Phase 0 objectives confirmed working on cPanel staging:
- GitHub repo → working
- cPanel deployment → working
- Laravel loads → working
- Login → working
- Filament admin login → working
- Migrations → working
- Seeders → working
- Roles seeded → working
- Super Admin `/admin` access → working
- Blade fallback pages → working (no Node required)

---

## Phase 1 Status — Complete ✅

### Implemented

#### Database
- [x] `pending` status added to users.status ENUM
- [x] `user_profiles` table — extended profile data per user
- [x] `audit_logs` table — immutable event audit trail

#### Models
- [x] `UserProfile` model with `belongsTo(User)` relationship
- [x] `AuditLog` model — immutable, blocks updates/deletes at app level
- [x] `User` model — added `profile()` hasOne, status helpers, `isAccessBlocked()`

#### Services
- [x] `AuditLogger` service — convenience methods for all Phase 1 events
  - `userCreated`, `userUpdated`, `roleChanged`, `statusChanged`
  - `passwordChanged`, `profileUpdated`, `login`

#### Middleware
- [x] `CheckAccountStatus` — blocks suspended/inactive users from dashboards
- [x] Registered as `check.status` alias in `bootstrap/app.php`

#### Filament — GVOS Ops Console
- [x] `UserResource` — User management panel
  - View all users (super_admin + operations_admin)
  - Create users with name, email, password, role, status (super_admin only)
  - Edit user details, role and status (super_admin only)
  - Deletion disabled — use status changes instead
  - Search by name / email
  - Filter by role and status
  - Status badges with colour coding
  - Audit log entries on create and edit

#### Controllers
- [x] `ProfileController` — show and update profile + extended profile
- [x] `PasswordController` — change password with current_password validation + audit log
- [x] `AuthenticatedSessionController` — login audit log added

#### Routes
- [x] `GET/PUT /profile` — profile editing (all authenticated roles)
- [x] `GET /account/status` — suspended / inactive holding page
- [x] All dashboards now use `check.status` middleware
- [x] `role:` middleware on all portal routes

#### Blade Views (Phase 0 Blade fallback maintained)
- [x] `profile/edit.blade.php` — full profile + password change form
- [x] `account/status.blade.php` — suspended / inactive holding page
- [x] All 8 role dashboards improved:
  - Personalised welcome (uses first_name from profile)
  - Status badge + role badge
  - Working quick-action links (profile, admin panel)
  - Dashed placeholder cards for coming modules
  - Phase 1 coloured notice banner

---

## Admin Credentials (Staging Only)

> ⚠️ Change these before any production use.

| Field | Value |
|-------|-------|
| Email | admin@gvos.local |
| Password | password |
| Role | super_admin |
| Portal | /admin (Filament Ops Console) |

---

## cPanel Deployment — Commands to Run After Each Pull

```bash
git pull origin main
php artisan migrate
php artisan optimize:clear
php artisan permission:cache-reset
```

---

## Architecture Notes

- **Auth guard:** `web` (session)
- **Blade CDN:** Tailwind CDN via `<script src="https://cdn.tailwindcss.com">` — Phase 0/1 staging only
- **Node/npm:** Not required for Phase 1. Phase 2+ will introduce Vite React builds.
- **Filament panel:** `/admin` — protected by `canAccessPanel()` on User model
- **Role middleware:** Spatie `role:` middleware on all portal prefixes
- **Status middleware:** `check.status` alias blocks suspended/inactive users

---

## Next Steps

1. cPanel: run `php artisan migrate` and `php artisan optimize:clear`
2. Test Filament Users resource at `/admin/users`
3. Test profile editing at `/profile`
4. Test password change
5. Test account suspension blocking access
6. Get Phase 1 approval
7. Begin Phase 2: People and Organizations (companies, departments, talent profiles, manager profiles)
