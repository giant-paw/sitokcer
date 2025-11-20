<?php

namespace App\Models\produksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Models\Master\MasterKegiatan;
use App\Models\Master\MasterPetugas;

class ProduksiTahunan extends Model
{
    use HasFactory;

    protected $table = 'produksi_tahunan'; 
    protected $primaryKey = 'id_produksi'; 
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
