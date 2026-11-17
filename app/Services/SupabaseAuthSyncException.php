<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class SupabaseAuthSyncException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }
}
