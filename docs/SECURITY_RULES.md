# GVOS — Security Rules

## Overview
GVOS handles sensitive business data, credentials, personal information, and work communications. Security is not optional — it is a core product requirement.

---

## Authentication

### Login
- Email + password authentication (Laravel default)
- Passwords hashed using bcrypt (Laravel default)
- No plain-text passwords stored anywhere
- Rate limiting on login: max 5 attempts per minute per IP
- Lockout after repeated failures (configurable)

### Session
- Laravel session with encrypted session ID
- Session expires after inactivity (configurable, default 120 minutes)
- Force logout on role change or suspension

### Password Policy
- Minimum 8 characters
- Recommended: 12+ characters, mixed case, numbers, symbols
- Passwords never shown in interface after set
- Password reset via email token (time-limited, single-use)

### 2FA
- Out of scope for Phase 1
- Planned for Phase 10 (security hardening)
- All admin accounts should use 2FA when implemented

---

## Authorization

### Default Deny
- All resources default to NO ACCESS
- Access must be explicitly granted via role or permission
- Never rely on front-end hiding alone — always enforce server-side

### Role-Based Access
- Spatie Laravel Permission manages roles
- Laravel Policies enforce per-resource access
- Route middleware: `role:` guards protect all routes
- Filament: panel-level and resource-level guards

### Cross-Tenant Isolation
- Users can ONLY access data belonging to their workspace(s)
- No user can query data from another workspace
- All queries include workspace_id scope where relevant
- Admin-level access is restricted to Filament, never exposed via client portal

---

## Data Protection

### Sensitive Fields
Encrypted at rest using Laravel `encrypt()`/`decrypt()`:
- `vault_credentials.username`
- `vault_credentials.password`
- `vault_credentials.url`
- `vault_credentials.notes`

### Personally Identifiable Information (PII)
- User email addresses
- Phone numbers
- Company addresses
- Stored in standard columns but access is role-gated

### Passwords in Vault
- Vault passwords use AES-256 encryption
- Encryption key stored separately from database
- Consider a dedicated key management approach (Phase 10)

### Files
- Files stored in non-public storage (Laravel `storage/` not `public/`)
- Access via signed temporary URLs
- File names stored in DB; actual files on disk/S3

---

## Activity Monitoring

### User Transparency
- **All users must be informed that activity is tracked and monitored**
- A notice is shown on first login and on the login page
- Platform terms and acknowledgment required on signup/first access

### What is Monitored
- Login and logout timestamps
- All resource access (audit log)
- File uploads and downloads
- Password vault reveals
- Task actions (create, edit, complete, approve, reject)
- Chat activity (manager-visible)
- Clock-in/clock-out events
- Report submissions

### Who Can Monitor
- Super Admin: everything
- Operations Admin: everything except platform settings
- Line Manager: assigned workspace activity only

---

## Audit Logs

### Immutability
- Audit log entries can NEVER be updated or deleted
- No soft deletes on audit_logs table
- Database-level constraints should prevent modification (Phase 10)

### What is Logged
Every sensitive action including:
- User role changes
- Workspace status changes (suspend, activate)
- Billing events (invoice created, payment recorded)
- Vault credential access and reveals
- File uploads/downloads
- Complaint creation and resolution
- Admin access to any user account
- Failed login attempts

### Log Format
```json
{
  "user_id": 1,
  "action": "workspace.suspended",
  "subject_type": "workspace",
  "subject_id": 42,
  "context": {
    "reason": "non_payment",
    "invoice_id": 15
  },
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-01-01T12:00:00Z"
}
```

---

## Platform Bypass Prevention

### Prohibited Activities
- Clients and talents sharing real contact details to communicate off-platform
- Using chat to share credentials outside the vault
- Attempting to access other workspaces' data
- Sharing login credentials with other people

### Sanctions
- First violation: warning
- Repeat violation: account suspension
- Severe violation: permanent suspension and fine
- All violations recorded in audit log

---

## Infrastructure Security (Deployment Phase)

### Environment Variables
- Sensitive config in `.env` (never committed to git)
- APP_KEY must be unique and secret
- Database credentials in `.env`
- Vault encryption key in `.env` (consider separate secret manager)

### CSRF
- Laravel default CSRF protection enabled on all non-API forms
- API routes use Sanctum tokens (Phase 1+)

### SQL Injection
- All database queries via Eloquent ORM or Query Builder
- No raw SQL with user input
- Use parameterized queries if raw SQL is ever necessary

### XSS
- React/Inertia sanitizes output by default
- Never render raw HTML from user input without sanitization
- Filament handles its own sanitization

### Headers
- Content-Security-Policy (Phase 12)
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- HTTPS only in production

---

## Calls (Phase 11)
- No call recording in MVP
- Call rooms embedded via third-party provider
- Call metadata (participants, timestamp, duration) logged
- No screen capture enforced in MVP

---

## Compliance Notes
- GVOS is not certified for GDPR/HIPAA in Phase 0
- Data residency: all data in host country by default
- Right to data deletion: manual in MVP, automated post-MVP
- Privacy policy required before production launch
