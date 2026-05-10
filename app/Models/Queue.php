<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
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

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function gate()
    {
        return $this->belongsTo(Gate::class);
    }

    public function logs()
    {
        return $this->hasMany(QueueLog::class);
    }
}
