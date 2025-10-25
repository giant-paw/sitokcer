<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sitokcer</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* * Ini adalah style untuk meletakkan card login di tengah halaman.
         * Ini meniru style 'guest-wrapper' dari layout app.blade.php
         */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Latar belakang abu-abu muda */
            font-family: sans-serif;
        }

        /* * Ini membatasi lebar card agar tidak terlalu lebar di layar desktop.
         */
        .login-wrapper {
            width: 100%;
            max-width: 450px; /* Lebar maksimum card */
            padding: 1rem; /* Jarak dari pinggir di layar HP */
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <!-- INI ADALAH KODE CARD YANG ANDA BERIKAN -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <!-- Menggunakan placeholder jika logo.png tidak ada -->
                    <img src="{{ asset('logo.png') }}" 
                         onerror="this.onerror=null; this.src='https://placehold.co/150x50/34495e/ffffff?text=Sitokcer&font=poppins';" 
                         alt="Logo Sitokcer" 
                         style="height: 50px;">
                    <h3 class="fw-bold mb-0 mt-2">Login Sitokcer</h3>
                </div>

                @if (session('status'))
                    <div class="alert alert-success mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">{{ __('Username') }}</label>
                        <input id="username" class="form-control" type="text" name="username" value="{{ old('username') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
                    </div>

                    <div class="mb-3 form-check">
                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                        <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mb-3">
                        @if (Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            {{ __('Log in') }}
                        </button>
                    </div>
                    
                    @if (Route::has('register'))
                    <p class="text-center mt-4 mb-0 small">
                        Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
                    </p>
                    @endif

                </form>
            </div>
        </div>
        <!-- AKHIR KODE CARD -->
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
