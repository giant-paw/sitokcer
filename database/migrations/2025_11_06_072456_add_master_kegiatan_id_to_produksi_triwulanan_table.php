<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produksi_triwulanan', function (Blueprint $table) {
            $table->integer('master_kegiatan_id') 
                  ->nullable()
                  ->after('id_produksi_triwulanan');
        });

        DB::statement('
            UPDATE produksi_triwulanan pt
            JOIN master_kegiatan mk ON pt.nama_kegiatan = mk.nama_kegiatan
            SET pt.master_kegiatan_id = mk.id_master_kegiatan
            WHERE pt.master_kegiatan_id IS NULL 
              AND mk.modul = "produksi_triwulanan";
        ');

        Schema::table('produksi_triwulanan', function (Blueprint $table) {
            $table->foreign('master_kegiatan_id', 'prod_triwulanan_master_keg_id_foreign')
                  ->references('id_master_kegiatan')
                  ->on('master_kegiatan')
                  ->onDelete('set null') 
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('produksi_triwulanan', function (Blueprint $table) {
            $table->dropForeign('prod_triwulanan_master_keg_id_foreign');
            $table->dropColumn('master_kegiatan_id');
        });
    }
};