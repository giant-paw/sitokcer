<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NwaTriwulanan extends Model
{
    protected $table = 'nwa_triwulanan';
    protected $primaryKey = 'id_nwa_triwulanan';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',        // contoh: "SKLNP-TW1", "Snaper-TW2", "SKTNP-TW3"
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',  // date / datetime (nullable)
        'flag_progress',        // Belum Mulai / Proses / Selesai
        'tanggal_pengumpulan',  // date / datetime (nullable)
    ];
}
