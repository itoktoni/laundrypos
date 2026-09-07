<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_schedule', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('schedule_id_laundry');
            $table->unsignedBigInteger('schedule_id_user');
            // 1=Senin .. 7=Minggu (ISO)
            $table->tinyInteger('schedule_hari');
            $table->time('schedule_jam_masuk');
            $table->time('schedule_jam_pulang');
            $table->timestamps();

            $table->foreign('schedule_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('schedule_id_user')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['schedule_id_laundry', 'schedule_id_user', 'schedule_hari'], 'staff_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedule');
    }
};
