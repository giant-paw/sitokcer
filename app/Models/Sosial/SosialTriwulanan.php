<?php 

namespace App\Models\Sosial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SosialTriwulanan extends Model
{
    use HasFactory;
    protected $table = 'sosial_triwulanan';
    protected $primaryKey = 'id_sosial_triwulanan';
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
        'target_penyelesaian' => 'datetime',
        'tanggal_pengumpulan' => 'datetime',
    ];
}