# RGV-System Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Core Modules & Features](#core-modules--features)
5. [Database Schema](#database-schema)
6. [Models & Relationships](#models--relationships)
7. [Key Controllers & Routes](#key-controllers--routes)
8. [Notifications System](#notifications-system)
9. [File Structure](#file-structure)
10. [Installation & Setup](#installation--setup)
11. [User Roles & Permissions](#user-roles--permissions)
12. [Key Features](#key-features)

---

## Project Overview

**RGV-System** is a comprehensive Laravel-based web application designed to manage bookings, inventory, and borrowing requests. The system provides role-based access control with separate dashboards for administrators and employees, along with public-facing booking functionality.

### Primary Functions:
- **Booking Management**: Users can create booking requests, track status, and receive notifications
- **Inventory Management**: Track items, categories, suppliers, and stock levels with QR code support
- **Borrow Request System**: Manage borrowing and returning of items with approval workflows
- **Reporting & Analytics**: Generate comprehensive reports on bookings, inventory, and user activities
- **User Management**: Manage users with role-based access control
- **AI Integration**: Chatbot and AI-powered report generation using OpenAI
- **Notifications**: Email and system notifications for important events
- **Activity Logging**: Track all system activities for audit purposes

---

## Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **Language**: PHP 8.2+
- **Database**: SQL (configurable - MySQL/PostgreSQL)
- **ORM**: Eloquent ORM
- **Queue System**: Laravel Queues
- **Authentication**: Laravel Breeze

### Frontend
- **Build Tool**: Vite 7.0.7
- **CSS Framework**: Tailwind CSS 3.1.0
- **JavaScript**: Alpine.js 3.4.2
- **HTTP Client**: Axios 1.11.0
- **Form Styling**: Tailwind CSS Forms

### Additional Libraries & Tools
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel
- **QR Code Generation**: simplesoftwareio/simple-qrcode
- **Activity Logging**: spatie/laravel-activitylog
- **Backup Management**: spatie/laravel-backup
- **Role-Based Access**: spatie/laravel-permission
- **AI Integration**: openai-php/client
- **HTTP Requests**: guzzlehttp/guzzle
- **Testing**: PHPUnit 11.5.50
- **Code Quality**: Laravel Pint

---

## System Architecture

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Public Interface                          │
│        (Booking Form, Tracking, Chatbot)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
   ┌────▼────┐    ┌─────▼─────┐   ┌─────▼─────┐
   │  Admin  │    │ Employee  │   │ Public    │
   │ Portal  │    │ Portal    │   │ API       │
   └────┬────┘    └─────┬─────┘   └─────┬─────┘
        │                │                │
        └────────────────┼────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
   ┌────▼──────┐  ┌─────▼──────┐  ┌─────▼──────┐
   │  Models   │  │ Controllers│  │ Services   │
   │ (Eloquent)│  │ (HTTP)     │  │ (Business) │
   └────┬──────┘  └─────┬──────┘  └─────┬──────┘
        │                │                │
        └────────────────┼────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
   ┌────▼────┐   ┌─────▼─────┐   ┌─────▼─────┐
   │ Database│   │ Cache     │   │Notifications
   │         │   │           │   │ (Queue)   │
   └─────────┘   └───────────┘   └───────────┘
```

### Request Flow
1. **User Request** → Routes Handler
2. **Route Matching** → Controller Dispatch
3. **Authentication & Authorization** → Middleware
4. **Business Logic** → Controller Methods
5. **Data Processing** → Models & Eloquent ORM
6. **Database Operation** → Query Execution
7. **Response Generation** → View/JSON
8. **Events/Jobs** → Notification Queue
9. **Background Processing** → Email/Notifications

---

## Core Modules & Features

### 1. Booking Management Module
**Purpose**: Handle booking requests, approvals, and lifecycle management

**Features**:
- Create booking requests from public interface
- Track booking status in real-time
- Admin approval/rejection workflows
- Employee assignment to bookings
- Booking cancellation
- Booking completion
- Calendar view for scheduled bookings
- PDF export of booking details

**Key Workflows**:
- Public User → Create Booking → Admin Review → Approve/Reject
- Approved Booking → Assign to Employee → Complete
- Track Status → Receive Notifications

---

### 2. Inventory Management Module
**Purpose**: Manage physical items and stock levels

**Features**:
- Create and manage inventory items
- Categorize items
- Track supplier information
- Monitor stock levels
- Low stock alerts
- QR code generation for items
- Inventory reports and analytics
- Item details and specifications

**Key Workflows**:
- Create Item → Add to Category → Set Stock Level
- Monitor Stock → Alert on Low Stock → Create Purchase Order
- Generate QR Codes → Scan for Tracking

---

### 3. Borrow Request System
**Purpose**: Manage borrowing and returning of items

**Features**:
- Create borrow requests for inventory items
- Admin approval/rejection workflow
- Track borrowed items
- Manage return requests
- History of all borrow transactions
- User borrowing history

**Key Workflows**:
- Employee → Request to Borrow Item → Admin Approval
- Approved → Item Marked as Borrowed
- Return Request → Admin Process → Item Returned to Stock

---

### 4. Report & Analytics Module
**Purpose**: Generate insights and reports on system activities

**Features**:
- Booking reports (status, trends, patterns)
- Inventory reports (stock levels, usage, categories)
- Borrow request analytics
- User activity reports
- AI-powered report generation
- Data export to Excel/PDF
- Custom date range filtering

**Report Types**:
- Booking Performance Report
- Inventory Status Report
- Borrow Request Summary
- User Activity Report
- Stock Movement Analysis

---

### 5. User Management Module
**Purpose**: Manage system users and access control

**Features**:
- User registration and authentication
- Role-based access control (Admin, Employee)
- User status management (Active/Inactive)
- User profile management
- Password management
- User activity tracking
- Permission management

**User Roles**:
- **Admin**: Full system access, user management, approvals
- **Employee**: Limited access, can create requests, view own items
- **Public**: Limited to booking and tracking functions

---

### 6. Notification System
**Purpose**: Keep users informed of important system events

**Types of Notifications**:
- Booking-related: Approved, Rejected, Assigned, Completed, Cancelled
- Borrow-related: Approved, Rejected, Borrowed, Returned
- Stock Alerts: Low stock warnings
- User Updates: Status changes
- New Requests: New booking/borrow requests (to admins)

**Delivery Channels**:
- Email notifications
- In-app system notifications
- Real-time updates (if configured)

---

### 7. AI Integration
**Purpose**: Enhance system with artificial intelligence capabilities

**AI Features**:
- **Chatbot**: Answers user queries about bookings, inventory, and system usage
- **Report Generation**: AI-powered insights and recommendations
- **Intelligent Suggestions**: AI-based recommendations for inventory management

**Implementation**:
- OpenAI API integration
- Real-time query processing
- Context-aware responses

---

## Database Schema

### Core Tables

#### `users` Table
Stores user account information and authentication details

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| name | string | User full name |
| email | string | Unique email address |
| password | string | Hashed password |
| phone | string | Contact number |
| role | enum | admin/employee/public |
| status | enum | active/inactive |
| created_at | timestamp | Account creation date |
| updated_at | timestamp | Last update date |

#### `bookings` Table
Stores booking request information

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| reference | string | Unique booking reference |
| user_id | bigint | Foreign key to users |
| title | string | Booking title |
| description | text | Booking details |
| start_date | datetime | Booking start time |
| end_date | datetime | Booking end time |
| status | enum | pending/approved/rejected/completed/cancelled |
| assigned_to | bigint | Employee assigned (nullable) |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

#### `inventories` Table
Stores inventory item information

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| name | string | Item name |
| description | text | Item details |
| category_id | bigint | Foreign key to categories |
| supplier_id | bigint | Foreign key to suppliers |
| quantity | integer | Current stock quantity |
| min_quantity | integer | Minimum stock level |
| unit_price | decimal | Price per unit |
| status | enum | active/inactive |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

#### `inventory_categories` Table
Categorizes inventory items

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| name | string | Category name |
| description | text | Category details |
| created_at | timestamp | Creation date |

#### `suppliers` Table
Stores supplier information

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| name | string | Supplier name |
| email | string | Contact email |
| phone | string | Contact phone |
| address | text | Physical address |
| city | string | City |
| created_at | timestamp | Creation date |

#### `borrow_requests` Table
Stores borrow request information

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| user_id | bigint | Foreign key to users |
| status | enum | pending/approved/rejected/borrowed/returned |
| requested_date | datetime | Request creation date |
| borrow_date | datetime | Actual borrow date (nullable) |
| return_date | datetime | Expected return date |
| actual_return_date | datetime | Actual return date (nullable) |
| created_at | timestamp | Creation date |

#### `borrow_items` Table
Stores individual items in a borrow request

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| borrow_request_id | bigint | Foreign key to borrow_requests |
| inventory_id | bigint | Foreign key to inventories |
| quantity | integer | Quantity borrowed |
| created_at | timestamp | Creation date |

#### `reports` Table
Stores generated reports

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| user_id | bigint | Foreign key to users |
| title | string | Report title |
| type | enum | bookings/inventory/borrow/users |
| content | json | Report data |
| created_at | timestamp | Generation date |

#### `system_notifications` Table
Stores in-app notifications

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| user_id | bigint | Foreign key to users |
| title | string | Notification title |
| message | text | Notification message |
| type | string | Notification type |
| is_read | boolean | Read status |
| created_at | timestamp | Creation date |

#### `activity_log` Table (Spatie)
Logs all system activities for audit trail

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary Key |
| log_name | string | Log category |
| description | string | Activity description |
| subject_type | string | Model class |
| subject_id | bigint | Model ID |
| causer_type | string | User class |
| causer_id | bigint | User ID |
| properties | json | Additional data |
| created_at | timestamp | Activity date |

---

## Models & Relationships

### Model Hierarchy

#### User Model
```
User
├── hasMany(Booking)
├── hasMany(BorrowRequest)
├── hasMany(Report)
└── hasMany(SystemNotification)
```

**Key Methods**:
- `hasRole($role)`: Check user role
- `getNotifications()`: Retrieve user notifications
- `getActivityLog()`: Get user activity history

---

#### Booking Model
```
Booking
├── belongsTo(User) - creator
├── belongsTo(User, 'assigned_to') - assigned employee
└── hasMany(Activity) - through activity log
```

**Key Methods**:
- `approve()`: Approve booking
- `reject()`: Reject booking
- `complete()`: Mark as completed
- `cancel()`: Cancel booking
- `assignEmployee($employee)`: Assign to employee
- `sendNotification()`: Notify relevant users

**Status Flow**:
`pending` → `approved`/`rejected` → `completed`/`cancelled`

---

#### Inventory Model
```
Inventory
├── belongsTo(InventoryCategory)
├── belongsTo(Supplier)
├── hasMany(BorrowItem)
└── hasMany(Activity)
```

**Key Methods**:
- `isLowStock()`: Check if stock is below minimum
- `updateStock($quantity)`: Update stock level
- `generateQrCode()`: Create QR code for item
- `getSupplier()`: Get supplier details

---

#### InventoryCategory Model
```
InventoryCategory
└── hasMany(Inventory)
```

---

#### Supplier Model
```
Supplier
└── hasMany(Inventory)
```

---

#### BorrowRequest Model
```
BorrowRequest
├── belongsTo(User)
├── hasMany(BorrowItem)
└── hasMany(Activity)
```

**Key Methods**:
- `approve()`: Approve request
- `reject()`: Reject request
- `markAsBorrowed()`: Update status when items collected
- `markAsReturned()`: Process return

**Status Flow**:
`pending` → `approved`/`rejected` → `borrowed` → `returned`

---

#### BorrowItem Model
```
BorrowItem
├── belongsTo(BorrowRequest)
└── belongsTo(Inventory)
```

---

#### Report Model
```
Report
└── belongsTo(User)
```

**Key Methods**:
- `getBookingReport()`: Generate booking data
- `getInventoryReport()`: Generate inventory data
- `getBorrowReport()`: Generate borrow data
- `getUserReport()`: Generate user activity data

---

#### SystemNotification Model
```
SystemNotification
└── belongsTo(User)
```

**Key Methods**:
- `markAsRead()`: Update read status
- `delete()`: Remove notification

---

## Key Controllers & Routes

### Admin Routes (`/admin`)

#### Bookings Controller
```
GET    /admin/bookings                    → index (list all bookings)
GET    /admin/bookings/{booking}          → show (booking details)
POST   /admin/bookings/{booking}/approve  → approve (approve booking)
POST   /admin/bookings/{booking}/reject   → reject (reject booking)
POST   /admin/bookings/{booking}/complete → complete (mark complete)
POST   /admin/bookings/{booking}/cancel   → cancel (cancel booking)
POST   /admin/bookings/{booking}/assign   → assignEmployee (assign to employee)
GET    /admin/bookings/calendar           → calendar (view calendar)
```

#### Inventory Controller
```
GET    /admin/inventories                  → index (list items)
GET    /admin/inventories/create           → create (new item form)
POST   /admin/inventories                  → store (save item)
GET    /admin/inventories/{inventory}      → show (item details)
GET    /admin/inventories/{inventory}/edit → edit (edit form)
PUT    /admin/inventories/{inventory}      → update (save changes)
DELETE /admin/inventories/{inventory}      → destroy (delete item)
GET    /admin/inventories/{inventory}/qrcode → generateQrCode (QR code)
GET    /admin/inventories/low-stock        → lowStock (low stock items)
```

#### Borrow Requests Controller
```
GET    /admin/borrow-requests/{borrowRequest}        → show (request details)
POST   /admin/borrow-requests/{borrowRequest}/approve → approve (approve)
POST   /admin/borrow-requests/{borrowRequest}/reject  → reject (reject)
GET    /admin/borrow-requests                         → index (list all)
```

#### Users Controller
```
GET  /admin/users              → index (list users)
GET  /admin/users/{user}       → show (user details)
POST /admin/users/{user}/toggle-status → toggleStatus (enable/disable)
```

#### Reports Controller
```
GET /admin/reports                 → index (reports dashboard)
GET /admin/reports/bookings        → bookings (booking report)
GET /admin/reports/inventory       → inventory (inventory report)
GET /admin/reports/borrow-requests → borrowRequests (borrow report)
GET /admin/reports/users           → users (user activity report)
```

#### Notifications Controller
```
GET    /admin/notifications                 → index (list notifications)
POST   /admin/notifications/{id}/mark-read  → markAsRead (mark read)
POST   /admin/notifications/mark-all-read   → markAllAsRead (mark all read)
DELETE /admin/notifications/{id}            → destroy (delete notification)
```

---

### Additional Admin Routes\n\n#### Backup Management\n```\nGET    /admin/backups                    → index (backup listing)\nPOST   /admin/backups/run                → run (manual backup)\nPOST   /admin/backups/{backup}/verify    → verify (checksum check)\nGET    /admin/backups/{backup}/download  → download (backup file)\nPOST   /admin/backups/clear-all          → clearAll (clear records)\nGET    /admin/backups/settings           → settings (backup config)\nPUT    /admin/backups/settings           → updateSettings (save config)\n```\n\n#### Site Settings\n```\nGET    /admin/settings         → index (7 tabs: Branding, Email, Security, Backup, Notifications, Maintenance, API)\nPOST   /admin/settings         → update (save settings by section)\n```\n\n#### Import/Export\n```\nGET    /admin/import-export                          → index\nPOST   /admin/import-export/inventory/import         → importInventory\nGET    /admin/import-export/inventory/export         → exportInventory\nGET    /admin/import-export/bookings/export          → exportBookings\nGET    /admin/import-export/users/export             → exportUsers\nGET    /admin/import-export/exports/{log}/download   → downloadExport\n```\n\n#### Trash Management\n```\nGET    /admin/trash                        → index (trashed records)\nPOST   /admin/trash/{type}/{id}/restore    → restore (recover)\nDELETE /admin/trash/{type}/{id}/force-delete → forceDelete (permanent)\n```\n\n#### Impersonation\n```\nPOST   /admin/users/{user}/impersonate   → start (impersonate)\nPOST   /admin/impersonation/stop         → stop (end impersonation)\n```\n\n#### Audit Logs\n```\nGET    /admin/audit-logs          → index\nGET    /admin/audit-logs/export   → export\nGET    /admin/audit-logs/{log}    → show\n```\n\n### Additional Employee Routes\n\n#### Borrow Request Actions\n```\nPOST   /employee/borrow-requests/{request}/mark-borrowed  → markBorrowed\nPOST   /employee/borrow-requests/{request}/return          → returnItem\nDELETE /employee/borrow-requests/{request}                 → destroy (soft-delete)\n```\n\n---\n\n$mark### Employee Routes (`/employee`)

#### Dashboard
```
GET /employee/dashboard → index (employee dashboard)
```

#### Borrow Requests
```
GET    /employee/borrow-requests           → index (my requests)
GET    /employee/borrow-requests/create    → create (new request form)
POST   /employee/borrow-requests           → store (create request)
GET    /employee/borrow-requests/{request} → show (request details)
```

#### Inventory
```
GET /employee/inventories → index (view available items)
```

#### Notifications
```
GET    /employee/notifications                 → index (notifications)
POST   /employee/notifications/{id}/mark-read  → markAsRead (mark read)
DELETE /employee/notifications/{id}            → destroy (delete)
```

---

### Public Routes

```
GET  /                            → PublicController@index (homepage)
GET  /booking                     → PublicController@bookingForm (booking form)
POST /booking                     → PublicController@storeBooking (create booking)
GET  /booking/track/{reference}   → PublicController@trackBooking (track booking)
POST /booking/search              → PublicController@searchBooking (search booking)
```

---

### API Routes

```
POST /api/chatbot/query → ChatbotController@query (chatbot interaction)
```

---

### Authentication Routes (`/auth`)

Handled by Laravel Breeze:
```
GET    /login              → Login form
POST   /login              → Process login
GET    /register           → Registration form
POST   /register           → Process registration
POST   /logout             → Process logout
GET    /forgot-password    → Forgot password form
POST   /forgot-password    → Send reset email
GET    /reset-password     → Reset password form
POST   /reset-password     → Update password
```

---

### Profile Routes

```
GET    /profile        → ProfileController@edit (edit profile)
PATCH  /profile        → ProfileController@update (update profile)
DELETE /profile        → ProfileController@destroy (delete account)
```

---

## Notifications System

### Notification Types

#### BookingApproved
**Triggered**: When admin approves a booking
**Recipients**: Booking creator, assigned employee
**Content**: Booking details and start date

#### BookingRejected
**Triggered**: When admin rejects a booking
**Recipients**: Booking creator
**Content**: Rejection reason

#### BookingAssigned
**Triggered**: When booking assigned to employee
**Recipients**: Assigned employee
**Content**: Booking details and instructions

#### BookingCompleted
**Triggered**: When booking is marked complete
**Recipients**: Booking creator
**Content**: Completion confirmation

#### BookingCancelled
**Triggered**: When booking is cancelled
**Recipients**: Booking creator, assigned employee
**Content**: Cancellation details

#### BorrowRequestApproved
**Triggered**: When admin approves a borrow request
**Recipients**: Requesting employee
**Content**: Approval confirmation

#### BorrowRequestRejected
**Triggered**: When admin rejects a borrow request
**Recipients**: Requesting employee
**Content**: Rejection reason

#### BorrowRequestBorrowed
**Triggered**: When borrowed items are collected
**Recipients**: Requesting employee
**Content**: Items received confirmation

#### BorrowRequestReturned
**Triggered**: When borrowed items are returned
**Recipients**: Requesting employee
**Content**: Return confirmation

#### LowStockAlert
**Triggered**: When inventory stock falls below minimum
**Recipients**: Admin
**Content**: Item details and current stock level

#### NewBookingReceived
**Triggered**: New booking created by public user
**Recipients**: Admin
**Content**: Booking details for review

#### NewBorrowRequestReceived
**Triggered**: New borrow request created
**Recipients**: Admin
**Content**: Request details for review

#### UserStatusChanged
**Triggered**: When user status is changed
**Recipients**: User
**Content**: Status change notification

---

## File Structure

```
rgv-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── BookingController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   ├── BorrowRequestController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── NotificationController.php
│   │   │   ├── Employee/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── BorrowRequestController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   └── NotificationController.php
│   │   │   ├── Controller.php
│   │   │   ├── ProfileController.php
│   │   │   ├── PublicController.php
│   │   │   ├── ChatbotController.php
│   │   │   ├── AiReportController.php
│   │   │   └── PdfExportController.php
│   │   ├── Middleware/
│   │   │   └── [Middleware classes]
│   │   └── Requests/
│   │       └── [Form Request classes]
│   ├── Models/
│   │   ├── User.php
│   │   ├── Booking.php
│   │   ├── BorrowRequest.php
│   │   ├── BorrowItem.php
│   │   ├── Inventory.php
│   │   ├── InventoryCategory.php
│   │   ├── Supplier.php
│   │   ├── Report.php
│   │   └── SystemNotification.php
│   ├── Notifications/
│   │   ├── BookingApproved.php
│   │   ├── BookingRejected.php
│   │   ├── BookingAssigned.php
│   │   ├── BookingCompleted.php
│   │   ├── BookingCancelled.php
│   │   ├── BorrowRequestApproved.php
│   │   ├── BorrowRequestRejected.php
│   │   ├── BorrowRequestBorrowed.php
│   │   ├── BorrowRequestReturned.php
│   │   ├── LowStockAlert.php
│   │   ├── NewBookingReceived.php
│   │   ├── NewBorrowRequestReceived.php
│   │   └── UserStatusChanged.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── View/
│       └── Components/
├── bootstrap/
│   ├── app.php
│   ├── providers.php
│   └── cache/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── backup.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── permission.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   ├── BookingFactory.php
│   │   ├── BorrowItemFactory.php
│   │   ├── BorrowRequestFactory.php
│   │   ├── InventoryFactory.php
│   │   ├── SupplierFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_05_09_154841_create_bookings_table.php
│   │   ├── 2026_05_09_154842_create_inventory_categories_table.php
│   │   ├── 2026_05_09_154842_create_inventories_table.php
│   │   ├── 2026_05_09_154842_create_suppliers_table.php
│   │   ├── 2026_05_09_155549_create_borrow_requests_table.php
│   │   ├── 2026_05_09_155553_create_borrow_items_table.php
│   │   ├── 2026_05_09_155558_create_notifications_table.php
│   │   ├── 2026_05_09_155600_create_reports_table.php
│   │   ├── 2026_05_09_155621_add_role_field_to_users_table.php
│   │   ├── 2026_05_09_155634_create_permission_tables.php
│   │   ├── 2026_05_09_155634_create_activity_log_table.php
│   │   ├── 2026_05_09_155635_add_event_column_to_activity_log_table.php
│   │   ├── 2026_05_09_155636_add_batch_uuid_column_to_activity_log_table.php
│   │   └── 2026_05_11_150511_make_notification_title_message_nullable.php
│   └── seeders/
│       ├── BookingSeeder.php
│       └── [Other seeders]
├── public/
│   ├── index.php
│   ├── robots.txt
│   └── build/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       └── [Blade templates]
├── routes/
│   ├── auth.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── TestCase.php
│   ├── Feature/
│   └── Unit/
├── vendor/
│   └── [Composer dependencies]
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── postcss.config.js
├── tailwind.config.js
├── vite.config.js
├── .env.example
├── .gitignore
└── README.md
```

---

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- Database server (MySQL/PostgreSQL)

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd rgv-system
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Edit `.env` file with your database credentials:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rgv_system
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_DRIVER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

OPENAI_API_KEY=your_openai_api_key
```

### Step 4: Database Setup
```bash
# Run migrations
php artisan migrate

# Run seeders (optional)
php artisan db:seed
```

### Step 5: Build Frontend Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 6: Start the Application
```bash
# Using Artisan
php artisan serve

# Application will be available at http://localhost:8000
```

## Gemini AI Integration

Add your Gemini API key to the project `.env` as follows:

GEMINI_API_KEY=your_api_key_here

A simple service and controller are provided at `app/Services/GeminiService.php` and `app/Http/Controllers/GeminiController.php`.

Route: `POST /ask-gemini` expects JSON `{ "prompt": "your question" }` and returns the generated text.

Use Google AI Studio to create an API key and set it in `.env` before making requests.
### Quick Setup Command
Run the automated setup script in composer.json:
```bash
composer run-script setup
```

---

## User Roles & Permissions

### Admin Role
**Full system access with responsibilities:**
- View and manage all bookings
- Approve/reject booking requests
- Assign bookings to employees
- Manage inventory items
- Monitor stock levels
- Approve/reject borrow requests
- View all reports
- Manage users
- View activity logs
- Send notifications

**Access**: `/admin/*`

---

### Employee Role
**Limited access for operational tasks:**
- View dashboard
- Create borrow requests
- View own borrow request history
- View available inventory
- View own notifications
- Update own profile

**Access**: `/employee/*`

---

### Public User Role
**Limited access for booking only:**
- View homepage
- Create booking requests
- Track booking status
- Use chatbot
- View own notifications

**Access**: Public routes and API

---

## Key Features

### 1. Real-time Booking System
- Instant booking creation and status tracking
- Email notifications on status changes
- Automated email reminders
- Calendar view for scheduled bookings
- PDF export of booking details

### 2. Smart Inventory Management
- Automatic low stock alerts
- QR code generation for items
- Supplier integration
- Category-based organization
- Stock movement tracking

### 3. Advanced Borrow Management
- Multi-item borrow requests
- Approval workflow
- Automatic return reminders
- Borrow history tracking
- Return status notifications

### 4. Comprehensive Reporting
- Multiple report types
- Custom date range filtering
- Excel/PDF export
- AI-powered insights
- Trend analysis

### 5. User Management
- Role-based access control
- User status management
- Profile management
- Activity audit trail
- Permission management

### 6. Notification System
- Email notifications
- In-app system notifications
- Real-time updates
- Customizable notification types
- Notification history

### 7. AI Integration
- Chatbot for user queries
- AI-powered report generation
- Intelligent recommendations
- Natural language processing

### 8. Security & Compliance
- Laravel Breeze authentication
- Password hashing
- Role-based authorization
- CSRF protection
- SQL injection prevention
- Activity logging for audit trail
- Data backup support

### 9. Performance Features
- Database query optimization
- Caching layer support
- Queue system for notifications
- Job scheduling
- Asset minification

### 10. Scalability
- Modular architecture
- RESTful API structure
- Database-agnostic ORM
- Horizontal scaling support
- Load balancing ready

---

## API Endpoints Reference

### Chatbot API
```
POST /api/chatbot/query
Content-Type: application/json

Request Body:
{
  "query": "Can I book a room?",
  "context": "booking" (optional)
}

Response:
{
  "success": true,
  "message": "Response from chatbot",
  "data": {}
}
```

---

## Development Best Practices

### Code Organization
- Keep controllers thin
- Use models for business logic
- Utilize services for complex operations
- Follow Laravel naming conventions
- Use requests for validation

### Database
- Use migrations for schema changes
- Use factories for testing data
- Index frequently queried columns
- Use transactions for data consistency

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookingTest.php

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Run code formatting
./vendor/bin/pint

# Run static analysis
php artisan tinker
```

---

## Troubleshooting

### Common Issues

#### Database Connection Error
**Solution**: Verify `.env` file has correct database credentials and database exists

#### Migration Errors
```bash
# Reset database
php artisan migrate:fresh

# Run specific migration
php artisan migrate --path=database/migrations/xxxx_xx_xx_xxxxxx_migration.php
```

#### Permission Denied Errors
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache

# Fix log permissions
chmod -R 777 storage/logs
```

#### npm Build Errors
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules
npm install
```

---

## Support & Resources

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Vite Documentation](https://vitejs.dev/guide/)
- [Alpine.js Documentation](https://alpinejs.dev/)

### Useful Commands

```bash
# Create new model with migration
php artisan make:model ModelName -m

# Create new controller
php artisan make:controller ControllerName

# Create new migration
php artisan make:migration create_table_name

# Create new notification
php artisan make:notification NotificationName

# Run queue
php artisan queue:work

# Cache clear
php artisan cache:clear

# Config cache
php artisan config:cache

# Optimize
php artisan optimize
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-05-11 | Initial release |

---

## License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## Contact & Support

For questions, issues, or support, please contact the development team or create an issue in the repository.

---

*Last Updated: May 18, 2026*
*Documentation Generated for RGV-System Laravel Application*
