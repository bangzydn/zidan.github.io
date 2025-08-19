@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Pelanggan</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning ms-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Pelanggan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Kode Pelanggan</strong></td>
                                <td>: {{ $customer->kode_pelanggan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Pelanggan</strong></td>
                                <td>: {{ $customer->nama_pelanggan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $customer->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: {{ $customer->telepon ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Alamat</strong></td>
                                <td>: {{ $customer->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat Pada</strong></td>
                                <td>: {{ $customer->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Diupdate</strong></td>
                                <td>: {{ $customer->updated_at->format('d F Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Riwayat Penjualan -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat Penjualan</h5>
            </div>
            <div class="card-body">
                @if($customer->sales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No. Penjualan</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->sales->take(10) as $sale)
                                <tr>
                                    <td>{{ $sale->nomor_penjualan }}</td>
                                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td>Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-success">Selesai</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Belum ada riwayat penjualan</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Pelanggan
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('customers.destroy', $customer) }}')">
                        <i class="bi bi-trash"></i> Hapus Pelanggan
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card shadow mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistik</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h4 class="text-primary">{{ $customer->sales->count() }}</h4>
                    <p class="text-muted">Total Transaksi</p>
                </div>
                <div class="text-center">
                    <h4 class="text-success">Rp {{ number_format($customer->sales->sum('total_bayar'), 0, ',', '.') }}</h4>
                    <p class="text-muted">Total Pembelian</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection