<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_migration_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_fingerprint', 64);
            $table->string('source_table');
            // Must be a bounded VARCHAR, not TEXT: MySQL/InnoDB cannot build a
            // unique index over a TEXT column (error 1170), while PostgreSQL
            // and SQLite both allow it. 191 chars keeps the composite key
            // inside InnoDB's 3072-byte index budget with utf8mb4.
            $table->string('source_key', 191);
            $table->text('target_key');
            $table->timestamps();
            $table->unique(['source_fingerprint', 'source_table', 'source_key'], 'data_migration_source_key_unique');
            $table->index(['source_fingerprint', 'source_table']);
        });
    }

    public function down(): void
    {
        // Retain verified source-to-target mappings for repeatable reconciliation.
    }
};
