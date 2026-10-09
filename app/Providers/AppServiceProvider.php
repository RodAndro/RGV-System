<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\Database\PostgresConnector;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\LoginHistory;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\BookingObserver;
use App\Observers\BorrowRequestObserver;
use App\Observers\InventoryObserver;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', PostgresConnector::class);

        Connection::resolverFor('pgsql', fn ($connection, $database, $prefix, $config) => new PostgresConnection($connection, $database, $prefix, $config));
    }

    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min((int) env('PASSWORD_MIN_LENGTH', 8))
                ->letters()
                ->mixedCase()
                ->numbers();
        });

        foreach ([Booking::class, BorrowRequest::class, Inventory::class, InventoryCategory::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        Booking::observe(BookingObserver::class);
        Inventory::observe(InventoryObserver::class);
        BorrowRequest::observe(BorrowRequestObserver::class);

        Event::listen(Login::class, function (Login $event) {
            RateLimiter::clear('failed-login-ip:' . request()->ip());
            $event->user->update(['last_login_at' => now()]);
            AuditLogger::log('login', $event->user);

            LoginHistory::create([
                'user_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_in_at' => now(),
                'session_id' => session()->getId(),
                'is_impersonation' => session()->has('impersonate.original_id'),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLogger::log('logout', $event->user);
                LoginHistory::where('user_id', $event->user->id)
                    ->whereNull('logged_out_at')
                    ->latest('logged_in_at')
                    ->first()
                    ?->update(['logged_out_at' => now()]);
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            RateLimiter::hit('failed-login-ip:' . request()->ip(), 3600);
            AuditLogger::log('failed login', null, [], ['email' => $event->credentials['email'] ?? null]);
        });
    }
}
