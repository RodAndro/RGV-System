<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('question_hash', 64);
            $table->string('page_type', 32);
            $table->json('source_refs')->nullable();
            $table->text('response')->nullable();
            $table->string('model_name', 100)->nullable();
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->string('outcome', 32);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index(['page_type', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_interactions');
    }
};
