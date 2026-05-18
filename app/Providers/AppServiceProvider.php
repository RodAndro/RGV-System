<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Book;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\LoginHistory;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\BookObserver;
use App\Observers\BookingObserver;
use App\Observers\BorrowRequestObserver;
use App\Observers\InventoryObserver;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
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

        Book::observe(BookObserver::class);
        Booking::observe(BookingObserver::class);
        Inventory::observe(InventoryObserver::class);
        BorrowRequest::observe(BorrowRequestObserver::class);

        Event::listen(Login::class, function (Login $event) {
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

        Event::listen(Failed::class, fn (Failed $event) => AuditLogger::log('failed login', null, [], ['email' => $event->credentials['email'] ?? null]));
    }
}
