<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModulToMasterKegiatanTable extends Migration
{
    public function up()
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            // Tambahkan kolom setelah 'tim' (atau sesuaikan posisinya)
            // Buat nullable agar data lama tidak error, lalu isi manual
            $table->string('modul', 50)->nullable()->after('tim'); 
            
            // Tambahkan index agar query WHERE modul lebih cepat
            $table->index('modul'); 
        });
    }

    public function down()
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->dropIndex(['modul']); // Hapus index dulu
            $table->dropColumn('modul');
        });
    }
}