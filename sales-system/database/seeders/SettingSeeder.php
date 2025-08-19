<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'nama_toko' => 'Toko Saya',
            'alamat_toko' => 'Jl. Contoh No. 123, Kota, Provinsi',
            'telepon_toko' => '081234567890',
            'email_toko' => 'info@tokosaya.com',
            'currency' => 'IDR'
        ]);
    }
}
