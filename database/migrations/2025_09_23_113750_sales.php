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
        Schema::create('sales', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // sales person
    $table->string('product_type'); // e.g. buns, big_breads, donuts
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->decimal('total_price', 12, 2);
    $table->enum('payment_method', ['cash','momo'])->default('cash');
    $table->timestamps();
});

Schema::create('bankings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // sales person
    $table->decimal('amount', 12, 2);
    $table->date('date');
    $table->string('receipt_number')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankings');
        Schema::dropIfExists('sales');
    }
};
