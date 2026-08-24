<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil Pengguna')]
class Index extends Component
{
    // Profile info form
    public string $full_name = '';
    public string $username = '';

    // Change password form
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->full_name = $user->full_name;
        $this->username = $user->username;
    }

    /**
     * Update basic profile info (and username if Administrator).
     */
    public function updateProfile(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrator');

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
        ];

        $messages = [
            'full_name.required' => 'Nama lengkap wajib diisi.',
        ];

        if ($isAdmin) {
            $rules['username'] = [
                'required',
                'string',
                'alpha_dash',
                'max:100',
                \Illuminate\Validation\Rule::unique('users')->ignore($user->id),
            ];
            $messages['username.required'] = 'Username wajib diisi.';
            $messages['username.unique'] = 'Username ini sudah digunakan.';
            $messages['username.alpha_dash'] = 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.';
        }

        $this->validate($rules, $messages);

        $updateData = [
            'full_name' => $this->full_name,
        ];

        if ($isAdmin) {
            $updateData['username'] = $this->username;
        }

        $user->update($updateData);

        session()->flash('profile_message', 'Informasi profil berhasil diperbarui.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini yang Anda masukkan salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password_message', 'Kata sandi Anda berhasil diperbarui.');
    }

    public function render()
    {
        /** @var User $user */
        $user = User::with(['role', 'division', 'position', 'supervisor'])->find(Auth::id());

        return view('livewire.profile.index', [
            'user' => $user,
        ]);
    }
}
