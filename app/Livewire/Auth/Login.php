<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $selectedRole = 'admin';
    public string $email        = '';
    public string $password     = '';
    public bool   $remember     = false;

    protected array $rules = [
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ];

    protected array $messages = [
        'email.required'    => 'Email wajib diisi.',
        'email.email'       => 'Format email tidak valid.',
        'password.required' => 'Password wajib diisi.',
    ];

    public function authenticate(): void
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();

        if ($this->selectedRole === 'admin' && !$user->isAdmin()) {
            Auth::logout();
            session()->invalidate();
            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses sebagai Admin.',
            ]);
        }

        if ($this->selectedRole === 'operator' && !$user->isOperator()) {
            Auth::logout();
            session()->invalidate();
            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses sebagai Operator.',
            ]);
        }

        session()->regenerate();

        if ($user->isAdmin()) {
            $this->redirect('/admin', navigate: false);
        } else {
            $this->redirect('/operator', navigate: false);
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.login');
    }
}
