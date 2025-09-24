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
        Schema::table('dispatches', function (Blueprint $table) {
        $table->decimal('net_handover', 12, 2)->default(0)->after('commission_total');
        $table->boolean('handover_received')->default(false)->after('net_handover'); 
        // true when Finance confirms deposit
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
