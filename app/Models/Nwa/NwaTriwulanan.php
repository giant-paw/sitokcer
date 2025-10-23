<?php

namespace App\Models\Nwa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NwaTriwulanan extends Model
{
    use hasfactory;
    protected $table = 'nwa_triwulanan';
    protected $primaryKey = 'id_nwa_triwulanan';
    public $timestamps = true;

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
        'target_penyelesaian' => 'date',
        'tanggal_pengumpulan' => 'date',
    ];
}
