<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\BorrowRequestController as AdminBorrowRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Employee\NotificationController as EmployeeNotificationController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\BookingController as EmployeeBookingController;
use App\Http\Controllers\Employee\BorrowRequestController;
use App\Http\Controllers\Employee\InventoryController as EmployeeInventoryController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\AiReportController;
use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public Routes
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/booking', [PublicController::class, 'bookingForm'])->name('booking.form');
Route::post('/booking', [PublicController::class, 'storeBooking'])->name('booking.store');
Route::get('/booking/track/{reference}', [PublicController::class, 'trackBooking'])->name('booking.track');
Route::post('/booking/search', [PublicController::class, 'searchBooking'])->name('booking.search');

// Chatbot API Route
Route::post('/api/chatbot/query', [ChatbotController::class, 'query'])->name('chatbot.query');
Route::post('/ask-gemini', [GeminiController::class, 'ask'])->name('gemini.ask');

// Auth Routes (from Laravel Breeze)
require __DIR__.'/auth.php';

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    if ($user && $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user && $user->hasRole('employee')) {
        return redirect()->route('employee.dashboard');
    }
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/export-personal-data', [ProfileController::class, 'exportPersonalData'])->name('profile.export-personal-data');
    Route::get('/profile/export-order-history', [ProfileController::class, 'exportOrderHistory'])->name('profile.export-order-history');
    Route::post('/profile/mfa/enable', [ProfileController::class, 'enableMfa'])->name('profile.mfa.enable');
    Route::post('/profile/mfa/disable', [ProfileController::class, 'disableMfa'])->name('profile.mfa.disable');
    Route::get('/profile/totp-code', [ProfileController::class, 'totpCode'])->name('profile.totp-code');
    Route::get('/profile/notification-preferences', [ProfileController::class, 'notificationPreferences'])->name('profile.notification-preferences');
    Route::put('/profile/notification-preferences', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notification-preferences.update');
});

Route::post('/session/extend', function () {
    session()->put('last_activity', time());
    return response()->json(['status' => 'ok']);
})->middleware('auth')->name('session.extend');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboard::class, 'stats'])->name('dashboard.stats');
    
    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/assign', [BookingController::class, 'assignEmployee'])->name('bookings.assign');
    
    // Inventory
    Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
    Route::get('/inventories/create', [InventoryController::class, 'create'])->name('inventories.create');
    Route::post('/inventories', [InventoryController::class, 'store'])->name('inventories.store');
    Route::get('/inventories/low-stock', [InventoryController::class, 'lowStock'])->name('inventories.low-stock');
    Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->name('inventories.show');
    Route::get('/inventories/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventories.edit');
    Route::put('/inventories/{inventory}', [InventoryController::class, 'update'])->name('inventories.update');
    Route::delete('/inventories/{inventory}', [InventoryController::class, 'destroy'])->name('inventories.destroy');
    Route::get('/inventories/{inventory}/qrcode', [InventoryController::class, 'generateQrCode'])->name('inventories.qrcode');
    Route::get('/inventories/import/template', [InventoryController::class, 'importTemplate'])->name('inventories.import-template');
    Route::post('/inventories/import', [InventoryController::class, 'import'])->name('inventories.import');
    
    // Borrow Requests
    Route::get('/borrow-requests', [AdminBorrowRequestController::class, 'index'])->name('borrow-requests.index');
    Route::get('/borrow-requests/{borrowRequest}', [AdminBorrowRequestController::class, 'show'])->name('borrow-requests.show');
    Route::post('/borrow-requests/{borrowRequest}/approve', [AdminBorrowRequestController::class, 'approve'])->name('borrow-requests.approve');
    Route::post('/borrow-requests/{borrowRequest}/reject', [AdminBorrowRequestController::class, 'reject'])->name('borrow-requests.reject');
    Route::post('/borrow-requests/{borrowRequest}/mark-borrowed', [AdminBorrowRequestController::class, 'markBorrowed'])->name('borrow-requests.mark-borrowed');
    Route::post('/borrow-requests/{borrowRequest}/return', [AdminBorrowRequestController::class, 'returnItems'])->name('borrow-returns.return');
    
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/analytics', [UserController::class, 'analytics'])->name('users.analytics');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');
    Route::post('/users/{user}/force-logout', [UserController::class, 'forceLogout'])->name('users.force-logout');
    Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/borrow-requests', [ReportController::class, 'borrowRequests'])->name('reports.borrow-requests');
    Route::get('/reports/users', [ReportController::class, 'users'])->name('reports.users');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    
    // PDF Exports
    Route::get('/export/bookings', [PdfExportController::class, 'exportBookings'])->name('export.bookings');
    Route::get('/export/inventory', [PdfExportController::class, 'exportInventory'])->name('export.inventory');
    Route::get('/export/booking/{booking}', [PdfExportController::class, 'exportBooking'])->name('export.booking');

    // Import / Export Operations
    Route::get('/import-export', [ImportExportController::class, 'index'])->name('import-export.index');
    Route::post('/import-export/inventory/import', [ImportExportController::class, 'importInventory'])->name('import-export.inventory.import');
    Route::post('/import-export/users/import', [ImportExportController::class, 'importUsers'])->name('import-export.users.import');
    Route::get('/import-export/imports/{importLog}', [ImportExportController::class, 'importStatus'])->name('import-export.imports.status');
    Route::get('/import-export/imports/{importLog}/errors', [ImportExportController::class, 'downloadImportErrors'])->name('import-export.imports.errors');
    Route::get('/import-export/inventory/export', [ImportExportController::class, 'exportInventory'])->name('import-export.inventory.export');
    Route::get('/import-export/bookings/export', [ImportExportController::class, 'exportBookings'])->name('import-export.bookings.export');
    Route::get('/import-export/users/export', [ImportExportController::class, 'exportUsers'])->name('import-export.users.export');
    Route::get('/import-export/exports/{exportLog}/download', [ImportExportController::class, 'downloadExport'])->name('import-export.exports.download');
    Route::get('/bookings/{booking}/invoice', [ImportExportController::class, 'customerInvoice'])->name('bookings.invoice');

    // Audit and Backups
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit.export');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');
    Route::post('/audit-logs/clear-all', [AuditLogController::class, 'clearAll'])->name('audit.clear-all');
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/run', [BackupController::class, 'run'])->name('backups.run');
    Route::post('/backups/{backup}/verify', [BackupController::class, 'verify'])->name('backups.verify');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/clear-all', [BackupController::class, 'clearAll'])->name('backups.clear-all');
    Route::get('/backups/settings', [BackupController::class, 'settings'])->name('backups.settings');
    Route::put('/backups/settings', [BackupController::class, 'updateSettings'])->name('backups.settings.update');

    // Settings
    Route::get('/system-health', fn () => view('admin.system-health'))->name('system-health');
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{type}/{id}/force-delete', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
    
    // AI Reports
    Route::get('/ai/insights', [AiReportController::class, 'generateInsights'])->name('ai.insights');
    Route::get('/ai/forecast', [AiReportController::class, 'generateBookingForecast'])->name('ai.forecast');
    Route::get('/ai/inventory-recommendations', [AiReportController::class, 'generateInventoryRecommendations'])->name('ai.inventory-recommendations');
});

// Employee Routes
Route::middleware(['auth', 'employee', 'mfa'])->prefix('employee')->name('employee.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');

    // Assigned Work
    Route::get('/bookings', [EmployeeBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [EmployeeBookingController::class, 'show'])->name('bookings.show');
    
    // Borrow Requests
    Route::get('/borrow-requests', [BorrowRequestController::class, 'index'])->name('borrow-requests.index');
    Route::get('/borrow-requests/create', [BorrowRequestController::class, 'create'])->name('borrow-requests.create');
    Route::post('/borrow-requests', [BorrowRequestController::class, 'store'])->name('borrow-requests.store');
    Route::get('/borrow-requests/{borrowRequest}', [BorrowRequestController::class, 'show'])->name('borrow-requests.show');
    Route::post('/borrow-requests/{borrowRequest}/mark-borrowed', [BorrowRequestController::class, 'markBorrowed'])->name('borrow-requests.mark-borrowed');
    Route::post('/borrow-requests/{borrowRequest}/return', [BorrowRequestController::class, 'returnItem'])->name('borrow-requests.return');
    Route::delete('/borrow-requests/{borrowRequest}', [BorrowRequestController::class, 'destroy'])->name('borrow-requests.destroy');
    
    // Inventory
    Route::get('/inventories', [EmployeeInventoryController::class, 'index'])->name('inventories.index');
    Route::get('/inventories/low-stock', [EmployeeInventoryController::class, 'lowStock'])->name('inventories.low-stock');
    Route::get('/inventories/{inventory}', [EmployeeInventoryController::class, 'show'])->name('inventories.show');
    
    // Notifications
    Route::get('/notifications', [EmployeeNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [EmployeeNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/{id}/open', [EmployeeNotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/mark-read', [EmployeeNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [EmployeeNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [EmployeeNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear-all', [EmployeeNotificationController::class, 'clearAll'])->name('notifications.clear-all');
});
