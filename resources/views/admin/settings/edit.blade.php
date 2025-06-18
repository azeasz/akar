@extends('admin.layouts.app')

@section('title', 'Edit Pengaturan')

@section('styles')
<style>
    .required::after {
        content: " *";
        color: red;
    }
    .tox-tinymce {
        border-radius: 0.25rem;
    }
    .badge-1 {
        background-color: #4e73df;
    }
    .badge-2 {
        background-color: #1cc88a;
    }
    .badge-3 {
        background-color: #36b9cc;
    }
    .badge-4 {
        background-color: #f6c23e;
    }
    .badge-5 {
        background-color: #e74a3b;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Pengaturan</h1>
    <div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('admin.settings.show', $setting->id) }}" class="btn btn-info ms-2">
            <i class="bi bi-eye me-1"></i> Lihat Detail
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <!-- Form Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan!</strong> Silakan periksa form di bawah.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0">Form Edit Pengaturan</h6>
                <span class="badge badge-{{ $setting->type }} text-white">{{ $setting->typeName }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update', $setting->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type" class="form-label required">Tipe</label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="1" {{ old('type', $setting->type) == 1 ? 'selected' : '' }}>Deskripsi</option>
                                    <option value="2" {{ old('type', $setting->type) == 2 ? 'selected' : '' }}>Privacy Policy</option>
                                    <option value="3" {{ old('type', $setting->type) == 3 ? 'selected' : '' }}>Terms & Conditions</option>
                                    <option value="4" {{ old('type', $setting->type) == 4 ? 'selected' : '' }}>About</option>
                                    <option value="5" {{ old('type', $setting->type) == 5 ? 'selected' : '' }}>FAQ</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title" class="form-label required">Judul</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $setting->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="10">{{ old('description', $setting->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted"><span class="text-danger">*</span> Wajib diisi</small>
                        </div>
                        <div class="d-flex">
                            <form action="{{ route('admin.settings.destroy', $setting->id) }}" method="POST" class="me-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin akan menghapus pengaturan ini?')">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                            </form>
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0">Informasi Lainnya</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="text-muted">ID:</label>
                    <p>{{ $setting->id }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted">Dibuat:</label>
                    <p>{{ $setting->created_at->format('d M Y, H:i:s') }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="text-muted">Terakhir Diperbarui:</label>
                    <p>{{ $setting->updated_at->format('d M Y, H:i:s') }}</p>
                </div>
                <div>
                    <label class="text-muted">Status:</label>
                    <p><span class="badge bg-success">Aktif</span></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/{{ config('tinymce.api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    const tinymceConfig = @json(config('tinymce.default_config'));
    tinymce.init({
        selector: '#description',
        ...tinymceConfig
    });
</script>
@endsection 