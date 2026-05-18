<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'inventories', 'borrow_requests', 'users'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedInteger('lock_version')->default(1);
            });
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'inventories', 'borrow_requests', 'users'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('lock_version');
            });
        }
    }
};
