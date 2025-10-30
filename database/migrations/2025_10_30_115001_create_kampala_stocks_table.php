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
          Schema::create('kampala_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('kampala_shops');
            $table->string('product_type');
            $table->integer('opening_stock')->default(0);
            $table->integer('dispatched')->default(0);
            $table->integer('sold')->default(0);
            $table->integer('remaining')->default(0);
            $table->decimal('unit_price', 8, 2);
            $table->timestamps();
            
            $table->unique(['shop_id', 'product_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kampala_stocks');
    }
};
