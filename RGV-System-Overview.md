# RGV-System-Overview

> **RGV Multi-Tech Services** — Business Operations Management System
>
> Backend: Laravel 12.0 | PHP 8.2+ | SQLite locally / PostgreSQL or MySQL configurable | Admin/Public: Tailwind CSS + Alpine.js | Mobile: Flutter (Android)

---

## What It Does

A dual-platform system for managing service bookings, inventory, and equipment borrowing for RGV Multi-Tech Services. Three user roles access different platforms:

| Role | Platform | Key Functions |
|------|----------|---------------|
| **Public** | Web (`/`) | Submit and track service bookings, use AI chatbot |
| **Employee** | Web portal + Flutter Android app | Manage assigned bookings on web; use the app for inventory lookup, borrow requests, returns, and account settings |
| **Admin** | Web (`/admin`) | Full control — bookings, inventory, users, reports, backups, settings, manage all operations |

---

## Core Modules

**Bookings** — Public submits a service request → Admin approves/rejects → Assigns employee → Completed. Real-time tracking via reference number. File attachments supported.

**Inventory** — Track items with categories, suppliers, stock levels, QR codes. Low-stock alerts. Borrow/return workflow with condition tracking.

**Borrow Requests** — In the Android app, employees scan a QR code or enter an item code, submit a request, and wait for admin approval. Pending requests reserve stock. For returns, employees select borrowed items, report condition, and submit a required proof photo; stock is restored only after the server-side Drive upload succeeds. Web and mobile use the same Laravel data and business rules.

**Users & Roles** — Spatie RBAC (Admin/Employee). MFA support (TOTP + email). Impersonation for admins. Login history and force-logout.

**Notifications** — 14 notification types across email + in-app channels in the web portals. The Android app currently does not expose notifications.

**Reports** — Bookings, inventory, borrow requests, users. Export to PDF/Excel/CSV/JSON. AI-powered insights, forecasts, and inventory recommendations.

**Import/Export** — Bulk CSV/Excel import for inventory and users with duplicate strategies. Multi-format export with background queuing for large datasets.

**Settings** — 7-tab admin panel: Branding, Email (SMTP), Security (password rules, login limits), Backup (scheduled + manual), Notifications, Maintenance mode, API rate limits.

**Backups** — Spatie backup package. Full + DB-only scheduled backups. Manual trigger. SHA-256 integrity verification. Downloadable zip files. Retention policies.

**Audit & Trash** — Tamper-evident audit trail with HMAC-SHA256 checksum chain. Soft-delete trash system with restore/force-delete for 5 entity types.

**AI Integration** — Chatbot (Ollama → Gemini → rule-based fallback). AI report generation for insights, booking forecasts, and inventory recommendations.

---

## Key Numbers

| Metric | Count |
|--------|:----:|
| Application tables | 38 |
| Eloquent models | 22 |
| Migration files | 28 |
| Controllers | 42 |
| Notification classes | 14 |
| JSON API endpoints | 34+ |
| Admin routes | 60+ |
| Middleware | 9 |

---

## API Endpoints

| Category | Endpoints | Auth |
|----------|:---:|:---:|
| Public REST | `/api/inventories`, `/api/books`, `/api/books/search`, `/api/books/{isbn}` | None |
| AI | `/api/chatbot/query`, `/ask-gemini` | None |
| Admin | Dashboard stats, notifications, AI insights, import/export status | Admin |
| Employee | Notification count | Employee+MFA |
| Mobile Employee | `/api/v1/mobile/*` (15 routes) | Sanctum bearer token + active `employee` role |
| Auth | Login, register, logout, MFA, password reset | Varies |

The public API uses tiered limits (30/60/300/1000 requests per minute,
configurable in Site Settings). Mobile login is limited to 20/minute and
protected employee mobile routes to 120/minute.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite (configurable to MySQL/PostgreSQL) |
| Auth | Laravel Breeze, Spatie Permission (RBAC), MFA (TOTP + email), Sanctum bearer tokens for mobile |
| Admin/Public Web | Tailwind CSS 3, Alpine.js 3, Blade templates |
| Employee Mobile | Flutter (Android native) with QR code scanning |
| QR Codes | simplesoftwareio/simple-qrcode (generation), Flutter camera (scanning) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |
| Backups | spatie/laravel-backup |
| Activity Log | spatie/laravel-activitylog + custom tamper-evident audit |
| AI | openai-php/client, Google Gemini, Ollama (local) |
| Testing | PHPUnit 11 |

---

## Documentation

| Document | Description |
|----------|-------------|
| `RGV-System.md` | Full system architecture, routes, modules |
| `RGV-Database-Schema.md` | Database tables, including mobile tokens and return evidence |
| `RGV-Technical-Documentation.md` | Architecture, security, web and mobile APIs, middleware |
| `RGV-System-API.md` | Web and mobile API reference |
| `RGV-Mobile-API.md` | Mobile endpoints, authentication, and Drive configuration |
| `mobile/README.md` | Flutter setup, run, and verification steps |
| `RGV-User-Manual.md` | User guide for admin, employee web/mobile, and public workflows |

---

*Last Updated: September 30, 2026*
