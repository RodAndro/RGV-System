<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_return_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')->constrained('borrow_requests')->onDelete('cascade');
            $table->string('idempotency_key')->unique();
            $table->string('drive_file_id')->nullable();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['borrow_request_id', 'uploaded_at']);
        });

        Schema::table('borrow_items', function (Blueprint $table) {
            $table->unsignedInteger('reserved_quantity')->default(0)->after('quantity');
            $table->foreignId('return_evidence_id')->nullable()->after('reserved_quantity')
                ->constrained('borrow_return_evidences')->nullOnDelete();
        });

        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('return_evidence_id');
            $table->dropColumn('reserved_quantity');
        });

        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });

        Schema::dropIfExists('borrow_return_evidences');
    }
};
