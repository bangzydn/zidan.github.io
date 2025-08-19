@extends('layouts.app')

@section('title', 'Detail Supplier')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Supplier</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning ms-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Supplier</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Kode Supplier</strong></td>
                                <td>: {{ $supplier->kode_supplier }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Supplier</strong></td>
                                <td>: {{ $supplier->nama_supplier }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $supplier->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: {{ $supplier->telepon ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Alamat</strong></td>
                                <td>: {{ $supplier->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat Pada</strong></td>
                                <td>: {{ $supplier->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Diupdate</strong></td>
                                <td>: {{ $supplier->updated_at->format('d F Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Riwayat Pembelian -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat Pembelian</h5>
            </div>
            <div class="card-body">
                @if($supplier->purchases->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No. Pembelian</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplier->purchases->take(10) as $purchase)
                                <tr>
                                    <td>{{ $purchase->nomor_pembelian }}</td>
                                    <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                    <td>Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-success">Selesai</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Belum ada riwayat pembelian</p>
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
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Supplier
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('suppliers.destroy', $supplier) }}')">
                        <i class="bi bi-trash"></i> Hapus Supplier
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
                    <h4 class="text-primary">{{ $supplier->purchases->count() }}</h4>
                    <p class="text-muted">Total Transaksi</p>
                </div>
                <div class="text-center">
                    <h4 class="text-success">Rp {{ number_format($supplier->purchases->sum('total_bayar'), 0, ',', '.') }}</h4>
                    <p class="text-muted">Total Pembelian</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection