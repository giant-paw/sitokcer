<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            // Tambahkan kolom 'tim' setelah 'deskripsi'
            $table->string('tim', 50)->after('deskripsi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->dropColumn('tim');
        });
    }
};