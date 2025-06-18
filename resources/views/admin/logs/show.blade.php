@extends('admin.layouts.app')

@section('title', 'Detail Log Aktivitas')

@section('styles')
<style>
    .badge-login {
        background-color: #4e73df;
    }
    .badge-logout {
        background-color: #e74a3b;
    }
    .badge-create {
        background-color: #1cc88a;
    }
    .badge-update {
        background-color: #f6c23e;
    }
    .badge-delete {
        background-color: #e74a3b;
    }
    .badge-approve {
        background-color: #36b9cc;
    }
    .badge-reject {
        background-color: #e74a3b;
    }
    .badge-export {
        background-color: #6f42c1;
    }
    .badge-promote {
        background-color: #1cc88a;
    }
    .badge-demote {
        background-color: #e74a3b;
    }
    .info-label {
        font-weight: bold;
        color: #bf6420;
    }
    .action-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 24px;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Log Aktivitas</h1>
    <div>
        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">Informasi Log</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="d-flex justify-content-center">
                        <div class="action-icon text-white mb-3 badge-{{ $log->action }}">
                            @if($log->action == 'login')
                                <i class="bi bi-box-arrow-in-right"></i>
                            @elseif($log->action == 'logout')
                                <i class="bi bi-box-arrow-left"></i>
                            @elseif($log->action == 'create')
                                <i class="bi bi-plus-circle"></i>
                            @elseif($log->action == 'update')
                                <i class="bi bi-pencil"></i>
                            @elseif($log->action == 'delete')
                                <i class="bi bi-trash"></i>
                            @elseif($log->action == 'approve')
                                <i class="bi bi-check-circle"></i>
                            @elseif($log->action == 'reject')
                                <i class="bi bi-x-circle"></i>
                            @elseif($log->action == 'export')
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            @else
                                <i class="bi bi-activity"></i>
                            @endif
                        </div>
                    </div>
                    <h4 class="mb-1">{{ strtoupper($log->action) }}</h4>
                    <div class="text-muted">ID: {{ $log->id }}</div>
                </div>
                <hr>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <span class="info-label">Deskripsi</span>
                    </div>
                    <div class="col-md-8">
                        {{ $log->description }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <span class="info-label">Waktu</span>
                    </div>
                    <div class="col-md-8">
                        {{ $log->created_at->format('d M Y, H:i:s') }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <span class="info-label">Dibuat</span>
                    </div>
                    <div class="col-md-8">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </div>
                @if($log->updated_at->ne($log->created_at))
                <div class="row mb-2">
                    <div class="col-md-4">
                        <span class="info-label">Diperbarui</span>
                    </div>
                    <div class="col-md-8">
                        {{ $log->updated_at->format('d M Y, H:i:s') }}
                    </div>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between">
                    <form action="{{ route('admin.logs.destroy', $log->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin akan menghapus log ini?')">
                            <i class="bi bi-trash me-1"></i> Hapus Log
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">User</h6>
            </div>
            <div class="card-body">
                @if($log->user)
                <div class="d-flex align-items-center mb-3">
                    @if($log->user->profile_picture)
                        <img src="{{ asset('storage/' . $log->user->profile_picture) }}" 
                            alt="{{ $log->user->name }}" 
                            class="rounded-circle me-3" 
                            style="width: 64px; height: 64px; object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                            style="width: 64px; height: 64px; font-size: 24px;">
                            {{ substr($log->user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h5 class="mb-0">{{ $log->user->name }}</h5>
                        <div class="text-muted">{{ $log->user->email }}</div>
                        <div class="mt-1">
                            <span class="badge {{ $log->user->isAdmin() ? 'bg-danger' : 'bg-primary' }}">
                                {{ $log->user->isAdmin() ? 'Admin' : 'User' }}
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="info-label">Username</span>
                    </div>
                    <div class="col-md-9">
                        {{ $log->user->username }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="info-label">ID User</span>
                    </div>
                    <div class="col-md-9">
                        {{ $log->user->id }}
                    </div>
                </div>
                @if($log->user->phone_number)
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="info-label">No. Telp</span>
                    </div>
                    <div class="col-md-9">
                        {{ $log->user->phone_number }}
                    </div>
                </div>
                @endif
                @if($log->user->organisasi)
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="info-label">Organisasi</span>
                    </div>
                    <div class="col-md-9">
                        {{ $log->user->organisasi }}
                    </div>
                </div>
                @endif
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="info-label">Tanggal Daftar</span>
                    </div>
                    <div class="col-md-9">
                        {{ $log->user->created_at->format('d M Y, H:i:s') }}
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.users.show', $log->user->id) }}" class="btn btn-primary">
                        <i class="bi bi-person me-1"></i> Lihat Detail User
                    </a>
                </div>
                @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-1"></i> User tidak ditemukan atau telah dihapus.
                </div>
                @endif
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">Log Aktivitas Terkait</h6>
            </div>
            <div class="card-body">
                @if($log->user)
                    <div class="timeline-container">
                        @foreach($log->user->activityLogs()->latest()->take(5)->get() as $relatedLog)
                            <div class="timeline-item pb-3 {{ $relatedLog->id == $log->id ? 'bg-light rounded p-2' : '' }}">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="timeline-icon text-white rounded-circle p-2 badge-{{ $relatedLog->action }}">
                                            <i class="bi bi-activity"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="timeline-content">
                                            <strong class="d-block">{{ strtoupper($relatedLog->action) }}</strong>
                                            <p class="mb-0">{{ $relatedLog->description }}</p>
                                            <small class="text-muted">{{ $relatedLog->created_at->diffForHumans() }}</small>
                                            @if($relatedLog->id == $log->id)
                                                <span class="badge bg-warning text-dark">Current</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.logs.index', ['user_id' => $log->user_id]) }}" class="btn btn-sm btn-outline-primary">
                            Lihat Semua Log User Ini
                        </a>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-1"></i> Tidak ada log aktivitas terkait.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 