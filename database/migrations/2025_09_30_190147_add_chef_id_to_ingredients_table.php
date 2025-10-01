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
    Schema::table('ingredients', function (Blueprint $table) {
        $table->unsignedBigInteger('chef_id')->nullable()->after('id');
        $table->foreign('chef_id')->references('id')->on('users')->onDelete('set null');
    });
}

public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['chef_id']);
            $table->dropColumn('chef_id');
        });
    }
};
