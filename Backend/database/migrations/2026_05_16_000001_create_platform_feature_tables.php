<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('inventory');
            $table->string('file_name')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('duplicate_strategy')->default('skip');
            $table->json('errors')->nullable();
            $table->string('error_report_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('format')->default('xlsx');
            $table->string('status')->default('pending');
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->unsignedInteger('record_count')->default(0);
            $table->string('file_path')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('checksum', 64);
            $table->string('previous_checksum', 64)->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
        });

        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('status')->default('started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('output')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();

            $table->index(['command', 'status']);
        });

        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tier')->default('public');
            $table->string('key');
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('limit_per_minute');
            $table->unsignedInteger('remaining');
            $table->boolean('blocked')->default(false);
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();

            $table->index(['key', 'reset_at']);
            $table->index(['tier', 'blocked']);
        });

        Schema::create('backup_monitoring', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('local');
            $table->string('status')->default('unknown');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['disk', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_monitoring');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('scheduled_tasks');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('import_logs');
    }
};
