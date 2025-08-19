@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Stok</h1>
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
        <form method="GET" action="{{ route('reports.stock') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="stock_status" class="form-label">Status Stok</label>
                        <select class="form-select" id="stock_status" name="stock_status">
                            <option value="">Semua Status</option>
                            <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Habis</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="search" class="form-label">Cari Produk</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nama atau kode produk">
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
                            Total Produk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_products'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box fa-2x text-gray-300"></i>
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
                            Stok Tersedia</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['available_stock'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
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
                            Stok Menipis</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['low_stock'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
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
                            Stok Habis</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['out_of_stock'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Stok Produk</h5>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered" id="stockTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Stok Minimum</th>
                        <th>Status</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Nilai Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->kode_barang }}</td>
                        <td>
                            <div>
                                <strong>{{ $product->nama_barang }}</strong>
                                @if($product->foto)
                                    <br>
                                    <img src="{{ asset('storage/' . $product->foto) }}" alt="{{ $product->nama_barang }}" 
                                         class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                @endif
                            </div>
                        </td>
                        <td>{{ $product->kategori }}</td>
                        <td>
                            <strong>{{ $product->stok }} {{ $product->satuan }}</strong>
                        </td>
                        <td>{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                        <td>
                            <span class="badge {{ $product->stok_status_color }}">
                                {{ $product->stok_status }}
                            </span>
                        </td>
                        <td>Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($product->harga_beli * $product->stok, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data produk</h5>
            <p class="text-muted">Tidak ada produk yang ditemukan dengan filter yang dipilih.</p>
        </div>
        @endif
    </div>
</div>

<!-- Low Stock Alert -->
@if($lowStockProducts->count() > 0)
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning text-white">
        <h5 class="card-title mb-0">
            <i class="bi bi-exclamation-triangle"></i> Peringatan Stok Menipis
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Stok Minimum</th>
                        <th>Kekurangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $product->nama_barang }}</strong>
                            <br>
                            <small class="text-muted">{{ $product->kode_barang }}</small>
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ $product->stok }} {{ $product->satuan }}</span>
                        </td>
                        <td>{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                        <td>
                            <span class="badge bg-danger">
                                {{ $product->stok_minimum - $product->stok }} {{ $product->satuan }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-success">
                                <i class="bi bi-plus-circle"></i> Beli Stok
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

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

// Auto-submit form when select changes
document.getElementById('category').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('stock_status').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Search with delay
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});
</script>
@endpush