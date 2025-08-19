@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Produk</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning ms-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Produk</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Kode Barang</strong></td>
                                <td>: {{ $product->kode_barang }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Barang</strong></td>
                                <td>: {{ $product->nama_barang }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kategori</strong></td>
                                <td>: {{ $product->kategori }}</td>
                            </tr>
                            <tr>
                                <td><strong>Satuan</strong></td>
                                <td>: {{ $product->satuan }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Harga Beli</strong></td>
                                <td>: Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Harga Jual</strong></td>
                                <td>: Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Stok</strong></td>
                                <td>: {{ $product->stok }} {{ $product->satuan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status Stok</strong></td>
                                <td>: 
                                    <span class="badge bg-{{ $product->stok_status_color }}">
                                        {{ $product->stok_status }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($product->deskripsi)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6><strong>Deskripsi:</strong></h6>
                        <p class="text-muted">{{ $product->deskripsi }}</p>
                    </div>
                </div>
                @endif
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6><strong>Informasi Tambahan:</strong></h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Stok Minimum</strong></td>
                                <td>: {{ $product->stok_minimum }} {{ $product->satuan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat Pada</strong></td>
                                <td>: {{ $product->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Diupdate</strong></td>
                                <td>: {{ $product->updated_at->format('d F Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Foto Produk</h5>
            </div>
            <div class="card-body text-center">
                @if($product->foto)
                    <img src="{{ asset('storage/products/' . $product->foto) }}" 
                         alt="{{ $product->nama_barang }}" 
                         class="img-fluid rounded" 
                         style="max-height: 400px;">
                @else
                    <div class="text-muted">
                        <i class="bi bi-image" style="font-size: 6rem;"></i>
                        <p class="mt-2">Tidak ada foto</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card shadow mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Produk
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('products.destroy', $product) }}')">
                        <i class="bi bi-trash"></i> Hapus Produk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection