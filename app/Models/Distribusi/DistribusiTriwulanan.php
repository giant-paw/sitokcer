<?php

namespace App\Models\Distribusi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Master\MasterKegiatan;
use App\Models\Master\MasterPetugas;
use Carbon\Carbon;

class DistribusiTriwulanan extends Model
{
    use HasFactory;

    protected $table = 'distribusi_triwulanan'; 
    protected $primaryKey = 'id_distribusi_triwulanan'; 
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
        // 'master_kegiatan_id' adalah Foreign Key di tabel INI (distribusi_triwulanan)
        // 'id_master_kegiatan' adalah Primary Key di tabel SANA (master_kegiatan)
        return $this->belongsTo(MasterKegiatan::class, 'master_kegiatan_id', 'id_master_kegiatan');
    }
}
