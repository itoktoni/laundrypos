<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id('product_id');
            $table->unsignedBigInteger('product_id_laundry');
            $table->unsignedBigInteger('product_id_kategori');
            $table->string('product_nama', 100);
            $table->string('product_satuan', 10)->default('kg');
            $table->decimal('product_harga_dasar', 12, 2);
            $table->integer('product_estimasi_jam')->default(24);
            $table->string('product_deskripsi', 500)->nullable();
            $table->boolean('product_is_aktif')->default(true);
            $table->timestamps();

            $table->foreign('product_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('product_id_kategori')->references('kategori_id')->on('kategori')->cascadeOnDelete();
            $table->unique(['product_id_laundry', 'product_id_kategori', 'product_nama'], 'product_unik_per_kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
