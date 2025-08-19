@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Pembelian</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportPDF()">
                <i class="bi bi-file-pdf"></i> Export PDF
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="exportExcel()">
                <i class="bi bi-file-excel"></i> Export Excel
            </button>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Laporan</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.purchases') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Transaksi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_transactions'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cart-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Pembelian</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_purchases'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Diskon</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-tag fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Bayar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_payment'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Purchases Table -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Pembelian</h5>
    </div>
    <div class="card-body">
        @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered" id="purchasesTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor Pembelian</th>
                        <th>Supplier</th>
                        <th>Kasir</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Total Bayar</th>
                        <th>Metode Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $index => $purchase)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $purchase->nomor_pembelian }}</td>
                        <td>
                            @if($purchase->supplier)
                                {{ $purchase->supplier->nama_supplier }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $purchase->user->name }}</td>
                        <td>Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($purchase->total_diskon, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($purchase->total_bayar, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-primary">{{ ucfirst($purchase->metode_pembayaran) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $purchases->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data pembelian</h5>
            <p class="text-muted">Tidak ada transaksi pembelian yang ditemukan untuk periode yang dipilih.</p>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function exportPDF() {
    const form = document.getElementById('filterForm');
    const action = form.action;
    form.action = action + '?export=pdf';
    form.submit();
    form.action = action;
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const action = form.action;
    form.action = action + '?export=excel';
    form.submit();
    form.action = action;
}

// Auto-submit form when date changes
document.getElementById('start_date').addEventListener('change', function() {
    if (this.value && document.getElementById('end_date').value) {
        document.getElementById('filterForm').submit();
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    if (this.value && document.getElementById('start_date').value) {
        document.getElementById('filterForm').submit();
    }
});
</script>
@endpush