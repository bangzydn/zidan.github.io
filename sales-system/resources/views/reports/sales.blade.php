@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Penjualan</h1>
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
        <form method="GET" action="{{ route('reports.sales') }}" id="filterForm">
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
                        <label for="customer_id" class="form-label">Pelanggan</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">Semua Pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->nama_pelanggan }}
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
                        <i class="bi bi-receipt fa-2x text-gray-300"></i>
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
                            Total Penjualan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
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
                            Total Keuntungan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Table -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Penjualan</h5>
    </div>
    <div class="card-body">
        @if($sales->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered" id="salesTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor Penjualan</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Total Bayar</th>
                        <th>Keuntungan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $index => $sale)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->nomor_penjualan }}</td>
                        <td>
                            @if($sale->customer)
                                {{ $sale->customer->nama_pelanggan }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $sale->user->name }}</td>
                        <td>Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->total_diskon, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->total_bayar, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->keuntungan, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $sales->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data penjualan</h5>
            <p class="text-muted">Tidak ada transaksi penjualan yang ditemukan untuk periode yang dipilih.</p>
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