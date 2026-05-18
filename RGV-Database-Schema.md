# RGV Database Schema

> **RGV Multi-Tech Services** — Complete Database Schema Reference
>
> Tables: 36 | Migrations: 26 | Models: 21 | ORM: Laravel Eloquent | Last Updated: May 18, 2026

---

## Table of Contents

1. [Entity Relationship Diagram](#entity-relationship-diagram)
2. [Table Groups Overview](#table-groups-overview)
3. [Core Business Tables](#core-business-tables)
4. [Auth & User Tables](#auth--user-tables)
5. [Permissions & Roles (Spatie)](#permissions--roles-spatie)
6. [Platform & Operational Tables](#platform--operational-tables)
7. [Books & Performance Tables](#books--performance-tables)
8. [Laravel Infrastructure Tables](#laravel-infrastructure-tables)
9. [Index Reference](#index-reference)
10. [SQL Dump Reference](#sql-dump-reference)

---

## Entity Relationship Diagram

```
                                    ┌──────────────────────┐
                                    │     permissions       │
                                    │  id, name, guard_name │
                                    └──────┬───────────────┘
                                           │
                               ┌───────────┴───────────┐
                               │                       │
                     ┌─────────▼──────────┐  ┌─────────▼──────────┐
                     │model_has_permission│  │role_has_permissions│
                     │  morphs (user)     │  │ permission_id      │
                     └────────────────────┘  │ role_id            │
                                             └────────┬───────────┘
                                                      │
                     ┌────────────────────┐  ┌─────────▼──────────┐
                     │  model_has_roles   │  │       roles         │
                     │  morphs (user)     │  │  id, name,guard_name│
                     └────────────────────┘  └─────────────────────┘

    ┌──────────────────────────────────────────────────────────────────────────┐
    │                               users                                       │
    │  id, name, email, password, mfa_*, phone, address, avatar, is_active...  │
    └──────┬──────────┬──────────┬──────────┬──────────┬───────────┬───────────┘
           │          │          │          │          │           │
    ┌──────▼──┐ ┌─────▼────┐ ┌───▼────┐ ┌───▼─────┐ ┌─▼──────┐ ┌──▼───────────┐
    │ bookings│ │ borrow   │ │reports │ │notifi-  │ │login   │ │notification  │
    │         │ │_requests │ │        │ │cations  │ │history │ │_preferences  │
    │ employ- │ │ employee │ │genera- │ │ user_id │ │ user_id│ │ user_id      │
    │ ee_id   │ │ _id      │ │ted_by  │ │         │ │        │ │              │
    │         │ │ approved │ │        │ │         │ │        │ │              │
    └─────────┘ │ _by      │ └────────┘ └─────────┘ └────────┘ └──────────────┘
                └───┬──────┘
                    │
         ┌──────────▼──────────┐
         │   borrow_items       │────────────┐
         │ borrow_request_id    │            │
         │ inventory_id         │            │
         └──────────────────────┘            │
                                             │
                              ┌──────────────▼──────────┐
                              │       inventories        │
                              │  item_code, name, qty,   │
                              │  status, condition...    │
                              └────┬──────────┬──────────┘
                                   │          │
                         ┌─────────▼──┐  ┌────▼──────────┐
                         │ inventory  │  │  suppliers     │
                         │_categories │  │  id, name...   │
                         │ id, name,  │  └───────────────┘
                         │ slug       │
                         └────────────┘

    ┌──────────────────────────────────────────────────────────────────┐
    │                           books                                   │
    │  id, isbn, isbn13, title, slug, category_id, author, stock...    │
    └────┬──────────┬──────────┬───────────────┬───────────────────────┘
         │          │          │               │
    ┌────▼─────┐ ┌──▼───────┐ ┌▼────────────┐ ┌▼───────────────┐
    │book_shard│ │search    │ │mv_bestseller│ │query_perform-  │
    │ metadata │ │_indexing │ │_stats       │ │ance_logs       │
    │          │ │_queue    │ │             │ │                │
    └──────────┘ └──────────┘ └─────────────┘ └────────────────┘

    ┌──────────────────────────────────────────────────────────────┐
    │                     activity_log (Spatie)                     │
    │  id, log_name, description, subject, causer, properties...   │
    └──────────────────────────────────────────────────────────────┘

    ┌──────────────────────────────────────────────────────────────┐
    │                      audit_logs (Custom)                      │
    │  id, user_id, event, auditable (morphs), old/new values...   │
    │  checksum (HMAC-SHA256 chain for tamper evidence)            │
    └──────────────────────────────────────────────────────────────┘

    ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
    │  import_logs    │  │  export_logs    │  │ api_rate_limits │
    │  user_id, type  │  │  user_id, type  │  │ key, tier...    │
    └─────────────────┘  └─────────────────┘  └─────────────────┘

    ┌───────────────────┐  ┌─────────────────────┐  ┌──────────────┐
    │ scheduled_tasks   │  │ backup_monitoring   │  │  settings    │
    │ command, status   │  │ disk, status, size  │  │ key, value   │
    └───────────────────┘  └─────────────────────┘  └──────────────┘
```

---

## Table Groups Overview

| Group | Tables | Description |
|-------|--------|-------------|
| **Auth / User Core** | `users`, `password_reset_tokens`, `sessions`, `login_history` | Authentication, sessions, login tracking |
| **Permissions** | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | Spatie RBAC |
| **Activity Logging** | `activity_log`, `audit_logs` | Spatie activitylog + custom tamper-evident audit |
| **Core Business** | `bookings`, `inventories`, `inventory_categories`, `suppliers`, `borrow_requests`, `borrow_items`, `notifications`, `reports` | Main business domain |
| **Platform Ops** | `import_logs`, `export_logs`, `scheduled_tasks`, `api_rate_limits`, `backup_monitoring`, `settings`, `notification_preferences` | System operations |
| **Books / Performance** | `books`, `mv_bestseller_stats`, `query_performance_logs`, `search_indexing_queue`, `book_shards` | Book catalog + performance |
| **Infrastructure** | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Laravel framework tables |

---

## Core Business Tables

### `bookings`

Service booking requests submitted by the public.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `reference_number` | VARCHAR UNIQUE | NO | `BK-{uniqid}` | Auto-generated reference number |
| `full_name` | VARCHAR | NO | — | Customer full name |
| `email` | VARCHAR | NO | — | Customer email |
| `contact_number` | VARCHAR | NO | — | Customer phone |
| `address` | TEXT | NO | — | Customer address |
| `preferred_date` | DATE | NO | — | Preferred service date |
| `preferred_time` | TIME | NO | — | Preferred service time |
| `purpose_category` | VARCHAR | NO | — | Service category |
| `reason` | TEXT | YES | — | Detailed reason/description |
| `status` | VARCHAR | NO | `pending` | pending/approved/rejected/completed/cancelled |
| `employee_id` | FK → users.id | YES | SET NULL | Assigned employee |
| `remarks` | TEXT | YES | — | Admin/employee remarks |
| `attachment_path` | VARCHAR | YES | — | Uploaded file path |
| `approved_at` | TIMESTAMP | YES | — | Approval timestamp |
| `rejected_at` | TIMESTAMP | YES | — | Rejection timestamp |
| `completed_at` | TIMESTAMP | YES | — | Completion timestamp |
| `cancelled_at` | TIMESTAMP | YES | — | Cancellation timestamp |
| `lock_version` | UNSIGNED INT | NO | 1 | Optimistic locking |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Indexes:** `[status, preferred_date]`, `reference_number`

**Relationships:**
- `employee()` → BelongsTo → `User` on `employee_id`

**Status Flow:**
```
pending → approved → completed
pending → rejected
pending → cancelled
approved → cancelled
```

---

### `inventories`

Inventory/asset items tracked in the system.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `item_code` | VARCHAR UNIQUE | NO | — | Unique item code |
| `name` | VARCHAR | NO | — | Item name |
| `description` | TEXT | YES | — | Item description |
| `category_id` | FK → inventory_categories.id | NO | CASCADE | Item category |
| `supplier_id` | FK → suppliers.id | YES | SET NULL | Supplier |
| `quantity` | INT | NO | 0 | Current stock quantity |
| `unit` | VARCHAR | NO | `pcs` | Unit of measurement |
| `unit_cost` | DECIMAL(10,2) | YES | — | Cost per unit |
| `status` | VARCHAR | NO | `available` | available/borrowed/maintenance/damaged |
| `condition` | VARCHAR | NO | `good` | new/good/fair/poor |
| `location` | VARCHAR | YES | — | Storage location |
| `image_path` | VARCHAR | YES | — | Item image |
| `low_stock_threshold` | INT | NO | 5 | Low stock warning threshold |
| `date_added` | DATE | NO | — | Date added to inventory |
| `is_active` | BOOLEAN | NO | true | Active status |
| `lock_version` | UNSIGNED INT | NO | 1 | Optimistic locking |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Indexes:** `[category_id, status]`, `item_code`

**Relationships:**
- `category()` → BelongsTo → `InventoryCategory`
- `supplier()` → BelongsTo → `Supplier`
- `borrowItems()` → HasMany → `BorrowItem`

---

### `inventory_categories`

Categories for inventory items.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | NO | — | Category name |
| `slug` | VARCHAR UNIQUE | NO | — | URL-friendly slug |
| `description` | TEXT | YES | — | Category description |
| `icon` | VARCHAR | YES | — | Icon identifier |
| `is_active` | BOOLEAN | NO | true | Active status |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Relationships:**
- `inventories()` → HasMany → `Inventory`

---

### `suppliers`

Inventory suppliers/vendors.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | NO | — | Supplier name |
| `contact_person` | VARCHAR | YES | — | Contact person |
| `email` | VARCHAR | YES | — | Email address |
| `phone` | VARCHAR | YES | — | Phone number |
| `address` | TEXT | YES | — | Street address |
| `city` | VARCHAR | YES | — | City |
| `country` | VARCHAR | NO | `Philippines` | Country |
| `is_active` | BOOLEAN | NO | true | Active status |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Relationships:**
- `inventories()` → HasMany → `Inventory`

---

### `borrow_requests`

Employee requests to borrow inventory items.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `request_number` | VARCHAR UNIQUE | NO | `BR-{uniqid}` | Auto-generated reference |
| `employee_id` | FK → users.id | NO | CASCADE | Requesting employee |
| `approved_by` | FK → users.id | YES | SET NULL | Approving admin |
| `status` | VARCHAR | NO | `pending` | pending/approved/rejected/borrowed/returned/overdue |
| `reason` | TEXT | NO | — | Reason for borrowing |
| `borrow_date` | DATE | NO | — | Date borrowed |
| `due_date` | DATE | NO | — | Expected return date |
| `return_date` | DATE | YES | — | Actual return date |
| `penalty_notes` | TEXT | YES | — | Late return penalties |
| `admin_remarks` | TEXT | YES | — | Admin notes |
| `approved_at` | TIMESTAMP | YES | — | Approval timestamp |
| `rejected_at` | TIMESTAMP | YES | — | Rejection timestamp |
| `borrowed_at` | TIMESTAMP | YES | — | When items taken |
| `returned_at` | TIMESTAMP | YES | — | When items returned |
| `lock_version` | UNSIGNED INT | NO | 1 | Optimistic locking |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Indexes:** `[employee_id, status]`, `request_number`

**Relationships:**
- `employee()` → BelongsTo → `User` on `employee_id`
- `approvedBy()` → BelongsTo → `User` on `approved_by`
- `borrowItems()` → HasMany → `BorrowItem`

**Status Flow:**
```
pending → approved → borrowed → returned
pending → rejected
borrowed → overdue
```

---

### `borrow_items`

Pivot table linking borrow requests to inventory items, with additional tracking fields.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `borrow_request_id` | FK → borrow_requests.id | NO | CASCADE | Parent borrow request |
| `inventory_id` | FK → inventories.id | NO | CASCADE | Borrowed item |
| `quantity` | INT | NO | 1 | Quantity borrowed |
| `condition_borrowed` | VARCHAR | NO | `good` | Condition when borrowed |
| `condition_returned` | VARCHAR | YES | — | Condition when returned |
| `is_returned` | BOOLEAN | NO | false | Return status |
| `returned_at` | TIMESTAMP | YES | — | Return timestamp |
| `damage_notes` | TEXT | YES | — | Damage description if any |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[borrow_request_id, inventory_id]`

**Relationships:**
- `borrowRequest()` → BelongsTo → `BorrowRequest`
- `inventory()` → BelongsTo → `Inventory`

---

### `notifications`

In-app system notifications for users.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | YES | CASCADE | Target user |
| `type` | VARCHAR | NO | — | booking/borrow_request/inventory/system |
| `title` | VARCHAR | YES | — | Notification title |
| `message` | TEXT | YES | — | Notification body |
| `link` | VARCHAR | YES | — | Action link URL |
| `is_read` | BOOLEAN | NO | false | Read status |
| `read_at` | TIMESTAMP | YES | — | When read |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[user_id, is_read]`

**Relationships:**
- `user()` → BelongsTo → `User`

**Model:** `SystemNotification` (maps to `notifications` table)

---

### `reports`

Generated reports (PDF, Excel, CSV) with optional AI generation.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `report_number` | VARCHAR UNIQUE | NO | `RPT-{uniqid}` | Auto-generated reference |
| `generated_by` | FK → users.id | NO | CASCADE | User who generated |
| `type` | VARCHAR | NO | — | booking/inventory/borrow/ai_analytics/monthly |
| `title` | VARCHAR | NO | — | Report title |
| `summary` | TEXT | YES | — | Report summary |
| `data` | JSON | YES | — | Report data |
| `file_path` | VARCHAR | YES | — | Generated file path |
| `file_format` | VARCHAR | NO | `pdf` | pdf/excel/csv |
| `report_date` | DATE | NO | — | Report date |
| `start_date` | DATE | YES | — | Data start date |
| `end_date` | DATE | YES | — | Data end date |
| `is_ai_generated` | BOOLEAN | NO | false | AI-generated flag |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[type, report_date]`

**Relationships:**
- `generatedBy()` → BelongsTo → `User`

---

## Auth & User Tables

### `users`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | NO | — | Full name |
| `email` | VARCHAR UNIQUE | NO | — | Email (login) |
| `email_verified_at` | TIMESTAMP | YES | — | Verification timestamp |
| `password` | VARCHAR | NO | — | Bcrypt hashed |
| `mfa_enabled` | BOOLEAN | NO | false | MFA toggle |
| `mfa_secret` | VARCHAR | YES | — | TOTP secret |
| `mfa_type` | VARCHAR | NO | `email` | email/totp |
| `mfa_verified_at` | TIMESTAMP | YES | — | MFA setup verified |
| `mfa_recovery_codes` | JSON | YES | — | 8 recovery codes |
| `remember_token` | VARCHAR | YES | — | Remember-me token |
| `phone` | VARCHAR | YES | — | Phone number |
| `address` | TEXT | YES | — | Address |
| `avatar_path` | VARCHAR | YES | — | Avatar image path |
| `is_active` | BOOLEAN | NO | true | Account active |
| `last_login_at` | TIMESTAMP | YES | — | Last login timestamp |
| `lock_version` | UNSIGNED INT | NO | 1 | Optimistic locking |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Relationships:**
- `bookings()` → HasMany → `Booking`
- `borrowRequests()` → HasMany → `BorrowRequest`
- `approvedBorrowRequests()` → HasMany → `BorrowRequest`
- `reports()` → HasMany → `Report`
- `notifications()` → HasMany → `SystemNotification`
- `notificationPreferences()` → HasMany → `NotificationPreference`
- `loginHistory()` → HasMany → `LoginHistory`

### `login_history`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | NO | CASCADE | User |
| `ip_address` | VARCHAR(45) | YES | — | Login IP |
| `user_agent` | TEXT | YES | — | Browser user agent |
| `logged_in_at` | TIMESTAMP | YES | — | Login time |
| `logged_out_at` | TIMESTAMP | YES | — | Logout time |
| `session_id` | VARCHAR | YES | — | Session ID |
| `is_impersonation` | BOOLEAN | NO | false | Impersonation flag |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `logged_in_at`

### `password_reset_tokens` (Laravel built-in)

| Column | Type | Nullable |
|--------|------|----------|
| `email` | VARCHAR PK | NO |
| `token` | VARCHAR | NO |
| `created_at` | TIMESTAMP | YES |

### `sessions` (Laravel built-in)

| Column | Type | Nullable |
|--------|------|----------|
| `id` | VARCHAR PK | NO |
| `user_id` | FK → users.id | YES |
| `ip_address` | VARCHAR(45) | YES |
| `user_agent` | TEXT | YES |
| `payload` | LONGTEXT | NO |
| `last_activity` | INT | NO |

---

## Permissions & Roles (Spatie)

### `permissions`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | BIGINT PK | NO | — |
| `name` | VARCHAR | NO | Permission name |
| `guard_name` | VARCHAR | NO | `web` |
| `created_at` | TIMESTAMP | NO | — |
| `updated_at` | TIMESTAMP | NO | — |

**Unique:** `[name, guard_name]`

### `roles`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | BIGINT PK | NO | — |
| `name` | VARCHAR | NO | admin/employee |
| `guard_name` | VARCHAR | NO | `web` |
| `created_at` | TIMESTAMP | NO | — |
| `updated_at` | TIMESTAMP | NO | — |

**Unique:** `[name, guard_name]`

### `model_has_permissions`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `permission_id` | FK → permissions.id | NO | CASCADE delete |
| `model_type` | VARCHAR | NO | Morph type |
| `model_id` | BIGINT | NO | Morph id |

**Primary:** `[permission_id, model_id, model_type]`

### `model_has_roles`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `role_id` | FK → roles.id | NO | CASCADE delete |
| `model_type` | VARCHAR | NO | Morph type |
| `model_id` | BIGINT | NO | Morph id |

**Primary:** `[role_id, model_id, model_type]`

### `role_has_permissions`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `permission_id` | FK → permissions.id | NO | CASCADE delete |
| `role_id` | FK → roles.id | NO | CASCADE delete |

**Primary:** `[permission_id, role_id]`

---

## Platform & Operational Tables

### `activity_log` (Spatie ActivityLog)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | BIGINT PK | NO | Primary key |
| `log_name` | VARCHAR | YES | Log category |
| `description` | TEXT | NO | Activity description |
| `subject_type` | VARCHAR | YES | Morph type (subject) |
| `subject_id` | BIGINT | YES | Morph id (subject) |
| `causer_type` | VARCHAR | YES | Morph type (causer) |
| `causer_id` | BIGINT | YES | Morph id (causer) |
| `event` | VARCHAR | YES | created/updated/deleted |
| `properties` | JSON | YES | Changed attributes |
| `batch_uuid` | UUID | YES | Batch identifier |
| `created_at` | TIMESTAMP | NO | — |
| `updated_at` | TIMESTAMP | NO | — |

### `audit_logs` (Custom — Tamper-Evident)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | BIGINT PK | NO | Primary key |
| `user_id` | FK → users.id | YES | SET NULL |
| `event` | VARCHAR | NO | Action performed |
| `auditable_type` | VARCHAR | YES | Morph type |
| `auditable_id` | BIGINT | YES | Morph id |
| `old_values` | JSON | YES | Values before change |
| `new_values` | JSON | YES | Values after change |
| `ip_address` | VARCHAR | YES | Request IP |
| `user_agent` | TEXT | YES | Browser info |
| `url` | VARCHAR | YES | Request URL |
| `checksum` | VARCHAR(64) | NO | HMAC-SHA256 chain |
| `previous_checksum` | VARCHAR(64) | YES | Previous entry hash |
| `archived_at` | TIMESTAMP | YES | Archive timestamp |
| `created_at` | TIMESTAMP | NO | — |
| `updated_at` | TIMESTAMP | NO | — |

**Indexes:** `[event, created_at]`

**Tamper Evidence:** Each entry's `checksum` is computed as `HMAC-SHA256(previous_checksum + entry_data)`, creating an immutable chain.

### `import_logs`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | YES | SET NULL | Importing user |
| `type` | VARCHAR | NO | `inventory` | Import type |
| `file_name` | VARCHAR | YES | — | Uploaded file name |
| `status` | VARCHAR | NO | `pending` | pending/processing/completed/failed |
| `total_rows` | UNSIGNED INT | NO | 0 | Total rows in file |
| `processed_rows` | UNSIGNED INT | NO | 0 | Rows processed |
| `successful_rows` | UNSIGNED INT | NO | 0 | Successfully imported |
| `failed_rows` | UNSIGNED INT | NO | 0 | Failed rows |
| `duplicate_strategy` | VARCHAR | NO | `skip` | skip/update/error |
| `errors` | JSON | YES | — | Error details |
| `error_report_path` | VARCHAR | YES | — | Error file path |
| `started_at` | TIMESTAMP | YES | — | Start time |
| `completed_at` | TIMESTAMP | YES | — | End time |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[type, status]`

### `export_logs`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | YES | SET NULL | Exporting user |
| `type` | VARCHAR | NO | — | Export type |
| `format` | VARCHAR | NO | `xlsx` | xlsx/csv/pdf |
| `status` | VARCHAR | NO | `pending` | pending/processing/completed/failed |
| `filters` | JSON | YES | — | Applied filters |
| `columns` | JSON | YES | — | Selected columns |
| `record_count` | UNSIGNED INT | NO | 0 | Records exported |
| `file_path` | VARCHAR | YES | — | Output file path |
| `failure_message` | TEXT | YES | — | Error message |
| `started_at` | TIMESTAMP | YES | — | Start time |
| `completed_at` | TIMESTAMP | YES | — | End time |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[type, status]`

### `scheduled_tasks`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `command` | VARCHAR | NO | — | Artisan command |
| `status` | VARCHAR | NO | `started` | started/running/completed/failed |
| `started_at` | TIMESTAMP | YES | — | Start time |
| `finished_at` | TIMESTAMP | YES | — | End time |
| `duration_ms` | UNSIGNED INT | YES | — | Duration in ms |
| `output` | TEXT | YES | — | Command output |
| `failure_message` | TEXT | YES | — | Error if failed |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[command, status]`

### `api_rate_limits`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | YES | SET NULL | Authenticated user |
| `tier` | VARCHAR | NO | `public` | public/standard/premium/admin |
| `key` | VARCHAR | NO | — | Rate limit key |
| `ip_address` | VARCHAR | YES | — | Request IP |
| `limit_per_minute` | UNSIGNED INT | NO | — | Max requests/min |
| `remaining` | UNSIGNED INT | NO | — | Remaining requests |
| `blocked` | BOOLEAN | NO | false | Blocked flag |
| `reset_at` | TIMESTAMP | YES | — | Reset timestamp |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[key, reset_at]`, `[tier, blocked]`

### `backup_monitoring`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `disk` | VARCHAR | NO | `local` | Backup disk |
| `status` | VARCHAR | NO | `unknown` | queued/processing/success/failed |
| `file_path` | VARCHAR | YES | — | Backup file path (e.g., `Laravel/2026-05-17-10-15-46.zip`) |
| `size_bytes` | UNSIGNED BIGINT | NO | 0 | File size in bytes |
| `checksum` | VARCHAR(64) | YES | — | SHA-256 hash of the backup file |
| `message` | TEXT | YES | — | Status message |
| `started_at` | TIMESTAMP | YES | — | Start time |
| `completed_at` | TIMESTAMP | YES | — | End time |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[disk, status]`

**Backup File Location:** Backups are stored in the `local` disk under a directory named after the application (`config('backup.backup.name')`). The default `APP_NAME` is `"RGV Multi-Tech Services"`. Files use `.zip` extension.

### `settings` (Key-Value Store)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `key` | VARCHAR UNIQUE | NO | — | Setting key |
| `value` | TEXT | YES | — | Setting value |
| `type` | VARCHAR | NO | `string` | string/int/bool/json |
| `group` | VARCHAR | NO | `general` | Setting group |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

### `notification_preferences`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `user_id` | FK → users.id | NO | CASCADE | User |
| `type` | VARCHAR | NO | — | Notification type |
| `email_enabled` | BOOLEAN | NO | true | Email notifications |
| `in_app_enabled` | BOOLEAN | NO | true | In-app notifications |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Unique:** `[user_id, type]`

---

## Books & Performance Tables

### `books`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `isbn` | VARCHAR(17) UNIQUE | NO | — | ISBN-10 with hyphens |
| `isbn13` | VARCHAR(13) UNIQUE | NO | — | ISBN-13 |
| `title` | VARCHAR | NO | — | Book title |
| `slug` | VARCHAR UNIQUE | NO | — | URL slug |
| `category_id` | FK → inventory_categories.id | NO | CASCADE | Category |
| `author` | VARCHAR | NO | — | Author name |
| `publisher` | VARCHAR | YES | — | Publisher |
| `format` | VARCHAR(32) | NO | `paperback` | paperback/hardcover/ebook |
| `price` | DECIMAL(10,2) | NO | — | Price |
| `stock` | UNSIGNED INT | NO | 0 | Stock count |
| `sales_count` | UNSIGNED BIGINT | NO | 0 | Total sales |
| `rating` | DECIMAL(3,2) | NO | 0 | Average rating (0-5) |
| `description` | TEXT | NO | — | Book description |
| `is_active` | BOOLEAN | NO | true | Active listing |
| `published_at` | DATE | YES | — | Publication date |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:**
- `books_catalog_cover_idx`: `[is_active, category_id, price, id]` (covering index for catalog queries)
- `books_category_filter_idx`: `[category_id, is_active, stock, price]`
- `books_active_created_idx`: `[is_active, created_at, id]`
- `books_sales_rating_idx`: `[sales_count, rating]`
- `published_at`
- **FULLTEXT:** `books_fulltext_idx` on `(title, author, description)` (MySQL only)

### `mv_bestseller_stats` (Materialized View Emulation)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `category_id` | FK UNIQUE → inventory_categories.id | NO | CASCADE | Category |
| `book_count` | UNSIGNED BIGINT | NO | 0 | Books in category |
| `total_stock` | UNSIGNED BIGINT | NO | 0 | Total stock |
| `total_sales` | UNSIGNED BIGINT | NO | 0 | Total sales |
| `avg_price` | DECIMAL(10,2) | NO | 0 | Average price |
| `avg_rating` | DECIMAL(3,2) | NO | 0 | Average rating |
| `refreshed_at` | TIMESTAMP | YES | — | Last refresh |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[total_sales, avg_rating]`

### `query_performance_logs`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | NO | — | Query name |
| `duration_ms` | UNSIGNED INT | NO | — | Execution time (ms) |
| `rows_returned` | UNSIGNED INT | NO | 0 | Result count |
| `cache_hit` | BOOLEAN | NO | false | Cache hit flag |
| `context` | JSON | YES | — | Query context |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[name, duration_ms]`

### `search_indexing_queue`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `book_id` | FK → books.id | NO | CASCADE | Book to index |
| `status` | VARCHAR | NO | `pending` | pending/processing/completed/failed |
| `attempts` | UNSIGNED TINYINT | NO | 0 | Retry count |
| `failure_message` | TEXT | YES | — | Error message |
| `indexed_at` | TIMESTAMP | YES | — | Index timestamp |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

**Indexes:** `[status, created_at]`

### `book_shards` (Database Sharding)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `shard_key` | VARCHAR UNIQUE | NO | — | Shard identifier |
| `connection` | VARCHAR | NO | — | Database connection |
| `range_start` | UNSIGNED BIGINT | YES | — | ID range start |
| `range_end` | UNSIGNED BIGINT | YES | — | ID range end |
| `is_active` | BOOLEAN | NO | true | Shard active |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |

---

## Laravel Infrastructure Tables

### `cache`

| Column | Type | Nullable |
|--------|------|----------|
| `key` | VARCHAR PK | NO |
| `value` | MEDIUMTEXT | NO |
| `expiration` | INT | NO |

### `cache_locks`

| Column | Type | Nullable |
|--------|------|----------|
| `key` | VARCHAR PK | NO |
| `owner` | VARCHAR | NO |
| `expiration` | INT | NO |

### `jobs`

| Column | Type | Nullable |
|--------|------|----------|
| `id` | BIGINT PK | NO |
| `queue` | VARCHAR | NO |
| `payload` | LONGTEXT | NO |
| `attempts` | UNSIGNED TINYINT | NO |
| `reserved_at` | UNSIGNED INT | YES |
| `available_at` | UNSIGNED INT | NO |
| `created_at` | UNSIGNED INT | NO |

### `job_batches`

| Column | Type | Nullable |
|--------|------|----------|
| `id` | VARCHAR PK | NO |
| `name` | VARCHAR | NO |
| `total_jobs` | INT | NO |
| `pending_jobs` | INT | NO |
| `failed_jobs` | INT | NO |
| `failed_job_ids` | LONGTEXT | NO |
| `options` | MEDIUMTEXT | YES |
| `cancelled_at` | INT | YES |
| `created_at` | INT | NO |
| `finished_at` | INT | YES |

### `failed_jobs`

| Column | Type | Nullable |
|--------|------|----------|
| `id` | BIGINT PK | NO |
| `uuid` | VARCHAR UNIQUE | NO |
| `connection` | TEXT | NO |
| `queue` | TEXT | NO |
| `payload` | LONGTEXT | NO |
| `exception` | LONGTEXT | NO |
| `failed_at` | TIMESTAMP | NO |

---

## Index Reference

### Composite Indexes

| Table | Index Name | Columns | Purpose |
|-------|-----------|---------|---------|
| `bookings` | — | `[status, preferred_date]` | Booking listing/sorting |
| `inventories` | — | `[category_id, status]` | Inventory filtering |
| `borrow_requests` | — | `[employee_id, status]` | Employee borrow listing |
| `reports` | — | `[type, report_date]` | Report queries |
| `borrow_items` | — | `[borrow_request_id, inventory_id]` | Borrow item lookup |
| `books` | `books_catalog_cover_idx` | `[is_active, category_id, price, id]` | Catalog cover queries |
| `books` | `books_category_filter_idx` | `[category_id, is_active, stock, price]` | Category filtering |
| `books` | `books_active_created_idx` | `[is_active, created_at, id]` | Recent books |
| `books` | `books_sales_rating_idx` | `[sales_count, rating]` | Bestseller/popularity sort |

### Fulltext Indexes

| Table | Index Name | Columns |
|-------|-----------|---------|
| `books` | `books_fulltext_idx` | `title, author, description` |

---

## SQL Dump Reference

### Create Core Tables

```sql
-- Users
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    mfa_secret VARCHAR(255) NULL,
    mfa_type VARCHAR(255) NOT NULL DEFAULT 'email',
    mfa_verified_at TIMESTAMP NULL,
    mfa_recovery_codes JSON NULL,
    remember_token VARCHAR(100) NULL,
    phone VARCHAR(255) NULL,
    address TEXT NULL,
    avatar_path VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    lock_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Bookings
CREATE TABLE bookings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    contact_number VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    purpose_category VARCHAR(255) NOT NULL,
    reason TEXT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    employee_id BIGINT NULL,
    remarks TEXT NULL,
    attachment_path VARCHAR(255) NULL,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    lock_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX (status, preferred_date),
    INDEX (reference_number),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Inventory Categories
CREATE TABLE inventory_categories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    icon VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Suppliers
CREATE TABLE suppliers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    address TEXT NULL,
    city VARCHAR(255) NULL,
    country VARCHAR(255) NOT NULL DEFAULT 'Philippines',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Inventories
CREATE TABLE inventories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category_id BIGINT NOT NULL,
    supplier_id BIGINT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(255) NOT NULL DEFAULT 'pcs',
    unit_cost DECIMAL(10,2) NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'available',
    condition VARCHAR(255) NOT NULL DEFAULT 'good',
    location VARCHAR(255) NULL,
    image_path VARCHAR(255) NULL,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    date_added DATE NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    lock_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX (category_id, status),
    INDEX (item_code),
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Borrow Requests
CREATE TABLE borrow_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(255) UNIQUE NOT NULL,
    employee_id BIGINT NOT NULL,
    approved_by BIGINT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    reason TEXT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    penalty_notes TEXT NULL,
    admin_remarks TEXT NULL,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    borrowed_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    lock_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX (employee_id, status),
    INDEX (request_number),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Borrow Items
CREATE TABLE borrow_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    borrow_request_id BIGINT NOT NULL,
    inventory_id BIGINT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    condition_borrowed VARCHAR(255) NOT NULL DEFAULT 'good',
    condition_returned VARCHAR(255) NULL,
    is_returned BOOLEAN NOT NULL DEFAULT FALSE,
    returned_at TIMESTAMP NULL,
    damage_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (borrow_request_id, inventory_id),
    FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE
);

-- Notifications
CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NULL,
    message TEXT NULL,
    link VARCHAR(255) NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (user_id, is_read),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reports
CREATE TABLE reports (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    report_number VARCHAR(255) UNIQUE NOT NULL,
    generated_by BIGINT NOT NULL,
    type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT NULL,
    data JSON NULL,
    file_path VARCHAR(255) NULL,
    file_format VARCHAR(255) NOT NULL DEFAULT 'pdf',
    report_date DATE NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_ai_generated BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (type, report_date),
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### Create Platform Tables

```sql
-- Audit Logs (Tamper-Evident)
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    event VARCHAR(255) NOT NULL,
    auditable_type VARCHAR(255) NULL,
    auditable_id BIGINT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(255) NULL,
    user_agent TEXT NULL,
    url VARCHAR(255) NULL,
    checksum VARCHAR(64) NOT NULL,
    previous_checksum VARCHAR(64) NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (event, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Import Logs
CREATE TABLE import_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'inventory',
    file_name VARCHAR(255) NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    total_rows INT UNSIGNED NOT NULL DEFAULT 0,
    processed_rows INT UNSIGNED NOT NULL DEFAULT 0,
    successful_rows INT UNSIGNED NOT NULL DEFAULT 0,
    failed_rows INT UNSIGNED NOT NULL DEFAULT 0,
    duplicate_strategy VARCHAR(255) NOT NULL DEFAULT 'skip',
    errors JSON NULL,
    error_report_path VARCHAR(255) NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (type, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Export Logs
CREATE TABLE export_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    type VARCHAR(255) NOT NULL,
    format VARCHAR(255) NOT NULL DEFAULT 'xlsx',
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    filters JSON NULL,
    columns JSON NULL,
    record_count INT UNSIGNED NOT NULL DEFAULT 0,
    file_path VARCHAR(255) NULL,
    failure_message TEXT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (type, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings
CREATE TABLE settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'string',
    `group` VARCHAR(255) NOT NULL DEFAULT 'general',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Login History
CREATE TABLE login_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    logged_in_at TIMESTAMP NULL,
    logged_out_at TIMESTAMP NULL,
    session_id VARCHAR(255) NULL,
    is_impersonation BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (logged_in_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notification Preferences
CREATE TABLE notification_preferences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    type VARCHAR(255) NOT NULL,
    email_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    in_app_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE (user_id, type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Scheduled Tasks
CREATE TABLE scheduled_tasks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    command VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'started',
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    duration_ms INT UNSIGNED NULL,
    output TEXT NULL,
    failure_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (command, status)
);

-- API Rate Limits
CREATE TABLE api_rate_limits (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    tier VARCHAR(255) NOT NULL DEFAULT 'public',
    `key` VARCHAR(255) NOT NULL,
    ip_address VARCHAR(255) NULL,
    limit_per_minute INT UNSIGNED NOT NULL,
    remaining INT UNSIGNED NOT NULL,
    blocked BOOLEAN NOT NULL DEFAULT FALSE,
    reset_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (`key`, reset_at),
    INDEX (tier, blocked),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Backup Monitoring
CREATE TABLE backup_monitoring (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    disk VARCHAR(255) NOT NULL DEFAULT 'local',
    status VARCHAR(255) NOT NULL DEFAULT 'unknown',
    file_path VARCHAR(255) NULL,
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    checksum VARCHAR(64) NULL,
    message TEXT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (disk, status)
);
```

### Create Books Tables

```sql
-- Books
CREATE TABLE books (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(17) UNIQUE NOT NULL,
    isbn13 VARCHAR(13) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category_id BIGINT NOT NULL,
    author VARCHAR(255) NOT NULL,
    publisher VARCHAR(255) NULL,
    format VARCHAR(32) NOT NULL DEFAULT 'paperback',
    price DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    sales_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    rating DECIMAL(3,2) NOT NULL DEFAULT 0,
    description TEXT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    published_at DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX books_catalog_cover_idx (is_active, category_id, price, id),
    INDEX books_category_filter_idx (category_id, is_active, stock, price),
    INDEX books_active_created_idx (is_active, created_at, id),
    INDEX books_sales_rating_idx (sales_count, rating),
    INDEX (published_at),
    FULLTEXT INDEX books_fulltext_idx (title, author, description),
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE
);

-- Bestseller Stats (Materialized View)
CREATE TABLE mv_bestseller_stats (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNIQUE NOT NULL,
    book_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    total_stock BIGINT UNSIGNED NOT NULL DEFAULT 0,
    total_sales BIGINT UNSIGNED NOT NULL DEFAULT 0,
    avg_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0,
    refreshed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (total_sales, avg_rating),
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE
);

-- Query Performance Logs
CREATE TABLE query_performance_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    duration_ms INT UNSIGNED NOT NULL,
    rows_returned INT UNSIGNED NOT NULL DEFAULT 0,
    cache_hit BOOLEAN NOT NULL DEFAULT FALSE,
    context JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (name, duration_ms)
);

-- Search Indexing Queue
CREATE TABLE search_indexing_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    book_id BIGINT NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    failure_message TEXT NULL,
    indexed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (status, created_at),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Book Shards
CREATE TABLE book_shards (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    shard_key VARCHAR(255) UNIQUE NOT NULL,
    connection VARCHAR(255) NOT NULL,
    range_start BIGINT UNSIGNED NULL,
    range_end BIGINT UNSIGNED NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## Relationship Summary

| Parent | Relationship | Child | Foreign Key |
|--------|-------------|-------|-------------|
| `users` | HasMany | `bookings` | `employee_id` |
| `users` | HasMany | `borrow_requests` | `employee_id` |
| `users` | HasMany | `borrow_requests` | `approved_by` |
| `users` | HasMany | `reports` | `generated_by` |
| `users` | HasMany | `notifications` | `user_id` |
| `users` | HasMany | `notification_preferences` | `user_id` |
| `users` | HasMany | `login_history` | `user_id` |
| `inventory_categories` | HasMany | `inventories` | `category_id` |
| `inventory_categories` | HasMany | `books` | `category_id` |
| `suppliers` | HasMany | `inventories` | `supplier_id` |
| `borrow_requests` | HasMany | `borrow_items` | `borrow_request_id` |
| `inventories` | HasMany | `borrow_items` | `inventory_id` |

### Polymorphic (Morph) Relationships

| Table | Morph Field | Related To |
|-------|-------------|-------------|
| `model_has_permissions` | `model_type`+`model_id` | Any model (typically `User`) |
| `model_has_roles` | `model_type`+`model_id` | Any model (typically `User`) |
| `activity_log` | `subject_type`+`subject_id` | Any model |
| `activity_log` | `causer_type`+`causer_id` | Any model (typically `User`) |
| `audit_logs` | `auditable_type`+`auditable_id` | Any model |
