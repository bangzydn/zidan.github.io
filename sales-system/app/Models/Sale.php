<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_penjualan',
        'customer_id',
        'user_id',
        'total_harga',
        'total_diskon',
        'total_bayar',
        'bayar',
        'kembalian',
        'metode_pembayaran',
        'catatan'
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'bayar' => 'decimal:2',
        'kembalian' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function getKeuntunganAttribute()
    {
        $totalHargaPokok = 0;
        foreach ($this->saleDetails as $detail) {
            $totalHargaPokok += $detail->product->harga_beli * $detail->quantity;
        }
        return $this->total_bayar - $totalHargaPokok;
    }
}
