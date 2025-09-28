@extends('admin.layouts.app')

@section('title', 'Kelola Fauna Prioritas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-exclamation text-warning"></i>
            Kelola Fauna Prioritas
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.priority-fauna.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <a href="{{ route('admin.priority-fauna.fauna.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Fauna
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.priority-fauna.fauna') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Cari Fauna</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nama taksa, ilmiah, atau umum">
                    </div>
                    <div class="col-md-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->type }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="is_monitored" class="form-label">Status Monitoring</label>
                        <select class="form-select" id="is_monitored" name="is_monitored">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_monitored') === '1' ? 'selected' : '' }}>Dipantau</option>
                            <option value="0" {{ request('is_monitored') === '0' ? 'selected' : '' }}>Tidak Dipantau</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="btn-group w-100" role="group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.priority-fauna.fauna') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Fauna Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Fauna Prioritas 
                <span class="badge bg-secondary">{{ $fauna->total() }}</span>
            </h6>
            @if($fauna->where('is_monitored', true)->count() > 0)
            <form action="{{ route('admin.priority-fauna.sync-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Sinkronisasi semua fauna yang perlu update?')">
                    <i class="bi bi-arrow-clockwise"></i> Sync All
                </button>
            </form>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Taksa</th>
                            <th>Kategori</th>
                            <th>Status IUCN</th>
                            <th>Status Perlindungan</th>
                            <th>Monitoring</th>
                            <th>Sync Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fauna as $item)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $item->display_name }}</div>
                                    @if($item->scientific_name && $item->scientific_name !== $item->display_name)
                                        <small class="text-muted fst-italic">{{ $item->scientific_name }}</small>
                                    @endif
                                    @if($item->taxa_id)
                                        <br><small class="text-muted">Taxa ID: {{ $item->taxa_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $item->category->color_code }}">
                                    {{ $item->category->name }}
                                </span>
                                <br><small class="text-muted">{{ $item->category->type }}</small>
                            </td>
                            <td>
                                @if($item->iucn_status)
                                    <span class="badge bg-warning">{{ $item->iucn_status }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->protection_status)
                                    @if($item->protection_status === 'Dilindungi')
                                        <span class="badge bg-success">{{ $item->protection_status }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $item->protection_status }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_monitored)
                                    <span class="badge bg-success">
                                        <i class="bi bi-eye-fill"></i> Dipantau
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-eye-slash"></i> Tidak Dipantau
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item->last_api_sync)
                                    <small class="text-muted">{{ $item->last_api_sync->diffForHumans() }}</small>
                                    @if($item->needsApiSync())
                                        <br><span class="badge bg-warning">Perlu Sync</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Belum Sync</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.priority-fauna.fauna.show', $item) }}" 
                                       class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($item->needsApiSync())
                                    <form action="{{ route('admin.priority-fauna.fauna.sync', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                title="Sync Data" onclick="return confirm('Sinkronisasi data fauna ini?')">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.priority-fauna.fauna.destroy', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                title="Hapus" onclick="return confirm('Yakin ingin menghapus fauna ini dari daftar prioritas?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                Belum ada fauna prioritas
                                <br>
                                <a href="{{ route('admin.priority-fauna.fauna.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-circle"></i> Tambah Fauna Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $fauna->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on select change
    const categorySelect = document.getElementById('category_id');
    const monitoringSelect = document.getElementById('is_monitored');
    
    [categorySelect, monitoringSelect].forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endsection
