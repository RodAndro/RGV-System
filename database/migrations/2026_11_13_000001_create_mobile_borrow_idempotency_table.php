<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_borrow_idempotency', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('idempotency_key', 64);
            $table->char('request_hash', 64);
            $table->jsonb('response_payload')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->primary(['employee_id', 'idempotency_key']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.mobile_borrow_idempotency ENABLE ROW LEVEL SECURITY');
            DB::statement('REVOKE ALL ON public.mobile_borrow_idempotency FROM anon, authenticated');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_borrow_idempotency');
    }
};
