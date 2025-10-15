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
    Schema::table('expenses', function (Blueprint $table) {
        $table->string('receipt')->nullable()->after('expense_date');
    });
}

public function down(): void
{
    Schema::table('expenses', function (Blueprint $table) {
        $table->dropColumn('receipt');
    });
}

};
