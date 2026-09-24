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
        Schema::create('parcel_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cate_id')->nullable();
            $table->string('r_name');
            $table->string('r_phone');
            $table->string('r_city');
            $table->string('r_country');
            $table->string('r_lat');
            $table->string('r_lng');
            $table->string('s_name');
            $table->string('s_phone');
            $table->string('s_city');
            $table->string('s_country');
            $table->string('s_lat');
            $table->string('s_lng');
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->dateTime('order_date');
            $table->string('status');
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->foreign('carrier_id')->references('id')->on('app_users')->onDelete('set null');
            $table->foreign('cate_id')->references('id')->on('parcel_cates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_orders');
    }
};
