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
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('cost_of_inputs', 14, 2)->nullable()->after('total_value');
            $table->decimal('cost_of_production', 14, 2)->nullable()->after('cost_of_inputs');
            $table->decimal('profit', 14, 2)->nullable()->after('cost_of_production');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['cost_of_inputs', 'cost_of_production', 'profit']);
        });
    }
};
