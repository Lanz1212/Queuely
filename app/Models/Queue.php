<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Queue
 * Menyimpan data utama antrean pelanggan, termasuk status, informasi kendaraan, dan waktu proses.
 */
class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'queue_date',
        'daily_sequence',
        'service_id',
        'gate_id',
        'status',
        'driver_name',
        'phone',
        'vehicle_plate',
        'company',
        'qr_code_hash',
        'registered_at',
        'called_at',
        'completed_at',
    ];

    /**
     * Mengatur konversi tipe data otomatis untuk beberapa atribut.
     */
    protected function casts(): array
    {
        return [
            'queue_date'      => 'date',
            'daily_sequence'  => 'integer',
            'registered_at'   => 'datetime',
            'called_at'       => 'datetime',
            'completed_at'    => 'datetime',
        ];
    }

    /**
     * Relasi ke model Service.
     * Mengambil data jenis layanan untuk antrean ini.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relasi ke model Gate.
     * Mengambil data gerbang yang memproses antrean ini.
     */
    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    /**
     * Relasi ke model QueueLog.
     * Mengambil seluruh riwayat log yang terkait dengan antrean ini.
     */
    public function logs()
    {
        return $this->hasMany(QueueLog::class);
    }

    /**
     * Scope: Mengambil data antrean berdasarkan tanggal tertentu.
     * Jika tanggal tidak diberikan, secara default akan menggunakan tanggal hari ini (reset setiap tengah malam).
     */
    public function scopeForDate(Builder $query, $date = null): Builder
    {
        return $query->whereDate('queue_date', $date ?? now()->toDateString());
    }

    /**
     * Scope: Mengambil data antrean khusus untuk hari ini.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('queue_date', now()->toDateString());
    }
}
