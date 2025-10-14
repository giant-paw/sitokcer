<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>@yield('title', 'Sitokcer')</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex h-screen">
        
        @include('sidebar')

        {{-- Area konten utama --}}
        <div class="flex-1 flex flex-col">
            
            {{-- Header / Navbar --}}
            <header class="bg-white shadow-md w-full p-4 flex justify-between items-center">
                {{-- Judul halaman yang dinamis --}}
                <h1 class="text-xl font-semibold text-gray-700">
                    @yield('header-title', 'Dashboard')
                </h1>

                <div class="relative">
                    {{-- Tombol untuk membuka/menutup dropdown --}}
                    <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                        <span class="text-gray-600 font-medium">Nama User</span>
                        {{-- Icon Avatar Sederhana --}}
                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                           U
                        </div>
                        {{-- Icon Panah Dropdown --}}
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Konten Dropdown --}}
                    <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                        <div class="border-t border-gray-100"></div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
                </header>

            {{-- Konten Utama Halaman --}}
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('user-menu-button');
            const dropdown = document.getElementById('user-menu-dropdown');

            // Toggle dropdown saat tombol diklik
            menuButton.addEventListener('click', function() {
                dropdown.classList.toggle('hidden');
            });

            // Tutup dropdown saat mengklik di luar area menu
            window.addEventListener('click', function(e) {
                if (!menuButton.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>

