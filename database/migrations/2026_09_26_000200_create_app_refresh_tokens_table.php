<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rotating refresh tokens for the mobile API (the access tokens are
 * Sanctum personal_access_tokens). A `family` is one login on one device:
 * every refresh replaces the token inside the family, and presenting an
 * already-used token revokes the whole family (token theft).
 * Idempotent so it can be re-run on the dump-based DB with --path=.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('app_refresh_tokens')) {
            Schema::create('app_refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('app_user_id')->index();
                $table->uuid('family')->index();
                $table->char('token_hash', 64)->unique();   // sha256 of the token
                $table->string('device', 120)->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();    // rotated
                $table->timestamp('revoked_at')->nullable(); // logout / theft / password change
                $table->string('ip', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_refresh_tokens');
    }
};
