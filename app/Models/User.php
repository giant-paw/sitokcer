<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// TAMBAHKAN INI UNTUK softDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    // TAMBAHKAN TRAIT INI
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nama tabel yang terhubung dengan model.
     * * @var string
     */
    protected $table = 'users'; // Sesuaikan dengan nama tabel Anda

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // Sesuaikan dengan primary key Anda

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',     // Menggantikan 'nama'
        'username',
        'email',
        'password',
        'role',     // Menggantikan 'tim'
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
        'password' => 'hashed', // Biarkan ini untuk auto-hash
    ];
}