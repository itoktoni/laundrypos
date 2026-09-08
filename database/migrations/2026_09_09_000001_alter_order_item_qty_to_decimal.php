<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->decimal('order_item_qty', 10, 3)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->integer('order_item_qty')->default(1)->change();
        });
    }
};
