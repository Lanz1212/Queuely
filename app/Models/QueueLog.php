<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model QueueLog
 * Menyimpan riwayat perubahan status dan aksi yang terjadi pada setiap antrean.
 */
class QueueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'user_id',
        'action_type',
        'old_status',
        'new_status',
        'notes',
    ];

    /**
     * Relasi ke model Queue.
     * Mengambil data antrean yang terkait dengan log ini.
     */
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * Relasi ke model User.
     * Mengambil data pengguna/operator yang melakukan aksi pencatatan log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
