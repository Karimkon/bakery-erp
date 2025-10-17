<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatches', 'dispatch_no')) {
                $table->unsignedInteger('dispatch_no')->default(1)->after('dispatch_date');
            }
            if (!Schema::hasColumn('dispatches', 'commission_total')) {
                $table->decimal('commission_total', 12, 2)->default(0)->after('total_sales_value');
            }
            if (!Schema::hasColumn('dispatches', 'cash_received')) {
                $table->decimal('cash_received', 12, 2)->default(0)->after('commission_total');
            }
            if (!Schema::hasColumn('dispatches', 'balance_due')) {
                $table->decimal('balance_due', 12, 2)->default(0)->after('cash_received');
            }
            if (!Schema::hasColumn('dispatches', 'driver_expenses')) {
                $table->decimal('driver_expenses', 12, 2)->default(0)->after('commission_total');
            }
            if (!Schema::hasColumn('dispatches', 'expected_cash_after_deductions')) {
                $table->decimal('expected_cash_after_deductions', 12, 2)->nullable()->after('driver_expenses');
            }
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_items', 'sold_cash')) {
                $table->unsignedInteger('sold_cash')->default(0)->after('dispatched_qty');
            }
            if (!Schema::hasColumn('dispatch_items', 'sold_credit')) {
                $table->unsignedInteger('sold_credit')->default(0)->after('sold_cash');
            }
            if (!Schema::hasColumn('dispatch_items', 'commission')) {
                $table->decimal('commission', 12, 2)->default(0)->after('line_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            if (Schema::hasColumn('dispatches', 'expected_cash_after_deductions')) {
                $table->dropColumn('expected_cash_after_deductions');
            }
            if (Schema::hasColumn('dispatches', 'driver_expenses')) {
                $table->dropColumn('driver_expenses');
            }
            if (Schema::hasColumn('dispatches', 'balance_due')) {
                $table->dropColumn('balance_due');
            }
            if (Schema::hasColumn('dispatches', 'cash_received')) {
                $table->dropColumn('cash_received');
            }
            if (Schema::hasColumn('dispatches', 'commission_total')) {
                $table->dropColumn('commission_total');
            }
            if (Schema::hasColumn('dispatches', 'dispatch_no')) {
                $table->dropColumn('dispatch_no');
            }
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            if (Schema::hasColumn('dispatch_items', 'commission')) {
                $table->dropColumn('commission');
            }
            if (Schema::hasColumn('dispatch_items', 'sold_credit')) {
                $table->dropColumn('sold_credit');
            }
            if (Schema::hasColumn('dispatch_items', 'sold_cash')) {
                $table->dropColumn('sold_cash');
            }
        });
    }
};


