<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id('customer_id');
            $table->unsignedBigInteger('customer_id_laundry');
            $table->string('customer_nama', 100);
            $table->string('customer_telepon', 15);
            $table->string('customer_email', 254)->nullable();
            $table->string('customer_alamat', 255)->nullable();
            $table->timestamps();

            $table->foreign('customer_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->unique(['customer_id_laundry', 'customer_telepon']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
