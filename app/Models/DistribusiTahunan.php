<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistribusiTahunan extends Model
{
    protected $table = 'distribusi_tahunan'; // Specify your table name
    protected $primaryKey = 'id_distribusi'; // Set primary key to 'id_distribusi'
    public $timestamps = true; // Enable timestamps (created_at and updated_at)

    // Specify the fillable fields
    protected $fillable = [
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'tahun_kegiatan',
        'flag_progress',
        'tanggal_pengumpulan'
    ];
}
