@extends('layouts.app')

@section('title', 'Laporan Keuntungan')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Keuntungan</h1>
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
        <form method="GET" action="{{ route('reports.profit') }}" id="filterForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-4">
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
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Penjualan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-up-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Total Pembelian</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($summary['total_purchases'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-down-circle fa-2x text-gray-300"></i>
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
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

<!-- Profit Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Grafik Keuntungan</h5>
            </div>
            <div class="card-body">
                <canvas id="profitChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Profit Table -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Keuntungan per Transaksi</h5>
    </div>
    <div class="card-body">
        @if($profits->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered" id="profitTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor Penjualan</th>
                        <th>Pelanggan</th>
                        <th>Total Penjualan</th>
                        <th>Harga Pokok</th>
                        <th>Keuntungan</th>
                        <th>Margin (%)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profits as $index => $profit)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $profit->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $profit->nomor_penjualan }}</td>
                        <td>
                            @if($profit->customer)
                                {{ $profit->customer->nama_pelanggan }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($profit->total_bayar, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($profit->harga_pokok, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $profit->keuntungan >= 0 ? 'bg-success' : 'bg-danger' }}">
                                Rp {{ number_format($profit->keuntungan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $profit->margin >= 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ number_format($profit->margin, 1) }}%
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('sales.show', $profit->id) }}" class="btn btn-sm btn-info">
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
            {{ $profits->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data keuntungan</h5>
            <p class="text-muted">Tidak ada transaksi penjualan yang ditemukan untuk periode yang dipilih.</p>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

// Profit Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('profitChart').getContext('2d');
    
    // Sample data - in real implementation, this would come from the controller
    const chartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Penjualan',
            data: [12000000, 19000000, 15000000, 25000000, 22000000, 30000000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Pembelian',
            data: [8000000, 12000000, 10000000, 18000000, 15000000, 22000000],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }, {
            label: 'Keuntungan',
            data: [4000000, 7000000, 5000000, 7000000, 7000000, 8000000],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Keuntungan Bulanan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush