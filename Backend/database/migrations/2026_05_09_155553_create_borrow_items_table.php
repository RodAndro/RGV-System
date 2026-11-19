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
        Schema::create('borrow_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')->constrained('borrow_requests')->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->string('condition_borrowed')->default('good');
            $table->string('condition_returned')->nullable();
            $table->boolean('is_returned')->default(false);
            $table->timestamp('returned_at')->nullable();
            $table->text('damage_notes')->nullable();
            $table->timestamps();
            
            $table->index(['borrow_request_id', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_items');
    }
};
