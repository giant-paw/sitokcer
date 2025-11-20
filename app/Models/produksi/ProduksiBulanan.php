<?php

namespace App\Models\produksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Master\MasterKegiatan;
use App\Models\Master\MasterPetugas;

class ProduksiBulanan extends Model
{
    use HasFactory;
    protected $table = 'produksi_bulanan'; 
    protected $primaryKey = 'id_produksi_bulanan'; 
    public $timestamps = true; 

    protected $fillable = [
        'master_kegiatan_id',
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