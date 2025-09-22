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
            if (!Schema::hasColumn('dispatches','commission_total')) {
                $table->decimal('commission_total', 12, 2)->default(0)->after('total_sales_value');
            }
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_items','commission')) {
                $table->decimal('commission', 12, 2)->default(0)->after('line_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            if (Schema::hasColumn('dispatches','commission_total')) {
                $table->dropColumn('commission_total');
            }
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            if (Schema::hasColumn('dispatch_items','commission')) {
                $table->dropColumn('commission');
            }
        });
    }
};
