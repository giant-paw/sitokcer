<?php

namespace App\Models\produksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProduksiTriwulanan extends Model
{
    protected $table = 'produksi_triwulanan'; 
    protected $primaryKey = 'id_produksi_triwulanan'; 
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
