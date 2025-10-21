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
        Schema::create('manager_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
    $table->decimal('daily_target', 15, 2)->default(3000000);
    $table->decimal('monthly_target', 15, 2)->nullable();
    $table->decimal('fixed_salary', 15, 2)->default(0);
    $table->decimal('commission_percentage', 5, 2)->default(100);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_targets');
    }
};
