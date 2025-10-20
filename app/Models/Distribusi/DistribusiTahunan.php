<?php

namespace App\Models\Distribusi;

use App\Models\Master\MasterKegiatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistribusiTahunan extends Model
{
    protected $table = 'distribusi_tahunan'; 
    protected $primaryKey = 'id_distribusi'; 
    public $timestamps = true; 

    protected $fillable = [
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'tahun_kegiatan',
        'flag_progress',
        'tanggal_pengumpulan'
    ];

    public function masterKegiatan()
    {
        return $this->belongsTo(MasterKegiatan::class, 'nama_kegiatan', 'nama_kegiatan');
    }
}
