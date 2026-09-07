<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense', function (Blueprint $table) {
            $table->id('expense_id');
            $table->foreignId('expense_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->string('expense_nama', 255);
            $table->string('expense_kategori', 100)->comment('Listrik, Air, Gaji, Sewa, Perlengkapan, Operasional, Lainnya');
            $table->decimal('expense_nominal', 12, 2);
            $table->date('expense_tanggal');
            $table->string('expense_catatan', 500)->nullable();
            $table->string('expense_metode_pembayaran', 20)->default('tunai')->comment('tunai, transfer, dompet_digital');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense');
    }
};
