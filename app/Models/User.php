<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar_path',
        'is_active',
        'last_login_at',
        'mfa_enabled',
        'mfa_type',
        'mfa_secret',
        'mfa_verified_at',
        'mfa_recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'mfa_recovery_codes' => 'array',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'employee_id');
    }

    public function borrowRequests()
    {
        return $this->hasMany(BorrowRequest::class, 'employee_id');
    }

    public function approvedBorrowRequests()
    {
        return $this->hasMany(BorrowRequest::class, 'approved_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function notifications()
    {
        return $this->hasMany(SystemNotification::class, 'user_id');
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isEmployee()
    {
        return $this->hasRole('employee');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function enableMfa(string $type = 'totp'): void
    {
        $secret = $this->generateTotpSecret();
        $this->update([
            'mfa_enabled' => true,
            'mfa_type' => $type,
            'mfa_secret' => $secret,
            'mfa_verified_at' => now(),
            'mfa_recovery_codes' => $this->generateRecoveryCodes(),
        ]);
    }

    public function verifyTotp(string $code): bool
    {
        if (!$this->mfa_secret) return false;
        $timeSlice = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if (hash_equals($this->generateTotpCode($this->mfa_secret, $timeSlice + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    private function generateTotpSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    private function generateTotpCode(string $secret, int $timeSlice): string
    {
        $secret = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7F) << 24
            | (ord($hash[$offset + 1]) & 0xFF) << 16
            | (ord($hash[$offset + 2]) & 0xFF) << 8
            | (ord($hash[$offset + 3]) & 0xFF);
        return str_pad((string)($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = rtrim($secret, '=');
        $binary = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $result .= chr(bindec($byte));
        }
        return $result;
    }

    public function getCurrentTotpCode(): ?string
    {
        if (!$this->mfa_secret) return null;
        $timeSlice = floor(time() / 30);
        return $this->generateTotpCode($this->mfa_secret, $timeSlice);
    }

    public function disableMfa(): void
    {
        $this->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
            'mfa_verified_at' => null,
            'mfa_recovery_codes' => null,
        ]);
    }

    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4)))->toArray();
    }
}
