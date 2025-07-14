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
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            $table->unsignedBigInteger('setoran_paguyuban_id')->nullable()->after('pengurus_besar_id');
            $table->foreign('setoran_paguyuban_id')
                  ->references('id')
                  ->on('setoran_paguyubans')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['setoran_paguyuban_id']);

            // Drop kolomnya
            $table->dropColumn('setoran_paguyuban_id');
        });
    }
};