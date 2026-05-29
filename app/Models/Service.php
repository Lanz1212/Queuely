<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Service
 * Merepresentasikan jenis layanan yang tersedia untuk pelanggan (contoh: Pendaftaran, Pembayaran).
 */
class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code_prefix',
        'color',
        'estimated_time',
        'is_active',
    ];

    /**
     * Mengatur tipe data bawaan untuk atribut tertentu.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'estimated_time' => 'integer',
        ];
    }

    /**
     * Relasi ke model Queue.
     * Mengambil semua antrean yang terdaftar di layanan ini.
     */
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
