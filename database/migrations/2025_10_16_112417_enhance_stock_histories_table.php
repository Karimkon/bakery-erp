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
        // Step 1: rename the column first
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->renameColumn('quantity_added', 'quantity_changed');
        });

        // Step 2: now safely add and modify other columns
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->enum('transaction_type', ['addition', 'usage', 'adjustment'])
                ->default('addition')
                ->after('quantity_changed');

            $table->decimal('quantity_before', 10, 2)
                ->nullable()
                ->after('transaction_type');

            $table->decimal('quantity_after', 10, 2)
                ->nullable()
                ->after('quantity_before');

            // Modify renamed column type
            $table->decimal('quantity_changed', 10, 2)->change();

            $table->foreignId('production_id')
                ->nullable()
                ->after('chef_id')
                ->constrained()
                ->onDelete('cascade');

            $table->text('notes')->nullable()->after('added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->renameColumn('quantity_changed', 'quantity_added');
            $table->dropColumn(['transaction_type', 'quantity_before', 'quantity_after', 'production_id', 'notes']);
        });
    }
};
