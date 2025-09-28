@extends('admin.layouts.app')

@section('title', 'Manajemen Admin')

@section('styles')
<style>
    .admin-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .filter-card {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Admin</h1>
    <div>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Admin
        </a>
        <a href="{{ route('admin.admins.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success ms-2">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 filter-card">
    <div class="card-body">
        <form action="{{ route('admin.admins.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email...">
            </div>
            <div class="col-md-3">
                <label for="sort_by" class="form-label">Urutkan</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Registrasi</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama</option>
                    <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort_order" class="form-label">Urutan</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Naik</option>
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Turun</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ms-2">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Admin List Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">Daftar Admin</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="adminTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Nama</th>
                        <th width="25%">Email</th>
                        <th width="20%">User Terkait</th>
                        <th width="15%">Terdaftar</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->id }}</td>
                        <td>{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            @if($admin->user)
                                <a href="{{ route('admin.users.show', $admin->user_id) }}">{{ $admin->user->name }}</a>
                            @else
                                <span class="text-muted">Tidak Ada</span>
                            @endif
                        </td>
                        <td>{{ $admin->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $admin->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                
                                <!-- Delete Modal for each admin -->
                                <div class="modal fade" id="deleteModal{{ $admin->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $admin->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $admin->id }}">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus admin <strong>{{ $admin->name }}</strong>?</p>
                                                <p class="text-danger">Perhatian: Tindakan ini tidak dapat dibatalkan.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data admin</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $admins->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection 