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
        Schema::create('transaksi_keuangans', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_transaksi', ['pengeluaran', 'pemasukan']);
            $table->string(column: 'kategori')->nullable();
            $table->integer('jumlah');
            $table->string(column: 'bukti_transaksi')->nullable();
            $table->date('tanggal_transaksi');
            $table->boolean('status_pembayaran')->default(false);
           $table->foreignId('pengurus_besar_id')->constrained('orang_tuas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangans');
    }
};
