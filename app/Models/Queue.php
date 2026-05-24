<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Scope: only queues created on the given business date (defaults to today).
     * Use this everywhere you need "today's" queues so it auto-resets at midnight.
     */
    public function scopeForDate(Builder $query, $date = null): Builder
    {
        return $query->whereDate('queue_date', $date ?? now()->toDateString());
    }

    /**
     * Scope: only queues for today's business date.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('queue_date', now()->toDateString());
    }
}
