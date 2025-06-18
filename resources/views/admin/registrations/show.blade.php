@extends('admin.layouts.app')

@section('title', 'Detail Pendaftaran')

@section('styles')
<style>
    .profile-header {
        background-color: #f8f9fc;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .profile-image-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        color: #adb5bd;
        border: 5px solid white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .user-info dt {
        font-weight: bold;
    }
    
    .user-info dd {
        margin-bottom: 10px;
    }
    
    .approval-buttons {
        display: flex;
        gap: 10px;
    }
    
    .approval-buttons .btn {
        flex: 1;
        padding-top: 10px;
        padding-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Pendaftaran</h1>
    <a href="{{ route('admin.registrations.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
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

<!-- Profile Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="profile-header d-flex flex-column flex-md-row">
                    <div class="text-center mb-4 mb-md-0 me-md-4">
                        @if($registration->profile_picture)
                            <img src="{{ asset('storage/' . $registration->profile_picture) }}" alt="{{ $registration->name }}" class="profile-image">
                        @else
                            <div class="profile-image-placeholder">
                                <i class="bi bi-person"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h2>{{ $registration->name }}</h2>
                        <p class="text-muted mb-1">
                            <i class="bi bi-envelope me-1"></i> {{ $registration->email }}
                            <span class="badge bg-warning ms-1">Belum Verifikasi</span>
                        </p>
                        <p class="text-muted mb-1">
                            <i class="bi bi-person-badge me-1"></i> {{ $registration->username }}
                        </p>
                        @if($registration->phone_number)
                            <p class="text-muted mb-1">
                                <i class="bi bi-telephone me-1"></i> {{ $registration->phone_number }}
                            </p>
                        @endif
                        <p class="text-muted mb-3">
                            <i class="bi bi-calendar me-1"></i> Terdaftar pada {{ $registration->created_at->format('d F Y H:i') }}
                        </p>
                        
                        <div class="approval-buttons">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-check-circle me-1"></i> Setujui Pendaftaran
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i> Tolak Pendaftaran
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Informasi Pribadi</h5>
                        <dl class="row user-info">
                            <dt class="col-sm-4">Nama Lengkap</dt>
                            <dd class="col-sm-8">{{ $registration->name }}</dd>
                            
                            <dt class="col-sm-4">Nama Depan</dt>
                            <dd class="col-sm-8">{{ $registration->firstname ?: 'Tidak Ada' }}</dd>
                            
                            <dt class="col-sm-4">Nama Belakang</dt>
                            <dd class="col-sm-8">{{ $registration->lastname ?: 'Tidak Ada' }}</dd>
                            
                            <dt class="col-sm-4">Nama Alias</dt>
                            <dd class="col-sm-8">{{ $registration->alias_name ?: 'Tidak Ada' }}</dd>
                            
                            <dt class="col-sm-4">Organisasi</dt>
                            <dd class="col-sm-8">{{ $registration->organisasi ?: 'Tidak Ada' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Informasi Kontak</h5>
                        <dl class="row user-info">
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $registration->email }}</dd>
                            
                            <dt class="col-sm-4">Status Email</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-warning">Belum Terverifikasi</span>
                            </dd>
                            
                            <dt class="col-sm-4">Nomor Telepon</dt>
                            <dd class="col-sm-8">{{ $registration->phone_number ?: 'Tidak Ada' }}</dd>
                            
                            <dt class="col-sm-4">Social Media</dt>
                            <dd class="col-sm-8">{{ $registration->social_media ?: 'Tidak Ada' }}</dd>
                        </dl>
                    </div>
                </div>
                
                @if($registration->reason)
                <div class="row mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">Alasan Pendaftaran</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $registration->reason }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Konfirmasi Persetujuan</h5>
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
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Konfirmasi Penolakan</h5>
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
@endsection 