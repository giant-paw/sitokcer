<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- Menggunakan judul dinamis, dengan judul default 'Aplikasi Saya' --}}
    <title>@yield('title', 'Aplikasi Saya')</title>

    {{-- 1. Memuat Tailwind CSS dari CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex h-screen">
        
        {{-- 2. Memanggil Sidebar yang sudah di-styling dengan Tailwind --}}
        @include('sidebar')

        {{-- Area konten utama --}}
        <div class="flex-1 flex flex-col">
            
            {{-- 3. Menambahkan Header / Navbar --}}
            <header class="bg-white shadow-md w-full p-4 flex justify-between items-center">
                {{-- Judul halaman yang dinamis --}}
                <h1 class="text-xl font-semibold text-gray-700">
                    @yield('header-title', 'Dashboard')
                </h1>
                <div class="user-menu">
                    <span class="text-gray-600">Selamat datang, User!</span>
                </div>
            </header>

            {{-- 4. Konten Utama Halaman --}}
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>

    </div>

</body>
</html>

