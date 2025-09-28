@extends('admin.layouts.app')

@section('title', 'Log Aktivitas')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .action-badge {
        font-size: 0.8rem;
    }
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
    .timeline-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Log Aktivitas</h1>
    <div>
        <a href="{{ route('admin.logs.export') }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0">Filter Log Aktivitas</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.logs.index') }}" method="GET">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">User</label>
                    <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="action" class="form-label">Jenis Aktivitas</label>
                    <select name="action" id="action" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Aktivitas --</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control date-picker"
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control date-picker"
                           value="{{ request('end_date') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari log..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
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
                        <th>ID</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Deskripsi</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center">
                                        @if($log->user->profile_picture)
                                            <img src="{{ asset('storage/' . $log->user->profile_picture) }}" 
                                                alt="{{ $log->user->name }}" 
                                                class="rounded-circle me-2" 
                                                style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" 
                                                style="width: 32px; height: 32px;">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div>{{ $log->user->name }}</div>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span>System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->action }} text-white action-badge">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.logs.show', $log->id) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.logs.destroy', $log->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Yakin akan menghapus log ini?')">
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
                                    <h5 class="text-muted">Tidak ada data log aktivitas</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $logs->withQueryString()->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d"
        });
        
        // Auto submit filter
        document.getElementById('user_id').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
        
        document.getElementById('action').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
</script>
@endsection 