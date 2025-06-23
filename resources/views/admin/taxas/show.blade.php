@extends('admin.layouts.app')

@section('title', 'Detail Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Taxa</h1>
        <div>
            <a href="{{ route('admin.taxas.compare') }}" class="btn btn-sm btn-info">
                <i class="bi bi-columns-gap"></i> Perbandingan
            </a>
            <a href="{{ route('admin.taxas.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi Taxa</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">ID Taxa</th>
                                <td>{{ $taxa->id }}</td>
                            </tr>
                            <tr>
                                <th>Nama Ilmiah</th>
                                <td>{{ $taxa->scientific_name }}</td>
                            </tr>
                            <tr>
                                <th>Nama Umum</th>
                                <td>{{ $taxa->common_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Rank</th>
                                <td>{{ $taxa->rank ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kingdom</th>
                                <td>{{ $taxa->kingdom ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Phylum</th>
                                <td>{{ $taxa->phylum ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Class</th>
                                <td>{{ $taxa->class ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Order</th>
                                <td>{{ $taxa->order ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Family</th>
                                <td>{{ $taxa->family ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Genus</th>
                                <td>{{ $taxa->genus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Species</th>
                                <td>{{ $taxa->species ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status IUCN</th>
                                <td>
                                    @if($taxa->iucn_status)
                                        <span class="badge bg-{{ $taxa->iucn_badge_color }}">
                                            {{ $taxa->iucn_status }}
                                        </span>
                                        <small class="d-block mt-1">{{ $taxa->iucn_status_text }}</small>
                                        @if($taxa->iucn_criteria)
                                            <small class="d-block">({{ $taxa->iucn_criteria }})</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status CITES</th>
                                <td>
                                    @if($taxa->cites_status)
                                        <span class="badge bg-{{ $taxa->cites_badge_color }}">
                                            {{ $taxa->cites_status }}
                                        </span>
                                        <small class="d-block mt-1">{{ $taxa->cites_status_text }}</small>
                                        @if($taxa->cites_listing_date)
                                            <small class="d-block">(Terdaftar: {{ $taxa->cites_listing_date->format('d M Y') }})</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Terakhir Diperbarui</th>
                                <td>{{ $taxa->updated_at ? $taxa->updated_at->format('d M Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Disinkronkan</th>
                                <td>{{ $taxa->last_synced_at ? $taxa->last_synced_at->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    @if($taxa->description)
                        <div class="mt-4">
                            <h6 class="font-weight-bold">Deskripsi</h6>
                            <p>{{ $taxa->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Gambar</h6>
                </div>
                <div class="card-body text-center">
                    @if($taxa->image_url)
                        <img src="{{ $taxa->image_url }}" alt="{{ $taxa->scientific_name }}" class="img-fluid rounded mb-3" style="max-height: 300px;">
                    @else
                        <div class="alert alert-info">
                            Tidak ada gambar tersedia.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Aksi</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.taxas.update_iucn', $taxa->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-arrow-repeat"></i> Update Status IUCN
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.taxas.update_cites', $taxa->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-arrow-repeat"></i> Update Status CITES
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 