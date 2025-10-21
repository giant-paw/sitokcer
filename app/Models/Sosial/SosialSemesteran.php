<?php

namespace App\Models\Sosial;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SosialSemesteran extends Model
{
    protected $table = 'sosial_semesteran';
    protected $primaryKey = 'id_sosial_semesteran';
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

    /**
     * [PERBAIKAN] Accessor untuk mengkonversi 'target_penyelesaian'
     * dari format string 'd/m/Y' menjadi objek Carbon saat diakses.
     * Ini akan memperbaiki error parsing secara permanen.
     *
     * @param  string|null  $value
     * @return \Carbon\Carbon|null
     */
    public function getTargetPenyelesaianAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {

            return Carbon::parse($value);
        }
    }


    protected $casts = [
        'tanggal_pengumpulan' => 'datetime',
    ];
}

