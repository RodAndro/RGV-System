# RGV-System-Overview

> **RGV Multi-Tech Services** — Business Operations Management System
>
> Backend: Laravel 12.0 | PHP 8.2+ | SQLite | Admin/Public: Tailwind CSS + Alpine.js | Mobile: Flutter (Android)

---

## What It Does

A dual-platform system for managing service bookings, inventory, and equipment borrowing for RGV Multi-Tech Services. Three user roles access different platforms:

| Role | Platform | Key Functions |
|------|----------|---------------|
| **Public** | Web (`/`) | Submit and track service bookings, use AI chatbot |
| **Employee** | Mobile App (Flutter Android) | View assigned bookings, scan QR codes to borrow/return tools, track borrowed items, view notifications |
| **Admin** | Web (`/admin`) | Full control — bookings, inventory, users, reports, backups, settings, manage all operations |

---

## Core Modules

**Bookings** — Public submits a service request → Admin approves/rejects → Assigns employee → Completed. Real-time tracking via reference number. File attachments supported.

**Inventory** — Track items with categories, suppliers, stock levels, QR codes. Low-stock alerts. Borrow/return workflow with condition tracking.

**Borrow Requests** — Employees use mobile app to scan QR codes on tools → Request submission → Admin approves → Items marked borrowed → Employee scans QR on return → Condition report submitted via app → Stock restored. Full lifecycle with soft-delete. Real-time sync between web and mobile.

**Users & Roles** — Spatie RBAC (Admin/Employee). MFA support (TOTP + email). Impersonation for admins. Login history and force-logout.

**Notifications** — 14 notification types across email + in-app channels. Bell icon with real-time polling. Click-to-mark-read. Per-user notification preferences.

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
| Database tables | 36 |
| Eloquent models | 21 |
| Migration files | 26 |
| Controllers | 30 |
| Notification classes | 14 |
| API endpoints | 19 |
| Admin routes | 60+ |
| Middleware | 8 |

---

## API Endpoints

| Category | Endpoints | Auth |
|----------|:---:|:---:|
| Public REST | `/api/inventories`, `/api/books`, `/api/books/search`, `/api/books/{isbn}` | None |
| AI | `/api/chatbot/query`, `/ask-gemini` | None |
| Admin | Dashboard stats, notifications, AI insights, import/export status | Admin |
| Employee | Notification count | Employee+MFA |
| Auth | Login, register, logout, MFA, password reset | Varies |

Rate limited at 30/60/300/1000 req/min per tier. Configurable via Site Settings.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite (configurable to MySQL/PostgreSQL) |
| Auth | Laravel Breeze, Spatie Permission (RBAC), MFA (TOTP + email) |
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
| `RGV-Database-Schema.md` | Complete database schema (36 tables) |
| `RGV-Technical-Documentation.md` | Architecture, security, AI, middleware |
| `RGV-System-API.md` | API reference (all 19 endpoints) |
| `RGV-User-Manual.md` | User guide for admin, employee, public |

---

*Last Updated: May 21, 2026*
