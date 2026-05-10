<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'notes',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
