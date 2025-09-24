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
        Schema::create('shop_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name'); // e.g. Kampala Main Shop
            $table->string('product_type'); // buns, breads
            $table->integer('opening_stock')->default(0);
            $table->integer('dispatched')->default(0);
            $table->integer('sold')->default(0);
            $table->integer('remaining')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
