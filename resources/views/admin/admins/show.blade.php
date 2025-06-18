@extends('admin.layouts.app')

@section('title', 'Detail Admin')

@section('styles')
<style>
    .admin-card {
        background-color: #fff;
    }
    
    .admin-info dt {
        font-weight: bold;
    }
    
    .admin-info dd {
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Admin</h1>
    <div>
        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ms-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Admin Info Card -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4 admin-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold">Informasi Admin</h6>
                <div>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-1"></i> Hapus Admin
                    </button>
                </div>
            </div>
            <div class="card-body">
                <dl class="row admin-info">
                    <dt class="col-sm-3">ID</dt>
                    <dd class="col-sm-9">{{ $admin->id }}</dd>
                    
                    <dt class="col-sm-3">Nama</dt>
                    <dd class="col-sm-9">{{ $admin->name }}</dd>
                    
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $admin->email }}
                        @if($admin->email_verified_at)
                            <span class="badge bg-success ms-1">Terverifikasi</span>
                        @else
                            <span class="badge bg-warning ms-1">Belum Terverifikasi</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-3">Terdaftar Pada</dt>
                    <dd class="col-sm-9">{{ $admin->created_at->format('d F Y H:i:s') }}</dd>
                    
                    <dt class="col-sm-3">Terakhir Diupdate</dt>
                    <dd class="col-sm-9">{{ $admin->updated_at->format('d F Y H:i:s') }}</dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">User Terkait</h6>
            </div>
            <div class="card-body">
                @if($admin->user)
                    <div class="text-center mb-4">
                        @if($admin->user->profile_picture)
                            <img src="{{ asset('storage/' . $admin->user->profile_picture) }}" alt="{{ $admin->user->name }}" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 40px;">
                                <span>{{ substr($admin->user->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="text-center">{{ $admin->user->name }}</h5>
                    <p class="text-center text-muted">{{ $admin->user->email }}</p>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('admin.users.show', $admin->user_id) }}" class="btn btn-info">
                            <i class="bi bi-eye me-1"></i> Lihat Detail User
                        </a>
                    </div>
                    
                    <hr>
                    
                    <p class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Admin ini terhubung dengan akun user di atas. Admin memiliki akses penuh ke panel admin.
                    </p>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-person-x fs-1 text-muted"></i>
                        <p class="mt-3">Tidak ada user yang terkait dengan admin ini.</p>
                        <p class="text-muted small">
                            Admin ini dibuat secara independen tanpa terhubung ke akun user manapun.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
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
                    <button type="submit" class="btn btn-danger">Hapus Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 