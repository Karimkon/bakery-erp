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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Chef
            $table->date('production_date');
            $table->decimal('flour_bags', 8, 2)->default(0);

            // Output items
            $table->integer('buns')->default(0);
            $table->integer('small_breads')->default(0);
            $table->integer('big_breads')->default(0);
            $table->integer('donuts')->default(0);
            $table->integer('half_cakes')->default(0);
            $table->integer('block_cakes')->default(0);
            $table->integer('slab_cakes')->default(0);
            $table->integer('birthday_cakes')->default(0);

            // Value & variance
            $table->decimal('total_value', 12, 2)->default(0);
            $table->boolean('has_variance')->default(false);
            $table->text('variance_notes')->nullable();

            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
