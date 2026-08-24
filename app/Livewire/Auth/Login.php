<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Masuk - Work Activity')]
class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Validation rules in Indonesian.
     */
    protected function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Handle login authentication.
     */
    public function authenticate()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->username).'|'.request()->ip());

        // Check Rate Limiter (Max 5 attempts per minute)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Attempt Auth
        if (!Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        }

        // Check if user is active
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isActive()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'username' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.',
            ]);
        }

        // Clear rate limiter & update last login
        RateLimiter::clear($throttleKey);
        $user->update(['last_login_at' => now()]);

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
