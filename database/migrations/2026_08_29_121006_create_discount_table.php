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
        Schema::create('discount', function (Blueprint $table) {
            $table->id('discount_id');
            $table->unsignedBigInteger('discount_id_laundry');
            $table->string('discount_nama', 100);
            $table->string('discount_kode', 30);
            $table->enum('discount_tipe', ['persen', 'nominal']);
            $table->decimal('discount_nilai', 12, 2);
            $table->decimal('discount_min_pembelian', 12, 2)->default(0);
            $table->decimal('discount_max_diskon', 12, 2)->nullable();
            $table->date('discount_mulai')->nullable();
            $table->date('discount_selesai')->nullable();
            $table->boolean('discount_is_aktif')->default(true);
            $table->timestamps();

            $table->foreign('discount_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->unique(['discount_id_laundry', 'discount_kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount');
    }
};
