# GVOS — Known Issues

## Format
Each issue: Date | Phase | Severity | Description | Status | Resolution

Severity levels: Critical | High | Medium | Low | Info

---

## Open Issues

### 2026-05-27 | Phase 0 | Info | PHP/Composer/Node.js not installed on build machine

**Description:**
PHP 8.2+, Composer, and Node.js are not installed on the development machine. As a result, `composer install`, `php artisan` commands, `npm install`, and the dev server cannot be run from this machine.

**Impact:**
- Cannot run migrations or seeders
- Cannot start Laravel dev server
- Cannot build frontend assets
- Cannot verify login or Filament panel

**Resolution Steps:**
1. Install PHP 8.2+ (recommended: Laragon on Windows, which bundles PHP and MySQL)
2. Install Composer from https://getcomposer.org/
3. Install Node.js 18 LTS from https://nodejs.org/
4. Run `.\setup-gvos.ps1` from the project root

**Status:** Open — waiting for tool installation

---

<!-- Add new issues below this line -->

## Resolved Issues

<!-- Move issues here once resolved, with resolution date and notes -->
