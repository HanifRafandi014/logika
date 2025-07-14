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
        Schema::create('manajemen_skus', function (Blueprint $table) {
            $table->id();
            $table->text('keterangan_sku');
            $table->text('item_pencapaian_sku');
            $table->enum('tingkatan', ['Ramu', 'Rakit', 'Terap']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manajemen_skus');
    }
};
