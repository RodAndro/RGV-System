<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_return_idempotency', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('borrow_request_id')->constrained('borrow_requests')->cascadeOnDelete();
            $table->string('idempotency_key', 64);
            $table->jsonb('request_payload');
            $table->jsonb('response_payload');
            $table->timestampTz('created_at')->useCurrent();
            $table->primary(['employee_id', 'borrow_request_id', 'idempotency_key']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.mobile_return_idempotency ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON public.mobile_return_idempotency FROM anon, authenticated');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_return_idempotency');
    }
};