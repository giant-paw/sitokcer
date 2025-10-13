@extends('layouts.app')

{{-- Judul untuk tab browser --}}
@section('title', 'Dashboard Tim A')

{{-- Judul yang akan tampil di header halaman --}}
@section('header-title', 'Dashboard Tim A')

{{-- Konten utama halaman --}}
@section('content')
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-blue-600">Analitik Tim A</h2>
        <p class="text-gray-700">
            Selamat datang di pusat komando Tim A. Di sini Anda dapat melihat metrik performa, tugas yang sedang berjalan, dan progres proyek terbaru.
        </p>
        {{-- Anda bisa menambahkan chart atau tabel di sini --}}
    </div>
@endsection