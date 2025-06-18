@extends('admin.layouts.app')

@section('title', 'Detail Pengaturan')

@section('styles')
<style>
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
    .content-preview {
        padding: 20px;
        border: 1px solid #e3e6f0;
        border-radius: 5px;
        margin-top: 10px;
        background-color: #f8f9fc;
    }
    .content-preview h1, 
    .content-preview h2, 
    .content-preview h3, 
    .content-preview h4, 
    .content-preview h5, 
    .content-preview h6 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }
    .content-preview p {
        margin-bottom: 1rem;
    }
    .content-preview ul, 
    .content-preview ol {
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .content-preview img {
        max-width: 100%;
        height: auto;
    }
    .content-preview table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }
    .content-preview table th,
    .content-preview table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }
    .content-preview table th {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Pengaturan</h1>
    <div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('admin.settings.edit', $setting->id) }}" class="btn btn-primary ms-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0">Informasi Pengaturan</h6>
                <span class="badge badge-{{ $setting->type }} text-white">{{ $setting->typeName }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">ID:</label>
                            <p>{{ $setting->id }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Tipe:</label>
                            <p>{{ $setting->typeName }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Dibuat pada:</label>
                            <p>{{ $setting->created_at->format('d M Y, H:i:s') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">Judul:</label>
                            <p class="fw-bold fs-5">{{ $setting->title }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted">Terakhir diubah:</label>
                            <p>{{ $setting->updated_at->format('d M Y, H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="text-muted">Status:</label>
                            <p><span class="badge bg-success">Aktif</span></p>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-4">
                    <h6 class="mb-3">Preview Konten:</h6>
                    <div class="content-preview">
                        {!! $setting->description !!}
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#rawContentModal">
                            <i class="bi bi-code-slash me-1"></i> Lihat HTML
                        </a>
                    </div>
                    <div class="d-flex">
                        <form action="{{ route('admin.settings.destroy', $setting->id) }}" method="POST" class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin akan menghapus pengaturan ini?')">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Raw Content Modal -->
<div class="modal fade" id="rawContentModal" tabindex="-1" aria-labelledby="rawContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rawContentModalLabel">Raw HTML Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre class="bg-light p-3" style="max-height: 500px; overflow-y: auto;"><code id="htmlContent">{{ htmlspecialchars($setting->description) }}</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="copyHtml">
                    <i class="bi bi-clipboard me-1"></i> Salin HTML
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/{{ config('tinymce.api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.getElementById('copyHtml').addEventListener('click', function() {
        const htmlContent = document.getElementById('htmlContent').textContent;
        navigator.clipboard.writeText(htmlContent)
            .then(() => {
                this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Disalin!';
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-clipboard me-1"></i> Salin HTML';
                }, 2000);
            })
            .catch(err => {
                console.error('Gagal menyalin teks: ', err);
            });
    });
</script>
@endsection 