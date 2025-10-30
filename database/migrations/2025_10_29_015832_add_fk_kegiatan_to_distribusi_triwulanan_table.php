<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distribusi_triwulanan', function (Blueprint $table) {
            $table->integer('master_kegiatan_id')->nullable()->after('id_distribusi_triwulanan');
        });

        // 2. Update data yang sudah ada (Backfill)
        // Cocokkan nama_kegiatan di tabel ini dengan nama_kegiatan di master
        DB::statement('
            UPDATE distribusi_triwulanan dt
            JOIN master_kegiatan mk ON dt.nama_kegiatan = mk.nama_kegiatan
            SET dt.master_kegiatan_id = mk.id_master_kegiatan
            WHERE dt.master_kegiatan_id IS NULL
        ');

        // 3. Tambahkan Foreign Key Constraint
        Schema::table('distribusi_triwulanan', function (Blueprint $table) {

            $table->foreign('master_kegiatan_id')
                  ->references('id_master_kegiatan') // Asumsi PK di master adalah 'id_master_kegiatan'
                  ->on('master_kegiatan')
                  ->onDelete('restrict') // RESTRICT: Mencegah penghapusan master jika masih dipakai
                  ->onUpdate('cascade');  // CASCADE: Jika ID master berubah, di sini ikut berubah
        });
    }

    public function down()
    {
        Schema::table('distribusi_triwulanan', function (Blueprint $table) {
            $table->dropForeign(['master_kegiatan_id']);
            $table->dropColumn('master_kegiatan_id');
        });
    }
};