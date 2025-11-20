<?php

namespace App\Models\Nwa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\MasterKegiatan;
use App\Models\Master\MasterPetugas;

class NwaTriwulanan extends Model
{
    use hasfactory;
    protected $table = 'nwa_triwulanan';
    protected $primaryKey = 'id_nwa_triwulanan';
    public $timestamps = true;

    protected $fillable = [
        'master_kegiatan_id',
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

    public function masterKegiatan()
    {
        return $this->belongsTo(MasterKegiatan::class, 'master_kegiatan_id', 'id_master_kegiatan');
    }
}
