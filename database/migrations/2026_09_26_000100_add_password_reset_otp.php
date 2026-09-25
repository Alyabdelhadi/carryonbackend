<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Password reset by emailed 6-digit code (api/password/*). The code is
 * stored hashed; the one-time reset token reuses app_users.reset_token.
 * Idempotent so it can be re-run on the dump-based DB with --path=.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_users', 'reset_otp_hash')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->string('reset_otp_hash')->nullable();
                $table->timestamp('reset_otp_expires_at')->nullable();
                $table->timestamp('reset_otp_sent_at')->nullable();
                $table->unsignedTinyInteger('reset_otp_attempts')->default(0);
                $table->timestamp('reset_token_expires_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_users', 'reset_otp_hash')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->dropColumn([
                    'reset_otp_hash', 'reset_otp_expires_at', 'reset_otp_sent_at',
                    'reset_otp_attempts', 'reset_token_expires_at',
                ]);
            });
        }
    }
};
