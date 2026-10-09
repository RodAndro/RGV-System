<?php

namespace App\Database;

use Closure;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use PDOException;
use Throwable;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Treat any SSL SYSCALL error as a lost connection so Laravel retries
     * the query against a fresh connection. Supabase's pooler drops
     * connections mid-operation, surfacing as "SSL SYSCALL error: No error".
     */
    protected function causedByLostConnection(Throwable $e): bool
    {
        if (Str::contains($e->getMessage(), 'SSL SYSCALL error')) {
            return true;
        }

        return parent::causedByLostConnection($e);
    }

    /**
     * Retry a lost connection multiple times with backoff instead of the
     * single retry the base implementation performs. Supabase's pooler can
     * drop several consecutive attempts, so one retry is often not enough.
     * A reconnect that fails during connection establishment surfaces as a
     * PDOException, so both exception types are retried.
     */
    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        $attempts = (int) ($this->config['retry_attempts'] ?? 3);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            $cause = $e instanceof QueryException ? ($e->getPrevious() ?: $e) : $e;

            if (! $this->causedByLostConnection($cause)) {
                throw $e;
            }

            usleep($this->retryBackoffMicroseconds($attempt));

            try {
                $this->reconnect();

                return $this->runQueryCallback($query, $bindings, $callback);
            } catch (QueryException|PDOException $next) {
                $e = $next;
            }
        }

        throw $e;
    }

    /**
     * Exponential backoff capped at 500ms: 100ms, 200ms, 400ms, ...
     */
    protected function retryBackoffMicroseconds(int $attempt): int
    {
        return min(500_000, 100_000 * (2 ** $attempt));
    }
}
