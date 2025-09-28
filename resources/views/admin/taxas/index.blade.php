@extends('admin.layouts.app')

@section('title', 'Daftar Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Taxa</h1>
        <div>
            <a href="{{ route('admin.taxas.search') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i> Cari Taxa
            </a>
            <a href="{{ route('admin.taxas.sync') }}" class="btn btn-sm btn-success">
                <i class="bi bi-arrow-repeat"></i> Sinkronisasi
            </a>
            <a href="{{ route('admin.taxas.compare') }}" class="btn btn-sm btn-info">
                <i class="bi bi-columns-gap"></i> Perbandingan
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Taxa Lokal</h6>
                    <form action="{{ route('admin.taxas.index') }}" method="GET" class="d-flex">
                        <input type="text" class="form-control form-control-sm me-2" name="search" value="{{ request('search') }}" placeholder="Cari...">
                        <select class="form-select form-select-sm me-2" name="kingdom">
                            <option value="">Semua Kingdom</option>
                            <option value="Animalia" {{ request('kingdom') == 'Animalia' ? 'selected' : '' }}>Animalia</option>
                            <option value="Plantae" {{ request('kingdom') == 'Plantae' ? 'selected' : '' }}>Plantae</option>
                            <option value="Fungi" {{ request('kingdom') == 'Fungi' ? 'selected' : '' }}>Fungi</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if($taxas->isEmpty())
                        <div class="alert alert-info">
                            Tidak ada data taxa yang tersedia.
                            <a href="{{ route('admin.taxas.search') }}" class="alert-link">Cari taxa</a> atau
                            <a href="{{ route('admin.taxas.sync') }}" class="alert-link">sinkronisasi dari database amaturalist</a>.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Ilmiah</th>
                                        <th>Nama Umum</th>
                                        <th>Rank</th>
                                        <th>Kingdom</th>
                                        <th>Status IUCN</th>
                                        <th>Status CITES</th>
                                        <th>Terakhir Diperbarui</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taxas as $taxa)
                                        <tr>
                                            <td>{{ $taxa->id }}</td>
                                            <td>{{ $taxa->scientific_name }}</td>
                                            <td>{{ $taxa->common_name ?? '-' }}</td>
                                            <td>{{ $taxa->rank ?? '-' }}</td>
                                            <td>{{ $taxa->kingdom ?? '-' }}</td>
                                            <td>
                                                @if($taxa->iucn_status)
                                                    <span class="badge bg-{{ $taxa->iucn_badge_color }}">
                                                        {{ $taxa->iucn_status }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($taxa->cites_status)
                                                    <span class="badge bg-{{ $taxa->cites_badge_color }}">
                                                        {{ $taxa->cites_status }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $taxa->updated_at ? $taxa->updated_at->format('d M Y H:i') : '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.taxas.show', $taxa->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $taxas->withQueryString()->links('vendor.pagination.custom') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 