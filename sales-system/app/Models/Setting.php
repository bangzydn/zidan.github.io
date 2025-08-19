<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_toko',
        'alamat_toko',
        'telepon_toko',
        'email_toko',
        'logo_toko',
        'currency'
    ];

    public static function getSettings()
    {
        return static::first() ?? new static();
    }
}
