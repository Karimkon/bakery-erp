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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('ALTER TABLE dispatches DROP INDEX dispatches_driver_id_dispatch_date_unique;');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE dispatches ADD UNIQUE dispatches_driver_id_dispatch_date_unique (driver_id, dispatch_date);');
    }
   
   
};
