@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pengaturan Toko</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Form Pengaturan Toko</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_toko" class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_toko') is-invalid @enderror" 
                                       id="nama_toko" name="nama_toko" value="{{ old('nama_toko', $settings->nama_toko) }}" required>
                                @error('nama_toko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telepon_toko" class="form-label">Telepon Toko <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('telepon_toko') is-invalid @enderror" 
                                       id="telepon_toko" name="telepon_toko" value="{{ old('telepon_toko', $settings->telepon_toko) }}" required>
                                @error('telepon_toko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email_toko" class="form-label">Email Toko</label>
                                <input type="email" class="form-control @error('email_toko') is-invalid @enderror" 
                                       id="email_toko" name="email_toko" value="{{ old('email_toko', $settings->email_toko) }}">
                                @error('email_toko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Mata Uang <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                    <option value="IDR" {{ old('currency', $settings->currency) == 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
                                    <option value="USD" {{ old('currency', $settings->currency) == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                    <option value="EUR" {{ old('currency', $settings->currency) == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat_toko" class="form-label">Alamat Toko <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alamat_toko') is-invalid @enderror" 
                                  id="alamat_toko" name="alamat_toko" rows="3" required>{{ old('alamat_toko', $settings->alamat_toko) }}</textarea>
                        @error('alamat_toko')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo_toko" class="form-label">Logo Toko</label>
                        <input type="file" class="form-control @error('logo_toko') is-invalid @enderror" 
                               id="logo_toko" name="logo_toko" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah logo.</div>
                        @error('logo_toko')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Logo Saat Ini</h5>
            </div>
            <div class="card-body text-center">
                @if($settings->logo_toko)
                    <img src="{{ asset('storage/settings/' . $settings->logo_toko) }}" 
                         alt="Logo Toko" 
                         class="img-fluid rounded" 
                         style="max-height: 200px;">
                @else
                    <div class="text-muted">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                        <p class="mt-2">Tidak ada logo</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card shadow mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Preview Logo Baru</h5>
            </div>
            <div class="card-body text-center">
                <div id="preview-container" class="d-none">
                    <img id="preview-image" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                </div>
                <div id="no-preview" class="text-muted">
                    <i class="bi bi-image" style="font-size: 4rem;"></i>
                    <p class="mt-2">Belum ada logo dipilih</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('logo_toko').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const noPreview = document.getElementById('no-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
            noPreview.classList.add('d-none');
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.classList.add('d-none');
        noPreview.classList.remove('d-none');
    }
});
</script>
@endpush