# GVOS — AI Instructions for Claude Code

## Purpose
This file gives Claude Code (and any other AI assistant used on this project) the context and rules it needs to work safely and consistently on GVOS.

**Always read this file at the start of a new session before making any changes.**

---

## Product Identity

- **Product name:** GVOS (not "GetVirtual Workspace", not "GetVirtual Platform")
- **Company:** GetVirtual
- **Portals:**
  - Admin: GVOS Ops Console
  - Client: GVOS Client Portal
  - Talent: GVOS Talent Portal
  - Manager: GVOS Manager Console

**Never rename the product or portals without explicit instruction.**

---

## Stack — Always Use

| Layer | Technology |
|-------|-----------|
| Backend | Laravel (PHP 8.2+) |
| Admin panel | Filament 3.x |
| Frontend portals | Inertia + React + Tailwind CSS |
| Authorization | Spatie Laravel Permission + Laravel Policies |
| Database | MySQL or PostgreSQL |
| Auth scaffolding | Laravel Breeze (React) |

**Never introduce other frameworks, ORMs, or UI libraries without approval. Do not use Antigravity or Codex.**

---

## UI Design Rules

- Stitch UI export is the **source of truth** for all visual design
- Do not redesign screens without explicit approval
- Implement Stitch screens as-is, adapting only what is necessary for the framework
- Reference screens are in `/design-reference/screens/`
- The UI must feel: premium, clean, secure, workspace-centered, role-aware

---

## Phase Rules

- **Only build within the current approved phase**
- Check `CURRENT_STATUS.md` for the current phase
- Do not build Phase N+1 features in Phase N
- Each phase has a checklist in `BUILD_PHASES.md` — complete all items
- Do not create placeholder stubs for future modules unless explicitly asked

---

## Code Quality Rules

1. Follow Laravel conventions (naming, structure, PSR-4 autoloading)
2. Use Eloquent relationships, not raw SQL
3. Use Form Requests for validation
4. Use Laravel Policies for authorization (not just middleware)
5. Use Route model binding wherever applicable
6. Always scope queries by workspace or user — never return unfiltered data
7. No hardcoded secrets — all config via `.env`
8. All seeders must be idempotent (safe to run multiple times)
9. All migrations must be reversible (include `down()` method)
10. Write clean, readable code — prefer clarity over cleverness

---

## Security Rules

1. Default deny on all resources
2. Every sensitive action must write to audit_logs
3. Vault fields encrypted using `encrypt()`/`decrypt()`
4. Files stored in private disk (not `public/`)
5. CSRF enabled on all non-API routes
6. Rate limiting on auth routes
7. Never return data the current user is not authorized to see

---

## Git Rules

1. Commit after each phase milestone (not every file change)
2. Use descriptive commit messages: `Phase N: Feature description`
3. Never commit `.env` — only `.env.example`
4. Never commit `vendor/` or `node_modules/`
5. Create feature branches for each phase (optional but recommended)

---

## Documentation Rules

1. Update `CURRENT_STATUS.md` after every significant change
2. Update `IMPLEMENTATION_LOG.md` with each phase completion
3. Update `KNOWN_ISSUES.md` when issues are discovered
4. Update `TESTING_CHECKLIST.md` as tests are run
5. Keep documentation truthful — do not leave stale information

---

## Reporting Rules

When asked to complete a task or phase, always provide a structured report:
1. What was created/modified (file list)
2. Any commands the user needs to run
3. Any issues or warnings
4. Recommended next step

---

## What to Check Before Starting Any Session

1. Read `CURRENT_STATUS.md` to understand where the project is
2. Read `BUILD_PHASES.md` to understand what phase is active
3. Check `KNOWN_ISSUES.md` for anything that needs attention
4. Read the relevant phase section of `BUILD_PHASES.md`
5. Review the Stitch screen for the UI you are about to implement

---

## When in Doubt

- Do not assume. Ask the product owner for clarification.
- Do not build beyond the current phase.
- Do not introduce packages that are not in the approved stack.
- Do not modify the Stitch UI direction without approval.
- Check `PRD.md` and `PERMISSION_MATRIX.md` for business rules.
