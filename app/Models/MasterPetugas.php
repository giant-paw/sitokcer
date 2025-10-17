<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPetugas extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'master_petugas';

    /**
     * Primary key untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_petugas';

    /**
     * Atribut yang dapat diisi secara massal.
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
    'tmt_pengangkatan',
    'no_sk',
    'tgl_sk',
    'pejabat_pengangkatan',
    'foto',
    'status',
];



    /**
     * Menonaktifkan timestamps (created_at dan updated_at).
     *
     * @var bool
     */
    public $timestamps = false;
}

