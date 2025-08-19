<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'email',
        'telepon',
        'alamat'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
