# RGV-System-Overview

> **RGV Multi-Tech Services** — Business Operations Management System
>
> Backend: Laravel 12 | PHP 8.2+ | Supabase PostgreSQL | Admin/Public: Tailwind CSS + Alpine.js | Mobile: Flutter Android employee app (Supabase-direct)
>
> Updated: October 2, 2026

---

## What It Does

A current multi-platform system for managing service bookings, inventory, and equipment borrowing for RGV Multi-Tech Services. The implementation combines a Laravel web application, a role-based admin and employee portal, and a dedicated employee mobile API with a Flutter Android client.

This documentation set is aligned to the live codebase in `routes/web.php`, `routes/api.php`, and the Android app under `mobile/` as of October 2026.

| Role | Platform | Key Functions |
|------|----------|---------------|
| **Public** | Web (`/`) | Submit and track service bookings and service requests through the public portal |
| **Employee** | Web portal + Flutter Android app | Manage assigned bookings on the web; use the app for inventory lookup, borrow requests, returns, and account settings |
| **Admin** | Web (`/admin`) | Full control — bookings, inventory, users, reports, backups, settings, audit trails, and import/export workflows |

---

## Core Modules

**Bookings** — Public submits a service request → Admin approves/rejects → Assigns employee → Completed. Real-time tracking via reference number. Booking file uploads are not supported.

**Inventory** — Track items with categories, stock levels, permanent QR codes, and low-stock alerts. Inventory image uploads are not supported. Borrow/return workflow with condition tracking.

**Borrow Requests** — In the Android app, employees scan a QR code or enter an item code, submit a request, and wait for admin approval. Pending requests reserve stock. For returns, employees select borrowed items, report condition, and choose a return location; photos are not required. Web and mobile share the same Supabase database and business rules; the native app enforces them through the same restricted RPCs the website's data lives behind.

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
| Application tables | 33 |
| Eloquent models | 18 |
| Migration files | 27 |
| Controllers | 42 |
| Notification classes | 14 |
| JSON API endpoints | 34+ |
| Admin routes | 60+ |
| Middleware | 9 |

---

## API Endpoints

| Category | Endpoints | Auth |
|----------|:---:|:---:|
| Public REST | `/api/inventories` | None |
| AI | `/api/chatbot/query` | Admin session |
| Admin | Dashboard stats, notifications, AI insights, import/export status | Admin |
| Employee | Notification count | Employee+MFA |
| Mobile Employee | Supabase Auth + restricted PostgreSQL RPCs | Supabase JWT (AAL2 when MFA is required) |
| Auth | Login, register, logout, MFA, password reset | Varies |

The public API uses tiered limits (30/60/300/1000 requests per minute,
configurable in Site Settings). The legacy Laravel mobile API limits
login to 20/minute and protected employee routes to 120/minute; the
native app's Supabase RPCs are governed by Supabase's own rate limits.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | Supabase PostgreSQL (Laravel `pgsql` + native RPCs) |
| Auth | Laravel Breeze, Spatie Permission (RBAC), MFA (TOTP + email) for web; Supabase Auth + TOTP MFA for mobile |
| Admin/Public Web | Tailwind CSS 3, Alpine.js 3, Blade templates |
| Employee Mobile | Flutter (Android native) with QR code scanning |
| QR Codes | simplesoftwareio/simple-qrcode (generation), Flutter camera (scanning) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |
| Backups | spatie/laravel-backup |
| Activity Log | spatie/laravel-activitylog + custom tamper-evident audit |
| AI | openai-php/client, Google Gemini, Ollama (local) |
| Testing | PHPUnit 11 (hermetic SQLite in-memory, forced via phpunit.xml) |

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

*Last Updated: October 2, 2026*
