@extends('layouts.app')

@section('title', 'Detail Transaksi Pembelian')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Transaksi Pembelian</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button type="button" class="btn btn-danger ms-2" onclick="confirmDelete('{{ route('purchases.destroy', $purchase->id) }}')">
            <i class="bi bi-trash"></i> Hapus
        </button>
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
                                <td width="40%"><strong>Nomor Pembelian</strong></td>
                                <td width="60%">{{ $purchase->nomor_pembelian }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Supplier</strong></td>
                                <td>
                                    @if($purchase->supplier)
                                        {{ $purchase->supplier->nama_supplier }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Kasir</strong></td>
                                <td width="60%">{{ $purchase->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($purchase->metode_pembayaran) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>
                                    <span class="badge bg-success">Selesai</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($purchase->catatan)
                <div class="mt-3">
                    <strong>Catatan:</strong>
                    <p class="mb-0">{{ $purchase->catatan }}</p>
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
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Harga Beli</th>
                                <th>Jumlah</th>
                                <th>Diskon</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->purchaseDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $detail->product->nama_barang }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $detail->product->kode_barang }}</small>
                                    </div>
                                </td>
                                <td>Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
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
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Total Harga</strong></td>
                        <td class="text-end">Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Diskon</strong></td>
                        <td class="text-end">Rp {{ number_format($purchase->total_diskon, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Total Bayar</strong></td>
                        <td class="text-end">
                            <strong>Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Informasi Supplier -->
        @if($purchase->supplier)
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Supplier</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Nama</strong></td>
                        <td width="60%">{{ $purchase->supplier->nama_supplier }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kode</strong></td>
                        <td>{{ $purchase->supplier->kode_supplier }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $purchase->supplier->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Telepon</strong></td>
                        <td>{{ $purchase->supplier->telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Alamat</strong></td>
                        <td>{{ $purchase->supplier->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmDelete(url) {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi pembelian ini?')) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush