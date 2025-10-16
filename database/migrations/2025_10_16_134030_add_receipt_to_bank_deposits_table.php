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
    Schema::table('bank_deposits', function (Blueprint $table) {
        $table->string('receipt')->nullable()->after('amount');
    });
}

public function down()
{
    Schema::table('bank_deposits', function (Blueprint $table) {
        $table->dropColumn('receipt');
    });
}

};
