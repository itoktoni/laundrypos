<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->foreignId('inventory_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->string('inventory_nama', 100);
            $table->string('inventory_satuan', 20)->default('pcs');
            $table->integer('inventory_min_stok')->default(0);
            $table->string('inventory_keterangan', 500)->nullable();
            $table->timestamps();

            $table->unique(['inventory_id_laundry', 'inventory_nama'], 'inventory_unik_per_laundry');
        });

        Schema::create('inventory_movement', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('movement_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->foreignId('movement_id_inventory')->constrained('inventory', 'inventory_id')->cascadeOnDelete();
            $table->date('movement_tanggal');
            $table->string('movement_tipe', 10)->comment('masuk, keluar');
            $table->string('movement_uom', 20)->default('pcs');
            $table->integer('movement_qty');
            $table->decimal('movement_nominal', 14, 2)->default(0)->comment('total nominal baris (qty x harga)');
            $table->string('movement_keterangan', 500)->nullable();
            $table->timestamps();

            $table->index(['movement_id_inventory', 'movement_tanggal', 'movement_id'], 'movement_kartu_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movement');
        Schema::dropIfExists('inventory');
    }
};
