<?php

namespace App\Models\Sosial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SosialSemesteran extends Model
{
    use HasFactory;
    protected $table = 'sosial_semesteran';
    protected $primaryKey = 'id_sosial_semesteran';
    public $timestamps = true; // Perbaiki typo

    protected $fillable = [
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'flag_progress',
        'tanggal_pengumpulan',
        'tahun_kegiatan', // Tambahkan jika ada kolom ini
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
}
