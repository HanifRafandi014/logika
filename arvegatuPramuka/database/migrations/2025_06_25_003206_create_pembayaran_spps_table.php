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
        Schema::create('pembayaran_spps', function (Blueprint $table) {
            $table->id();
            $table->json('bulan_bayar')->nullable();
            $table->string(column: 'bukti_bayar')->nullable();
            $table->boolean('status_pembayaran');
            $table->foreignId('besaran_biaya_id')->nullable()->constrained('besaran_biayas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('orang_tua_id')->nullable()->constrained('orang_tuas')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_spps');
    }
};
