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
        Schema::create('driver_back_debt_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('dispatch_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('previous_balance', 12, 2)->default(0);
            $table->decimal('amount_changed', 12, 2); // Positive = driver owes, Negative = bakery owes
            $table->decimal('new_balance', 12, 2);
            $table->string('transaction_type'); // dispatch_update, cash_payment, manual_adjustment
            $table->text('description');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['driver_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_back_debt_transactions');
    }
};