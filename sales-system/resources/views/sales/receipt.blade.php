<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
        }
        .transaction-info {
            margin-bottom: 20px;
        }
        .transaction-info table {
            width: 100%;
        }
        .transaction-info td {
            padding: 2px 0;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .products-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
        }
        .summary td {
            padding: 2px 0;
        }
        .summary .total {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings->nama_toko }}</h1>
        <p>{{ $settings->alamat_toko }}</p>
        <p>Telp: {{ $settings->telepon_toko }}</p>
        <p>Email: {{ $settings->email_toko ?? '-' }}</p>
    </div>

    <div class="transaction-info">
        <table>
            <tr>
                <td width="100">No. Penjualan</td>
                <td>: {{ $sale->nomor_penjualan }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $sale->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $sale->customer->nama_pelanggan ?? 'Umum' }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ $sale->user->name }}</td>
            </tr>
        </table>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Produk</th>
                <th width="15%">Harga</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Diskon</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleDetails as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->product->nama_barang }}</td>
                <td class="text-right">{{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                <td class="text-center">{{ $detail->quantity }}</td>
                <td class="text-right">{{ number_format($detail->diskon, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td width="70%">Total Harga</td>
                <td class="text-right">Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Diskon</td>
                <td class="text-right">Rp {{ number_format($sale->total_diskon, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td>Total Bayar</td>
                <td class="text-right">Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td class="text-right">Rp {{ number_format($sale->bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="text-right">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($sale->catatan)
    <div style="margin-bottom: 20px;">
        <strong>Catatan:</strong><br>
        {{ $sale->catatan }}
    </div>
    @endif

    <div class="footer">
        <p>Terima kasih telah berbelanja di {{ $settings->nama_toko }}</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p>{{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>