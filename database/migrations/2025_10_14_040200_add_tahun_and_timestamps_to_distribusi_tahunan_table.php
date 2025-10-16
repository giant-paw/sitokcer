<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distribusi_tahunan', function (Blueprint $table) {
            // Menambahkan kolom tahun setelah kolom target_penyelesaian
            $table->year('tahun_kegiatan')->nullable()->after('target_penyelesaian');

            // Menambahkan kolom created_at dan updated_at
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::table('distribusi_tahunan', function (Blueprint $table) {
            $table->dropColumn('tahun_kegiatan');
            $table->dropTimestamps();
        });
    }
};
