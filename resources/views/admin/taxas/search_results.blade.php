@extends('admin.layouts.app')

@section('title', 'Hasil Pencarian Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Hasil Pencarian Taxa</h1>
        <div>
            <a href="{{ route('admin.taxas.search') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Pencarian
            </a>
            <a href="{{ route('admin.taxas.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-list"></i> Daftar Taxa
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Hasil Pencarian: "{{ $query }}"</h6>
                    <form action="{{ route('admin.taxas.search.results') }}" method="GET" class="d-flex">
                        <input type="text" class="form-control form-control-sm me-2" name="q" value="{{ $query }}" placeholder="Cari...">
                        <select class="form-select form-select-sm me-2" name="kingdom">
                            <option value="">Semua</option>
                            <option value="Animalia" {{ $kingdom == 'Animalia' ? 'selected' : '' }}>Animalia</option>
                            <option value="Plantae" {{ $kingdom == 'Plantae' ? 'selected' : '' }}>Plantae</option>
                            <option value="Fungi" {{ $kingdom == 'Fungi' ? 'selected' : '' }}>Fungi</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if($results->isEmpty())
                        <div class="alert alert-info">
                            Tidak ditemukan hasil untuk "{{ $query }}".
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Ilmiah</th>
                                        <th>Nama Umum</th>
                                        <th>Rank</th>
                                        <th>Kingdom</th>
                                        <th>Status IUCN</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $taxa)
                                        <tr>
                                            <td>{{ $taxa->scientific_name }}</td>
                                            <td>{{ $taxa->common_name ?? '-' }}</td>
                                            <td>{{ $taxa->rank ?? '-' }}</td>
                                            <td>{{ $taxa->kingdom ?? '-' }}</td>
                                            <td>
                                                @if($taxa->iucn_status)
                                                    <span class="badge bg-{{ $taxa->iucn_status == 'LC' ? 'success' : ($taxa->iucn_status == 'NT' ? 'warning' : 'danger') }}">
                                                        {{ $taxa->iucn_status }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.taxas.show', $taxa->taxa_id) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $results->withQueryString()->links('vendor.pagination.custom') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 