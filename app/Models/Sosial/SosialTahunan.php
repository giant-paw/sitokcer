<?php

namespace App\Models\Sosial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Master\MasterKegiatan;

class SosialTahunan extends Model
{
    use HasFactory;
    protected $table = 'sosial_tahunan';
    protected $primaryKey = 'id_sosial';
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
