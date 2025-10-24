@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">
            <h3 class="fw-bold mb-3">Lupa Password</h3>
            <p class="text-muted">
                {{ __('Masukkan email Anda dan kami akan mengirimkan link untuk reset password.') }}
            </p>
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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                    {{ __('Email Password Reset Link') }}
                </button>
            </div>
            
            <p class="text-center mt-4 mb-0 small">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </p>

        </form>
    </div>
</div>
@endsection