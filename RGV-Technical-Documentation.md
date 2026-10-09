# RGV Technical Documentation

> **RGV Multi-Tech Services** — Business Operations Management System
>
> > Version: 1.6 | Framework: Laravel 12 | PHP: ^8.2 | **Last Updated: October 2, 2026**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Authentication & Authorization](#authentication--authorization)
5. [API Reference](#api-reference)
  - [Mobile Employee API](#mobile-employee-api)
6. [Web Routes Reference](#web-routes-reference)
7. [Middleware](#middleware)
8. [Services](#services)
9. [Notifications](#notifications)
10. [AI Integration](#ai-integration)
11. [Security](#security)
12. [Configuration](#configuration)

---

## Architecture Overview

The RGV System follows the **Laravel MVC** pattern with additional service and repository layers. The current implementation includes a public booking layer, a web admin and employee experience, and a dedicated employee mobile API consumed by the Android app under `mobile/`.

```
┌──────────────────────────────────────────────────┐
│                  HTTP Request                      │
└────────────┬─────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────┐
│  Global Middleware (SecurityHeaders, ForceHttps,  │
│  LogPageVisit)                                     │
└────────────┬─────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────┐
│  Route Middleware (auth, admin, employee, mfa,    │
│  api.rate, verified)                               │
└────────────┬─────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────┐
│  Controller (Admin / Employee / Api / Public)     │
└────────────┬─────────────────────────────────────┘
             ▼
┌─────────────┐  ┌───────────────┐  ┌──────────────┐
│  Service    │  │  Repository   │  │  Model        │
│  Layer      │  │  Layer        │  │  (Eloquent)   │
└─────────────┘  └───────────────┘  └──────┬────────┘
                                           ▼
                                   ┌────────────────┐
                                   │  Supabase      │
                                   │  PostgreSQL    │
                                   └────────────────┘
```

**Role Hierarchy:**
- **Admin** — Full system access, user management, reports, configurations, and AI tooling
- **Employee** — Assigned bookings through web; borrow requests and inventory lookup through web and Android app
- **Public/Unauthenticated** — Booking form submission and booking tracking; the AI chatbot is restricted to authenticated admin sessions

---

## Technology Stack

### Backend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.0 |
| Language | PHP | ^8.2 |
| Database | Supabase PostgreSQL (session pooler or direct host) | — |
| Auth Scaffolding | Laravel Breeze | ^2.4 |
| Mobile Auth | Supabase Auth (JWT + TOTP MFA) | — |
| RBAC | Spatie Laravel-Permission | * |
| Activity Logging | Spatie Laravel-Activitylog | ^4.12 |
| Backups | Spatie Laravel-Backup | * |
| PDF Generation | Barryvdh Laravel-DOMPDF | * |
| Excel | Maatwebsite Laravel-Excel | * |
| QR Codes | SimpleSoftwareIO Simple-QRCode | ^4.2 |
| AI/LLM | OpenAI-PHP Client | * |
| HTTP Client | Guzzle | * |

### Frontend

| Component | Technology | Version |
|-----------|-----------|---------|
| Build Tool | Vite | ^7.0.7 |
| CSS Framework | TailwindCSS | ^3.1.0 |
| JS Framework | Alpine.js | ^3.4.2 |
| HTTP Client | Axios | ^1.11.0 |

### Employee Mobile

| Component | Technology |
|-----------|-----------|
| App | Flutter (Android) |
| QR scanning | `mobile_scanner` |
| API client | Dart `http` with Sanctum bearer tokens |
| Token storage | `flutter_secure_storage` |

### AI Services

| Service | Model | Usage |
|---------|-------|-------|
| OpenAI | GPT (via openai-php) | Chatbot, AI reports |
| Ollama (Local) | llama3.1 | Primary reports chatbot provider |
| Google Gemini | gemini-2.0-flash | Reports chatbot fallback provider |

---

## Directory Structure

```
rgv-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Api/
│   │   │   │   └── Mobile/       (employee API)
│   │   │   ├── Auth/             (Breeze)
│   │   │   ├── Employee/
│   │   │   └── [root]
│   │   └── Middleware/           (web and API middleware)
│   ├── Models/                   (Eloquent models)
│   ├── Services/                 (business and integration services)
│   ├── Notifications/            (notification classes)
│   ├── Repositories/             (data access layer)
│   ├── Jobs/                     (queued jobs)
│   ├── Mail/                     (email classes)
│   ├── Exports/                  (Excel export classes)
│   ├── Imports/                  (Excel import classes)
│   ├── Observers/                (model observers)
│   └── Traits/                   (shared traits)
├── bootstrap/                    (app bootstrap)
├── config/                       (application and integration config)
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/                  (database seeders)
├── mobile/                       (Flutter Android employee app)
├── public/                       (web root - index.php, assets)
├── resources/
│   ├── views/                    (Blade templates)
│   │   ├── admin/                (admin panel views)
│   │   ├── employee/             (employee panel views)
│   │   ├── auth/                 (authentication views)
│   │   ├── layouts/              (shared layouts)
│   │   ├── components/           (Blade components)
│   │   ├── pdf/                  (PDF templates)
│   │   └── mail/                 (email templates)
│   ├── css/                      (CSS source)
│   └── js/                       (JavaScript source)
├── routes/
│   ├── web.php                   (main web routes)
│   ├── api.php                   (public and /v1/mobile API routes)
│   ├── auth.php                  (Breeze auth routes)
│   └── console.php               (Artisan commands)
├── storage/                      (logs, cache, uploads)
├── tests/                        (Feature + Unit tests)
└── vendor/                       (Composer dependencies)
```

---

## Authentication & Authorization

### Auth Flow

1. User registers via `/register` or admin creates user
2. Login at `/login` with email/password
3. Email verification required before accessing `/dashboard`
4. Optional MFA (TOTP or email-based) after password confirmation
5. Role-based redirect: Admin → `/admin/dashboard`, Employee → `/employee/dashboard`

The Android app authenticates directly against Supabase Auth with
email/password (plus TOTP MFA at AAL2 where required) and calls the
restricted PostgreSQL RPCs provisioned by
`database/supabase/mobile_backend.sql`. It does not use Laravel
sessions or Sanctum tokens, and it keeps working while the Laravel
process is unavailable. Access requires an active employee account
linked to a confirmed Supabase identity.

### Roles & Permissions (Spatie)

| Role | Guard | Description |
|------|-------|-------------|
| Admin | web | Full system access, can impersonate users |
| Employee | web | Assigned bookings on web; active employees can use the mobile borrow, inventory, return, and account APIs |

Permissions are managed via Spatie Laravel-Permission package with 5 tables:
- `permissions` — permission definitions
- `roles` — role definitions
- `model_has_permissions` — direct user-to-permission mapping
- `model_has_roles` — user-to-role mapping
- `role_has_permissions` — role-to-permission mapping

### MFA (Multi-Factor Authentication)

- TOTP-based (time-based one-time passwords)
- Email-based fallback codes
- 8 recovery codes stored as JSON
- MFA verification required before accessing protected areas

### Impersonation

Admins can impersonate other users via `POST /admin/users/{user}/impersonate` and stop via `POST /admin/impersonation/stop`. All impersonation sessions are logged in `login_history` with `is_impersonation = true`.

---

## API Reference

### Mobile Employee API

The Flutter client connects directly to Supabase Auth and restricted PostgreSQL
RPCs; it does not depend on the Laravel process. The native return workflow
records condition and location but does not upload photos. The legacy Laravel
mobile API remains available to external clients. See [RGV-Mobile-API.md](RGV-Mobile-API.md)
for its endpoint and payload contract.

### Public API

**Base URL:** `/api`

All responses are automatically converted to camelCase via `CamelCaseJsonResponse` middleware.

#### Rate Limiting

Rate limits are configurable via Site Settings (API tab) and stored as environment variables. The `TieredRateLimitMiddleware` reads from `API_PUBLIC_RATE_LIMIT`, `API_STANDARD_RATE_LIMIT`, `API_PREMIUM_RATE_LIMIT`, and `API_ADMIN_RATE_LIMIT` env vars.

| Tier | Default (req/min) | Env Variable |
|------|-------------------|-------------|
| public | 30 | `API_PUBLIC_RATE_LIMIT` |
| standard | 60 | `API_STANDARD_RATE_LIMIT` |
| premium | 300 | `API_PREMIUM_RATE_LIMIT` |
| admin | 1000 | `API_ADMIN_RATE_LIMIT` |

---

### Public Inventory API

#### `GET /api/inventory`

List inventory items.

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer | Filter by category ID |
| `status` | string | Filter by status (available/borrowed/maintenance/damaged) |
| `search` | string | Search by name or item code |

---

#### `GET /api/inventory/{inventory}`

Get a single inventory item.

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "itemCode": "INV-001",
    "name": "Laptop",
    "description": "Dell XPS 15",
    "quantity": 10,
    "unit": "pcs",
    "unitCost": 1500.00,
    "status": "available",
    "condition": "good",
    "location": "Storage Room A",
    "category": { "id": 1, "name": "Electronics" }
  }
}
```

---

### AI Chatbot API

#### `POST /api/chatbot/query`

Send a message to the AI chatbot.

**Request Body:**
```json
{
  "message": "How do I book a service?",
  "context": "booking"
}
```

**Response (200):**
```json
{
  "reply": "To book a service, visit our booking page at /booking and fill out the form..."
}
```

---

## Web Routes Reference

### Public Routes (No Auth Required)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Homepage |
| GET | `/booking` | Booking form |
| POST | `/booking` | Submit booking |
| GET | `/booking/track/{reference}` | Track booking by reference number |
| POST | `/booking/search` | Search booking by reference |
| POST | `/api/chatbot/query` | AI chatbot |

### Auth Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/register` | Registration form |
| POST | `/register` | Create account |
| GET | `/login` | Login form |
| POST | `/login` | Authenticate |
| POST | `/logout` | Logout |
| GET | `/forgot-password` | Password reset request |
| GET | `/reset-password/{token}` | Password reset form |
| GET | `/verify-email` | Email verification notice |
| GET | `/mfa/verify` | MFA verification form |
| POST | `/mfa/verify` | Verify MFA code |
| POST | `/mfa/resend` | Resend MFA code |

### Admin Routes (`/admin/*`)

Full CRUD for: Bookings, Inventory, Users, Borrow Requests, Reports, Notifications, Audit Logs, Backups, Trash Management, AI Insights, Import/Export, PDF Generation, Impersonation.

See detailed listing in the database schema document.

### Employee Routes (`/employee/*`)

Dashboard, assigned bookings, borrow requests, inventory viewing, notifications.

---

## Middleware

### Global Middleware

| Middleware | Description |
|-----------|-------------|
| `SecurityHeaders` | CSP, HSTS, X-Frame-Options, X-Content-Type-Options |
| `ForceHttps` | Redirect to HTTPS in production |
| `LogPageVisit` | Logs GET page visits to `audit_logs` |

### Route Middleware

| Alias | Class | Description |
|-------|-------|-------------|
| `admin` | `IsAdmin` | Checks `auth()->user()->isAdmin()`, returns 403 |
| `employee` | `IsEmployee` | Checks `auth()->user()->isEmployee()`, returns 403 |
| `mfa` | `RequireMfa` | Redirects to MFA verify if MFA enabled & not verified |
| `api.rate` | `TieredRateLimitMiddleware` | Tiered rate limiting by user tier |
| `camel.json` | `CamelCaseJsonResponse` | Converts JSON keys to camelCase |
| `auth:sanctum` | Laravel Sanctum | Authenticates employee mobile bearer tokens |
| `active.employee` | `EnsureActiveEmployee` | Requires an active account with the `employee` role |
| `throttle` | Laravel throttle | Limits mobile login and authenticated API requests |

---

## Services

| Service | Purpose |
|---------|---------|
| `AuditLogger` | Centralized audit trail logging with tamper-evident HMAC-SHA256 checksums |
| `GeminiService` | Google Gemini AI API integration |
| `OllamaService` | Local Ollama LLM integration (llama3.1) |
| `SystemMetricsService` | System performance and usage metrics |
| `BorrowService` | Borrow reservations and stock transitions for approval, cancellation, and return |

---

## Notification System

### Notification Bell (Real-time)

The notification bell component (`resources/views/components/notification-bell.blade.php`) provides:
- **Auto-polling** — Fetches unread count every 30 seconds via `GET /admin/notifications/unread-count`
- **Auto-mark-as-read** — Clicking any notification in the dropdown marks it as read before navigating to the linked resource
- **Mark all as read** — One-click to clear all unread notifications
- **Badge count** — Red badge showing current unread count

### Notification Classes

13 notification classes handle email and in-app notifications via the `RespectsPreferences` trait:

| Notification | Trigger |
|-------------|---------|
| BookingApproved | Admin approves a booking |
| BookingRejected | Admin rejects a booking |
| BookingCompleted | Booking marked as completed |
| BookingCancelled | Booking cancelled |
| BookingAssigned | Employee assigned to booking |
| NewBookingReceived | New booking submitted (→ all admins) |
| BorrowRequestApproved | Admin approves borrow request |
| BorrowRequestRejected | Admin rejects borrow request |
| BorrowRequestBorrowed | Items marked as borrowed |
| BorrowRequestReturned | All items returned |
| NewBorrowRequestReceived | New borrow request submitted (→ all admins) |
| LowStockAlert | Inventory first enters low-stock state, including after a stock reservation |
| UserStatusChanged | User activated/deactivated |

Borrow-request creation alerts are sent to admins only after the creating transaction commits. Employees are notified after approval, rejection, marking items borrowed, and the first completion of a full return. Low-stock alerts are sent once when quantity crosses from above the configured threshold to at or below it; later edits while stock remains low do not repeat the alert.

### Entity Update Email Notifications

Model observers automatically send email notifications to the configured admin email (`ADMIN_NOTIFICATION_EMAIL`, `BACKUP_NOTIFICATION_EMAIL`, or `SYSTEM_NOTIFICATION_EMAIL`) when entities are modified:

| Observer | Events |
|----------|--------|
| `BookingObserver` | Status changes, employee assignment |
| `InventoryObserver` | Created, meaningful updates, deleted; quantity-only stock movements are excluded |
| `BorrowRequestObserver` | Status changes |

Queued entity-update emails are released after the database transaction commits. Email notifications are gated by `NOTIFICATION_EMAIL_ENABLED` (configurable in Site Settings → Notifications tab); this does not disable in-app notifications.

### RespectsPreferences Trait

The `App\Notifications\RespectsPreferences` trait determines delivery channels based on user notification preferences. If no preference exists, both `mail` and `database` channels are enabled by default. Guest booking updates use email only. Users can toggle per-type preferences at `/profile/notification-preferences`; opening a bell notification marks it read before redirecting, and mark-all-read updates the inbox for both roles.

---

## AI Integration

The admin reports chatbot retrieves database aggregates server-side and uses
Ollama as its primary provider, Gemini as fallback, then a deterministic
database-backed response.

### AI Features

| Endpoint | Description |
|----------|-------------|
| `POST /api/chatbot/query` | Admin reports assistant (database-grounded) |
| `GET /admin/ai/insights` | AI-generated business insights |
| `GET /admin/ai/forecast` | AI booking forecast |
| `GET /admin/ai/inventory-recommendations` | AI inventory recommendations |

---

## Security

### Security Headers

- **Content-Security-Policy** — Restricts resource loading sources
- **X-Frame-Options** — `DENY` (prevents clickjacking)
- **X-Content-Type-Options** — `nosniff`
- **Strict-Transport-Security** — HSTS in production
- **Referrer-Policy** — `strict-origin-when-cross-origin`

### Audit Trail

- **Spatie ActivityLog** — Automatic model event logging
- **Custom `audit_logs`** — Tamper-evident audit chain using HMAC-SHA256 checksums linking sequential entries
- Each audit log includes: user, event, old values, new values, IP, user agent, URL, checksum

### Data Protection

- Passwords hashed with bcrypt
- MFA with TOTP + recovery codes
- Optimistic locking (`lock_version`) on critical tables
- Soft deletes with trash management and restore capability
- Force logout capability for admins
- Rate limiting on all API endpoints

---

## Configuration

### Key Environment Variables

```
APP_NAME="RGV Multi-Tech Services"
APP_ENV=local
APP_URL=http://rgv-system.test

DB_CONNECTION=sqlite

GEMINI_API_KEY=
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.1
```

For mobile production deployments, see [RGV-Mobile-API.md](RGV-Mobile-API.md)
for database and Sanctum settings. Keep AI provider keys on the Laravel server
and out of source control.

### Config Files

| File | Purpose |
|------|---------|
| `app.php` | Application settings, timezone, locale |
| `auth.php` | Auth guards, password reset settings |
| `backup.php` | Spatie backup configuration |
| `cache.php` | Cache driver configuration |
| `database.php` | Database connections |
| `mail.php` | Email driver settings |
| `permission.php` | Spatie permission models and settings |
| `services.php` | Third-party service settings for OpenAI, Gemini, and Ollama |
