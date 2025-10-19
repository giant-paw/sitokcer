<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPetugas extends Model
{
    use HasFactory;

    /**
     * 
     *
     * @var string
     */
    protected $table = 'master_petugas';

    /**
     * 
     *
     * @var string
     */
    protected $primaryKey = 'id_petugas';

    /**
     * 
     *
     * @var array
     */
    protected $fillable = [
    'nama_petugas',
    'kategori',
    'nik',
    'alamat',
    'no_hp',
    'posisi',
    'email',
    'pendidikan',
    'tgl_lahir',
    'kecamatan',
    'pekerjaan'
];

}