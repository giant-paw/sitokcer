<?php

// app/Models/SosialSemesteran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SosialSemesteran extends Model
{
    protected $table = 'sosial_semesteran';
    protected $primaryKey = 'id_sosial_semesteran';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',        // contoh: "Sakernas Februari"
        'BS_Responden',         // contoh: "009B"
        'pencacah',
        'pengawas',
        'target_penyelesaian',  // tanggal (nullable)
        'flag_progress',        // "Selesai" / dst
        'tanggal_pengumpulan',  // datetime (nullable)
    ];

    // Format target_penyelesaian jika perlu manipulasi format
    public function getTargetPenyelesaianFormattedAttribute()
    {
        if (!$this->target_penyelesaian) return '-';
        try {
            // Parsing tanggal format dd/mm/yyyy
            return \Carbon\Carbon::createFromFormat('d/m/Y', $this->target_penyelesaian)->format('d/m/Y');
        } catch (\Exception $e) {
            // Jika format salah, langsung tampilkan apa adanya agar tidak error
            return $this->target_penyelesaian;
        }
    }


    // Format tanggal_pengumpulan jika perlu manipulasi format
    public function getTanggalPengumpulanFormattedAttribute()
    {
        if (!$this->tanggal_pengumpulan) return '-';
        try {
            // Parsing format dd/mm/yyyy atau dd/mm/yyyy H:i
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $this->tanggal_pengumpulan)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', substr($this->tanggal_pengumpulan, 0, 10))->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($this->tanggal_pengumpulan)->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $this->tanggal_pengumpulan;
        }
    }
}
