<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NwaTahunan extends Model
{
    // Tabel & PK sesuai database kamu
    protected $table = 'nwa_tahunan';
    protected $primaryKey = 'id_nwa';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',        // contoh: SINASI, SKSPPI, dsb
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',  // date/datetime (nullable)
        'flag_progress',        // Belum Mulai / Proses / Selesai
        'tanggal_pengumpulan',  // date/datetime (nullable)
    ];
}
