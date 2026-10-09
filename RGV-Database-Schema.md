# RGV Database Schema

> **RGV Multi-Tech Services** — Complete Database Schema Reference
>
> Tables: 33 | Migrations: 33 | Models: 17 | ORM: Laravel Eloquent | Last Updated: October 2, 2026

---

## Table of Contents

1. [Entity Relationship Diagram](#entity-relationship-diagram)
2. [Table Groups Overview](#table-groups-overview)
3. [Core Business Tables](#core-business-tables)
4. [Auth & User Tables](#auth--user-tables)
5. [Permissions & Roles (Spatie)](#permissions--roles-spatie)
6. [Platform & Operational Tables](#platform--operational-tables)
7. [Laravel Infrastructure Tables](#laravel-infrastructure-tables)
8. [Index Reference](#index-reference)
9. [SQL Dump Reference](#sql-dump-reference)

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
                         │ inventory  │
                         │_categories │
                         │ id, name,  │
                         │ slug       │
                         └────────────┘

    ┌──────────────────────────────────────────────────────────────────┐
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
| **Auth / User Core** | `users`, `password_reset_tokens`, `sessions`, `login_history`, `personal_access_tokens` | Web sessions, legacy Sanctum tokens, login tracking |
| **Permissions** | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | Spatie RBAC |
| **Activity Logging** | `activity_log`, `audit_logs` | Spatie activitylog + custom tamper-evident audit |
| **Core Business** | `bookings`, `inventories`, `inventory_categories`, `borrow_requests`, `borrow_items`, `borrow_return_evidences`, `notifications`, `reports` | Main business domain; evidence rows are retained for historical returns |
| **Mobile** | `mobile_return_idempotency` | Deduplicates retryable Supabase-native return RPCs |
| **Platform Ops** | `import_logs`, `export_logs`, `scheduled_tasks`, `api_rate_limits`, `backup_monitoring`, `settings`, `notification_preferences` | System operations |
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
| `attachment_path` | VARCHAR | YES | — | Legacy nullable path; current booking flows do not upload or display attachments |
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
| `qr_token` | VARCHAR UNIQUE | YES | — | Server-generated, immutable QR lookup token (backfilled nullable; the app always generates one) |
| `name` | VARCHAR | NO | — | Item name |
| `description` | TEXT | YES | — | Item description |
| `category_id` | FK → inventory_categories.id | NO | CASCADE | Item category |
| `quantity` | INT | NO | 0 | Current stock quantity |
| `unit` | VARCHAR | NO | `pcs` | Unit of measurement |
| `unit_cost` | DECIMAL(10,2) | YES | — | Cost per unit |
| `status` | VARCHAR | NO | `available` | Manual classification: available/borrowed/maintenance/damaged; independent of quantity |
| `condition` | VARCHAR | NO | `good` | new/good/fair/poor |
| `location` | VARCHAR | YES | — | Storage location |
| `image_path` | VARCHAR | YES | — | Legacy nullable path; current inventory flows do not upload or display images |
| `low_stock_threshold` | INT | NO | 5 | Low stock warning threshold |
| `date_added` | DATE | NO | — | Date added to inventory |
| `is_active` | BOOLEAN | NO | true | Active status |
| `lock_version` | UNSIGNED INT | NO | 1 | Optimistic locking |
| `created_at` | TIMESTAMP | NO | — | — |
| `updated_at` | TIMESTAMP | NO | — | — |
| `deleted_at` | TIMESTAMP | YES | — | Soft delete |

**Indexes:** `[category_id, status]`, `item_code`

Borrow eligibility is derived from `is_active = true`, `status = 'available'`,
and `quantity > 0`; `status` is not automatically changed when stock is
reserved or returned. Mobile inventory search and QR lookup return this
availability separately from the stored status.

> **Deployment drift (October 2026):** the live Supabase project runs a
> newer revision of `mobile_inventory_lookup` / `mobile_inventory_search`
> (applied by the deployed `2026_10_09_000001_remove_mobile_inventory_image_payloads`
> migration, not present in this snapshot) that omits `image_path` from the
> RPC payloads. The scripts in this repository still include it; the Flutter
> models treat `image_path` as nullable and work with either revision.

**Relationships:**
- `category()` → BelongsTo → `InventoryCategory`
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

---

### `borrow_requests`

Employee requests to borrow inventory items.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `request_number` | VARCHAR UNIQUE | NO | `BR-{uniqid}` | Auto-generated reference |
| `employee_id` | FK → users.id | NO | CASCADE | Requesting employee |
| `approved_by` | FK → users.id | YES | SET NULL | Approving admin |
| `status` | VARCHAR | NO | `pending` | pending/approved/rejected/borrowed/returned |
| `reason` | TEXT | NO | — | Reason for borrowing |
| `borrow_date` | DATE | NO | — | Date borrowed |
| `due_date` | DATE | NO | — | Expected return date |
| `return_date` | DATE | YES | — | Actual return date |
| `return_location` | VARCHAR | YES | — | Location where items were returned |
| `admin_remarks` | TEXT | YES | — | Admin notes |
| `approved_at` | TIMESTAMP | YES | — | Approval timestamp |
| `rejected_at` | TIMESTAMP | YES | — | Rejection timestamp |
| `cancelled_at` | TIMESTAMP | YES | — | Cancellation timestamp |
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
pending → cancelled
```

Overdue indicators are computed from a borrowed request's due date; overdue is
not a persisted status and penalty notes are not stored.

---

### `borrow_items`

Pivot table linking borrow requests to inventory items, with additional tracking fields.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `borrow_request_id` | FK → borrow_requests.id | NO | CASCADE | Parent borrow request |
| `inventory_id` | FK → inventories.id | NO | CASCADE | Borrowed item |
| `quantity` | INT | NO | 1 | Quantity borrowed |
| `reserved_quantity` | UNSIGNED INT | NO | 0 | Quantity reserved while request is pending |
| `return_evidence_id` | FK → borrow_return_evidences.id | YES | SET NULL | Legacy return-evidence record; current native flow does not upload photos |
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
- `returnEvidence()` → BelongsTo → `BorrowReturnEvidence`

### `borrow_return_evidences`

Legacy return-evidence metadata retained for backward compatibility. The current
mobile return workflow does not upload photos to Google Drive and does not rely
on `drive_file_id` for normal processing. The `mobile_request_payload` RPC
surfaces `return_evidence` (`file_name`, `uploaded_at`) for returned items,
so evidence attached through the website is visible to the native app.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT PK | NO | AUTO_INCREMENT | Primary key |
| `borrow_request_id` | FK → borrow_requests.id | NO | CASCADE | Parent request |
| `idempotency_key` | VARCHAR UNIQUE | NO | — | Prevent duplicate processing on retry |
| `drive_file_id` | VARCHAR | YES | — | Legacy external-file reference retained for historical records; not used by the current workflow |
| `file_name` | VARCHAR | NO | — | Stored evidence filename |
| `mime_type` | VARCHAR | YES | — | Uploaded file MIME type |
| `uploaded_by` | FK → users.id | YES | SET NULL | Employee who uploaded the evidence |
| `uploaded_at` | TIMESTAMP | YES | — | Upload timestamp |
| `created_at` | TIMESTAMP | YES | — | — |
| `updated_at` | TIMESTAMP | YES | — | — |

**Indexes:** Unique `idempotency_key`, `[borrow_request_id, uploaded_at]`

**Relationships:**
- `borrowRequest()` → BelongsTo → `BorrowRequest`
- `uploadedBy()` → BelongsTo → `User`

### `mobile_return_idempotency`

Persists successful Supabase-native return RPC requests and responses. The
composite key is employee + borrow request + idempotency key. A retry with the
same payload replays its original response; reusing the key with different
details is rejected.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `employee_id` | FK → users.id | NO | — | Authenticated employee |
| `borrow_request_id` | FK → borrow_requests.id | NO | — | Returned request |
| `idempotency_key` | VARCHAR(64) | NO | — | Client-generated retry key |
| `request_payload` | JSONB | NO | — | Return location and submitted items |
| `response_payload` | JSONB | NO | — | Original successful RPC response |
| `created_at` | TIMESTAMP WITH TIME ZONE | NO | current time | Successful submission time |

**Primary key:** `[employee_id, borrow_request_id, idempotency_key]`

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
| `supabase_auth_id` | UUID UNIQUE | YES | — | Confirmed Supabase Auth identity linked to this employee |
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

### `personal_access_tokens` (Laravel Sanctum)

Bearer tokens for the **legacy Laravel mobile API** used by external
clients. The current Flutter app authenticates through Supabase Auth
and does not use these tokens. Tokens are hashed in storage; the
plaintext token is returned only when issued.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | BIGINT PK | NO | Primary key |
| `tokenable_type` | VARCHAR | NO | Model type for polymorphic owner |
| `tokenable_id` | BIGINT | NO | User identifier |
| `name` | TEXT | NO | Device/token name |
| `token` | VARCHAR(64) UNIQUE | NO | Hashed bearer token |
| `abilities` | TEXT | YES | Token abilities |
| `last_used_at` | TIMESTAMP | YES | Last authenticated use |
| `expires_at` | TIMESTAMP | YES | Optional expiry |
| `created_at` | TIMESTAMP | YES | — |
| `updated_at` | TIMESTAMP | YES | — |

**Indexes:** Unique `token`; `expires_at`

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
| `borrow_return_evidences` | — | `[borrow_request_id, uploaded_at]` | Legacy evidence history |
| `inventories` | — | `qr_token` | Unique immutable QR lookup |

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

-- Sanctum tokens for mobile API authentication
CREATE TABLE personal_access_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (tokenable_type, tokenable_id),
    INDEX (expires_at)
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

-- Inventories
CREATE TABLE inventories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(255) UNIQUE NOT NULL,
    qr_token VARCHAR(40) UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category_id BIGINT NOT NULL,
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
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE
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
    return_location VARCHAR(255) NULL,
    admin_remarks TEXT NULL,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
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

-- Legacy return-evidence metadata retained for historical compatibility.
-- The current return workflow does not use Google Drive uploads.
CREATE TABLE borrow_return_evidences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    borrow_request_id BIGINT NOT NULL,
    idempotency_key VARCHAR(255) UNIQUE NOT NULL,
    drive_file_id VARCHAR(255) NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NULL,
    uploaded_by BIGINT NULL,
    uploaded_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (borrow_request_id, uploaded_at),
    FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Borrow Items
CREATE TABLE borrow_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    borrow_request_id BIGINT NOT NULL,
    inventory_id BIGINT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    reserved_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    return_evidence_id BIGINT NULL,
    condition_borrowed VARCHAR(255) NOT NULL DEFAULT 'good',
    condition_returned VARCHAR(255) NULL,
    is_returned BOOLEAN NOT NULL DEFAULT FALSE,
    returned_at TIMESTAMP NULL,
    damage_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (borrow_request_id, inventory_id),
    FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE,
    FOREIGN KEY (return_evidence_id) REFERENCES borrow_return_evidences(id) ON DELETE SET NULL
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
