# GVOS — Current Status

**Last Updated:** 2026-05-29
**Current Phase:** Phase 1 — Identity and Access Foundation ✅ Complete (v2 patch applied)

---

## Phase 0 Status — Complete ✅

All Phase 0 objectives confirmed working on cPanel staging.

---

## Phase 1 Status — Complete ✅ (patch applied 2026-05-29)

### Phase 1 Core (initial release)
- [x] user_profiles table — extended profile data
- [x] audit_logs table — immutable event trail
- [x] UserProfile and AuditLog models
- [x] AuditLogger service
- [x] CheckAccountStatus middleware
- [x] ProfileController (show + update)
- [x] PasswordController (change password + audit log)
- [x] Filament UserResource (view, create, edit, filters)
- [x] Profile editing page at /profile
- [x] Account status holding page
- [x] All 8 dashboards improved

### Phase 1 Patch — Fixes and UX improvements

#### PART A — Critical Bug Fix: Spatie Role Middleware
- [x] **Root cause:** In Laravel 11, Spatie middleware aliases (`role`, `permission`, `role_or_permission`) are NOT auto-registered. Visiting `/talent/dashboard` as a Talent user threw `Target class [role] does not exist`.
- [x] **Fix:** Registered all three Spatie middleware aliases in `bootstrap/app.php` using `$middleware->alias([...])`.

#### PART B — User Management: First & Last Name
- [x] Filament user create/edit form now includes `first_name` and `last_name` fields.
- [x] Display name (`users.name`) is auto-generated from first + last if left blank.
- [x] Edit form pre-fills first_name / last_name from `user_profiles` via `mutateFormDataBeforeFill()`.
- [x] `CreateUser` and `EditUser` pages save first/last name to `user_profiles` table in `afterCreate()` / `afterSave()`.

#### PART C — Friendly Role Labels
- [x] Internal role slugs (`line_manager`, `business_client_admin`) are replaced with human-readable labels in all Filament UI (form dropdown, table column, role filter).
- [x] Label mapping in `UserResource::roleLabels()` static method.
- [x] Database role slugs are unchanged.

#### PART D — Timezone Dropdown
- [x] Filament user form: timezone is now a searchable Select with 11 practical options.
- [x] Profile edit Blade page: timezone is now a `<select>` with the same 11 options.
- [x] Default timezone: `Africa/Lagos` (GetVirtual primary market).
- [x] ProfileController validates timezone against the allowed list (`Rule::in`).

#### PART E — Improved Audit Logging
- [x] `EditUser` now snapshots User fields **before** save (since `getDirty()` is empty after save).
- [x] Audit context includes from/to values for changed fields.
- [x] First/last name changes included in audit context.

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

## cPanel — Commands to Run After Each Pull

```bash
git pull origin main
php artisan migrate
php artisan optimize:clear
php artisan permission:cache-reset
```

---

## Architecture Notes

- **Role middleware:** `role:X` aliases now correctly registered via `bootstrap/app.php`
- **Auth guard:** `web` (session)
- **Blade CDN:** Tailwind CDN — Phase 0/1 staging only
- **Node/npm:** Not required for Phase 0/1
- **Filament panel:** `/admin` — `canAccessPanel()` restricts to super_admin + operations_admin
- **Status middleware:** `check.status` blocks suspended/inactive users from dashboards
- **Timezones:** 11-option dropdown list, default `Africa/Lagos`
- **Role labels:** Friendly labels in UI; slug values stored in DB

---

## Next Steps

1. cPanel: run `git pull && php artisan optimize:clear && php artisan permission:cache-reset`
2. Test: create a Talent user in Filament, log in, confirm `/talent/dashboard` loads
3. Test: profile editing, password change, account suspension flow
4. Test: role badges show friendly names in Filament user list
5. Get Phase 1 approval
6. Begin Phase 2: People and Organizations
