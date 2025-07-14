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
        Schema::create('setoran_paguyubans', function (Blueprint $table) {
            $table->id();
            $table->string('kelas')->nullable();
            $table->json('bulan_setor')->nullable();
            $table->integer('jumlah');
            $table->integer('total');
            $table->string(column: 'bukti_setor')->nullable();
            $table->integer('kekurangan')->nullable();
            $table->boolean('status_verifikasi');
            $table->foreignId('besaran_biaya_id')->constrained('besaran_biayas')->onDelete('cascade');
            $table->foreignId('pengurus_kelas_id')->constrained('orang_tuas')->onDelete('cascade');
            $table->foreignId('pengurus_besar_id')->nullable()->constrained('orang_tuas')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_paguyubans');
    }
};
