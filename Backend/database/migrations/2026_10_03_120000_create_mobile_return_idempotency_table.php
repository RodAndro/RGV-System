<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_return_idempotency', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('borrow_request_id')->constrained('borrow_requests')->cascadeOnDelete();
            $table->string('idempotency_key', 64);
            $table->json('request_payload');
            $table->json('response_payload');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['employee_id', 'borrow_request_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_return_idempotency');
    }
};
