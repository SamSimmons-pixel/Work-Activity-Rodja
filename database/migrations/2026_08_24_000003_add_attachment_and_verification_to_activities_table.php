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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('status');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->timestamp('verified_at')->nullable()->after('attachment_name');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['attachment_path', 'attachment_name', 'verified_at', 'verified_by']);
        });
    }
};
