<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 17)->unique();
            $table->string('isbn13', 13)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->string('author')->index();
            $table->string('publisher')->nullable();
            $table->string('format', 32)->default('paperback');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedBigInteger('sales_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->date('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category_id', 'price', 'id'], 'books_catalog_cover_idx');
            $table->index(['category_id', 'is_active', 'stock', 'price'], 'books_category_filter_idx');
            $table->index(['is_active', 'created_at', 'id'], 'books_active_created_idx');
            $table->index(['sales_count', 'rating'], 'books_sales_rating_idx');
            $table->index('published_at');
        });

        Schema::create('mv_bestseller_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->unsignedBigInteger('book_count')->default(0);
            $table->unsignedBigInteger('total_stock')->default(0);
            $table->unsignedBigInteger('total_sales')->default(0);
            $table->decimal('avg_price', 10, 2)->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();

            $table->unique('category_id');
            $table->index(['total_sales', 'avg_rating']);
        });

        Schema::create('query_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('duration_ms');
            $table->unsignedInteger('rows_returned')->default(0);
            $table->boolean('cache_hit')->default(false);
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['name', 'duration_ms']);
        });

        Schema::create('search_indexing_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('failure_message')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('book_shards', function (Blueprint $table) {
            $table->id();
            $table->string('shard_key')->unique();
            $table->string('connection');
            $table->unsignedBigInteger('range_start')->nullable();
            $table->unsignedBigInteger('range_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books ADD FULLTEXT books_fulltext_idx (title, author, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('book_shards');
        Schema::dropIfExists('search_indexing_queue');
        Schema::dropIfExists('query_performance_logs');
        Schema::dropIfExists('mv_bestseller_stats');
        Schema::dropIfExists('books');
    }
};
