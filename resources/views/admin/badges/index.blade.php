@extends('admin.layouts.app')

@section('title', 'Manajemen Badge Akar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-award"></i> Manajemen Badge Akar
                        </h4>
                        <a href="{{ route('admin.badges.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Tambah Badge
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Pencarian</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Cari judul badge...">
                            </div>

                            <div class="col-md-3">
                                <label for="type" class="form-label">Tipe Badge</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">Semua Tipe</option>
                                    @foreach($badgeTypes as $badgeType)
                                        <option value="{{ $badgeType['id'] }}" {{ request('type') == $badgeType['id'] ? 'selected' : '' }}>
                                            {{ $badgeType['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="has_total" class="form-label">Target</label>
                                <select class="form-select" id="has_total" name="has_total">
                                    <option value="">Semua</option>
                                    <option value="1" {{ request('has_total') == '1' ? 'selected' : '' }}>Dengan Target</option>
                                    <option value="0" {{ request('has_total') == '0' ? 'selected' : '' }}>Tanpa Target</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="sort_by" class="form-label">Urutkan</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                                    <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Judul</option>
                                    <option value="type" {{ request('sort_by') == 'type' ? 'selected' : '' }}>Tipe</option>
                                </select>
                            </div>

                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                                    <a href="{{ route('admin.badges.index') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Judul</th>
                                    <th width="150">Tipe</th>
                                    <th width="100">Target</th>
                                    <th width="120">Icon Active</th>
                                    <th width="120">Icon Inactive</th>
                                    <th width="150">Dibuat</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($badges as $badge)
                                <tr>
                                    <td>{{ $badge['id'] }}</td>
                                    <td>
                                        <strong>{{ $badge['title'] }}</strong>
                                        @if(!empty($badge['congratulations']['texts']['text_1']))
                                            <br><small class="text-muted">{{ Str::limit($badge['congratulations']['texts']['text_1'], 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $badge['type_data']['name'] ?? 'N/A' }}</span>
                                        @if($badge['type_data']['requires_total'] ?? false)
                                            <br><small class="text-muted">Dengan Target</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($badge['total'])
                                            <span class="badge bg-success">{{ number_format($badge['total']) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($badge['icons']['active']['url']))
                                            <img src="{{ $badge['icons']['active']['url'] }}" alt="Active Icon" 
                                                 class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($badge['icons']['unactive']['url']))
                                            <img src="{{ $badge['icons']['unactive']['url'] }}" alt="Inactive Icon" 
                                                 class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($badge['timestamps']['created_at'])->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.badges.show', $badge['id']) }}" 
                                               class="btn btn-outline-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.badges.edit', $badge['id']) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.badges.destroy', $badge['id']) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus badge ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Tidak ada badge ditemukan untuk aplikasi Akar
                                            <br>
                                            <a href="{{ route('admin.badges.create') }}" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus"></i> Buat Badge Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            @if(isset($pagination) && $pagination)
                                Menampilkan {{ $pagination['from'] ?? 0 }} - {{ $pagination['to'] ?? 0 }} dari {{ $pagination['total'] ?? 0 }} data
                            @else
                                Menampilkan {{ count($badges) }} data
                            @endif
                        </div>
                        <div>
                            @if(isset($pagination) && $pagination && $pagination['last_page'] > 1)
                                <nav aria-label="Navigasi halaman">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($pagination['current_page'] > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" title="Halaman sebelumnya">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="fas fa-angle-left"></i></span>
                                            </li>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                                            @if($i == $pagination['current_page'])
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Next Page Link --}}
                                        @if ($pagination['current_page'] < $pagination['last_page'])
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" title="Halaman selanjutnya">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="fas fa-angle-right"></i></span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .img-thumbnail {
        border-radius: 8px;
    }
</style>
@endpush
