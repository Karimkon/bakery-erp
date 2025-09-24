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
    Schema::table('sales', function (Blueprint $table) {
        $table->string('notes')->nullable()->after('payment_method');
    });
}

public function down(): void
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropColumn('notes');
    });
}

};
