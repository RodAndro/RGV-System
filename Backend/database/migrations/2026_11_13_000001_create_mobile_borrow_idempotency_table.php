<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_borrow_idempotency', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('idempotency_key', 64);
            $table->char('request_hash', 64);
            $table->json('response_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['employee_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_borrow_idempotency');
    }
};
