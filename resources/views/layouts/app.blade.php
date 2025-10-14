<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sitokcer')</title>
    
    {{-- CSS Global Anda --}}
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    
    {{-- CSS Sidebar --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans">

    <div x-data="{ sidebarOpen: true }" class="flex flex-col h-screen">

        {{-- Header --}}
        <header class="bg-white shadow-md w-full p-4 flex justify-between items-center z-20 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-gray-800">
                    <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" class="h-10 w-10 object-contain">
                    <h1 class="text-2xl font-bold font-['Poppins',_sans-serif] tracking-wide hidden sm:block">
                        Sitokcer
                    </h1>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <h1 class="text-xl font-semibold text-gray-800 hidden md:block mr-auto">
                    @yield('header-title', 'Dashboard')
                </h1>
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                        <span class="text-gray-800 font-medium hidden lg:block">Nama User</span>
                        <div
                            class="w-8 h-8 rounded-full bg-[#2c3e50] flex items-center justify-center text-white font-bold">
                            U
                        </div>
                    </button>
                    <div id="user-menu-dropdown"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                        <div class="border-t border-gray-100"></div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        {{-- Container Flex untuk Sidebar dan Content --}}
        <div class="flex flex-1 overflow-hidden">

            {{-- Overlay untuk mobile --}}
            <div x-show="sidebarOpen" @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:leave="transition-opacity ease-linear duration-300" 
                x-transition:enter-start="opacity-0"
                x-transition:leave-start="opacity-100" 
                class="fixed inset-0 bg-gray-900/50 z-30 lg:hidden"
                aria-hidden="true">
            </div>

            {{-- Sidebar dengan lebar tetap --}}
            <aside x-show="sidebarOpen" 
                x-transition:enter="transition ease-in-out duration-300"
                x-transition:enter-start="-translate-x-full" 
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300" 
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="w-[270px] flex-shrink-0 border-r border-white/20 fixed lg:relative inset-y-0 left-0 z-40 lg:z-0">
                @include('sidebar')
            </aside>

            {{-- Main Content - FLEX TANPA MARGIN --}}
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.getElementById('user-menu-button');
            const dropdown = document.getElementById('user-menu-dropdown');

            if (menuButton) {
                menuButton.addEventListener('click', function () {
                    dropdown.classList.toggle('hidden');
                });
            }

            window.addEventListener('click', function (e) {
                if (menuButton && !menuButton.contains(e.target) && dropdown && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>

     <script src="{{ asset('js/sidebar.js') }}"></script>
</body>

</html>