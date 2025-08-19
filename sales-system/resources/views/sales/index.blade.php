@extends('layouts.app')

@section('title', 'Manajemen Penjualan')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Penjualan</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Transaksi Baru
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Penjualan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Total Harga</th>
                        <th>Total Bayar</th>
                        <th>Metode Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $sale)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sale->nomor_penjualan }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer->nama_pelanggan ?? 'Umum' }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($sale->metode_pembayaran) }}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('sales.show', $sale) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" 
                                   class="btn btn-sm btn-success" 
                                   title="Cetak"
                                   target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('sales.destroy', $sale) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data penjualan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sales->hasPages())
        <div class="d-flex justify-content-center">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection