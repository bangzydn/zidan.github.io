@extends('layouts.app')

@section('title', 'Transaksi Pembelian Baru')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Transaksi Pembelian Baru</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    
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
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">Pilih Supplier (Opsional)</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select @error('metode_pembayaran') is-invalid @enderror" id="metode_pembayaran" name="metode_pembayaran" required>
                                    <option value="">Pilih Metode</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="kartu">Kartu</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                                @error('metode_pembayaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="2"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Produk -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Produk</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addProductRow()">
                        <i class="bi bi-plus-circle"></i> Tambah Produk
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="productTable">
                            <thead>
                                <tr>
                                    <th width="30%">Produk</th>
                                    <th width="15%">Harga Beli</th>
                                    <th width="10%">Jumlah</th>
                                    <th width="15%">Diskon</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <!-- Product rows will be added here -->
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
                    <div class="mb-3">
                        <label for="total_harga" class="form-label">Total Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_harga" name="total_harga" value="0" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total_diskon" class="form-label">Total Diskon</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_diskon" name="total_diskon" value="0" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total_bayar" class="form-label">Total Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_bayar" name="total_bayar" value="0" readonly>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                            <i class="bi bi-check-circle"></i> Simpan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Hidden inputs for form submission -->
<input type="hidden" name="items" id="itemsInput">

@endsection

@push('scripts')
<script>
let productRowIndex = 0;

// Initialize first product row
document.addEventListener('DOMContentLoaded', function() {
    addProductRow();
    updateTotals();
});

function addProductRow() {
    const tbody = document.getElementById('productTableBody');
    const row = document.createElement('tr');
    row.id = `productRow_${productRowIndex}`;
    
    row.innerHTML = `
        <td>
            <select class="form-select product-select" name="product_id" onchange="onProductChange(${productRowIndex})" required>
                <option value="">Pilih Produk</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" 
                            data-harga="{{ $product->harga_beli }}" 
                            data-stok="{{ $product->stok }}"
                            data-satuan="{{ $product->satuan }}">
                        {{ $product->nama_barang }} (Stok: {{ $product->stok }} {{ $product->satuan }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control harga-beli" name="harga_beli" value="0" min="0" step="100" onchange="calculateSubtotal(${productRowIndex})" required>
        </td>
        <td>
            <input type="number" class="form-control quantity" name="quantity" value="1" min="1" onchange="calculateSubtotal(${productRowIndex})" required>
        </td>
        <td>
            <input type="number" class="form-control diskon" name="diskon" value="0" min="0" step="100" onchange="calculateSubtotal(${productRowIndex})" required>
        </td>
        <td>
            <input type="text" class="form-control subtotal" name="subtotal" value="0" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(${productRowIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    productRowIndex++;
}

function removeProductRow(index) {
    const row = document.getElementById(`productRow_${index}`);
    if (row) {
        row.remove();
        updateTotals();
    }
}

function onProductChange(index) {
    const row = document.getElementById(`productRow_${index}`);
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    const hargaInput = row.querySelector('.harga-beli');
    
    if (option.value) {
        const harga = option.getAttribute('data-harga');
        hargaInput.value = harga;
        calculateSubtotal(index);
    }
}

function calculateSubtotal(index) {
    const row = document.getElementById(`productRow_${index}`);
    const harga = parseFloat(row.querySelector('.harga-beli').value) || 0;
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const diskon = parseFloat(row.querySelector('.diskon').value) || 0;
    
    const subtotal = (harga * quantity) - diskon;
    row.querySelector('.subtotal').value = subtotal.toFixed(0);
    
    updateTotals();
}

function updateTotals() {
    let totalHarga = 0;
    let totalDiskon = 0;
    
    // Calculate totals from all product rows
    const rows = document.querySelectorAll('#productTableBody tr');
    rows.forEach(row => {
        const harga = parseFloat(row.querySelector('.harga-beli').value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const diskon = parseFloat(row.querySelector('.diskon').value) || 0;
        
        totalHarga += harga * quantity;
        totalDiskon += diskon;
    });
    
    const totalBayar = totalHarga - totalDiskon;
    
    document.getElementById('total_harga').value = totalHarga.toFixed(0);
    document.getElementById('total_diskon').value = totalDiskon.toFixed(0);
    document.getElementById('total_bayar').value = totalBayar.toFixed(0);
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = totalBayar <= 0;
}

// Form submission
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect all product data
    const items = [];
    const rows = document.querySelectorAll('#productTableBody tr');
    
    rows.forEach(row => {
        const productId = row.querySelector('.product-select').value;
        if (productId) {
            items.push({
                product_id: productId,
                quantity: row.querySelector('.quantity').value,
                harga_beli: row.querySelector('.harga-beli').value,
                diskon: row.querySelector('.diskon').value,
                subtotal: row.querySelector('.subtotal').value
            });
        }
    });
    
    // Set items to hidden input
    document.getElementById('itemsInput').value = JSON.stringify(items);
    
    // Submit form
    this.submit();
});
</script>
@endpush