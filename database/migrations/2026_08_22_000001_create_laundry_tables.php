<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry', function (Blueprint $table) {
            $table->id('laundry_id');
            $table->string('laundry_nama', 100);
            $table->string('laundry_kode', 20)->unique();
            $table->string('laundry_alamat', 255)->nullable();
            $table->string('laundry_telepon', 15)->nullable();
            $table->boolean('laundry_is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('laundry_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laundry_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('laundry_id')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['laundry_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_user');
        Schema::dropIfExists('laundry');
    }
};
