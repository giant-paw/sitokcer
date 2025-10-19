<?php

namespace App\Models\Distribusi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistribusiTahunan extends Model
{
    protected $table = 'distribusi_tahunan'; 
    protected $primaryKey = 'id_distribusi'; 
    public $timestamps = true; 

    protected $fillable = [
        'id_master_kegiatan',
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'tahun_kegiatan',
        'flag_progress',
        'tanggal_pengumpulan'
    ];

    // Relasi ke MasterKegiatan
    public function masterKegiatan()
    {
        return $this->belongsTo(MasterKegiatan::class, 'id_master_kegiatan', 'id_master_kegiatan');
    }
}
