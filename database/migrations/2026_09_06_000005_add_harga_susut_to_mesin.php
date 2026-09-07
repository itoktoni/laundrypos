<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesin', function (Blueprint $table) {
            $table->decimal('mesin_harga', 14, 2)->default(0)->after('mesin_kapasitas')->comment('harga perolehan');
            $table->integer('mesin_umur_tahun')->default(5)->after('mesin_harga')->comment('umur manfaat (tahun) untuk garis lurus');
            $table->decimal('mesin_nilai_residu', 14, 2)->default(0)->after('mesin_umur_tahun')->comment('nilai sisa akhir umur');
        });
    }

    public function down(): void
    {
        Schema::table('mesin', function (Blueprint $table) {
            $table->dropColumn(['mesin_harga', 'mesin_umur_tahun', 'mesin_nilai_residu']);
        });
    }
};
