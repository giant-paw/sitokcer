
<x-guest-layout>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" style="height: 50px;">
                <h3 class="fw-bold mb-0 mt-2">Login Sitokcer</h3>
            </div>

            <!-- Menampilkan status (jika ada) -->
            <x-auth-session-status class="mb-4" :status="session('status')" />
            
            <!-- Menampilkan error validasi (termasuk "email/password salah") -->
            {{-- Kita gunakan komponen error bawaan Breeze agar lebih rapi --}}
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

                <!-- UBAH KE EMAIL -->
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
                </div>

                <!-- Remember me -->
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
</x-guest-layout>

