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
        Schema::create('payrolls', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('pay_month'); // e.g. 2025-10-01 for October
    $table->decimal('base_salary', 12, 2)->default(0);
    $table->decimal('commission', 12, 2)->default(0);
    $table->decimal('total_salary', 12, 2)->default(0);
    $table->string('status')->default('pending'); // pending, paid
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
