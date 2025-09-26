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
            $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn(['cash_received', 'balance_due']);
        });
    }

};
