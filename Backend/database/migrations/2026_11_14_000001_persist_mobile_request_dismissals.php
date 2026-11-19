<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mobile_recent_request_dismissals')) {
            Schema::create('mobile_recent_request_dismissals', function (Blueprint $table) {
                $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('borrow_request_id')->constrained('borrow_requests')->cascadeOnDelete();
                $table->timestamp('dismissed_at')->useCurrent();
                $table->primary(['employee_id', 'borrow_request_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_recent_request_dismissals');
    }
};