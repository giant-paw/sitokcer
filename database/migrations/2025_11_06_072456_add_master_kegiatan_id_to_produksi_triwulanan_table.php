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
        Schema::table('produksi_triwulanan', function (Blueprint $table) {
            // 1. Tambahkan kolomnya
            $table->unsignedBigInteger('master_kegiatan_id')
                  ->nullable() // 'nullable()' penting agar data lama tidak error
                  ->after('id_produksi_triwulanan'); // (Opsional) Menjaga kerapian

            $table->foreign('master_kegiatan_id')
                  ->references('id_master_kegiatan')
                  ->on('master_kegiatan')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_triwulanan', function (Blueprint $table) {
            // Hapus relasi DULU
            $table->dropForeign(['master_kegiatan_id']);
            // Hapus kolomnya
            $table->dropColumn('master_kegiatan_id');
        });
    }
};