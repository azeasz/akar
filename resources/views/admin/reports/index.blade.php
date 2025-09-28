@extends('admin.layouts.app')

@section('title', 'Laporan Masalah')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .badge-status {
        font-size: 0.8rem;
    }
    .badge-resolved {
        background-color: #1cc88a;
    }
    .badge-unresolved {
        background-color: #e74a3b;
    }
    .report-text {
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    .report-text::after {
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
    <h1 class="h3 mb-0 text-gray-800">Laporan Masalah</h1>
    <div>
        <a href="{{ route('admin.reports.export') }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reports.index') }}" method="GET" id="filter-form">
            <div class="row mb-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label for="is_resolved" class="form-label">Status</label>
                    <select name="is_resolved" id="is_resolved" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('is_resolved') == '1' ? 'selected' : '' }}>Laporan Lengkap</option>
                        <option value="0" {{ request('is_resolved') == '0' ? 'selected' : '' }}>Laporan Tidak Lengkap</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Cari</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Cari laporan..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control date-picker" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control date-picker" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Terapkan Filter
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
                        <th width="5%">ID</th>
                        <th width="15%">User</th>
                        <th width="40%">Masalah</th>
                        <th width="10%">Status</th>
                        <th width="15%">Tanggal Laporan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>
                                @if($report->user)
                                <div class="d-flex align-items-center">
                                    @if($report->user->profile_picture)
                                        <img src="{{ asset('storage/' . $report->user->profile_picture) }}" 
                                            alt="{{ $report->user->name }}" 
                                            class="rounded-circle me-2" 
                                            style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" 
                                            style="width: 32px; height: 32px;">
                                            {{ substr($report->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div>{{ $report->user->name }}</div>
                                        <small class="text-muted">{{ $report->user->email }}</small>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">User tidak ditemukan</span>
                                @endif
                            </td>
                            <td>
                                <div class="report-text">{{ $report->masalah }}</div>
                            </td>
                            <td>
                                @if($report->is_resolved)
                                    <span class="badge badge-resolved text-white badge-status">Selesai</span>
                                @else
                                    <span class="badge badge-unresolved text-white badge-status">Belum Selesai</span>
                                @endif
                            </td>
                            <td>{{ $report->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.reports.show', $report->id) }}" class="btn btn-sm btn-info me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @if($report->is_resolved)
                                        <form action="{{ route('admin.reports.unresolve', $report->id) }}" method="POST" class="me-1">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-warning" title="Tandai sebagai belum selesai">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST" class="me-1">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success" title="Tandai sebagai selesai">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Yakin akan menghapus laporan ini?')">
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
                                    <h5 class="text-muted">Tidak ada laporan masalah</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item {{ $reports->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $reports->previousPageUrl() }}" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    @for ($i = 1; $i <= $reports->lastPage(); $i++)
                        <li class="page-item {{ $reports->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $reports->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $reports->onLastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $reports->nextPageUrl() }}">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
    <h6 class="m-0">Statistik Laporan</h6>    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Laporan</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reports->total() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-danger h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Belum Selesai</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \App\Models\Report::where('is_resolved', 0)->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selesai</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \App\Models\Report::where('is_resolved', 1)->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Persentase Selesai</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            @php
                                                $total = \App\Models\Report::count();
                                                $resolved = \App\Models\Report::where('is_resolved', 1)->count();
                                                $percentage = $total > 0 ? round(($resolved / $total) * 100) : 0;
                                            @endphp
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clipboard-data fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    });
</script>
@endsection 