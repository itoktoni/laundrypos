<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status', function (Blueprint $table) {
            $table->id('order_status_id');
            $table->unsignedBigInteger('order_status_id_laundry');
            $table->string('order_status_nama', 50);
            $table->integer('order_status_urutan');
            $table->boolean('order_status_is_batal')->default(false);
            $table->boolean('order_status_is_selesai')->default(false);
            $table->string('order_status_warna', 7)->default('#2563eb');
            $table->timestamps();

            $table->foreign('order_status_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status');
    }
};
