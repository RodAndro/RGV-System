# RGV System

**RGV Multi-Tech Services** — Business Operations Management System

Laravel 12 | PHP 8.2+ | Supabase PostgreSQL | Web + Flutter Android

---

## Overview

A web and Android system for managing service bookings, inventory, borrowing, and operational reporting. The current implementation is a Laravel 12 backend with the live admin and employee web portals, a public booking workflow, and a Flutter Android employee application.

- **Public** — Submit and track service bookings and service requests through the public booking workflow
- **Employee** — Manage assigned bookings in the web portal and use the Android app for inventory lookup, borrow requests, returns, and account tasks
- **Admin** — Full control over bookings, inventory, users, reports, backups, settings, audit history, and import/export workflows

> Current documentation refresh: this repository reflects the Laravel website, the Supabase-backed Flutter app in `mobile/`, and current system operations.

### Current system status

The application is organized as a role-based business platform with a shared backend and distinct user experiences:

- **Public web flow**: booking submission, tracking, QR inventory lookup, and service reference lookup
- **Employee web flow**: dashboard, assigned bookings, borrow requests, inventory checks, and notification management
- **Admin web flow**: bookings, users, reports, settings, import/export, backups, audit logs, AI insights, and system health
- **Mobile employee app**: Supabase Auth plus restricted PostgreSQL RPCs for inventory, borrow, return, and profile actions; independent of the Laravel web/API process


## Core Modules

| Module | Description |
|--------|-------------|
| **Bookings** | Public submits service request → Admin approves → Assigns employee → Completed. Real-time tracking via reference number. |
| **Inventory** | Track items with categories, stock levels, permanent QR codes, and low-stock alerts. Borrow/return workflow. |
| **Borrow Requests** | Employees request items → Admin approves → Borrowed → Returned with condition report → Stock restored. |
| **Employee Android App** | Flutter app for employee login, QR/item lookup, borrow requests, and location-based returns. |
| **Users & Roles** | Spatie RBAC (Admin/Employee). MFA support (TOTP + email). Impersonation, login history, force-logout. |
| **Notifications** | 14 notification types across email + in-app channels. Bell icon with real-time polling. Per-user preferences. |
| **Reports** | Bookings, inventory, borrow requests, users. Export to PDF/Excel/CSV/JSON. AI-powered insights and forecasts. |
| **Import/Export** | Bulk CSV/Excel import with duplicate strategies. Multi-format export with background queuing. |
| **Settings** | Branding, Email (SMTP), Security, Backup, Notifications, Maintenance mode, API rate limits. |
| **Backups** | Full + DB-only scheduled backups. SHA-256 integrity verification. Downloadable zips. Retention policies. |
| **Audit & Trash** | Tamper-evident audit trail with HMAC-SHA256 checksums. Soft-delete trash with restore/force-delete. |
| **AI Integration** | Chatbot (Ollama → Gemini → rule-based fallback). AI report generation for insights, forecasts, and recommendations. |

## Key Numbers

| Metric | Count |
|--------|:----:|
| Application tables | 33 |
| Eloquent models | 18 |
| Migration files | 27 |
| Controllers | 42 |
| Notification classes | 14 |
| JSON API endpoints | 34+ |
| Middleware | 9 |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | Supabase PostgreSQL |
| Auth | Laravel Breeze, Spatie Permission (RBAC), website MFA, Supabase Auth and TOTP MFA for mobile |
| Frontend | Tailwind CSS 3, Alpine.js 3, Blade templates |
| Employee Mobile | Flutter Android app using Supabase Auth and transactional database RPCs |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |
| QR Codes | simplesoftwareio/simple-qrcode |
| Backups | spatie/laravel-backup |
| Activity Log | spatie/laravel-activitylog + custom tamper-evident audit |
| AI | openai-php/client, Google Gemini, Ollama (local) |
| Testing | PHPUnit 11 |

## Quick Start

```bash
# Clone and install
git clone <repo-url> && cd rgv-system

# Automated setup (installs deps, creates .env, generates key, migrates, builds assets)
composer run-script setup

# Or manual setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install && npm run build

# Start dev environment (server, queue, logs, vite)
composer run dev

# Or just the server
php artisan serve
```

### Environment

```env
# Set DB_CONNECTION and DB_* for the chosen database; see .env.example.
# Deployment database credentials must be supplied through the server environment.

GEMINI_API_KEY=           # Optional — AI chatbot / report generation
OPENAI_API_KEY=           # Optional — AI features
```

## Useful Commands

```bash
# Database
php artisan migrate:fresh --seed

# Testing
php artisan test
```

The test suite is hermetic: `phpunit.xml` forces SQLite
in-memory (both `<env>` and `<server>` entries, so inherited
shell variables cannot redirect tests to the live Supabase
project). Keep it that way when editing test configuration.

```bash
# Code quality
./vendor/bin/pint
```

## Mobile App

The Flutter Android app authenticates directly against Supabase Auth
and calls restricted PostgreSQL RPCs on the **same Supabase project**
that Laravel's `pgsql` connection uses. It does not depend on the
Laravel process: native login, inventory lookup, borrow requests,
returns, and profile updates keep working while the website or Laravel
process is unavailable, as long as Supabase is reachable. Both apps
read and write the same committed rows, so no sync worker exists.

The Supabase URL and publishable (anon) key are injected at build
time. Never put the server-only `SUPABASE_SECRET_KEY` in a Flutter
build; Laravel uses it solely to provision employee accounts.

```powershell
# From mobile/
flutter run --dart-define=SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co --dart-define=SUPABASE_ANON_KEY=YOUR_PUBLISHABLE_KEY
```

Release builds require HTTPS. See [mobile/README.md](mobile/README.md)
for Flutter setup and verification, and
[RGV-Mobile-API.md](RGV-Mobile-API.md) for the legacy Laravel mobile
API that remains available to external clients.

## Documentation

| Document | Description |
|----------|-------------|
| [RGV-System-Overview.md](RGV-System-Overview.md) | Architecture, modules, API summary |
| [RGV-System.md](RGV-System.md) | Full system documentation — models, controllers, routes |
| [RGV-Database-Schema.md](RGV-Database-Schema.md) | Application schema, including mobile tokens and return evidence |
| [RGV-Technical-Documentation.md](RGV-Technical-Documentation.md) | Architecture, security, web/mobile APIs, and middleware |
| [RGV-System-API.md](RGV-System-API.md) | Web and mobile API reference |
| [RGV-Mobile-API.md](RGV-Mobile-API.md) | Mobile endpoint contract and deployment configuration |
| [mobile/README.md](mobile/README.md) | Flutter app setup and verification |
| [RGV-User-Manual.md](RGV-User-Manual.md) | User guide for admin, employee web/mobile, and public workflows |

## License

MIT
