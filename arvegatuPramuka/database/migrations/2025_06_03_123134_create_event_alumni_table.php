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
        Schema::create('event_alumni', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_event');
            $table->string('judul');
            $table->string('gambar');
            $table->string('keterangan');
            $table->foreignId('alumni_id')->nullable()->constrained('alumnis')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_alumni');
    }
};
