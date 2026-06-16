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
        'activity_type',
    ];

    /**
     * Mengatur tipe data bawaan untuk atribut tertentu.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'estimated_time' => 'integer',
            'activity_type' => 'string',
        ];
    }

    /**
     * Helper: Mengembalikan label singkat aktivitas.
     * Contoh: "Muat" atau "Bongkar"
     */
    public function getActivityLabelAttribute(): string
    {
        return in_array($this->activity_type, ['bongkar', 'retur']) ? 'Bongkar' : 'Muat';
    }

    /**
     * Helper: Mengembalikan judul aktivitas untuk tiket dan display.
     * Contoh: "Muat Produk" atau "Bongkar Bahan Baku"
     */
    public function getActivityTitleAttribute(): string
    {
        return match ($this->activity_type) {
            'bongkar' => 'Bongkar Bahan Baku',
            'retur'   => 'Bongkar Produk',
            default   => 'Muat Produk',
        };
    }

    /**
     * Helper: Mengembalikan teks status aktivitas yang sedang berjalan.
     * Contoh: "Sedang Muat" atau "Sedang Bongkar"
     */
    public function getActivityStatusAttribute(): string
    {
        return in_array($this->activity_type, ['bongkar', 'retur']) ? 'Sedang Bongkar' : 'Sedang Muat';
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
