<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distribusi_bulanan', function (Blueprint $table) {
            $table->integer('master_kegiatan_id')->nullable()->after('id_distribusi_bulanan');
        });

        DB::statement('
            UPDATE distribusi_bulanan dt
            JOIN master_kegiatan mk ON dt.nama_kegiatan = mk.nama_kegiatan
            SET dt.master_kegiatan_id = mk.id_master_kegiatan
            WHERE dt.master_kegiatan_id IS NULL
        ');

        Schema::table('distribusi_bulanan', function (Blueprint $table) {

            $table->foreign('master_kegiatan_id')
                  ->references('id_master_kegiatan')
                  ->on('master_kegiatan')
                  ->onDelete('restrict') 
                  ->onUpdate('cascade'); 
        });
    }

    public function down()
    {
        Schema::table('distribusi_bulanan', function (Blueprint $table) {
            $table->dropForeign(['master_kegiatan_id']);
            $table->dropColumn('master_kegiatan_id');
        });
    }
};
