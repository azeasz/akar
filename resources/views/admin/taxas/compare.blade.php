@extends('admin.layouts.app')

@section('title', 'Perbandingan Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Perbandingan Taxa</h1>
        <div>
            <a href="{{ route('admin.taxas.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Perbandingan Data Taxa</h6>
                    <form action="{{ route('admin.taxas.compare') }}" method="GET" class="d-flex">
                        <input type="text" class="form-control form-control-sm me-2" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama ilmiah...">
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
                    
                    @if($results->isEmpty())
                        <div class="alert alert-info">
                            Tidak ada data taxa yang ditemukan.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Ilmiah</th>
                                        <th>Status di Lokal</th>
                                        <th>Status di Amaturalist</th>
                                        <th>IUCN Status</th>
                                        <th>CITES Status</th>
                                        <th>Terakhir Diperbarui</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $taxa)
                                        <tr>
                                            <td>{{ $taxa['id'] }}</td>
                                            <td>{{ $taxa['scientific_name'] }}</td>
                                            <td>
                                                @if($taxa['in_local'])
                                                    <span class="badge bg-success">Ada</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Ada</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($taxa['in_amaturalist'])
                                                    <span class="badge bg-success">Ada</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Ada</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($taxa['iucn_status'])
                                                    <span class="badge bg-{{ $taxa['iucn_badge_color'] }}">
                                                        {{ $taxa['iucn_status'] }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($taxa['cites_status'])
                                                    <span class="badge bg-{{ $taxa['cites_badge_color'] }}">
                                                        {{ $taxa['cites_status'] }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $taxa['updated_at'] }}</td>
                                            <td>
                                                @if(!$taxa['in_local'] && $taxa['in_amaturalist'])
                                                    <form action="{{ route('admin.taxas.import', $taxa['id']) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-download"></i> Import
                                                        </button>
                                                    </form>
                                                @elseif($taxa['in_local'] && $taxa['in_amaturalist'])
                                                    <form action="{{ route('admin.taxas.sync_single', $taxa['id']) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-info">
                                                            <i class="bi bi-arrow-repeat"></i> Sinkronkan
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($taxa['in_local'])
                                                    <a href="{{ route('admin.taxas.show', $taxa['id']) }}" class="btn btn-sm btn-primary mt-1">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </a>
                                                @endif
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