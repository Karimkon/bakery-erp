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
        Schema::table('chef_targets', function (Blueprint $table) {
            $table->decimal('fixed_salary', 10, 2)->default(0)->after('commission_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('chef_targets', function (Blueprint $table) {
            $table->dropColumn('fixed_salary');
        });
    }
};
