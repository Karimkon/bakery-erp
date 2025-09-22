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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->date('dispatch_date');
            $table->text('notes')->nullable();

            // quick reporting
            $table->unsignedInteger('total_items_sold')->default(0);
            $table->decimal('total_sales_value', 12, 2)->default(0);

            $table->timestamps();
            $table->unique(['driver_id','dispatch_date']); // one per driver per date
        });

        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('dispatches')->cascadeOnDelete();
            $table->string('product'); // key from config('bakery_products')
            $table->unsignedInteger('opening_stock')->default(0);
            $table->unsignedInteger('dispatched_qty')->default(0);
            $table->unsignedInteger('sold_qty')->default(0);
            $table->unsignedInteger('remaining_qty')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0); // sold_qty * unit_price
            $table->timestamps();

            $table->unique(['dispatch_id','product']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_items');
        Schema::dropIfExists('dispatches');
    }
};
