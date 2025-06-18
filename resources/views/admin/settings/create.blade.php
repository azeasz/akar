@extends('admin.layouts.app')

@section('title', 'Buat Pengaturan Baru')

@section('styles')
<style>
    .required::after {
        content: " *";
        color: red;
    }
    .tox-tinymce {
        border-radius: 0.25rem;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Buat Pengaturan Baru</h1>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
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
            <div class="card-header py-3">
                <h6 class="m-0">Form Pengaturan Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type" class="form-label required">Tipe</label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>Deskripsi</option>
                                    <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Privacy Policy</option>
                                    <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Terms & Conditions</option>
                                    <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>About</option>
                                    <option value="5" {{ old('type') == '5' ? 'selected' : '' }}>FAQ</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title" class="form-label required">Judul</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="10">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted"><span class="text-danger">*</span> Wajib diisi</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
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
        <h6 class="m-0">Panduan Tipe Pengaturan</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Deskripsi</h6>
                        <p class="mb-0 text-muted">Deskripsi aplikasi, pengantar, atau informasi umum tentang aplikasi.</p>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Privacy Policy</h6>
                        <p class="mb-0 text-muted">Kebijakan privasi untuk pengguna aplikasi.</p>
                    </div>
                </div>
                <div class="card mb-md-0 mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Terms & Conditions</h6>
                        <p class="mb-0 text-muted">Syarat dan ketentuan penggunaan aplikasi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">About</h6>
                        <p class="mb-0 text-muted">Informasi tentang aplikasi, pengembang, atau perusahaan.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2">FAQ</h6>
                        <p class="mb-0 text-muted">Pertanyaan yang sering diajukan dan jawabannya.</p>
                    </div>
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