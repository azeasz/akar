@extends('admin.layouts.app')

@section('title', 'Sinkronisasi Taxa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sinkronisasi Taxa</h1>
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
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Sinkronisasi Data Taxa</h6>
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
                    
                    <p>Gunakan form ini untuk menyinkronkan data taxa dari database amaturalist ke database lokal.</p>
                    
                    <form action="{{ route('admin.taxas.process_sync') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="limit" class="form-label">Jumlah Data</label>
                            <input type="number" class="form-control" id="limit" name="limit" value="100" min="1" max="1000">
                            <div class="form-text">Jumlah data yang akan disinkronkan dalam satu proses.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="offset" class="form-label">Offset</label>
                            <input type="number" class="form-control" id="offset" name="offset" value="0" min="0">
                            <div class="form-text">Mulai dari data ke-n.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Mulai Sinkronisasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi</h6>
                </div>
                <div class="card-body">
                    <p>Proses sinkronisasi akan mengambil data dari database amaturalist dan menyimpannya ke database lokal.</p>
                    <p>Hal-hal yang perlu diperhatikan:</p>
                    <ul>
                        <li>Proses ini membutuhkan waktu tergantung jumlah data yang disinkronkan.</li>
                        <li>Jika terjadi error, coba kurangi jumlah data atau gunakan offset yang berbeda.</li>
                        <li>Data yang sudah ada akan diperbarui jika terdapat perubahan.</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Untuk sinkronisasi otomatis, gunakan command <code>php artisan taxa:sync</code> yang dapat dijadwalkan menggunakan cron job.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 