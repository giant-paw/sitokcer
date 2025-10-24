<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            
            // Kolom 'username' Anda, kita tambahkan unique agar tidak ada yg sama
            $table->string('username')->unique(); 
            
            $table->string('email')->unique(); // 'email' harus 'unique'
            $table->timestamp('email_verified_at')->nullable(); // Untuk fitur verifikasi email
            $table->string('password'); // Kolom ini akan menyimpan password yang SUDAH DI-HASH
            
            // Ini adalah kolom 'role' yang Anda butuhkan
            // Kita beri default 'user' agar aman
            $table->string('role')->default('user'); // 'admin' atau 'user'

            // Menggantikan kolom 'tim' Anda. Jika 'tim' beda, bisa ditambahkan lagi
            // $table->string('tim')->nullable(); 

            $table->rememberToken(); // Diperlukan untuk fitur "Remember Me"
            $table->softDeletes(); // Best practice! Menambah kolom 'deleted_at'
            $table->timestamps(); // Otomatis membuat 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};