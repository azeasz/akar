@extends('admin.layouts.app')

@section('title', 'Detail User')

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
    
    .tab-content {
        padding: 20px;
        background-color: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 5px 5px;
    }
    
    .activity-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .no-activity {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail User</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">
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

<!-- Profile Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="profile-header d-flex flex-column flex-md-row">
                    <div class="text-center mb-4 mb-md-0 me-md-4">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="profile-image">
                        @else
                            <div class="profile-image-placeholder">
                                <i class="bi bi-person"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h2>{{ $user->name }}
                            @if($user->level == 2)
                                <span class="badge bg-primary">Admin</span>
                            @endif
                        </h2>
                        <p class="text-muted mb-1">
                            <i class="bi bi-envelope me-1"></i> {{ $user->email }}
                            @if($user->email_verified_at)
                                <span class="badge bg-success ms-1">Terverifikasi</span>
                            @else
                                <span class="badge bg-warning ms-1">Belum Verifikasi</span>
                            @endif
                        </p>
                        <p class="text-muted mb-1">
                            <i class="bi bi-person-badge me-1"></i> {{ $user->username }}
                        </p>
                        @if($user->phone_number)
                            <p class="text-muted mb-1">
                                <i class="bi bi-telephone me-1"></i> {{ $user->phone_number }}
                            </p>
                        @endif
                        <p class="text-muted mb-3">
                            <i class="bi bi-calendar me-1"></i> Terdaftar sejak {{ $user->created_at->format('d F Y') }}
                        </p>
                        
                        <div class="d-flex flex-wrap">
                            @if($user->level == 1)
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-inline me-2 mb-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="username" value="{{ $user->username }}">
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="level" value="2">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Promosikan ke Admin
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-inline me-2 mb-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="username" value="{{ $user->username }}">
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="level" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Turunkan ke User
                                    </button>
                                </form>
                            @endif
                            
                            <button type="button" class="btn btn-sm btn-outline-danger mb-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-1"></i> Hapus User
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="userTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="true">
                            <i class="bi bi-info-circle me-1"></i> Informasi Detail
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="checklists-tab" data-bs-toggle="tab" data-bs-target="#checklists-tab-pane" type="button" role="tab" aria-controls="checklists-tab-pane" aria-selected="false">
                            <i class="bi bi-list-check me-1"></i> Checklist
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-tab-pane" type="button" role="tab" aria-controls="activity-tab-pane" aria-selected="false">
                            <i class="bi bi-activity me-1"></i> Aktivitas
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="userTabContent">
                    <!-- Info Tab -->
                    <div class="tab-pane fade show active" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Informasi Pribadi</h5>
                                <dl class="row user-info">
                                    <dt class="col-sm-4">Nama Lengkap</dt>
                                    <dd class="col-sm-8">{{ $user->name }}</dd>
                                    
                                    <dt class="col-sm-4">Nama Depan</dt>
                                    <dd class="col-sm-8">{{ $user->firstname ?: 'Tidak Ada' }}</dd>
                                    
                                    <dt class="col-sm-4">Nama Belakang</dt>
                                    <dd class="col-sm-8">{{ $user->lastname ?: 'Tidak Ada' }}</dd>
                                    
                                    <dt class="col-sm-4">Nama Alias</dt>
                                    <dd class="col-sm-8">{{ $user->alias_name ?: 'Tidak Ada' }}</dd>
                                    
                                    <dt class="col-sm-4">Organisasi</dt>
                                    <dd class="col-sm-8">{{ $user->organisasi ?: 'Tidak Ada' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Informasi Kontak</h5>
                                <dl class="row user-info">
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8">{{ $user->email }}</dd>
                                    
                                    <dt class="col-sm-4">Status Email</dt>
                                    <dd class="col-sm-8">
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Terverifikasi pada {{ \Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="badge bg-warning">Belum Terverifikasi</span>
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-sm-4">Nomor Telepon</dt>
                                    <dd class="col-sm-8">{{ $user->phone_number ?: 'Tidak Ada' }}</dd>
                                    
                                    <dt class="col-sm-4">Social Media</dt>
                                    <dd class="col-sm-8">{{ $user->social_media ?: 'Tidak Ada' }}</dd>
                                </dl>
                            </div>
                        </div>
                        
                        @if($user->reason)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Alasan Pendaftaran</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $user->reason }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Checklists Tab -->
                    <div class="tab-pane fade" id="checklists-tab-pane" role="tabpanel" aria-labelledby="checklists-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipe</th>
                                        <th>Lokasi</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Jumlah Fauna</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->checklists as $checklist)
                                    <tr>
                                        <td>{{ $checklist->id }}</td>
                                        <td>{{ $checklist->type }}</td>
                                        <td>{{ $checklist->nama_lokasi }}</td>
                                        <td>{{ $checklist->tanggal->format('d/m/Y') }}</td>
                                        <td>
                                            @if($checklist->status == 'published')
                                                <span class="badge bg-success">Dipublikasikan</span>
                                            @else
                                                <span class="badge bg-warning">Draft</span>
                                            @endif
                                        </td>
                                        <td>{{ $checklist->faunas_count ?? $checklist->faunas->count() }}</td>
                                        <td>
                                            <a href="{{ route('admin.checklists.show', $checklist->id) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">User ini belum memiliki checklist</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Activity Tab -->
                    <div class="tab-pane fade" id="activity-tab-pane" role="tabpanel" aria-labelledby="activity-tab" tabindex="0">
                        <div class="activity-list">
                            @forelse($user->activityLogs as $activity)
                            <div class="activity-item d-flex align-items-start">
                                <div class="activity-icon bg-light rounded-circle p-2 me-3">
                                    @switch($activity->action)
                                        @case('login')
                                            <i class="bi bi-box-arrow-in-right text-primary"></i>
                                            @break
                                        @case('logout')
                                            <i class="bi bi-box-arrow-left text-danger"></i>
                                            @break
                                        @case('create')
                                            <i class="bi bi-plus-circle text-success"></i>
                                            @break
                                        @case('update')
                                            <i class="bi bi-pencil text-warning"></i>
                                            @break
                                        @case('delete')
                                            <i class="bi bi-trash text-danger"></i>
                                            @break
                                        @default
                                            <i class="bi bi-activity text-info"></i>
                                    @endswitch
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ ucfirst($activity->action) }}</strong>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0">{{ $activity->description }}</p>
                                    <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                                </div>
                            </div>
                            @empty
                            <div class="no-activity">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                <p>Tidak ada aktivitas yang tercatat</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
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
                <p>Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>?</p>
                <p class="text-danger">Perhatian: Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data yang terkait dengan user ini.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus User</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 