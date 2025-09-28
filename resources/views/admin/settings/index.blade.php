@extends('admin.layouts.app')

@section('title', 'Pengaturan Aplikasi')

@section('styles')
<style>
    .type-badge {
        font-size: 0.8rem;
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
    .content-preview {
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    .content-preview::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: linear-gradient(rgba(255,255,255,0), rgba(255,255,255,1));
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pengaturan Aplikasi</h1>
    <div>
        <a href="{{ route('admin.settings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Baru
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0">Filter Pengaturan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.index') }}" method="GET">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <select name="type" class="form-select">
                            <option value="">-- Semua Tipe --</option>
                            <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Deskripsi</option>
                            <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Privacy Policy</option>
                            <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>Terms & Conditions</option>
                            <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>About</option>
                            <option value="5" {{ request('type') == '5' ? 'selected' : '' }}>FAQ</option>
                        </select>
                        <input type="text" class="form-control" name="search" placeholder="Cari pengaturan..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Tipe</th>
                        <th width="30%">Judul</th>
                        <th>Isi</th>
                        <th width="15%">Tanggal Update</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $setting)
                        <tr>
                            <td>{{ $setting->id }}</td>
                            <td>
                                <span class="badge badge-{{ $setting->type }} text-white type-badge">
                                    {{ $setting->typeName }}
                                </span>
                            </td>
                            <td>{{ $setting->title }}</td>
                            <td>
                                <div class="content-preview">
                                    {!! Str::limit(strip_tags($setting->description), 200) !!}
                                </div>
                            </td>
                            <td>{{ $setting->updated_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.settings.show', $setting->id) }}" class="btn btn-sm btn-info me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.settings.edit', $setting->id) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.settings.destroy', $setting->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Yakin akan menghapus pengaturan ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-exclamation-circle text-muted mb-2" style="font-size: 2rem;"></i>
                                    <h5 class="text-muted">Tidak ada data pengaturan</h5>
                                </div>
                            </td>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item {{ $settings->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $settings->previousPageUrl() }}" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    @for($i = 1; $i <= $settings->lastPage(); $i++)
                        <li class="page-item {{ $settings->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $settings->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $settings->onLastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $settings->nextPageUrl() }}">Next</a>
                    </li>
                </ul>
            </nav>
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
                        <h6 class="mb-3"><span class="badge badge-1 text-white">Deskripsi</span></h6>
                        <p class="mb-0 text-muted">Deskripsi aplikasi, pengantar, atau informasi umum tentang aplikasi.</p>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3"><span class="badge badge-2 text-white">Privacy Policy</span></h6>
                        <p class="mb-0 text-muted">Kebijakan privasi untuk pengguna aplikasi.</p>
                    </div>
                </div>
                <div class="card mb-md-0 mb-3">
                    <div class="card-body">
                        <h6 class="mb-3"><span class="badge badge-3 text-white">Terms & Conditions</span></h6>
                        <p class="mb-0 text-muted">Syarat dan ketentuan penggunaan aplikasi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3"><span class="badge badge-4 text-white">About</span></h6>
                        <p class="mb-0 text-muted">Informasi tentang aplikasi, pengembang, atau perusahaan.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3"><span class="badge badge-5 text-white">FAQ</span></h6>
                        <p class="mb-0 text-muted">Pertanyaan yang sering diajukan dan jawabannya.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 