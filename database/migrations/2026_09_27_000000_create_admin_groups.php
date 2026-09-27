<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin groups with per-page permissions (view / create / edit / delete),
 * one group per dashboard account. Every existing account becomes a Super
 * Admin so nobody loses access on deploy.
 *
 *   php artisan migrate --path=database/migrations/2026_09_27_000000_create_admin_groups.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            // full access, permissions ignored; cannot be edited or deleted
            $table->boolean('is_super')->default(false);
            // {"users": ["view", "edit"], ...}
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_group_id')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('admin_group_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        $superId = DB::table('admin_groups')->insertGetId([
            'name' => 'Super Admin',
            'description' => 'Full access to every page, including admins and groups.',
            'is_super' => true,
            'permissions' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->update(['admin_group_id' => $superId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_group_id', 'is_active', 'last_login_at']);
        });
        Schema::dropIfExists('admin_groups');
    }
};
