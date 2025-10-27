<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sitokcer</title>
    
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Inisialisasi font default Tailwind (Inter) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <!-- Container Utama - Mencentang card di tengah layar -->
    <div class="flex items-center justify-center min-h-screen p-4">

        <div class="w-full max-w-md">
            
            <!-- Session Status (Pesan sukses, dll) -->
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-lg" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors (Pesan error, misal: "email salah") -->
            @if ($errors->any())
                <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-lg" role="alert">
                    <div class="font-medium">Oops! Ada yang salah.</div>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Card Login -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200">
                <div class="p-8 sm:p-10">

                    <!-- Header Logo dan Judul -->
                    <div class="flex flex-col items-center mb-6">
                        <img src="{{ asset('logo.png') }}"
                             onerror="this.onerror=null; this.src='https://placehold.co/150x50/34495e/ffffff?text=Sitokcer&font=poppins';"
                             alt="Logo Sitokcer" class="h-12"> <!-- Tinggi diatur via class -->
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">Login Sitokcer</h3>
                    </div>

                    <!-- Form Login -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                            <input id="email" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
                            <input id="password" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                                   type="password" 
                                   name="password" 
                                   required
                                   autocomplete="current-password">
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center">
                            <input id="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" name="remember">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-900">{{ __('Remember me') }}</label>
                        </div>
                        
                        <!-- Tombol Login -->
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                {{ __('Log in') }}
                            </button>
                        </div>

                        <!-- Link "Forgot Password" dan "Sign Up" sudah dihapus -->

                    </form>
                </div>
            </div>
            <!-- AKHIR KODE CARD -->

        </div>
    </div>

</body>
</html>

