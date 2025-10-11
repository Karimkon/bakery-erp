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
    Schema::table('dispatches', function (Blueprint $table) {
        $table->decimal('driver_expenses', 10, 2)->default(0);
        $table->decimal('expected_cash_after_deductions', 10, 2)->default(0);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            //
        });
    }
};
