<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('denda_terlambat', 15, 2)->default(5000)->after('gaji_absensi');
            $table->decimal('denda_checkout', 15, 2)->default(5000)->after('denda_terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['denda_terlambat', 'denda_checkout']);
        });
    }
};
