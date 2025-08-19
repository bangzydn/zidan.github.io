# Sistem Penjualan dan Pembelian - Laravel

Aplikasi sistem penjualan dan pembelian barang berbasis Laravel dengan fitur manajemen produk, transaksi penjualan/pembelian, laporan, dan manajemen stok.

## 🚀 Fitur Utama

### 1. Manajemen Produk
- CRUD produk dengan foto
- Upload gambar ke storage
- Validasi input
- Manajemen stok dan harga

### 2. Transaksi Penjualan
- Pilih produk dari database
- Kalkulasi otomatis total harga dan diskon
- Update stok otomatis
- Cetak struk PDF
- Manajemen pembayaran

### 3. Transaksi Pembelian
- Pilih produk atau input produk baru
- Update stok otomatis
- Manajemen supplier

### 4. Manajemen Data
- Pelanggan (Customers)
- Supplier
- Pengaturan toko

### 5. Laporan
- Laporan penjualan (harian, bulanan, range tanggal)
- Laporan pembelian
- Laporan keuntungan
- Laporan stok
- Export PDF dan Excel

### 6. Keamanan
- Autentikasi dengan Laravel Breeze
- Role-based access control (Admin & Kasir)
- CSRF protection
- Validasi input

## 📋 Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (untuk asset compilation)

## 🛠️ Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd sales-system
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_system
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Migration dan Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 6. Setup Storage Link
```bash
php artisan storage:link
```

### 7. Compile Assets (Opsional)
```bash
npm run dev
# atau untuk production
npm run build
```

### 8. Jalankan Server
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👤 Akun Default

Setelah menjalankan seeder, tersedia akun default:

### Admin
- Email: admin@example.com
- Password: password
- Role: Admin (akses penuh)

### Kasir
- Email: kasir@example.com
- Password: password
- Role: Kasir (akses terbatas)

## 📁 Struktur Database

### Tabel Utama
- `users` - Pengguna sistem
- `products` - Data produk
- `customers` - Data pelanggan
- `suppliers` - Data supplier
- `sales` - Transaksi penjualan
- `sale_details` - Detail penjualan
- `purchases` - Transaksi pembelian
- `purchase_details` - Detail pembelian
- `settings` - Pengaturan toko

## 🔧 Konfigurasi Tambahan

### 1. File Upload
Pastikan folder `storage/app/public` memiliki permission yang benar:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 2. Queue (Opsional)
Untuk performa yang lebih baik, gunakan queue untuk export PDF/Excel:
```bash
php artisan queue:work
```

### 3. Cron Job (Opsional)
Untuk backup otomatis dan maintenance:
```bash
# Tambahkan ke crontab
* * * * * cd /path/to/sales-system && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Laporan

### 1. Laporan Penjualan
- Filter berdasarkan tanggal dan pelanggan
- Ringkasan total transaksi, penjualan, diskon, dan keuntungan
- Export PDF dan Excel

### 2. Laporan Pembelian
- Filter berdasarkan tanggal dan supplier
- Ringkasan total transaksi dan pembelian
- Export PDF dan Excel

### 3. Laporan Keuntungan
- Analisis keuntungan per transaksi
- Grafik keuntungan bulanan
- Perhitungan margin

### 4. Laporan Stok
- Status stok produk (tersedia, menipis, habis)
- Peringatan stok minimum
- Nilai stok berdasarkan harga beli

## 🔐 Keamanan

### Role-based Access Control
- **Admin**: Akses penuh ke semua fitur
- **Kasir**: Akses terbatas (tidak bisa akses laporan dan pengaturan)

### Validasi Input
- Form Request Validation untuk semua input
- Sanitasi data
- CSRF protection

## 🎨 UI/UX

- Bootstrap 5 untuk tampilan responsif
- Bootstrap Icons untuk ikon
- SweetAlert2 untuk notifikasi
- Chart.js untuk grafik
- Modern dan clean design

## 📱 Responsive Design

Aplikasi didesain responsif untuk:
- Desktop
- Tablet
- Mobile

## 🚀 Deployment

### Production Checklist
1. Set `APP_ENV=production` di `.env`
2. Set `APP_DEBUG=false` di `.env`
3. Optimize autoloader: `composer install --optimize-autoloader --no-dev`
4. Cache config: `php artisan config:cache`
5. Cache routes: `php artisan route:cache`
6. Cache views: `php artisan view:cache`
7. Compile assets: `npm run build`

### Server Requirements
- PHP >= 8.1
- MySQL >= 5.7
- Apache/Nginx
- SSL certificate (recommended)

## 🐛 Troubleshooting

### Common Issues

1. **Permission Denied**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Class not found**
   ```bash
   composer dump-autoload
   ```

3. **Migration failed**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Storage link not working**
   ```bash
   php artisan storage:link
   ```

## 📞 Support

Untuk bantuan dan dukungan:
- Email: support@example.com
- Documentation: [Link ke dokumentasi]
- Issues: [Link ke repository issues]

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

**Dibuat dengan ❤️ menggunakan Laravel**
