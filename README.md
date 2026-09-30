# RGV System

**RGV Multi-Tech Services** — Business Operations Management System

Laravel 12 | PHP 8.2+ | SQLite locally / PostgreSQL or MySQL configurable | Web + Flutter Android

---

## Overview

A web and Android system for managing service bookings, inventory, and equipment borrowing. Three user roles access separate portals:

- **Public** — Submit and track service bookings, use AI chatbot
- **Employee** — Manage assigned bookings on web; borrow and return inventory through web or Android app
- **Admin** — Full control over bookings, inventory, users, reports, backups, and settings

## Core Modules

| Module | Description |
|--------|-------------|
| **Bookings** | Public submits service request → Admin approves → Assigns employee → Completed. Real-time tracking via reference number. |
| **Inventory** | Track items with categories, suppliers, stock levels, QR codes. Low-stock alerts. Borrow/return workflow. |
| **Borrow Requests** | Employees request items → Admin approves → Borrowed → Returned with condition report → Stock restored. |
| **Employee Android App** | Flutter app for employee login, QR/item lookup, borrow requests, and photo-backed returns. |
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
| Application tables | 38 |
| Eloquent models | 22 |
| Migration files | 28 |
| Controllers | 42 |
| Notification classes | 14 |
| JSON API endpoints | 34+ |
| Middleware | 9 |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite (configurable to MySQL/PostgreSQL) |
| Auth | Laravel Breeze, Spatie Permission (RBAC), MFA (TOTP + email), Sanctum bearer tokens for mobile |
| Frontend | Tailwind CSS 3, Alpine.js 3, Blade templates |
| Employee Mobile | Flutter Android app using the Laravel mobile API |
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

# Book catalog performance
php artisan db:seed --class=MassBookSeeder
php artisan books:benchmark --iterations=10
php artisan books:load-test --users=50 --requests=10
php artisan books:warm-cache --sync
php artisan books:refresh-bestseller-stats

# Testing
php artisan test

# Code quality
./vendor/bin/pint
```

## Mobile App

The Flutter Android app uses Laravel's `/api/v1/mobile` API; it does not connect
to the database directly. Start Laravel from the project root and run Flutter
from `mobile/` (the Android emulator reaches the host at `10.0.2.2`):

```powershell
php artisan serve --host=0.0.0.0 --port=8000

# In another terminal, from mobile/
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1/mobile
```

See [mobile/README.md](mobile/README.md) for Flutter setup and
[RGV-Mobile-API.md](RGV-Mobile-API.md) for API and deployment configuration.

## Deployment

For Firebase Hosting with Laravel on Cloud Run and Supabase PostgreSQL, see
[docs/firebase-cloud-run-deployment.md](docs/firebase-cloud-run-deployment.md).

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
| [docs/book-performance.md](docs/book-performance.md) | Book catalog scalability and benchmarking |

## License

MIT
