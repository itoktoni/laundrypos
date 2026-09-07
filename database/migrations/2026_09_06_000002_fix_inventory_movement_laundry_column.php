<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->dropForeign(['movement_id_laundry']);
            $table->renameColumn('movement_id_laundry', 'inventory_movement_id_laundry');
        });

        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->foreign('inventory_movement_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->dropForeign(['inventory_movement_id_laundry']);
            $table->renameColumn('inventory_movement_id_laundry', 'movement_id_laundry');
        });

        Schema::table('inventory_movement', function (Blueprint $table) {
            $table->foreign('movement_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
        });
    }
};
