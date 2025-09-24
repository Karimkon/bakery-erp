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
        Schema::table('bankings', function (Blueprint $table) {
            // only add if column does not exist
            if (!Schema::hasColumn('bankings', 'notes')) {
                $table->string('notes')->nullable()->after('receipt_number');
            }
            if (!Schema::hasColumn('bankings', 'receipt_file')) {
                $table->string('receipt_file')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bankings', function (Blueprint $table) {
            if (Schema::hasColumn('bankings', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('bankings', 'receipt_file')) {
                $table->dropColumn('receipt_file');
            }
        });
    }
};
