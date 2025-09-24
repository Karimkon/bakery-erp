<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->string('category'); // e.g. wages, rent, fuel, utilities
        $table->string('description')->nullable();
        $table->decimal('amount', 12, 2);
        $table->date('expense_date');
        $table->unsignedBigInteger('recorded_by'); // finance staff user_id
        $table->timestamps();

        $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
