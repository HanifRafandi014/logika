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
        Schema::create('penilaian_skks', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_skk');
            $table->enum('tingkatan', ['purwa', 'madya', 'utama']);
            $table->boolean('status');
            $table->date('tanggal');
            $table->foreignId('pembina_id')->nullable()->constrained('pembinas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('manajemen_skk_id')->nullable()->constrained('manajemen_skks')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_skks');
    }
};
