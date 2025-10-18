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
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'tahun_kegiatan',
        'flag_progress',
        'tanggal_pengumpulan'
    ];
}
