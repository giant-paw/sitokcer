<?php

namespace App\Models\Sosial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Master\MasterKegiatan;

class SosialSemesteran extends Model
{
    use HasFactory;
    protected $table = 'sosial_semesteran';
    protected $primaryKey = 'id_sosial_semesteran';
    public $timestamps = true; // Perbaiki typo

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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_penyelesaian' => 'datetime', 
        'tanggal_pengumpulan' => 'datetime', 
    ];

     public function masterKegiatan()
    {
        return $this->belongsTo(MasterKegiatan::class, 'master_kegiatan_id', 'id_master_kegiatan');
    }
    
}
