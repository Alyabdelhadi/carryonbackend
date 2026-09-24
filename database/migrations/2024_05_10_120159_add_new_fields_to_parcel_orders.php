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
            $table->string('r_addressname');
            $table->string('r_street');
            $table->string('r_building');
            $table->string('r_apartment');
            $table->string('r_nextto');
            $table->string('r_addressnotes');
            $table->string('s_addressname');
            $table->string('s_street');
            $table->string('s_building');
            $table->string('s_apartment');
            $table->string('s_nextto');
            $table->string('s_addressnotes');
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
