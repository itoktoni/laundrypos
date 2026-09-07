<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id_discount')->nullable()->after('order_total');
            $table->decimal('order_diskon', 12, 2)->default(0)->after('order_id_discount');
            $table->foreign('order_id_discount')->references('discount_id')->on('discount')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropForeign(['order_id_discount']);
            $table->dropColumn(['order_id_discount', 'order_diskon']);
        });
    }
};
