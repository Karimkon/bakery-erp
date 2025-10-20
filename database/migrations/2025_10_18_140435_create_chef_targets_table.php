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
        Schema::create('chef_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chef_id')->constrained('users')->onDelete('cascade');
            $table->decimal('daily_target', 15, 2);    // Daily output target
            $table->decimal('monthly_target', 15, 2);  // Monthly output target
            $table->json('days_off')->nullable();      // e.g., ["friday"]
            $table->decimal('commission_percentage', 5, 2)->default(0); // Commission %
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chef_targets');
    }
};
