<?php
// app/Models/SosialTahunan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SosialTahunan extends Model
{
    protected $table = 'sosial_tahunan';
    protected $primaryKey = 'id_sosial';
    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',
        'BS_Responden',
        'pencacah',
        'pengawas',
        'target_penyelesaian',
        'flag_progress',
        'tanggal_pengumpulan',
    ];

    private function fmt($v)
    {
        if (!$v) return null;
        try {
            return Carbon::createFromFormat('d/m/Y', $v)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $v;
        }
    }
    public function getTargetPenyelesaianFormattedAttribute()
    {
        return $this->fmt($this->target_penyelesaian);
    }
    public function getTanggalPengumpulanFormattedAttribute()
    {
        return $this->fmt($this->tanggal_pengumpulan);
    }
}
