<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesin', function (Blueprint $table) {
            $table->id('mesin_id');
            $table->foreignId('mesin_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->string('mesin_kode', 30);
            $table->string('mesin_nama', 100);
            $table->string('mesin_jenis', 20)->comment('washer, dryer, setrika, boiler, lainnya');
            $table->string('mesin_merk', 100)->nullable();
            $table->string('mesin_kapasitas', 30)->nullable()->comment('mis. 15 kg');
            $table->date('mesin_tanggal_beli')->nullable();
            $table->integer('mesin_interval_hari')->default(90)->comment('interval service rutin (hari)');
            $table->string('mesin_status', 20)->default('aktif')->comment('aktif, rusak, maintenance');
            $table->string('mesin_keterangan', 500)->nullable();
            $table->timestamps();

            $table->unique(['mesin_id_laundry', 'mesin_kode'], 'mesin_unik_per_laundry');
        });

        Schema::create('mesin_service', function (Blueprint $table) {
            $table->id('service_id');
            $table->foreignId('mesin_service_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->foreignId('service_id_mesin')->constrained('mesin', 'mesin_id')->cascadeOnDelete();
            $table->string('service_nomor', 30)->comment('nomor WO otomatis');
            $table->date('service_tanggal');
            $table->string('service_jenis', 20)->comment('rutin, perbaikan, darurat');
            $table->string('service_keluhan', 500)->nullable();
            $table->string('service_tindakan', 500)->nullable();
            $table->string('service_teknisi', 100)->nullable();
            $table->decimal('service_biaya', 14, 2)->default(0);
            $table->string('service_foto', 255)->nullable();
            $table->boolean('service_is_selesai')->default(false);
            $table->string('service_keterangan', 500)->nullable();
            $table->timestamps();

            $table->unique(['mesin_service_id_laundry', 'service_nomor'], 'wo_unik_per_laundry');
            $table->index(['service_id_mesin', 'service_tanggal'], 'service_riwayat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesin_service');
        Schema::dropIfExists('mesin');
    }
};
