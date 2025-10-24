<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Tambahkan Hash facade
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'user'; // Sesuaikan nama tabel

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_user'; // Sesuaikan primary key

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'tim', // Tambahkan kolom tim
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', // Biarkan ini jika ada
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Biarkan jika ada
        'password' => 'hashed', // Otomatis hash saat di-set (Laravel 10+)
    ];

    /**
     * Mutator untuk hashing password (jika Laravel < 10 atau ingin eksplisit).
     * Jika Anda menggunakan Laravel 10+, $casts['password' => 'hashed'] sudah cukup.
     * Jika tidak, gunakan method ini.
     *
     * @param  string  $value
     * @return void
     */
    // public function setPasswordAttribute($value)
    // {
    //     // Hanya hash jika value tidak kosong (untuk update opsional)
    //     if (!empty($value)) {
    //         $this->attributes['password'] = Hash::make($value);
    //     }
    // }
}