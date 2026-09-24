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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->tinyInteger('status')->default(0);
            $table->string('vcode')->nullable();
            $table->string('rcode')->nullable();
            $table->integer('wallet')->default(0);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('selfie')->nullable();
            $table->string('identity')->nullable();
            $table->float('rating')->default(0);
            $table->integer('rating_nb')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
