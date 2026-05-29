<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Gate
 * Merepresentasikan data gerbang atau lokasi layanan tempat antrean diproses.
 */
class Gate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'notes',
    ];

    /**
     * Relasi ke model Queue.
     * Mengambil semua antrean yang ditugaskan ke gerbang ini.
     */
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
