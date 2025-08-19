<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga_beli',
        'harga_jual',
        'stok',
        'satuan',
        'foto',
        'stok_minimum',
        'deskripsi'
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer'
    ];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function getStokStatusAttribute()
    {
        if ($this->stok <= 0) {
            return 'Habis';
        } elseif ($this->stok <= $this->stok_minimum) {
            return 'Stok Menipis';
        }
        return 'Tersedia';
    }

    public function getStokStatusColorAttribute()
    {
        if ($this->stok <= 0) {
            return 'danger';
        } elseif ($this->stok <= $this->stok_minimum) {
            return 'warning';
        }
        return 'success';
    }

    public function scopeLowStock($query)
    {
        return $query->where('stok', '<=', 'stok_minimum');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stok', '<=', 0);
    }
}
