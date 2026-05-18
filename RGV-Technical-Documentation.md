# RGV Technical Documentation

> **RGV Multi-Tech Services** — Business Operations Management System
>
> > Version: 1.5 | Framework: Laravel 12.0 | PHP: ^8.2 | **Last Updated: May 18, 2026**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Authentication & Authorization](#authentication--authorization)
5. [API Reference](#api-reference)
6. [Web Routes Reference](#web-routes-reference)
7. [Middleware](#middleware)
8. [Services](#services)
9. [Notifications](#notifications)
10. [AI Integration](#ai-integration)
11. [Security](#security)
12. [Configuration](#configuration)

---

## Architecture Overview

The RGV System follows the **Laravel MVC** pattern with additional service and repository layers:

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
                                  │   SQLite / DB   │
                                  └────────────────┘
```

**Role Hierarchy:**
- **Admin** — Full system access, user management, reports, configurations
- **Employee** — Assigned bookings, borrow requests, inventory viewing
- **Public/Unauthenticated** — Booking form submission, booking tracking, chatbot

---

## Technology Stack

### Backend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.0 |
| Language | PHP | ^8.2 |
| Database | SQLite (dev) | — |
| Auth Scaffolding | Laravel Breeze | ^2.4 |
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

### AI Services

| Service | Model | Usage |
|---------|-------|-------|
| OpenAI | GPT (via openai-php) | Chatbot, AI reports |
| Google Gemini | gemini-pro | Conversational AI (`/ask-gemini`) |
| Ollama (Local) | llama3.1 | Local AI inference |

---

## Directory Structure

```
rgv-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            (12 controllers)
│   │   │   ├── Api/              (2 controllers)
│   │   │   ├── Auth/             (10 controllers - Breeze)
│   │   │   ├── Employee/         (5 controllers)
│   │   │   └── [root]            (7 controllers)
│   │   └── Middleware/           (6 middleware classes)
│   ├── Models/                   (21 Eloquent models)
│   ├── Services/                 (5 service classes)
│   ├── Notifications/            (12 notification classes)
│   ├── Repositories/             (data access layer)
│   ├── Jobs/                     (queued jobs)
│   ├── Mail/                     (email classes)
│   ├── Exports/                  (Excel export classes)
│   ├── Imports/                  (Excel import classes)
│   ├── Observers/                (model observers)
│   └── Traits/                   (shared traits)
├── bootstrap/                    (app bootstrap)
├── config/                       (14 config files)
├── database/
│   ├── migrations/               (24 migration files)
│   ├── factories/                (6 model factories)
│   └── seeders/                  (database seeders)
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
│   ├── api.php                   (public API routes)
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

### Roles & Permissions (Spatie)

| Role | Guard | Description |
|------|-------|-------------|
| Admin | web | Full system access, can impersonate users |
| Employee | web | Assigned bookings, borrow requests, inventory viewing |

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

### Public Books API

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

#### `GET /api/books`

List all books with optional search and filtering.

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `search` | string | — | Full-text search across title, author, description |
| `category` | integer | — | Filter by category ID |
| `sort` | string | `created_at` | Sort field |
| `order` | string | `desc` | Sort direction (`asc`/`desc`) |
| `per_page` | integer | 15 | Items per page |
| `page` | integer | 1 | Page number |

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "isbn": "978-0-123456-78-9",
      "isbn13": "9780123456789",
      "title": "Book Title",
      "slug": "book-title",
      "author": "Author Name",
      "publisher": "Publisher",
      "format": "paperback",
      "price": 29.99,
      "stock": 50,
      "salesCount": 120,
      "rating": 4.5,
      "description": "Book description...",
      "category": {
        "id": 1,
        "name": "Fiction",
        "slug": "fiction"
      }
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 5,
    "perPage": 15,
    "total": 72
  }
}
```

---

#### `GET /api/books/{book}`

Get a single book by ID.

**Response (200):** Single book resource (same structure as list item).

---

#### `GET /api/books/isbn/{isbn}`

Lookup a book by ISBN (10 or 13 digit).

**Parameters:**

| Parameter | Location | Type | Description |
|-----------|----------|------|-------------|
| `isbn` | path | string | ISBN-10 (17 chars with hyphens) or ISBN-13 (13 chars) |

**Response (200):** Single book resource or 404.

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
    "category": { "id": 1, "name": "Electronics" },
    "supplier": { "id": 2, "name": "Tech Supplier Inc." }
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

#### `POST /ask-gemini`

Send a query to Google Gemini AI.

**Request Body:**
```json
{
  "prompt": "Analyze monthly sales trends",
  "data": {}
}
```

**Response (200):**
```json
{
  "response": "Based on the data, sales have increased 15% month-over-month..."
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
| POST | `/ask-gemini` | Gemini AI query |

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

---

## Services

| Service | Purpose |
|---------|---------|
| `AuditLogger` | Centralized audit trail logging with tamper-evident HMAC-SHA256 checksums |
| `CacheService` | Application-level caching abstraction |
| `GeminiService` | Google Gemini AI API integration |
| `OllamaService` | Local Ollama LLM integration (llama3.1) |
| `SystemMetricsService` | System performance and usage metrics |

---

## Notification System

### Notification Bell (Real-time)

The notification bell component (`resources/views/components/notification-bell.blade.php`) provides:
- **Auto-polling** — Fetches unread count every 30 seconds via `GET /admin/notifications/unread-count`
- **Auto-mark-as-read** — Clicking any notification in the dropdown marks it as read before navigating to the linked resource
- **Mark all as read** — One-click to clear all unread notifications
- **Badge count** — Red badge showing current unread count

### Notification Classes

14 notification classes handle both email and in-app notifications via the `RespectsPreferences` trait:

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
| LowStockAlert | Inventory below threshold |
| UserStatusChanged | User activated/deactivated |

### Entity Update Email Notifications

Model observers automatically send email notifications to the configured admin email (`ADMIN_NOTIFICATION_EMAIL` or `BACKUP_NOTIFICATION_EMAIL`) when entities are modified:

| Observer | Events |
|----------|--------|
| `BookingObserver` | Status changes, employee assignment |
| `InventoryObserver` | Created, updated, deleted |
| `BorrowRequestObserver` | Status changes |

Email sending is gated by `NOTIFICATION_EMAIL_ENABLED` env var (configurable in Site Settings → Notifications tab).

### RespectsPreferences Trait

The `App\Notifications\RespectsPreferences` trait determines delivery channels based on user notification preferences. If no preference exists, both `mail` and `database` channels are enabled by default. Users can toggle per-type preferences at `/profile/notification-preferences`.

---

## AI Integration

The system integrates with three AI backends:

1. **OpenAI** (via `openai-php/client`) — Primary chatbot engine, AI-powered report generation
2. **Google Gemini** (via `GeminiService`) — Alternative conversational AI at `/ask-gemini`
3. **Ollama** (local, llama3.1) — Local inference at `http://127.0.0.1:11434`

### AI Features

| Endpoint | Description |
|----------|-------------|
| `POST /api/chatbot/query` | Public AI chatbot for customer inquiries |
| `POST /ask-gemini` | Gemini-powered Q&A |
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

OPENAI_API_KEY=
GEMINI_API_KEY=
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.1
```

### Config Files

| File | Purpose |
|------|---------|
| `app.php` | Application settings, timezone, locale |
| `auth.php` | Auth guards, password reset settings |
| `backup.php` | Spatie backup configuration |
| `books.php` | Book catalog performance settings (sharding, caching) |
| `cache.php` | Cache driver configuration |
| `database.php` | Database connections, shard configuration |
| `mail.php` | Email driver settings |
| `permission.php` | Spatie permission models and settings |
| `scout.php` | Search indexing configuration |
| `services.php` | Third-party service keys (OpenAI, Gemini, Ollama) |
