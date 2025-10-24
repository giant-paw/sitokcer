@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">
            <img src="{{ asset('logo.png') }}" alt="Logo Sitokcer" style="height: 50px;">
            <h3 class="fw-bold mb-0 mt-2">Buat Akun Baru</h3>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">{{ __('Username') }}</label>
                <input id="username" class="form-control" type="text" name="username" value="{{ old('username') }}" required autocomplete="username">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password">
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                    {{ __('Register') }}
                </button>
            </div>

            <p class="text-center mt-4 mb-0 small">
                Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
            </p>

        </form>
    </div>
</div>
@endsection