@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Penjualan</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('sales.print', $sale) }}" class="btn btn-success ms-2" target="_blank">
            <i class="bi bi-printer"></i> Cetak
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informasi Transaksi -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>No. Penjualan</strong></td>
                                <td>: {{ $sale->nomor_penjualan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>: {{ $sale->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pelanggan</strong></td>
                                <td>: {{ $sale->customer->nama_pelanggan ?? 'Umum' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kasir</strong></td>
                                <td>: {{ $sale->user->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Metode Pembayaran</strong></td>
                                <td>: {{ ucfirst($sale->metode_pembayaran) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Harga</strong></td>
                                <td>: Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Diskon</strong></td>
                                <td>: Rp {{ number_format($sale->total_diskon, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Bayar</strong></td>
                                <td>: Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($sale->catatan)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6><strong>Catatan:</strong></h6>
                        <p class="text-muted">{{ $sale->catatan }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Detail Produk -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Detail Produk</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Harga Jual</th>
                                <th>Jumlah</th>
                                <th>Diskon</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->product->nama_barang }}</td>
                                <td>Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                <td>{{ $detail->quantity }} {{ $detail->product->satuan }}</td>
                                <td>Rp {{ number_format($detail->diskon, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Ringkasan Pembayaran -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Harga:</strong>
                    </div>
                    <div class="col-6 text-end">
                        Rp {{ number_format($sale->total_harga, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Diskon:</strong>
                    </div>
                    <div class="col-6 text-end">
                        Rp {{ number_format($sale->total_diskon, 0, ',', '.') }}
                    </div>
                </div>
                
                <hr>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Bayar:</strong>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</strong>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Bayar:</strong>
                    </div>
                    <div class="col-6 text-end">
                        Rp {{ number_format($sale->bayar, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Kembalian:</strong>
                    </div>
                    <div class="col-6 text-end">
                        Rp {{ number_format($sale->kembalian, 0, ',', '.') }}
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <strong>Keuntungan:</strong>
                    </div>
                    <div class="col-6 text-end">
                        <strong class="text-success">Rp {{ number_format($sale->keuntungan, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Aksi -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.print', $sale) }}" class="btn btn-success" target="_blank">
                        <i class="bi bi-printer"></i> Cetak Struk
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('sales.destroy', $sale) }}')">
                        <i class="bi bi-trash"></i> Hapus Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection