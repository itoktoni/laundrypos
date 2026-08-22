<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('order_id_laundry');
            $table->string('order_code', 20)->unique();
            $table->unsignedBigInteger('order_id_customer')->nullable();
            $table->string('order_walkin_nama', 100)->nullable();
            $table->string('order_walkin_telepon', 15)->nullable();
            $table->unsignedBigInteger('order_id_user');
            $table->string('order_metode_pengambilan', 20)->default('antar_toko');
            $table->string('order_alamat_jemput', 255)->nullable();
            $table->dateTime('order_slot_waktu')->nullable();
            $table->string('order_metode_pembayaran', 20)->default('tunai');
            $table->string('order_catatan', 500)->nullable();
            $table->decimal('order_subtotal', 12, 2)->default(0);
            $table->decimal('order_total', 12, 2)->default(0);
            $table->unsignedBigInteger('order_status_id');
            $table->dateTime('order_estimasi_selesai')->nullable();
            $table->timestamps();

            $table->foreign('order_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('order_status_id')->references('order_status_id')->on('order_status')->restrictOnDelete();
        });

        Schema::create('order_item', function (Blueprint $table) {
            $table->id('order_item_id');
            $table->unsignedBigInteger('order_item_id_order');
            $table->unsignedBigInteger('order_item_id_product');
            $table->string('order_item_nama_product', 100);
            $table->string('order_item_satuan', 10);
            $table->decimal('order_item_harga', 12, 2);
            $table->integer('order_item_qty')->default(1);
            $table->decimal('order_item_subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('order_item_id_order')->references('order_id')->on('order')->cascadeOnDelete();
        });

        Schema::create('order_status_log', function (Blueprint $table) {
            $table->id('order_status_log_id');
            $table->unsignedBigInteger('order_status_log_id_order');
            $table->unsignedBigInteger('order_status_log_id_from')->nullable();
            $table->unsignedBigInteger('order_status_log_id_to');
            $table->unsignedBigInteger('order_status_log_id_user');
            $table->string('order_status_log_keterangan', 255)->nullable();
            $table->timestamps();

            $table->foreign('order_status_log_id_order')->references('order_id')->on('order')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_log');
        Schema::dropIfExists('order_item');
        Schema::dropIfExists('order');
    }
};
