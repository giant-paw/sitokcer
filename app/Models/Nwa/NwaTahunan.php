<?php

namespace App\Models\Nwa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NwaTahunan extends Model
{

    use HasFactory;

    protected $table = 'nwa_tahunan';
    protected $primaryKey = 'id_nwa';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',       
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',  
        'flag_progress',        
        'tanggal_pengumpulan', 
    ];

    protected $casts = [
        'target_penyelesaian' => 'datetime',
        'tanggal_pengumpulan' => 'datetime',
    ];
}
