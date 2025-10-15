<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistribusiTahunan extends Model
{
    use HasFactory;

    protected $table = 'sitokcer_distribusi_tahunan';

    /**
     * Kolom yang diizinkan untuk diisi secara massal.
     * Menggunakan guarded kosong berarti semua kolom boleh diisi.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Jika primary key Anda bukan 'id', uncomment dan sesuaikan baris ini.
     *
     * @var string
     */
    // protected $primaryKey = 'id_distribusi';
}
