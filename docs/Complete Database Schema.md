# Complete Database Schema

**Engine:** MySQL 8.4 / MariaDB 10.4+ (UTF-8 `utf8mb4`, `utf8mb4_unicode_ci`)

**Source of truth:** the Laravel migrations in `Backend/database/migrations/`.
Everything below was read from the live schema after `php artisan migrate`.

**Note:** Supabase, PostgreSQL, PL/pgSQL and the separate `mobile-service` have
all been removed. The Flutter app is served by the Laravel REST API.

Generated tables: 38

## Object inventory

| Table | Rows (seed) |
|---|---|
| `activity_log` | 20 |
| `api_rate_limits` | 0 |
| `audit_logs` | 37 |
| `backup_monitoring` | 0 |
| `bookings` | 6 |
| `borrow_items` | 8 |
| `borrow_requests` | 7 |
| `borrow_return_evidences` | 0 |
| `cache` | 0 |
| `cache_locks` | 0 |
| `chatbot_interactions` | 0 |
| `data_migration_mappings` | 0 |
| `export_logs` | 0 |
| `failed_jobs` | 0 |
| `import_logs` | 0 |
| `inventories` | 13 |
| `inventory_categories` | 5 |
| `job_batches` | 0 |
| `jobs` | 0 |
| `login_history` | 4 |
| `migrations` | 35 |
| `mobile_borrow_idempotency` | 3 |
| `mobile_recent_request_dismissals` | 0 |
| `mobile_return_idempotency` | 1 |
| `model_has_permissions` | 0 |
| `model_has_roles` | 5 |
| `notification_preferences` | 0 |
| `notifications` | 6 |
| `password_reset_tokens` | 0 |
| `permissions` | 34 |
| `personal_access_tokens` | 3 |
| `reports` | 0 |
| `role_has_permissions` | 39 |
| `roles` | 2 |
| `scheduled_tasks` | 0 |
| `sessions` | 0 |
| `settings` | 0 |
| `users` | 5 |

## Columns by table

### `activity_log`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `log_name` | varchar(255) | YES | MUL | NULL |
| `description` | text | NO |  |  |
| `subject_type` | varchar(255) | YES | MUL | NULL |
| `event` | varchar(255) | YES |  | NULL |
| `subject_id` | bigint(20) unsigned | YES |  | NULL |
| `causer_type` | varchar(255) | YES | MUL | NULL |
| `causer_id` | bigint(20) unsigned | YES |  | NULL |
| `properties` | longtext | YES |  | NULL |
| `batch_uuid` | char(36) | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `api_rate_limits`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `tier` | varchar(255) | NO | MUL | 'public' |
| `key` | varchar(255) | NO | MUL |  |
| `ip_address` | varchar(255) | YES |  | NULL |
| `limit_per_minute` | int(10) unsigned | NO |  |  |
| `remaining` | int(10) unsigned | NO |  |  |
| `blocked` | tinyint(1) | NO |  | 0 |
| `reset_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `audit_logs`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `event` | varchar(255) | NO | MUL |  |
| `auditable_type` | varchar(255) | YES | MUL | NULL |
| `auditable_id` | bigint(20) unsigned | YES |  | NULL |
| `old_values` | longtext | YES |  | NULL |
| `new_values` | longtext | YES |  | NULL |
| `ip_address` | varchar(255) | YES |  | NULL |
| `user_agent` | text | YES |  | NULL |
| `url` | varchar(255) | YES |  | NULL |
| `checksum` | varchar(64) | NO |  |  |
| `previous_checksum` | varchar(64) | YES |  | NULL |
| `archived_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `backup_monitoring`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `disk` | varchar(255) | NO | MUL | 'local' |
| `status` | varchar(255) | NO |  | 'unknown' |
| `file_path` | varchar(255) | YES |  | NULL |
| `size_bytes` | bigint(20) unsigned | NO |  | 0 |
| `checksum` | varchar(64) | YES |  | NULL |
| `message` | text | YES |  | NULL |
| `started_at` | timestamp | YES |  | NULL |
| `completed_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `bookings`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `reference_number` | varchar(255) | NO | UNI |  |
| `full_name` | varchar(255) | NO |  |  |
| `email` | varchar(255) | NO |  |  |
| `contact_number` | varchar(255) | NO |  |  |
| `address` | text | NO |  |  |
| `preferred_date` | date | NO |  |  |
| `preferred_time` | time | NO |  |  |
| `purpose_category` | varchar(255) | NO |  |  |
| `reason` | text | YES |  | NULL |
| `status` | varchar(255) | NO | MUL | 'pending' |
| `employee_id` | bigint(20) unsigned | YES | MUL | NULL |
| `remarks` | text | YES |  | NULL |
| `attachment_path` | varchar(255) | YES |  | NULL |
| `approved_at` | timestamp | YES |  | NULL |
| `rejected_at` | timestamp | YES |  | NULL |
| `completed_at` | timestamp | YES |  | NULL |
| `cancelled_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |
| `deleted_at` | timestamp | YES |  | NULL |
| `lock_version` | int(10) unsigned | NO |  | 1 |

Foreign keys:

- `employee_id` -> `users`.`id`

### `borrow_items`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `borrow_request_id` | bigint(20) unsigned | NO | MUL |  |
| `inventory_id` | bigint(20) unsigned | NO | MUL |  |
| `quantity` | int(11) | NO |  | 1 |
| `reserved_quantity` | int(10) unsigned | NO |  | 0 |
| `return_evidence_id` | bigint(20) unsigned | YES | MUL | NULL |
| `condition_borrowed` | varchar(255) | NO |  | 'good' |
| `condition_returned` | varchar(255) | YES |  | NULL |
| `is_returned` | tinyint(1) | NO |  | 0 |
| `returned_at` | timestamp | YES |  | NULL |
| `damage_notes` | text | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `borrow_request_id` -> `borrow_requests`.`id`
- `inventory_id` -> `inventories`.`id`
- `return_evidence_id` -> `borrow_return_evidences`.`id`

### `borrow_requests`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `request_number` | varchar(255) | NO | UNI |  |
| `employee_id` | bigint(20) unsigned | NO | MUL |  |
| `approved_by` | bigint(20) unsigned | YES | MUL | NULL |
| `status` | varchar(255) | NO |  | 'pending' |
| `reason` | text | NO |  |  |
| `borrow_date` | date | NO |  |  |
| `due_date` | date | NO |  |  |
| `return_date` | date | YES |  | NULL |
| `return_location` | varchar(255) | YES |  | NULL |
| `admin_remarks` | text | YES |  | NULL |
| `approved_at` | timestamp | YES |  | NULL |
| `rejected_at` | timestamp | YES |  | NULL |
| `cancelled_at` | timestamp | YES |  | NULL |
| `borrowed_at` | timestamp | YES |  | NULL |
| `returned_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |
| `deleted_at` | timestamp | YES |  | NULL |
| `lock_version` | int(10) unsigned | NO |  | 1 |

Foreign keys:

- `approved_by` -> `users`.`id`
- `employee_id` -> `users`.`id`

### `borrow_return_evidences`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `borrow_request_id` | bigint(20) unsigned | NO | MUL |  |
| `idempotency_key` | varchar(255) | NO | UNI |  |
| `drive_file_id` | varchar(255) | YES |  | NULL |
| `file_name` | varchar(255) | NO |  |  |
| `mime_type` | varchar(255) | YES |  | NULL |
| `uploaded_by` | bigint(20) unsigned | YES | MUL | NULL |
| `uploaded_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `borrow_request_id` -> `borrow_requests`.`id`
- `uploaded_by` -> `users`.`id`

### `cache`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `key` | varchar(255) | NO | PRI |  |
| `value` | mediumtext | NO |  |  |
| `expiration` | int(11) | NO | MUL |  |

### `cache_locks`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `key` | varchar(255) | NO | PRI |  |
| `owner` | varchar(255) | NO |  |  |
| `expiration` | int(11) | NO | MUL |  |

### `chatbot_interactions`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `question_hash` | char(64) | NO |  |  |
| `page_type` | varchar(32) | NO | MUL |  |
| `source_refs` | longtext | YES |  | NULL |
| `response` | text | YES |  | NULL |
| `model_name` | varchar(100) | YES |  | NULL |
| `response_time_ms` | int(10) unsigned | NO |  | 0 |
| `outcome` | varchar(32) | NO |  |  |
| `created_at` | timestamp | NO |  | current_timestamp() |

Foreign keys:

- `user_id` -> `users`.`id`

### `data_migration_mappings`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `source_fingerprint` | varchar(64) | NO | MUL |  |
| `source_table` | varchar(255) | NO |  |  |
| `source_key` | varchar(191) | NO |  |  |
| `target_key` | text | NO |  |  |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `export_logs`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `type` | varchar(255) | NO | MUL |  |
| `format` | varchar(255) | NO |  | 'xlsx' |
| `status` | varchar(255) | NO |  | 'pending' |
| `filters` | longtext | YES |  | NULL |
| `columns` | longtext | YES |  | NULL |
| `record_count` | int(10) unsigned | NO |  | 0 |
| `file_path` | varchar(255) | YES |  | NULL |
| `failure_message` | text | YES |  | NULL |
| `started_at` | timestamp | YES |  | NULL |
| `completed_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `failed_jobs`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `uuid` | varchar(255) | NO | UNI |  |
| `connection` | text | NO |  |  |
| `queue` | text | NO |  |  |
| `payload` | longtext | NO |  |  |
| `exception` | longtext | NO |  |  |
| `failed_at` | timestamp | NO |  | current_timestamp() |

### `import_logs`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `type` | varchar(255) | NO | MUL | 'inventory' |
| `file_name` | varchar(255) | YES |  | NULL |
| `status` | varchar(255) | NO |  | 'pending' |
| `total_rows` | int(10) unsigned | NO |  | 0 |
| `processed_rows` | int(10) unsigned | NO |  | 0 |
| `successful_rows` | int(10) unsigned | NO |  | 0 |
| `failed_rows` | int(10) unsigned | NO |  | 0 |
| `duplicate_strategy` | varchar(255) | NO |  | 'skip' |
| `errors` | longtext | YES |  | NULL |
| `error_report_path` | varchar(255) | YES |  | NULL |
| `started_at` | timestamp | YES |  | NULL |
| `completed_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `inventories`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `item_code` | varchar(255) | NO | UNI |  |
| `qr_token` | varchar(255) | YES | UNI | NULL |
| `name` | varchar(255) | NO |  |  |
| `brand` | varchar(255) | YES |  | NULL |
| `description` | text | YES |  | NULL |
| `category_id` | bigint(20) unsigned | NO | MUL |  |
| `quantity` | int(11) | NO |  | 0 |
| `unit` | varchar(255) | NO |  | 'pcs' |
| `unit_cost` | decimal(10,2) | YES |  | NULL |
| `status` | varchar(255) | NO |  | 'available' |
| `condition` | varchar(255) | NO |  | 'good' |
| `location` | varchar(255) | YES |  | NULL |
| `image_path` | varchar(255) | YES |  | NULL |
| `low_stock_threshold` | int(11) | NO |  | 5 |
| `date_added` | date | NO |  |  |
| `is_active` | tinyint(1) | NO |  | 1 |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |
| `deleted_at` | timestamp | YES |  | NULL |
| `lock_version` | int(10) unsigned | NO |  | 1 |

Foreign keys:

- `category_id` -> `inventory_categories`.`id`

### `inventory_categories`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `name` | varchar(255) | NO |  |  |
| `slug` | varchar(255) | NO | UNI |  |
| `description` | text | YES |  | NULL |
| `icon` | varchar(255) | YES |  | NULL |
| `is_active` | tinyint(1) | NO |  | 1 |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |
| `deleted_at` | timestamp | YES |  | NULL |

### `job_batches`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | varchar(255) | NO | PRI |  |
| `name` | varchar(255) | NO |  |  |
| `total_jobs` | int(11) | NO |  |  |
| `pending_jobs` | int(11) | NO |  |  |
| `failed_jobs` | int(11) | NO |  |  |
| `failed_job_ids` | longtext | NO |  |  |
| `options` | mediumtext | YES |  | NULL |
| `cancelled_at` | int(11) | YES |  | NULL |
| `created_at` | int(11) | NO |  |  |
| `finished_at` | int(11) | YES |  | NULL |

### `jobs`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `queue` | varchar(255) | NO | MUL |  |
| `payload` | longtext | NO |  |  |
| `attempts` | tinyint(3) unsigned | NO |  |  |
| `reserved_at` | int(10) unsigned | YES |  | NULL |
| `available_at` | int(10) unsigned | NO |  |  |
| `created_at` | int(10) unsigned | NO |  |  |

### `login_history`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | NO | MUL |  |
| `ip_address` | varchar(45) | YES |  | NULL |
| `user_agent` | text | YES |  | NULL |
| `logged_in_at` | timestamp | YES | MUL | NULL |
| `logged_out_at` | timestamp | YES |  | NULL |
| `session_id` | varchar(255) | YES |  | NULL |
| `is_impersonation` | tinyint(1) | NO |  | 0 |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `migrations`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | int(10) unsigned | NO | PRI |  |
| `migration` | varchar(255) | NO |  |  |
| `batch` | int(11) | NO |  |  |

### `mobile_borrow_idempotency`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `employee_id` | bigint(20) unsigned | NO | PRI |  |
| `idempotency_key` | varchar(64) | NO | PRI |  |
| `request_hash` | char(64) | NO |  |  |
| `response_payload` | longtext | YES |  | NULL |
| `created_at` | timestamp | NO |  | current_timestamp() |

Foreign keys:

- `employee_id` -> `users`.`id`

### `mobile_recent_request_dismissals`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `employee_id` | bigint(20) unsigned | NO | PRI |  |
| `borrow_request_id` | bigint(20) unsigned | NO | PRI |  |
| `dismissed_at` | timestamp | NO |  | current_timestamp() |

Foreign keys:

- `borrow_request_id` -> `borrow_requests`.`id`
- `employee_id` -> `users`.`id`

### `mobile_return_idempotency`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `employee_id` | bigint(20) unsigned | NO | PRI |  |
| `borrow_request_id` | bigint(20) unsigned | NO | PRI |  |
| `idempotency_key` | varchar(64) | NO | PRI |  |
| `request_payload` | longtext | NO |  |  |
| `response_payload` | longtext | NO |  |  |
| `created_at` | timestamp | NO |  | current_timestamp() |

Foreign keys:

- `borrow_request_id` -> `borrow_requests`.`id`
- `employee_id` -> `users`.`id`

### `model_has_permissions`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `permission_id` | bigint(20) unsigned | NO | PRI |  |
| `model_type` | varchar(255) | NO | PRI |  |
| `model_id` | bigint(20) unsigned | NO | PRI |  |

Foreign keys:

- `permission_id` -> `permissions`.`id`

### `model_has_roles`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `role_id` | bigint(20) unsigned | NO | PRI |  |
| `model_type` | varchar(255) | NO | PRI |  |
| `model_id` | bigint(20) unsigned | NO | PRI |  |

Foreign keys:

- `role_id` -> `roles`.`id`

### `notification_preferences`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | NO | MUL |  |
| `type` | varchar(255) | NO |  |  |
| `email_enabled` | tinyint(1) | NO |  | 1 |
| `in_app_enabled` | tinyint(1) | NO |  | 1 |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `notifications`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `type` | varchar(255) | NO |  |  |
| `title` | varchar(255) | YES |  | NULL |
| `message` | text | YES |  | NULL |
| `link` | varchar(255) | YES |  | NULL |
| `is_read` | tinyint(1) | NO |  | 0 |
| `read_at` | timestamp | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `user_id` -> `users`.`id`

### `password_reset_tokens`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `email` | varchar(255) | NO | PRI |  |
| `token` | varchar(255) | NO |  |  |
| `created_at` | timestamp | YES |  | NULL |

### `permissions`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `name` | varchar(255) | NO | MUL |  |
| `guard_name` | varchar(255) | NO |  |  |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `personal_access_tokens`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `tokenable_type` | varchar(255) | NO | MUL |  |
| `tokenable_id` | bigint(20) unsigned | NO |  |  |
| `name` | text | NO |  |  |
| `token` | varchar(64) | NO | UNI |  |
| `abilities` | text | YES |  | NULL |
| `last_used_at` | timestamp | YES |  | NULL |
| `expires_at` | timestamp | YES | MUL | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `reports`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `report_number` | varchar(255) | NO | UNI |  |
| `generated_by` | bigint(20) unsigned | NO | MUL |  |
| `type` | varchar(255) | NO | MUL |  |
| `title` | varchar(255) | NO |  |  |
| `summary` | text | YES |  | NULL |
| `data` | longtext | YES |  | NULL |
| `file_path` | varchar(255) | YES |  | NULL |
| `file_format` | varchar(255) | NO |  | 'pdf' |
| `report_date` | date | NO |  |  |
| `start_date` | date | YES |  | NULL |
| `end_date` | date | YES |  | NULL |
| `is_ai_generated` | tinyint(1) | NO |  | 0 |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

Foreign keys:

- `generated_by` -> `users`.`id`

### `role_has_permissions`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `permission_id` | bigint(20) unsigned | NO | PRI |  |
| `role_id` | bigint(20) unsigned | NO | PRI |  |

Foreign keys:

- `permission_id` -> `permissions`.`id`
- `role_id` -> `roles`.`id`

### `roles`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `name` | varchar(255) | NO | MUL |  |
| `guard_name` | varchar(255) | NO |  |  |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `scheduled_tasks`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `command` | varchar(255) | NO | MUL |  |
| `status` | varchar(255) | NO |  | 'started' |
| `started_at` | timestamp | YES |  | NULL |
| `finished_at` | timestamp | YES |  | NULL |
| `duration_ms` | int(10) unsigned | YES |  | NULL |
| `output` | text | YES |  | NULL |
| `failure_message` | text | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `sessions`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | varchar(255) | NO | PRI |  |
| `user_id` | bigint(20) unsigned | YES | MUL | NULL |
| `ip_address` | varchar(45) | YES |  | NULL |
| `user_agent` | text | YES |  | NULL |
| `payload` | longtext | NO |  |  |
| `last_activity` | int(11) | NO | MUL |  |

### `settings`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `key` | varchar(255) | NO | UNI |  |
| `value` | text | YES |  | NULL |
| `type` | varchar(255) | NO |  | 'string' |
| `group` | varchar(255) | NO |  | 'general' |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |

### `users`

| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| `id` | bigint(20) unsigned | NO | PRI |  |
| `name` | varchar(255) | NO |  |  |
| `email` | varchar(255) | NO | UNI |  |
| `phone` | varchar(255) | YES |  | NULL |
| `address` | text | YES |  | NULL |
| `avatar_path` | varchar(255) | YES |  | NULL |
| `is_active` | tinyint(1) | NO |  | 1 |
| `last_login_at` | timestamp | YES |  | NULL |
| `email_verified_at` | timestamp | YES |  | NULL |
| `password` | varchar(255) | NO |  |  |
| `mfa_enabled` | tinyint(1) | NO |  | 0 |
| `mfa_secret` | varchar(255) | YES |  | NULL |
| `mfa_type` | varchar(255) | NO |  | 'email' |
| `mfa_verified_at` | timestamp | YES |  | NULL |
| `mfa_recovery_codes` | longtext | YES |  | NULL |
| `remember_token` | varchar(100) | YES |  | NULL |
| `created_at` | timestamp | YES |  | NULL |
| `updated_at` | timestamp | YES |  | NULL |
| `lock_version` | int(10) unsigned | NO |  | 1 |

## Integrity rules

- Primary keys on every table; foreign keys enforce referential integrity.
- `inventories.item_code` and `inventories.qr_token` are unique.
- Soft deletes (`deleted_at`) on `inventories`, `borrow_requests`, `bookings`
  and `inventory_categories`.
- The audit chain is serialised with a MySQL named lock (`get_lock`) so
  checksums cannot fork under concurrent writes.
- `mobile_*` tables hold idempotency keys and dashboard dismissals for the app.
