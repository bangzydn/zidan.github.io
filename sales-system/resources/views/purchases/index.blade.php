@extends('layouts.app')

@section('title', 'Manajemen Pembelian')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Pembelian</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
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
                        <th>No. Pembelian</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Kasir</th>
                        <th>Total Harga</th>
                        <th>Total Bayar</th>
                        <th>Metode Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $index => $purchase)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $purchase->nomor_pembelian }}</td>
                        <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $purchase->supplier->nama_supplier ?? 'Umum' }}</td>
                        <td>{{ $purchase->user->name }}</td>
                        <td>Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($purchase->metode_pembayaran) }}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('purchases.show', $purchase) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('purchases.destroy', $purchase) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data pembelian</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($purchases->hasPages())
        <div class="d-flex justify-content-center">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection