<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id('kategori_id');
            $table->unsignedBigInteger('kategori_id_laundry');
            $table->string('kategori_nama', 100);
            $table->string('kategori_deskripsi', 500)->nullable();
            $table->boolean('kategori_is_aktif')->default(true);
            $table->timestamps();

            $table->foreign('kategori_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->unique(['kategori_id_laundry', 'kategori_nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
