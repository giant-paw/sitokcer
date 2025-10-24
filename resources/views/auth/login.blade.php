@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">
            <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" style="height: 50px;">
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
@endsection