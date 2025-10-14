<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_sosial_tahunan_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sosial_tahunan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');                 // contoh: Polkam, Podes, dll
            $table->string('blok_sensus')->nullable();       // atau 'responden'
            $table->string('pencacah');                      // bisa fk ke tabel petugas jika sudah ada
            $table->string('pengawas');                      // idem
            $table->date('tgl_target')->nullable();          // tanggal target penyelesaian
            $table->enum('flag_progress', ['Belum Mulai', 'Proses', 'Selesai'])->default('Belum Mulai');
            $table->date('tgl_pengumpulan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sosial_tahunan');
    }
};
