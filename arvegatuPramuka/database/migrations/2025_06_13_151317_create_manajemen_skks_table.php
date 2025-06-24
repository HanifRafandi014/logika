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
        Schema::create('manajemen_skks', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_skk');
            $table->text('keterangan_skk');
            $table->enum('tingkatan', ['purwa', 'madya', 'utama']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manajemen_skks');
    }
};
