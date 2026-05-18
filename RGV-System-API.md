# RGV-System-API

> **RGV Multi-Tech Services** — Complete API Reference
>
> Version: 1.5 | Framework: Laravel 12.0 | Last Updated: May 18, 2026

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Rate Limiting](#rate-limiting)
4. [Public API Endpoints](#public-api-endpoints)
   - [GET /api/inventories](#get-apiinventories)
   - [GET /api/books](#get-apibooks)
   - [GET /api/books/search](#get-apibookssearch)
   - [GET /api/books/{isbn}](#get-apibooksisbn)
5. [AI & Chatbot Endpoints](#ai--chatbot-endpoints)
   - [POST /api/chatbot/query](#post-apichatbotquery)
   - [POST /ask-gemini](#post-ask-gemini)
6. [Admin JSON Endpoints](#admin-json-endpoints)
   - [GET /admin/dashboard/stats](#get-admindashboardstats)
   - [GET /admin/notifications/unread-count](#get-adminnotificationsunread-count)
   - [GET /admin/ai/insights](#get-adminaiinsights)
   - [GET /admin/ai/forecast](#get-adminaiforecast)
   - [GET /admin/ai/inventory-recommendations](#get-adminaiinventory-recommendations)
   - [GET /admin/import-export/imports/{importLog}](#get-adminimport-exportimportsimportlog)
   - [GET /admin/import-export/{type}/export](#get-adminimport-exporttypeexport)
7. [Employee JSON Endpoints](#employee-json-endpoints)
   - [GET /employee/notifications/unread-count](#get-employeenotificationsunread-count)
8. [Profile & Session Endpoints](#profile--session-endpoints)
   - [GET /profile/export-personal-data](#get-profileexport-personal-data)
   - [POST /session/extend](#post-sessionextend)
9. [Auth Endpoints](#auth-endpoints)
10. [Middleware Reference](#middleware-reference)
11. [Error Responses](#error-responses)
12. [Response Headers](#response-headers)

---

## Overview

The RGV System exposes 19+ JSON endpoints across three categories:

| Category | Count | Base Path | Auth Required |
|----------|-------|-----------|:---:|
| **Public REST API** | 4 | `/api/` | No |
| **AI & Chatbot** | 2 | `/api/chatbot`, `/ask-gemini` | No |
| **Admin JSON** | 7 | `/admin/` | Admin |
| **Employee JSON** | 1 | `/employee/` | Employee + MFA |
| **Profile & Session** | 2 | `/profile/`, `/session/` | Auth |
| **Auth** | 18 | `/register`, `/login`, `/logout`, `/mfa/verify`, etc. | Varies |

### Common Behaviors

- **Public API routes** (`/api/*`) use `camel.json` middleware — all response keys are automatically converted from `snake_case` to `camelCase`.
- **Web routes** returning JSON (`/admin/*`, `/employee/*`) use `snake_case` keys.
- **Cursor pagination** is used for list endpoints (no offset-based pages).
- **ETag caching** is supported on inventory and book list endpoints.
- **Rate limiting** applies to all `/api/*` routes via the tiered rate limiter.

---

## Authentication

Most endpoints that return JSON use **Laravel session-based authentication** (cookie `XSRF-TOKEN`). Public API routes under `/api/*` are unauthenticated.

#### CSRF Protection

For POST/PUT/DELETE requests to session-authenticated endpoints, include the CSRF token:

```http
X-CSRF-TOKEN: {token}
```

Or include it in the request body as `_token`.

#### Bearer Token (Future)

API token authentication (Sanctum) is not currently implemented. All authenticated API access uses session cookies.

---

## Rate Limiting

The `TieredRateLimitMiddleware` enforces per-minute request limits based on the user's tier:

| Tier | Default Limit | Env Variable |
|------|:---:|---|
| `public` | 30 req/min | `API_PUBLIC_RATE_LIMIT` |
| `standard` | 60 req/min | `API_STANDARD_RATE_LIMIT` |
| `premium` | 300 req/min | `API_PREMIUM_RATE_LIMIT` |
| `admin` | 1000 req/min | `API_ADMIN_RATE_LIMIT` |

**Rate limit headers** are included on every response:
```http
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 25
```

**Rate limit exceeded response (429):**
```json
{
  "message": "Too many requests.",
  "tier": "public",
  "retryAfter": 60
}
```
```http
Retry-After: 60
```

Rate limit values are configurable in **Admin → Site Settings → API**.

---

## Public API Endpoints

### `GET /api/inventories`

List inventory items with filtering, cursor pagination, and ETag caching.

**Auth:** None  
**Rate Limit:** 30 req/min (public tier)  
**Middleware:** `api.rate:public`, `camel.json`

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `fields` | string | all fields | Comma-separated field whitelist (see Field Reference) |
| `status` | string | — | Filter by status: `available`, `borrowed`, `maintenance`, `damaged` |
| `category_id` | int | — | Filter by inventory category ID |
| `per_page` | int | 20 | Items per page (cursor pagination) |

#### Field Reference

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | Primary key |
| `item_code` | string | Unique item identifier |
| `name` | string | Item name |
| `description` | string | Item description |
| `quantity` | int | Current stock quantity |
| `unit_cost` | string | Cost per unit (decimal string) |
| `status` | string | available/borrowed/maintenance/damaged |
| `condition` | string | new/good/fair/poor |
| `category_id` | int | Foreign key to category |

#### Response Headers

```http
ETag: "abc123..."
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 25
```

Supports `If-None-Match` → returns **304 Not Modified** if data hasn't changed.

#### Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "itemCode": "INV-001",
      "name": "Laptop Dell XPS 15",
      "description": "15-inch laptop for development",
      "quantity": 10,
      "unitCost": "1500.00",
      "status": "available",
      "condition": "good",
      "categoryId": 2,
      "createdAt": "2026-05-10T08:00:00.000000Z",
      "category": {
        "id": 2,
        "name": "Electronics",
        "slug": "electronics"
      }
    }
  ],
  "nextCursor": "eyJpZCI6MjB9",
  "prevCursor": null,
  "perPage": 20
}
```

---

### `GET /api/books`

List books with filtering and cursor pagination. Returns `BookResource` collection.

**Auth:** None  
**Rate Limit:** 30 req/min (public tier)

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `category_id` | int | — | Filter by category ID |
| `min_price` | numeric | — | Minimum price |
| `max_price` | numeric | — | Maximum price |
| `format` | string | — | Filter by format: `paperback`, `hardcover`, `ebook` |
| `per_page` | int | 50 | Items per page |

#### Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "isbn13": "9780123456789",
      "title": "The Great Adventure",
      "author": "Jane Smith",
      "format": "hardcover",
      "price": "29.99",
      "stock": 42,
      "rating": 4.5,
      "category": {
        "id": 3,
        "name": "Fiction",
        "slug": "fiction"
      }
    }
  ],
  "nextCursor": "...",
  "prevCursor": null,
  "perPage": 50
}
```

---

### `GET /api/books/search`

Full-text search across books by title, author, and description.

**Auth:** None  
**Rate Limit:** 30 req/min (public tier)

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|:---:|-------------|
| `q` | string | ✅ | Search term (minimum 2 characters) |
| `per_page` | int | — | Items per page (default 25) |

**MySQL:** Uses `MATCH ... AGAINST` fulltext index.  
**SQLite:** Falls back to `LIKE` queries.

#### Response (200 OK)

Same `BookResource` shape as `/api/books` (without `format` and `is_active` fields).

#### Validation Error (422)

```json
{
  "message": "The q field is required.",
  "errors": {
    "q": ["The q field is required."]
  }
}
```

---

### `GET /api/books/{isbn}`

Lookup a single book by ISBN-10 or ISBN-13.

**Auth:** None  
**Rate Limit:** 30 req/min (public tier)  
**Cache:** 900 seconds (15 minutes)

#### Route Parameter

| Parameter | Type | Description |
|-----------|------|-------------|
| `isbn` | string | ISBN-10 (e.g., `0-123456-78-9`) or ISBN-13 (e.g., `9780123456789`) |

#### Response (200 OK)

Single `BookResource` object (same shape as list item).

#### Response (404)

```json
{
  "message": "Not Found"
}
```

---

## AI & Chatbot Endpoints

### `POST /api/chatbot/query`

AI-powered chatbot for customer inquiries. Tries Ollama → Gemini → rule-based fallback.

**Auth:** None  
**Rate Limit:** None

#### Request Body (JSON)

| Parameter | Type | Required | Description |
|-----------|------|:---:|-------------|
| `message` | string | ✅ | User's question or message |
| `pageType` | string | No | Context: `bookings`, `inventory`, `borrow_requests`, `users`, `reports` |
| `history` | array | No | Chat history (max 5 messages): `[{"type": "user"|"bot", "content": "..."}]` |

#### Example Request

```json
{
  "message": "How many bookings are pending?",
  "pageType": "bookings",
  "history": [
    {"type": "user", "content": "Hello"},
    {"type": "bot", "content": "Welcome to RGV! How can I help?"}
  ]
}
```

#### Response (200 OK)

```json
{
  "success": true,
  "response": "**📋 Booking Summary**\n\n• Total Bookings: 120\n• Pending: 5\n• Approved: 80\n• Completed: 30\n• Rejected: 5"
}
```

#### Behavior Notes

- Greetings (`"hi"`, `"hello"`, `"help"`) return a canned welcome response.
- Report-related keywords trigger deterministic responses with live database counts.
- All other queries route through the AI pipeline (Ollama → Gemini → rule-based).

---

### `POST /ask-gemini`

Direct query to Google Gemini AI.

**Auth:** None  
**Rate Limit:** None

#### Request Body (JSON)

| Parameter | Type | Required | Description |
|-----------|------|:---:|-------------|
| `prompt` | string | ✅ | Prompt to send to Gemini |
| `data` | object | No | Optional context data |

#### Example Request

```json
{
  "prompt": "Analyze the booking trends for this month",
  "data": {
    "bookings_this_month": 45,
    "bookings_last_month": 38
  }
}
```

#### Response (200 OK)

```json
{
  "text": "Based on the data provided, bookings have increased by 18.4% compared to last month..."
}
```

#### Error Response (500)

```json
{
  "error": "AI request failed",
  "details": {
    "message": "API key invalid"
  }
}
```

---

## Admin JSON Endpoints

All admin endpoints require `auth` + `admin` middleware. Responses use `snake_case` keys (no camelCase conversion).

### `GET /admin/dashboard/stats`

Aggregated dashboard statistics with optional date filtering.

**Auth:** Admin  
**Rate Limit:** None

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `date_from` | date | Filter start date (YYYY-MM-DD) |
| `date_to` | date | Filter end date (YYYY-MM-DD) |

#### Response (200 OK)

```json
{
  "total_bookings": 120,
  "pending_bookings": 5,
  "approved_bookings": 80,
  "completed_bookings": 30,
  "rejected_bookings": 5,
  "total_employees": 15,
  "inventory_count": 200,
  "borrowed_items": 12,
  "low_stock_alerts": 8,
  "returned_items": 45,
  "running_imports": 1,
  "running_exports": 0,
  "latest_backup_status": "completed",
  "audit_events_today": 250,
  "api_requests_today": 1400,
  "monthly_bookings": [5, 8, 12, 15, 10, 8, 14, 18, 20, 22, 19, 25],
  "system": {
    "cpu_usage": 45.2,
    "memory_usage": 256.8,
    "disk_free": 50000
  }
}
```

---

### `GET /admin/notifications/unread-count`

Unread notification count and last 5 notifications. Polled every 30 seconds by the notification bell.

**Auth:** Admin  
**Rate Limit:** None

#### Response (200 OK)

```json
{
  "count": 7,
  "notifications": [
    {
      "id": 42,
      "title": "New Booking Received",
      "message": "New booking from John Doe for May 20, 2026",
      "link": "/admin/bookings/15",
      "created_at": "2 minutes ago"
    }
  ]
}
```

---

### `GET /admin/ai/insights`

AI-generated business insights with raw data.

**Auth:** Admin

#### Response (200 OK)

```json
{
  "data": {
    "total_bookings": 120,
    "pending_bookings": 5,
    "approved_bookings": 80,
    "completed_bookings": 30,
    "total_inventory": 200,
    "low_stock_items": 8,
    "total_borrow_requests": 60,
    "pending_borrow_requests": 3,
    "approved_borrow_requests": 40,
    "overdue_borrow_requests": 2
  },
  "insights": "Booking volume has increased 15% month-over-month. Low stock alerts are concentrated in the Electronics category..."
}
```

---

### `GET /admin/ai/forecast`

AI-generated 3-month booking forecast with historical data.

**Auth:** Admin

#### Response (200 OK)

```json
{
  "historical_data": [
    {"month": "2026-01", "count": 15},
    {"month": "2026-02", "count": 20},
    {"month": "2026-03", "count": 18},
    {"month": "2026-04", "count": 25},
    {"month": "2026-05", "count": 22}
  ],
  "forecast": "Based on historical trends, expect 28-32 bookings in June, 24-28 in July, and 20-25 in August..."
}
```

---

### `GET /admin/ai/inventory-recommendations`

AI-generated inventory stocking recommendations based on usage patterns.

**Auth:** Admin

#### Response (200 OK)

```json
{
  "inventory_data": [
    {
      "name": "Widget A",
      "category": "Tools",
      "quantity": 5,
      "threshold": 10,
      "status": "available",
      "borrow_count": 3
    }
  ],
  "recommendations": "Widget A is below threshold and has high borrow frequency. Recommend reordering 15 units..."
}
```

---

### `GET /admin/import-export/imports/{importLog}`

Poll import job status. Used for real-time progress updates during imports.

**Auth:** Admin

#### Route Parameter

| Parameter | Type | Description |
|-----------|------|-------------|
| `importLog` | int | ImportLog record ID |

#### Response (200 OK)

```json
{
  "id": 5,
  "status": "processing",
  "progress": 45,
  "processed_rows": 450,
  "successful_rows": 440,
  "failed_rows": 10,
  "error_report_path": "imports/errors/5.csv"
}
```

---

### `GET /admin/import-export/{type}/export`

Export data in various formats. Returns JSON when `format=json`.

**Auth:** Admin

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `format` | string | Output format: `json`, `xlsx`, `csv`, `pdf`, `xml` |
| `status` | string | Filter by status |
| `date_from` | date | Filter start date |
| `date_to` | date | Filter end date |
| `columns` | array | Column whitelist |

**Supported types:** `inventory`, `bookings`, `users`

**Note:** Exports with >10,000 records are queued as background jobs. Users PDF export is disabled.

---

## Employee JSON Endpoints

### `GET /employee/notifications/unread-count`

Unread notification count and last 5 notifications for the authenticated employee.

**Auth:** Employee + MFA  
**Rate Limit:** None

#### Response (200 OK)

Same schema as admin `/admin/notifications/unread-count`. Admin booking links are automatically rewritten to employee booking routes.

```json
{
  "count": 3,
  "notifications": [
    {
      "id": 15,
      "title": "Booking Assigned",
      "message": "You have been assigned to booking BK-ABC123",
      "link": "/employee/bookings/5",
      "created_at": "10 minutes ago"
    }
  ]
}
```

---

## Profile & Session Endpoints

### `GET /profile/export-personal-data`

Export all personal data as a JSON download (GDPR data portability).

**Auth:** Authenticated user

#### Response (200 OK)

Returns a JSON file download with `Content-Disposition: attachment`.

```json
{
  "profile": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+639123456789",
    "address": "123 Main St",
    "is_active": true,
    "created_at": "2026-01-15T08:00:00.000000Z"
  },
  "roles": ["employee"],
  "bookings": [...],
  "borrow_requests": [...]
}
```

---

### `POST /session/extend`

Extend the current session lifetime. Used by the frontend to prevent session timeout during active use.

**Auth:** Authenticated user

#### Response (200 OK)

```json
{
  "status": "ok"
}
```

Updates `session('last_activity')` to the current timestamp.

---

## Auth Endpoints

Standard Laravel Breeze authentication endpoints. Return HTML by default; can return JSON when `Accept: application/json` header is present.

| Method | URI | Controller | Auth | Throttle |
|--------|-----|-----------|:---:|:---:|
| GET | `/register` | `RegisteredUserController@create` | guest | — |
| POST | `/register` | `RegisteredUserController@store` | guest | — |
| GET | `/login` | `AuthenticatedSessionController@create` | guest | — |
| POST | `/login` | `AuthenticatedSessionController@store` | guest | `MAX_LOGIN_ATTEMPTS` |
| POST | `/logout` | `AuthenticatedSessionController@destroy` | auth | — |
| GET | `/forgot-password` | `PasswordResetLinkController@create` | guest | — |
| POST | `/forgot-password` | `PasswordResetLinkController@store` | guest | 6:1 |
| GET | `/reset-password/{token}` | `NewPasswordController@create` | guest | — |
| POST | `/reset-password` | `NewPasswordController@store` | guest | — |
| GET | `/verify-email` | `EmailVerificationPromptController` | auth | — |
| GET | `/verify-email/{id}/{hash}` | `VerifyEmailController` | auth+signed | 6:1 |
| POST | `/email/verification-notification` | `EmailVerificationNotificationController` | auth | 6:1 |
| GET | `/confirm-password` | `ConfirmablePasswordController@show` | auth | — |
| POST | `/confirm-password` | `ConfirmablePasswordController@store` | auth | — |
| PUT | `/password` | `PasswordController@update` | auth | — |
| GET | `/mfa/verify` | `MfaController@show` | session | — |
| POST | `/mfa/verify` | `MfaController@verify` | session | — |
| POST | `/mfa/resend` | `MfaController@resend` | session | — |

### Login Rate Limiting

Login attempts are throttled using `MAX_LOGIN_ATTEMPTS` (default: 5, configurable in **Site Settings → Security**). The throttle key is `{email}|{ip}`.

#### Login Success (200/Redirect)

Redirects to dashboard. Session cookie set.

#### Login Failure (422)

```json
{
  "message": "These credentials do not match our records.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

#### Login Throttled (429)

```json
{
  "message": "Too many login attempts. Please try again in 60 seconds.",
  "errors": {
    "email": ["Too many login attempts. Please try again in 60 seconds."]
  }
}
```

---

## Middleware Reference

| Middleware | Alias | Scope | Description |
|-----------|-------|-------|-------------|
| `TieredRateLimitMiddleware` | `api.rate` | `/api/*` | Per-tier rate limiting with configurable limits |
| `CamelCaseJsonResponse` | `camel.json` | `/api/*` | Converts all JSON keys from snake_case to camelCase |
| `IsAdmin` | `admin` | `/admin/*` | Requires `auth()->user()->isAdmin()`, returns 403 |
| `IsEmployee` | `employee` | `/employee/*` | Requires `auth()->user()->isEmployee()`, returns 403 |
| `RequireMfa` | `mfa` | `/employee/*` | Requires MFA verification if enabled on account |
| `SecurityHeaders` | global | all routes | CSP, HSTS, X-Frame-Options, X-Content-Type-Options |
| `ForceHttps` | global | production | Redirects HTTP to HTTPS |
| `LogPageVisit` | global | GET requests | Logs page visits to `audit_logs` |

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "Forbidden."
}
```

### 404 Not Found

```json
{
  "message": "Not Found"
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 429 Too Many Requests

```json
{
  "message": "Too many requests.",
  "tier": "public",
  "retryAfter": 60
}
```
```http
Retry-After: 60
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 0
```

### 500 Server Error

```json
{
  "message": "Server Error"
}
```

---

## Response Headers

### All API Responses (`/api/*`)

```http
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 25
Content-Type: application/json
```

### Inventory & Book List Endpoints

```http
ETag: "abc123def456..."
```

Supports conditional requests: send `If-None-Match: "abc123def456..."` to receive **304 Not Modified** if data hasn't changed.

### Export Downloads

```http
Content-Disposition: attachment; filename="inventory-export-20260518.json"
Content-Type: application/json
```

---

## Quick Reference

### All Endpoints at a Glance

| # | Method | URI | Auth | Rate Limit | ETag |
|---|--------|-----|:---:|:---:|:---:|
| 1 | GET | `/api/inventories` | — | 30/min | ✅ |
| 2 | GET | `/api/books` | — | 30/min | — |
| 3 | GET | `/api/books/search` | — | 30/min | — |
| 4 | GET | `/api/books/{isbn}` | — | 30/min | — |
| 5 | POST | `/api/chatbot/query` | — | — | — |
| 6 | POST | `/ask-gemini` | — | — | — |
| 7 | GET | `/admin/dashboard/stats` | Admin | — | — |
| 8 | GET | `/admin/notifications/unread-count` | Admin | — | — |
| 9 | GET | `/admin/ai/insights` | Admin | — | — |
| 10 | GET | `/admin/ai/forecast` | Admin | — | — |
| 11 | GET | `/admin/ai/inventory-recommendations` | Admin | — | — |
| 12 | GET | `/admin/import-export/imports/{log}` | Admin | — | — |
| 13 | GET | `/admin/import-export/{type}/export` | Admin | — | — |
| 14 | GET | `/employee/notifications/unread-count` | Employee+MFA | — | — |
| 15 | GET | `/profile/export-personal-data` | Auth | — | — |
| 16 | POST | `/session/extend` | Auth | — | — |
| 17 | POST | `/login` | — | MAX_LOGIN_ATTEMPTS | — |
| 18 | POST | `/register` | — | — | — |
| 19 | POST | `/logout` | Auth | — | — |

### Environment Variables Reference

| Variable | Default | Used By |
|----------|---------|---------|
| `API_PUBLIC_RATE_LIMIT` | 30 | Public tier rate limit |
| `API_STANDARD_RATE_LIMIT` | 60 | Standard tier rate limit |
| `API_PREMIUM_RATE_LIMIT` | 300 | Premium tier rate limit |
| `API_ADMIN_RATE_LIMIT` | 1000 | Admin tier rate limit |
| `MAX_LOGIN_ATTEMPTS` | 5 | Login throttle limit |
| `GEMINI_API_KEY` | — | Gemini AI integration |
| `OLLAMA_BASE_URL` | `http://127.0.0.1:11434` | Local Ollama LLM |
| `OLLAMA_MODEL` | `llama3.1` | Ollama model name |
