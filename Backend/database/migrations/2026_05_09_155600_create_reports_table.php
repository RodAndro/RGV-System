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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->string('type'); // booking, inventory, borrow, ai_analytics, monthly
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('data')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_format')->default('pdf'); // pdf, excel, csv
            $table->date('report_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamps();
            
            $table->index(['type', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
