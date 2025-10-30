<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Distribusi\DistribusiTahunan;

class MasterKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_kegiatan';

    protected $primaryKey = 'id_master_kegiatan';

    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tim',
        'target'
    ];

    public function distribusiTahunan()
    {
        return $this->hasMany(DistribusiTahunan::class, 'nama_kegiatan', 'nama_kegiatan');
    }
}
