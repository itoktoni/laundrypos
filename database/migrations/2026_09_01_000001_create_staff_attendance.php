<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundry', function (Blueprint $table) {
            if (! Schema::hasColumn('laundry', 'laundry_latitude')) {
                $table->decimal('laundry_latitude', 10, 7)->nullable()->after('laundry_telepon');
            }
            if (! Schema::hasColumn('laundry', 'laundry_longitude')) {
                $table->decimal('laundry_longitude', 10, 7)->nullable()->after('laundry_latitude');
            }
            if (! Schema::hasColumn('laundry', 'laundry_radius_m')) {
                $table->integer('laundry_radius_m')->default(50)->after('laundry_longitude');
            }
        });

        if (Schema::hasTable('staff_attendance')) {
            return;
        }
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->unsignedBigInteger('attendance_id_laundry');
            $table->unsignedBigInteger('attendance_id_user');
            $table->date('attendance_tanggal');
            $table->dateTime('attendance_checkin_at')->nullable();
            $table->decimal('attendance_checkin_lat', 10, 7)->nullable();
            $table->decimal('attendance_checkin_lng', 10, 7)->nullable();
            $table->integer('attendance_checkin_jarak')->nullable(); // meters
            $table->boolean('attendance_checkin_valid')->default(false);
            $table->dateTime('attendance_checkout_at')->nullable();
            $table->decimal('attendance_checkout_lat', 10, 7)->nullable();
            $table->decimal('attendance_checkout_lng', 10, 7)->nullable();
            $table->integer('attendance_checkout_jarak')->nullable();
            $table->boolean('attendance_checkout_valid')->default(false);
            $table->string('attendance_status', 20)->default('hadir'); // hadir, izin, sakit
            $table->timestamps();

            $table->foreign('attendance_id_laundry')->references('laundry_id')->on('laundry')->cascadeOnDelete();
            $table->foreign('attendance_id_user')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['attendance_id_laundry', 'attendance_id_user', 'attendance_tanggal'], 'attendance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
        Schema::table('laundry', function (Blueprint $table) {
            $table->dropColumn(['laundry_latitude', 'laundry_longitude', 'laundry_radius_m']);
        });
    }
};
