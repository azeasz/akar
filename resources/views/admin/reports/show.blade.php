@extends('admin.layouts.app')

@section('title', 'Detail Laporan')

@section('styles')
<style>
    .info-label {
        font-weight: bold;
        color: #bf6420;
    }
    .message-card {
        border-radius: 8px;
        background-color: #f8f9fc;
        border-left: 5px solid #e74a3b;
        padding: 20px;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Laporan</h1>
    <div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0">Info Laporan</h6>
                <span class="badge {{ $report->is_resolved ? 'bg-success' : 'bg-danger' }}">
                    {{ $report->is_resolved ? 'Selesai' : 'Belum Selesai' }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label mb-1">ID</div>
                    <div>{{ $report->id }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label mb-1">Dilaporkan pada</div>
                    <div>{{ $report->created_at->format('d M Y, H:i:s') }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label mb-1">Status</div>
                    <div>
                        @if($report->is_resolved)
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <span class="badge bg-danger">Belum Selesai</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="info-label mb-1">Terakhir diperbarui</div>
                    <div>{{ $report->updated_at->format('d M Y, H:i:s') }}</div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    @if($report->is_resolved)
                        <form action="{{ route('admin.reports.unresolve', $report->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-x-circle me-1"></i> Tandai Belum Selesai
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Tandai Selesai
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin akan menghapus laporan ini?')">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">Aksi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="{{ route('admin.users.show', $report->user_id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-person me-1"></i> Lihat Profil User
                    </a>
                </div>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#responseModal">
                        <i class="bi bi-reply me-1"></i> Kirim Tanggapan
                    </button>
                </div>
                
                <div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">Laporan Masalah</h6>
            </div>
            <div class="card-body">
                <div class="message-card mb-4">
                    <p class="mb-0">{{ $report->masalah }}</p>
                </div>
                
                <div class="d-flex align-items-center mb-4">
                    @if($report->user)
                        @if($report->user->profile_picture)
                            <img src="{{ asset('storage/' . $report->user->profile_picture) }}" 
                                alt="{{ $report->user->name }}" 
                                class="rounded-circle me-3" 
                                style="width: 64px; height: 64px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                style="width: 64px; height: 64px; font-size: 24px;">
                                {{ substr($report->user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $report->user->name }}</h5>
                            <div class="text-muted">{{ $report->user->email }}</div>
                            <div class="mt-1">
                                <span class="badge {{ $report->user->isAdmin() ? 'bg-danger' : 'bg-primary' }}">
                                    {{ $report->user->isAdmin() ? 'Admin' : 'User' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i> User tidak ditemukan atau telah dihapus.
                        </div>
                    @endif
                </div>
                
                <hr>
                
                <h6 class="mb-3">Informasi User</h6>
                @if($report->user)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label mb-1">Username</div>
                                <div>{{ $report->user->username }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label mb-1">Email</div>
                                <div>{{ $report->user->email }}</div>
                            </div>
                            @if($report->user->phone_number)
                                <div class="mb-3">
                                    <div class="info-label mb-1">No. Telp</div>
                                    <div>{{ $report->user->phone_number }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($report->user->organisasi)
                                <div class="mb-3">
                                    <div class="info-label mb-1">Organisasi</div>
                                    <div>{{ $report->user->organisasi }}</div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <div class="info-label mb-1">Tanggal Daftar</div>
                                <div>{{ $report->user->created_at->format('d M Y') }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label mb-1">Status Verifikasi</div>
                                <div>
                                    @if($report->user->email_verified_at)
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-warning">Belum Terverifikasi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i> Data user tidak tersedia.
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0">Riwayat Laporan User Ini</h6>
            </div>
            <div class="card-body">
                @if($report->user && $report->user->reports->count() > 1)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Masalah</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report->user->reports->where('id', '!=', $report->id)->take(5) as $otherReport)
                                    <tr>
                                        <td><a href="{{ route('admin.reports.show', $otherReport->id) }}">{{ $otherReport->id }}</a></td>
                                        <td>{{ Str::limit($otherReport->masalah, 50) }}</td>
                                        <td>
                                            @if($otherReport->is_resolved)
                                                <span class="badge bg-success">Selesai</span>
                                            @else
                                                <span class="badge bg-danger">Belum Selesai</span>
                                            @endif
                                        </td>
                                        <td>{{ $otherReport->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($report->user->reports->count() > 6)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.reports.index', ['user_id' => $report->user_id]) }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Laporan User Ini
                            </a>
                        </div>
                    @endif
                @elseif($report->user)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i> Tidak ada laporan lain dari user ini.
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i> Data user tidak tersedia.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Response -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Kirim Tanggapan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Tujuan</label>
                        <input type="email" class="form-control" id="email" value="{{ $report->user ? $report->user->email : '' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subjek</label>
                        <input type="text" class="form-control" id="subject" value="Tanggapan Laporan #{{ $report->id }}">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan</label>
                        <textarea class="form-control" id="message" rows="5">Kepada Yth. {{ $report->user ? $report->user->name : 'Pengguna' }},

Terima kasih telah menghubungi kami. Terkait laporan Anda tentang "{{ Str::limit($report->masalah, 50) }}".

[Silakan tulis respon di sini]

Salam,
Tim AKAR</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="sendResponse()">Kirim Tanggapan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function sendResponse() {
        // Simulasi pengiriman email (implementasi sebenarnya memerlukan backend)
        alert('Fitur pengiriman email masih dalam pengembangan');
        $('#responseModal').modal('hide');
        
        // Di sini Anda dapat menambahkan AJAX call ke backend untuk mengirim email
    }
</script>
@endsection 