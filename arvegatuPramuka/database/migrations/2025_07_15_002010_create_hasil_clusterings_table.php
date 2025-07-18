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
        Schema::create('hasil_clusterings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_siswa');
            $table->string('kategori_lomba');
            $table->decimal('rata_rata_skor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_clusterings');
    }
};
