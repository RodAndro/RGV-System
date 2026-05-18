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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->integer('quantity')->default(0);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('status')->default('available'); // available, borrowed, maintenance, damaged
            $table->string('condition')->default('good'); // new, good, fair, poor
            $table->string('location')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('low_stock_threshold')->default(5);
            $table->date('date_added');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category_id', 'status']);
            $table->index('item_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
