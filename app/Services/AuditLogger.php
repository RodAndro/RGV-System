<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'api_token',
        'secret',
    ];

    public static function log(string $event, ?Model $auditable = null, array $old = [], array $new = [], ?Request $request = null): void
    {
        $request ??= request();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => self::clean($old),
            'new_values' => self::clean($new),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
        ]);
    }

    public static function clean(array $values): array
    {
        return collect($values)
            ->reject(fn ($value, $key) => in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true))
            ->map(fn ($value) => is_array($value) ? self::clean($value) : $value)
            ->all();
    }
}
