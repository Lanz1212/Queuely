<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Komponen Livewire untuk halaman Login.
 * Menangani otentikasi admin dan operator dengan validasi peran.
 */
class Login extends Component
{
    public string $selectedRole = 'admin';
    public string $email        = '';
    public string $password     = '';
    public bool   $remember     = false;

    /**
     * Aturan validasi untuk form login.
     */
    protected array $rules = [
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ];

    /**
     * Pesan error kustom untuk validasi login.
     */
    protected array $messages = [
        'email.required'    => 'Email wajib diisi.',
        'email.email'       => 'Format email tidak valid.',
        'password.required' => 'Password wajib diisi.',
    ];

    /**
     * Memproses percobaan login dari pengguna.
     * Melakukan validasi kredensial dan pengecekan akses berdasarkan peran (role).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->validate();

        // Mencoba login dengan email dan password yang diberikan
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();

        // Memastikan pengguna yang login sebagai admin benar-benar memiliki peran admin
        if ($this->selectedRole === 'admin' && !$user->isAdmin()) {
            Auth::logout();
            session()->invalidate();
            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses sebagai Admin.',
            ]);
        }

        // Memastikan pengguna yang login sebagai operator benar-benar memiliki peran operator
        if ($this->selectedRole === 'operator' && !$user->isOperator()) {
            Auth::logout();
            session()->invalidate();
            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses sebagai Operator.',
            ]);
        }

        session()->regenerate();

        // Mengarahkan pengguna ke panel yang sesuai setelah berhasil login
        if ($user->isAdmin()) {
            $this->redirect('/admin', navigate: false);
        } else {
            $this->redirect('/operator', navigate: false);
        }
    }

    /**
     * Merender tampilan komponen login.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.login');
    }
}
