# RGV System

**RGV Multi-Tech Services** — Business Operations Management System

Laravel 12 | PHP 8.2+ | SQLite | Tailwind CSS + Alpine.js

---

## Overview

A web-based system for managing service bookings, inventory, and equipment borrowing. Three user roles access separate portals:

- **Public** — Submit and track service bookings, use AI chatbot
- **Employee** — View assigned bookings, request/return inventory items
- **Admin** — Full control over bookings, inventory, users, reports, backups, and settings

## Core Modules

| Module | Description |
|--------|-------------|
| **Bookings** | Public submits service request → Admin approves → Assigns employee → Completed. Real-time tracking via reference number. |
| **Inventory** | Track items with categories, suppliers, stock levels, QR codes. Low-stock alerts. Borrow/return workflow. |
| **Borrow Requests** | Employees request items → Admin approves → Borrowed → Returned with condition report → Stock restored. |
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
| Database tables | 36 |
| Eloquent models | 21 |
| Controllers | 30 |
| Notification classes | 14 |
| API endpoints | 19 |
| Middleware | 8 |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite (configurable to MySQL/PostgreSQL) |
| Auth | Laravel Breeze, Spatie Permission (RBAC), MFA (TOTP + email) |
| Frontend | Tailwind CSS 3, Alpine.js 3, Blade templates |
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
DB_CONNECTION=mysql      # or pgsql, sqlite
DB_DATABASE=rgv_system

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

## Documentation

| Document | Description |
|----------|-------------|
| [RGV-System-Overview.md](RGV-System-Overview.md) | Architecture, modules, API summary |
| [RGV-System.md](RGV-System.md) | Full system documentation — models, controllers, routes |
| [RGV-Database-Schema.md](RGV-Database-Schema.md) | Complete 36-table schema |
| [RGV-Technical-Documentation.md](RGV-Technical-Documentation.md) | Architecture, security, AI, middleware |
| [RGV-System-API.md](RGV-System-API.md) | API reference (all 19 endpoints) |
| [RGV-User-Manual.md](RGV-User-Manual.md) | User guide for admin, employee, public |
| [docs/book-performance.md](docs/book-performance.md) | Book catalog scalability and benchmarking |

## License

MIT
