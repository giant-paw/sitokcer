<?php

namespace App\Models\produksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Master\MasterKegiatan;
use App\Models\Master\MasterPetugas;

class ProduksiTriwulanan extends Model
{
    use HasFactory;
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

    protected $casts = [
        'target_penyelesaian' => 'datetime',
        'tanggal_pengumpulan' => 'datetime',
    ];


    public function masterKegiatan()
    {
        return $this->belongsTo(MasterKegiatan::class, 'master_kegiatan_id', 'id_master_kegiatan');
    }
}
