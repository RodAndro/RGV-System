<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;
use Illuminate\Support\Str;
use Throwable;

class PostgresConnector extends BasePostgresConnector
{
    /**
     * Append libpq's connect_timeout to the DSN so a stalled connection
     * attempt fails fast instead of hanging for the OS TCP timeout.
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        return isset($config['connect_timeout'])
            ? $dsn.";connect_timeout={$config['connect_timeout']}"
            : $dsn;
    }

    /**
     * The stock lost-connection detector only matches MySQL "HY000" SSL
     * SYSCALL errors, not the pgsql "08006 [7]" variant. Treat any SSL
     * SYSCALL error as a lost connection so the connector retries the
     * connection attempt instead of surfacing a 500.
     */
    protected function causedByLostConnection(Throwable $e): bool
    {
        if (Str::contains($e->getMessage(), 'SSL SYSCALL error')) {
            return true;
        }

        return parent::causedByLostConnection($e);
    }
}
