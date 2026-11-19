<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Requirement T4: each AI use must record a confidence or score alongside the
 * input reference, output, model and user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_interactions', function (Blueprint $table) {
            if (! Schema::hasColumn('chatbot_interactions', 'confidence')) {
                $table->decimal('confidence', 4, 3)->nullable()->after('model_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_interactions', function (Blueprint $table) {
            if (Schema::hasColumn('chatbot_interactions', 'confidence')) {
                $table->dropColumn('confidence');
            }
        });
    }
};
