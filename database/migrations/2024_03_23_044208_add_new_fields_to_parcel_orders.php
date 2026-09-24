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
        Schema::table('parcel_orders', function (Blueprint $table) {
            $table->string('r_lat');
            $table->string('r_lng');
            $table->string('s_lat');
            $table->string('s_lng');
            $table->integer('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_orders', function (Blueprint $table) {
            //
        });
    }
};
