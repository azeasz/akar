@extends('admin.layouts.app')

@section('title', 'Pencarian Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pencarian Taxa</h1>
        <div>
            <a href="{{ route('admin.taxas.compare') }}" class="btn btn-sm btn-info">
                <i class="bi bi-columns-gap"></i> Perbandingan
            </a>
            <a href="{{ route('admin.taxas.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-list"></i> Daftar Taxa
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Cari Taxa</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.taxas.search.results') }}" method="GET">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="q" class="form-label">Kata Kunci</label>
                                <input type="text" class="form-control" id="q" name="q" placeholder="Masukkan nama ilmiah atau nama umum..." required>
                            </div>
                            <div class="col-md-4">
                                <label for="kingdom" class="form-label">Kingdom</label>
                                <select class="form-select" id="kingdom" name="kingdom">
                                    <option value="">Semua</option>
                                    <option value="Animalia">Animalia</option>
                                    <option value="Plantae">Plantae</option>
                                    <option value="Fungi">Fungi</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi</h6>
                </div>
                <div class="card-body">
                    <p>Gunakan halaman ini untuk mencari taxa dari database amaturalist. Hasil pencarian akan disimpan di database lokal untuk penggunaan selanjutnya.</p>
                    <p>Anda dapat mencari berdasarkan:</p>
                    <ul>
                        <li>Nama ilmiah (scientific name), contoh: "Panthera tigris"</li>
                        <li>Nama umum (common name), contoh: "Tiger"</li>
                        <li>Genus, contoh: "Panthera"</li>
                    </ul>
                    <p>Untuk sinkronisasi data taxa dari database amaturalist ke database lokal, silakan kunjungi halaman <a href="{{ route('admin.taxas.sync') }}">Sinkronisasi Taxa</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 