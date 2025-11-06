<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFkKegiatanToProduksiCaturwulanTable extends Migration
{
    public function up()
    {
        // Langkah A: Tambah kolom
        Schema::table('produksi_caturwulanan', function (Blueprint $table) {
            // Pastikan tipe data 'integer' agar cocok dengan int(11) di master
            $table->integer('master_kegiatan_id')->nullable()->after('id_produksi_caturwulanan'); 
        });

        // Langkah B: Backfill (Isi data lama yang cocok)
        // Ini akan menghubungkan "Ubinan Padi Palawija (2)" ke master baru
        DB::statement('
            UPDATE produksi_caturwulanan pc
            JOIN master_kegiatan mk ON pc.nama_kegiatan = mk.nama_kegiatan
            SET pc.master_kegiatan_id = mk.id_master_kegiatan
            WHERE pc.master_kegiatan_id IS NULL 
              AND mk.modul = "produksi_caturwulanan";
        ');

        // Langkah C: Tambah Foreign Key Constraint
        Schema::table('produksi_caturwulanan', function (Blueprint $table) {
            $table->foreign('master_kegiatan_id')
                  ->references('id_master_kegiatan')
                  ->on('master_kegiatan')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('produksi_caturwulanan', function (Blueprint $table) {
            $table->dropForeign(['master_kegiatan_id']);
            $table->dropColumn('master_kegiatan_id');
        });
    }
}