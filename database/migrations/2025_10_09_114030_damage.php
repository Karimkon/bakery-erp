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
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users'); // who reports it
            $table->foreignId('admin_id')->nullable()->constrained('users'); // who approves it
            $table->string('product');
            $table->integer('quantity');
            $table->decimal('approved_price', 10, 2)->nullable(); // admin sets price
            $table->text('notes')->nullable(); // manager note
            $table->string('photo')->nullable(); // optional image
            $table->enum('status', ['pending','approved','rejected','sold'])->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
