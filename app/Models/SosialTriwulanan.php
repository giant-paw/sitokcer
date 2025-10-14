<?php
// app/Models/SosialTriwulanan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SosialTriwulanan extends Model
{
    protected $table = 'sosial_triwulanan';
    protected $primaryKey = 'id_sosial_triwulanan';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',        // contoh: "Seruti-TW1"
        'BS_Responden',         // contoh: "002B"
        'pencacah',
        'pengawas',
        'target_penyelesaian',  // tanggal (nullable)
        'flag_progress',        // "Selesai" / dst
        'tanggal_pengumpulan',  // datetime (nullable)
    ];
}
