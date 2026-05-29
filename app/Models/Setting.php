<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Setting
 * Digunakan untuk menyimpan pengaturan global aplikasi dalam format key-value.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];
}
