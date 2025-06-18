@extends('admin.layouts.app')

@section('title', 'Manajemen Checklist')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 4px;
    }
    
    .badge.bg-status-draft {
        background-color: #f8f9fa;
        color: #212529;
        border: 1px solid #dee2e6;
    }
    
    .badge.bg-status-published {
        background-color: #28a745;
        color: white;
    }
    
    .filter-card {
        background-color: #f8f9fc;
        border-left: 4px solid #bf6420;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Checklist</h1>
    <div>
        <a href="{{ route('admin.checklists.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Checklist
        </a>
        <a href="{{ route('admin.checklists.export') }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 filter-card">
    <div class="card-body">
        <form action="{{ route('admin.checklists.index') }}" method="GET" id="filter-form">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ isset($filters['user_id']) && $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ isset($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ isset($filters['status']) && $filters['status'] == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ isset($filters['status']) && $filters['status'] == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status Kelengkapan</label>
                    <select name="is_completed" class="form-select">
                        <option value="">Semua</option>
                        <option value="1" {{ isset($filters['is_completed']) && $filters['is_completed'] == '1' ? 'selected' : '' }}>Data Lengkap</option>
                        <option value="0" {{ isset($filters['is_completed']) && $filters['is_completed'] == '0' ? 'selected' : '' }}>Data Belum Lengkap</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="text" name="date_from" class="form-control datepicker" value="{{ $filters['date_from'] ?? '' }}" placeholder="Pilih tanggal">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="text" name="date_to" class="form-control datepicker" value="{{ $filters['date_to'] ?? '' }}" placeholder="Pilih tanggal">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pencarian</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari lokasi, pemilik, atau catatan..." value="{{ $filters['search'] ?? '' }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-secondary" type="button" id="reset-filter">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Checklist Table -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Lokasi</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Kelengkapan</th>
                        <th>Fauna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checklists as $checklist)
                    <tr>
                        <td>{{ $checklist->id }}</td>
                        <td>{{ $checklist->user->name }}</td>
                        <td>{{ $checklist->nama_lokasi }}</td>
                        <td>
                            @if(strtolower($checklist->type) === 'lainnya')
                                Pemeliharaan & Penangkaran
                            @else
                                {{ $checklist->type_text }}
                            @endif
                        </td>
                        <td>{{ $checklist->tanggal->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-status-{{ $checklist->status }}">
                                {{ ucfirst($checklist->status) }}
                            </span>
                        </td>
                        <td>
                            @if($checklist->completion_status == 'Selesai')
                                <i class="bi bi-check-circle-fill text-success" title="Data Lengkap"></i>
                            @else
                                <i class="bi bi-clock text-warning" title="Data Belum Lengkap"></i>
                            @endif
                        </td>
                        <td>{{ $checklist->faunas->count() }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.checklists.show', $checklist) }}" class="btn btn-info action-btn" title="Lihat Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('admin.checklists.edit', $checklist) }}" class="btn btn-warning action-btn" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @if($checklist->completion_status != 'Selesai')
                                <form action="{{ route('admin.checklists.complete', $checklist) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success action-btn" title="Tandai Data Lengkap">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                @endif
                                @if($checklist->status === 'draft')
                                <form action="{{ route('admin.checklists.publish', $checklist) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-primary action-btn" title="Publikasikan">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.checklists.destroy', $checklist) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus checklist ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger action-btn" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bi bi-clipboard-x" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                <h5 class="mt-2">Tidak ada data checklist ditemukan</h5>
                                <p class="text-muted">Coba atur filter atau buat checklist baru</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $checklists->firstItem() ?? 0 }} - {{ $checklists->lastItem() ?? 0 }} dari {{ $checklists->total() }} data
            </div>
            <div>
                {{ $checklists->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true,
        });
        
        // Reset filter
        document.getElementById('reset-filter').addEventListener('click', function() {
            window.location.href = "{{ route('admin.checklists.index') }}";
        });
        
        // Auto-submit form on select change
        const filterForm = document.getElementById('filter-form');
        const selectElements = filterForm.querySelectorAll('select');
        
        selectElements.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>
@endsection 