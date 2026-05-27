# GVOS — Current Status

**Last Updated:** 2026-05-27
**Current Phase:** Phase 0 — Documentation and Project Setup

---

## Phase 0 Status

### Completed
- [x] Project directory created
- [x] /docs folder created with 16 documentation files
- [x] /design-reference folder created with Stitch UI screens
- [x] .gitignore created
- [x] composer.json created (dependencies declared)
- [x] package.json created (JS dependencies declared)
- [x] .env.example created
- [x] Custom Laravel source files created:
  - app/Models/User.php
  - database/seeders/RoleSeeder.php
  - database/seeders/AdminUserSeeder.php
  - database/seeders/DatabaseSeeder.php
  - routes/web.php
  - app/Http/Controllers/DashboardController.php
  - resources/js/Pages/Dashboard/* (8 role dashboards)
  - resources/js/app.jsx
  - resources/js/Layouts/AppLayout.jsx
- [x] setup-gvos.ps1 created (full installation script)
- [x] Git initialized
- [x] First commit made

### Pending (requires PHP/Composer/Node.js on this machine)
- [ ] `composer install` — installs Laravel framework and all PHP dependencies
- [ ] `npm install` — installs React, Inertia, Tailwind and JS dependencies
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate`
- [ ] `php artisan db:seed`
- [ ] `npm run build` or `npm run dev`
- [ ] Confirm Filament admin panel accessible
- [ ] Confirm seeded admin can login

---

## Setup Prerequisites

Before running the project, install the following on your machine:

| Tool | Version | Download |
|------|---------|----------|
| PHP | 8.2 or 8.3 | https://windows.php.net/ or via Laragon/Herd |
| Composer | Latest | https://getcomposer.org/ |
| Node.js | 18 LTS or 20 LTS | https://nodejs.org/ |
| MySQL or PostgreSQL | 8.0+ / 15+ | Via Laragon or XAMPP |

**Recommended:** Use [Laragon](https://laragon.org/) on Windows — it bundles PHP, MySQL, and provides a clean dev environment.

---

## Admin Credentials (Local Testing Only)

> ⚠️ These are placeholder credentials for local development only. Never use these in production.

| Field | Value |
|-------|-------|
| Email | admin@gvos.local |
| Password | password |
| Role | super_admin |
| Portal | GVOS Ops Console (Filament) at /admin |

---

## How to Run Phase 0

After installing PHP, Composer, and Node.js:

```powershell
# Option A: Run the full setup script
.\setup-gvos.ps1

# Option B: Manual steps
composer install
cp .env.example .env
php artisan key:generate
# Edit .env to set your DB credentials
php artisan migrate
php artisan db:seed
npm install
npm run dev
```

Then visit:
- http://localhost:8000 — Laravel app
- http://localhost:8000/admin — Filament Ops Console
- http://localhost:5173 — Vite dev server (when using npm run dev)

---

## Environment Notes

- Database: Configure DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in `.env`
- Recommended local DB name: `gvos`
- Local URL: http://localhost:8000 (or configured Laragon domain)

---

## Next Steps

1. Install PHP 8.2+, Composer, Node.js 18+ on development machine
2. Install and configure MySQL (or PostgreSQL)
3. Run `.\setup-gvos.ps1` or follow manual steps above
4. Verify admin login works at /admin
5. Verify all 8 role dashboard placeholders exist
6. Get Phase 0 approved by product owner
7. Begin Phase 1: Identity and Access Foundation

---

## Team Notes

- Project owner: GetVirtual
- Build tool: Claude Code
- Product architect: ChatGPT (planning, QA guide, build moderator)
- UI design: Stitch
- Do not use Antigravity or Codex in this project
