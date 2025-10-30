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
          Schema::create('kampala_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('kampala_shops');
            $table->foreignId('manager_id')->constrained('users');
            $table->date('dispatch_date');
            $table->string('dispatch_no')->unique();
            $table->integer('total_items');
            $table->decimal('total_value', 10, 2);
            $table->enum('status', ['pending', 'received', 'partial', 'rejected'])->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kampala_dispatches');
    }
};
