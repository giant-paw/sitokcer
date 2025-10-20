<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKegiatan extends Model
{
    use HasFactory;

    protected $table = 'master_kegiatan';

    protected $primaryKey = 'id_master_kegiatan';

    public $timestamps = false;

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
    ];
}
