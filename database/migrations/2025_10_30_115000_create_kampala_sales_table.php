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
        Schema::create('kampala_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('kampala_shops');
            $table->foreignId('user_id')->constrained('users');
            $table->string('product_type');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_method', ['cash', 'mobile_money']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kampala_sales');
    }
};
