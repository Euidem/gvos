# GVOS — API Contract

## Status: Phase 0 Outline — API implemented per phase

## Overview
GVOS is primarily a server-rendered Inertia application. Most data flows happen via Inertia page responses (not JSON APIs). However, a JSON API is required for:
- Real-time features (chat, time tracking)
- Mobile client (future)
- External integrations (future)
- Webhook receivers (payment provider, future)

---

## API Design Principles

1. **REST conventions** — Standard HTTP methods (GET, POST, PUT, PATCH, DELETE)
2. **Consistent response format** — All API responses use a standard envelope
3. **Laravel Sanctum** — Token-based auth for API consumers
4. **Role-scoped** — API respects the same authorization rules as the web app
5. **Versioned** — All API routes prefixed with `/api/v1/`
6. **No over-fetching** — Return only the fields needed for the use case
7. **Validation** — All inputs validated using Laravel Form Requests

---

## Standard Response Format

### Success
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

### Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Authentication Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | /api/v1/auth/login | Login with email/password |
| POST | /api/v1/auth/logout | Logout (invalidate token) |
| POST | /api/v1/auth/password/forgot | Send password reset email |
| POST | /api/v1/auth/password/reset | Reset password with token |

---

## Workspace Endpoints (Phase 4)

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/v1/workspaces | List workspaces for current user |
| GET | /api/v1/workspaces/{id} | Get workspace detail |
| GET | /api/v1/workspaces/{id}/members | List workspace members |

---

## Task Endpoints (Phase 5)

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/v1/workspaces/{id}/tasks | List tasks for workspace |
| POST | /api/v1/workspaces/{id}/tasks | Create task |
| GET | /api/v1/tasks/{id} | Get task detail |
| PATCH | /api/v1/tasks/{id} | Update task |
| DELETE | /api/v1/tasks/{id} | Delete task |
| POST | /api/v1/tasks/{id}/approve | Client approves task |
| POST | /api/v1/tasks/{id}/reject | Client rejects task |
| POST | /api/v1/tasks/{id}/comments | Add comment |

---

## Chat Endpoints (Phase 6)

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/v1/workspaces/{id}/messages | Get messages (paginated) |
| POST | /api/v1/workspaces/{id}/messages | Send message |
| POST | /api/v1/messages/{id}/flag | Flag message (manager) |

---

## Time Tracking Endpoints (Phase 7)

| Method | Path | Description |
|--------|------|-------------|
| POST | /api/v1/workspaces/{id}/time-logs/clock-in | Clock in |
| POST | /api/v1/workspaces/{id}/time-logs/clock-out | Clock out |
| GET | /api/v1/workspaces/{id}/time-logs | Get time logs (role-gated) |
| POST | /api/v1/workspaces/{id}/reports/daily | Submit daily report |

---

## Vault Endpoints (Phase 10)

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/v1/workspaces/{id}/vault | List credentials (masked) |
| POST | /api/v1/workspaces/{id}/vault | Add credential |
| POST | /api/v1/vault/{id}/reveal | Reveal credential (logs action) |
| POST | /api/v1/vault/{id}/grant | Grant access to talent |
| DELETE | /api/v1/vault/{id}/grant/{userId} | Revoke access |

---

## Inertia vs API Decision

| Use Case | Use Inertia | Use API |
|----------|------------|---------|
| Page navigation | ✅ | |
| Form submissions | ✅ | |
| Real-time chat polling | | ✅ |
| Clock in/out | ✅ (Inertia POST) | |
| File upload | ✅ (multipart form) | |
| Mobile app (future) | | ✅ |
| Webhooks | | ✅ |

---

## Notes for Implementation
- All web portal routes use Inertia — no separate JSON response needed
- API routes (`/api/v1/`) are for future mobile and external integrations
- Phase 1 focus: Inertia routes only, no API needed
- Phase 6+ introduces API for real-time chat polling or broadcasting
- Laravel Sanctum configuration is done in Phase 1 but API tokens are Phase 6+
