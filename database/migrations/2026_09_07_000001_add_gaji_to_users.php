<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('gaji_pokok', 15, 2)->default(1000000)->after('phone');
            $table->decimal('gaji_absensi', 15, 2)->default(20000)->after('gaji_pokok');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gaji_pokok', 'gaji_absensi']);
        });
    }
};
