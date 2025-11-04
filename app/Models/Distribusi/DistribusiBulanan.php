<?php

namespace App\Models\Distribusi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Master\MasterKegiatan;

class DistribusiBulanan extends Model
{
    use HasFactory;

    protected $table = 'distribusi_bulanan'; 
    protected $primaryKey = 'id_distribusi_bulanan'; 
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
