<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- 1. TAMBAHKAN INI (karena ada deleted_at)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Support\Facades\Hash; // Tidak perlu jika pakai $casts 'hashed'

class User extends Authenticatable
{
    // 2. TAMBAHKAN INI
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'users'; // Ini sudah benar

    /**
     * Primary key tabel.
     *
     * @var string
     */
    // 3. HAPUS/KOMENTARI BARIS INI KARENA PRIMARY KEY ANDA 'id' (default)
    // protected $primaryKey = 'id_user'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',     // <-- 4. GANTI 'nama' menjadi 'name'
        'username', // Ini sudah benar
        'email',    // Ini sudah benar
        'password', // Ini sudah benar
        'role',     // <-- 5. GANTI 'tim' menjadi 'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ini sudah benar
    ];
}
