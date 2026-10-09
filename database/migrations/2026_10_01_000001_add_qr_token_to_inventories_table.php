<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('qr_token')->nullable()->unique()->after('item_code');
        });

        // Backfill existing rows so every item has a stable, unique QR identifier.
        // The application layer always generates a token on creation (see
        // Inventory::boot), so the column stays populated without a NOT NULL
        // constraint, which would require doctrine/dbal to alter.
        foreach (DB::table('inventories')->whereNull('qr_token')->get() as $row) {
            DB::table('inventories')
                ->where('id', $row->id)
                ->update(['qr_token' => $this->generateToken()]);
        }
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }

    private function generateToken(): string
    {
        do {
            $token = 'QR-'.Str::upper(Str::random(32));
        } while (DB::table('inventories')->where('qr_token', $token)->exists());

        return $token;
    }
};
