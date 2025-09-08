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
        Schema::table('nilai_non_akademiks', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('pembina_id')->nullable();

            $table->foreign('pembina_id')
                ->references('id')
                ->on('pembinas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_non_akademiks', function (Blueprint $table) {
            $table->dropForeign('pembina_id');
            $table->dropColumn('pembina_id');
        });
    }
};
