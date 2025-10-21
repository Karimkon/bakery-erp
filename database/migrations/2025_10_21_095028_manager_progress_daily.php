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
        Schema::create('manager_progress_daily', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manager_id')->constrained('users');
    $table->date('progress_date');
    $table->decimal('target_amount', 15, 2);  // From manager_targets.daily_target
    $table->decimal('achieved_amount', 15, 2); // Calculated from dispatches/sales
    $table->decimal('progress_percentage', 5, 2);
    $table->timestamps();
    
    $table->unique(['manager_id', 'progress_date']); // One record per manager per day
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
