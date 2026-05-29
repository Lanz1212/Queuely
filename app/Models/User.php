<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

/**
 * Model User
 * Mengelola data pengguna sistem, termasuk hak akses dan peran (role) pada aplikasi.
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Memeriksa apakah pengguna memiliki akses ke panel Filament tertentu berdasarkan perannya.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'    => $this->isAdmin(),
            'operator' => $this->isOperator(),
            default    => false,
        };
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'admin'.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'operator'.
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Atribut yang harus disembunyikan saat serialisasi (misalnya respons JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mendefinisikan tipe data untuk atribut tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
