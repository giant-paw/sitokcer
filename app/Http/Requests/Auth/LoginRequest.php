<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
<<<<<<< HEAD
<<<<<<< HEAD
        // UBAH DARI 'email' MENJADI 'username'
=======
        // [ DIUBAH ] dari 'email' menjadi 'username'
>>>>>>> b5f047e9b0b9758bca457f90c4fe8bf0e95f9600
        return [
            'username' => ['required', 'string'],
=======
        // KEMBALIKAN KE 'email'
        return [
            'email' => ['required', 'string', 'email'], // Ditambah validasi email
>>>>>>> baruuujay
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

<<<<<<< HEAD
<<<<<<< HEAD
        // UBAH DARI 'email' MENJADI 'username'
=======
        // [ DIUBAH ] dari $this->only('email', 'password')
>>>>>>> b5f047e9b0b9758bca457f90c4fe8bf0e95f9600
        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
<<<<<<< HEAD
                // UBAH DARI 'email' MENJADI 'username'
=======
                // [ DIUBAH ] dari 'email' menjadi 'username'
>>>>>>> b5f047e9b0b9758bca457f90c4fe8bf0e95f9600
                'username' => __('auth.failed'),
=======
        // KEMBALIKAN KE 'email'
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                // KEMBALIKAN KE 'email'
                'email' => __('auth.failed'),
>>>>>>> baruuujay
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
<<<<<<< HEAD
<<<<<<< HEAD
            // UBAH DARI 'email' MENJADI 'username'
=======
            // [ DIUBAH ] dari 'email' menjadi 'username'
>>>>>>> b5f047e9b0b9758bca457f90c4fe8bf0e95f9600
            'username' => trans('auth.throttle', [
=======
            // KEMBALIKAN KE 'email'
            'email' => trans('auth.throttle', [
>>>>>>> baruuujay
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
<<<<<<< HEAD
<<<<<<< HEAD
        // UBAH DARI 'email' MENJADI 'username'
=======
        // [ DIUBAH ] dari $this->input('email')
>>>>>>> b5f047e9b0b9758bca457f90c4fe8bf0e95f9600
        return Str::transliterate(Str::lower($this->input('username')).'|'.$this->ip());
    }
}
=======
        // KEMBALIKAN KE 'email'
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}

>>>>>>> baruuujay
