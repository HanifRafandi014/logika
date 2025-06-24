<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lombas', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_lomba');
            $table->integer('jumlah_siswa');
            $table->boolean('status');
            $table->json('nilai_akademiks')->nullable();
            $table->json('nilai_non_akademiks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('lombas', function (Blueprint $table) {
            $table->dropColumn('nilai_akademiks');
            $table->dropColumn('nilai_non_akademiks');
        });
    }
};
