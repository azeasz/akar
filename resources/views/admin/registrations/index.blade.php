@extends('admin.layouts.app')

@section('title', 'Pendaftaran Baru')

@section('styles')
<style>
    .filter-card {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
    
    .registration-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .registration-reason {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pendaftaran Baru</h1>
    <div>
        <span class="badge bg-info p-2">
            <i class="bi bi-info-circle me-1"></i> Total: {{ $registrations->total() }} pendaftaran menunggu persetujuan
        </span>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 filter-card">
    <div class="card-body">
        <form action="{{ route('admin.registrations.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, username...">
            </div>
            <div class="col-md-3">
                <label for="sort_by" class="form-label">Urutkan</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Registrasi</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama</option>
                    <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="username" {{ request('sort_by') == 'username' ? 'selected' : '' }}>Username</option>
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
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-secondary ms-2">
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

<!-- Registration List Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">Daftar Pendaftaran Baru</h6>
    </div>
    <div class="card-body">
        @if($registrations->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-clipboard-check fs-1 text-muted"></i>
            <p class="mt-3">Tidak ada pendaftaran yang menunggu persetujuan.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="registrationsTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Profil</th>
                        <th width="15%">Username</th>
                        <th width="15%">Email</th>
                        <th width="20%">Alasan Pendaftaran</th>
                        <th width="15%">Terdaftar</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $registration)
                    <tr>
                        <td>{{ $registration->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($registration->profile_picture)
                                    <img src="{{ asset('storage/' . $registration->profile_picture) }}" class="registration-avatar me-2" alt="{{ $registration->name }}">
                                @else
                                    <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center me-2 registration-avatar">
                                        <span>{{ substr($registration->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $registration->name }}</div>
                                    <small class="text-muted">{{ $registration->organisasi ?: 'Tidak ada organisasi' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $registration->username }}</td>
                        <td>{{ $registration->email }}</td>
                        <td>
                            <div class="registration-reason" title="{{ $registration->reason }}">
                                {{ $registration->reason ?: 'Tidak ada alasan' }}
                            </div>
                        </td>
                        <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex">
                                <a href="{{ route('admin.registrations.show', $registration->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $registration->id }}">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $registration->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            
                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $registration->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $registration->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="approveModalLabel{{ $registration->id }}">Konfirmasi Persetujuan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda yakin ingin menyetujui pendaftaran <strong>{{ $registration->name }}</strong>?</p>
                                            <p>Pengguna akan mendapatkan akses penuh ke sistem.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.registrations.approve', $registration->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-success">Setujui</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $registration->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $registration->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="rejectModalLabel{{ $registration->id }}">Konfirmasi Penolakan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda yakin ingin menolak pendaftaran <strong>{{ $registration->name }}</strong>?</p>
                                            <p class="text-danger">Perhatian: Tindakan ini akan menghapus akun pengguna dan tidak dapat dibatalkan.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.registrations.reject', $registration->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $registrations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection 