<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->unsignedInteger('sold_cash')->default(0)->after('dispatched_qty');
            $table->unsignedInteger('sold_credit')->default(0)->after('sold_cash');
        });
    }

    public function down(): void {
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->dropColumn(['sold_cash', 'sold_credit']);
        });
    }
};
