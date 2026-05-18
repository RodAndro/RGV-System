<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('contact_number');
            $table->text('address');
            $table->date('preferred_date');
            $table->time('preferred_time');
            $table->string('purpose_category');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, completed, cancelled
            $table->foreignId('employee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'preferred_date']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
