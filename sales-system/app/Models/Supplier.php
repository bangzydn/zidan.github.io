<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'email',
        'telepon',
        'alamat'
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
