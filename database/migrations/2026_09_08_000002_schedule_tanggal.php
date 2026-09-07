<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_schedule', function (Blueprint $table) {
            $table->dropForeign(['schedule_id_laundry']);
            $table->dropForeign(['schedule_id_user']);
            $table->dropUnique('staff_schedule_unique');
            $table->date('schedule_tanggal')->nullable()->after('schedule_id_user');
        });

        // Salin hari (minggu berjalan) menjadi tanggal konkret bila ada data.
        foreach (DB::table('staff_schedule')->get() as $row) {
            $date = now()->startOfWeek()->addDays(((int) $row->schedule_hari) - 1)->toDateString();
            DB::table('staff_schedule')->where('schedule_id', $row->schedule_id)->update(['schedule_tanggal' => $date]);
        }

        Schema::table('staff_schedule', function (Blueprint $table) {
            $table->dropColumn('schedule_hari');
            $table->foreign('schedule_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('schedule_id_user')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['schedule_id_laundry', 'schedule_id_user', 'schedule_tanggal'], 'staff_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::table('staff_schedule', function (Blueprint $table) {
            $table->dropUnique('staff_schedule_unique');
            $table->tinyInteger('schedule_hari')->nullable()->after('schedule_id_user');
            $table->dropColumn('schedule_tanggal');
        });
    }
};
