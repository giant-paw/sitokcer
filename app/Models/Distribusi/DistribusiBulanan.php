<?php

namespace App\Models\Distribusi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistribusiBulanan extends Model
{
    protected $table = 'distribusi_bulanan'; 
    protected $primaryKey = 'id_distribusi_bulanan'; 
    public $timestamps = true; 

    protected $fillable = [
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'flag_progress',
        'tanggal_pengumpulan'
    ];
}
