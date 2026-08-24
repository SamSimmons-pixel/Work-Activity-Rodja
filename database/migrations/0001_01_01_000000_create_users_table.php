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
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Role Permissions Pivot
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Divisions
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('head_user_id')->nullable();
            $table->string('status')->default('Active'); // Active / Inactive
            $table->timestamps();
        });

        // 5. Positions
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->string('name');
            $table->string('level')->nullable();
            $table->string('status')->default('Active'); // Active / Inactive
            $table->timestamps();
        });

        // 6. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('full_name');
            $table->string('password');
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->string('status')->default('Active'); // Active / Inactive
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 7. Add foreign key constraint for divisions.head_user_id after users table is created
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreign('head_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // 8. Sessions (for database session driver)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['head_user_id']);
        });
        Schema::dropIfExists('users');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
