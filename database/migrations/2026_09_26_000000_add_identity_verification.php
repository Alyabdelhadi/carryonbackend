<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Server-side Shufti identity verification: the result on the user and a
 * log of every attempt. Idempotent so it can be re-run on the dump-based
 * DB with --path=.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_users', 'identity_status')) {
            Schema::table('app_users', function (Blueprint $table) {
                // null (never checked) | pending | verified | declined | invalid
                $table->string('identity_status', 20)->nullable()->index();
                $table->timestamp('identity_verified_at')->nullable();
                $table->string('shufti_reference', 100)->nullable();
            });
        }

        if (!Schema::hasTable('identity_verifications')) {
            Schema::create('identity_verifications', function (Blueprint $table) {
                $table->id();
                // null while a signup attempt has not created the account
                $table->unsignedBigInteger('app_user_id')->nullable()->index();
                $table->string('reference', 100)->unique();
                $table->string('source', 20);                  // signup | reverify
                // Shufti event: verification.accepted | verification.declined |
                // request.invalid | request.pending | ... or `unreachable`
                $table->string('event', 60)->nullable();
                $table->string('status', 20)->index();         // pending | verified | declined | invalid | failed
                $table->string('message', 500)->nullable();
                $table->json('result')->nullable();            // Shufti verification_result (pass/fail flags, no PII)
                $table->string('selfie')->nullable();
                $table->string('identity')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verifications');
        if (Schema::hasColumn('app_users', 'identity_status')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->dropColumn(['identity_status', 'identity_verified_at', 'shufti_reference']);
            });
        }
    }
};
