@extends('admin.layouts.app')

@section('title', 'Detail Checklist')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .info-label {
        font-weight: bold;
        color: #595959;
    }
    
    .checklist-section {
        margin-bottom: 2rem;
    }
    
    .section-title {
        border-bottom: 2px solid #bf6420;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        color: #333;
        font-weight: 600;
    }
    
    .checklist-info-box {
        background-color: #f8f9fc;
        border-left: 4px solid #bf6420;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .fauna-item {
        border-left: 3px solid #1cc88a;
        padding: 1rem;
        background-color: #f8f9fc;
        margin-bottom: 1rem;
        border-radius: 4px;
    }
    
    .gallery-item {
        position: relative;
        margin-bottom: 15px;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .gallery-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
        transition: transform 0.3s;
    }
    
    .gallery-item:hover img {
        transform: scale(1.05);
    }
    
    .action-buttons {
        padding: 1rem;
        background-color: #f8f9fc;
        border-radius: 4px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.10);
    }
    
    .map-container {
        height: 300px;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 1rem;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.35rem 0.65rem;
    }
    
    .status-badge.draft {
        background-color: #f8f9fa;
        color: #212529;
        border: 1px solid #dee2e6;
    }
    
    .status-badge.published {
        background-color: #28a745;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Checklist</h1>
    <div>
        <a href="{{ route('admin.checklists.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Informasi Checklist -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Informasi Checklist</h6>
                <div>
                    <span class="badge status-badge {{ $checklist->status }}">
                        {{ ucfirst($checklist->status) }}
                    </span>
                    <span class="badge bg-{{ $completion_status == 'Selesai' ? 'success' : 'warning text-dark' }}">
                        {{ $completion_status }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">ID:</div>
                            <div>{{ $checklist->id }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Nama Lokasi:</div>
                            <div>{{ $checklist->nama_lokasi }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Tipe:</div>
                            <div>{{ $type_text }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Kategori:</div>
                            <div>{{ $category_text }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Tanggal:</div>
                            <div>{{ $checklist->tanggal->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="info-label">Dibuat oleh:</div>
                            <div>{{ $user->name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Pemilik:</div>
                            <div>{{ $pemilik_display }}</div>
                        </div>
                        @if($checklist->nama_event)
                        <div class="mb-3">
                            <div class="info-label">Nama Event:</div>
                            <div>{{ $checklist->nama_event }}</div>
                        </div>
                        @endif
                        @if($checklist->nama_arena)
                        <div class="mb-3">
                            <div class="info-label">Nama Arena:</div>
                            <div>{{ $checklist->nama_arena }}</div>
                        </div>
                        @endif
                        @if($checklist->total_hunter)
                        <div class="mb-3">
                            <div class="info-label">Total Hunter:</div>
                            <div>{{ $checklist->total_hunter }}</div>
                        </div>
                        @endif
                        @if($checklist->teknik_berburu)
                        <div class="mb-3">
                            <div class="info-label">Teknik Berburu:</div>
                            <div>{{ $checklist->teknik_berburu }}</div>
                        </div>
                        @endif
                        <div class="mb-3">
                            <div class="info-label">Tanggal Dibuat:</div>
                            <div>{{ $checklist->created_at->format('d F Y H:i') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Terakhir Diupdate:</div>
                            <div>{{ $checklist->updated_at->format('d F Y H:i') }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Catatan -->
                @if($checklist->catatan)
                <div class="checklist-info-box mt-3">
                    <div class="info-label">Catatan:</div>
                    <div>{{ $checklist->catatan }}</div>
                </div>
                @endif
                
                <!-- Peta Lokasi -->
                @if($checklist->latitude && $checklist->longitude)
                <div class="mt-4">
                    <div class="info-label mb-2">Lokasi:</div>
                    <div class="d-flex mb-2">
                        <div class="me-3">
                            <strong>Latitude:</strong> {{ $checklist->latitude }}
                        </div>
                        <div>
                            <strong>Longitude:</strong> {{ $checklist->longitude }}
                        </div>
                    </div>
                    <div class="map-container" id="map"></div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Data Fauna -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Data Fauna ({{ $faunas->count() }})</h6>
            </div>
            <div class="card-body">
                @if($faunas->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-emoji-frown" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <h5 class="mt-2">Tidak ada data fauna</h5>
                </div>
                @else
                <div class="accordion" id="faunaAccordion">
                    @foreach($faunas as $index => $fauna)
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading{{ $fauna->id }}">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $fauna->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $fauna->id }}">
                                <div class="d-flex justify-content-between w-100 me-3">
                                    <div>
                                        <strong class="me-2">{{ $fauna->nama_spesies }}</strong>
                                        <span class="badge bg-primary">{{ $fauna->jumlah }} ekor</span>
                                    </div>
                                    <span class="badge bg-{{ $fauna->status_text == 'Hidup' ? 'success' : ($fauna->status_text == 'Mati' ? 'danger' : 'secondary') }}">
                                        {{ $fauna->status_text }}
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $fauna->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $fauna->id }}" data-bs-parent="#faunaAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <strong>Gender:</strong> {{ $fauna->gender_text }}
                                        </div>
                                        <div class="mb-2">
                                            <strong>Cincin:</strong> {{ $fauna->cincin ? 'Ya' : 'Tidak' }}
                                        </div>
                                        @if($fauna->fauna_id)
                                        <div class="mb-2">
                                            <strong>ID Taxa:</strong> 
                                            <a href="{{ route('admin.checklist-faunas.find-taxa', $fauna->id) }}" class="btn btn-sm btn-info">
                                                {{ $fauna->fauna_id }} <i class="bi bi-search"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <strong>Tagging:</strong> {{ $fauna->tagging ? 'Ya' : 'Tidak' }}
                                        </div>
                                        @if($fauna->status_text == 'Mati')
                                        <div class="mb-2">
                                            <strong>Alat Buru:</strong> {{ $fauna->alat_buru ?? '-' }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($fauna->catatan)
                                <div class="mt-2">
                                    <strong>Catatan:</strong> 
                                    <div class="p-2 bg-light rounded mt-1">{{ $fauna->catatan }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        
        <!-- Gambar Checklist -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Gambar Dokumentasi ({{ $images->count() }})</h6>
            </div>
            <div class="card-body">
                @if($images->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-image" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <h5 class="mt-2">Tidak ada gambar</h5>
                </div>
                @else
                <div class="row">
                    @foreach($images as $image)
                    <div class="col-md-3 col-sm-6">
                        <div class="gallery-item">
                            <a href="{{ asset('storage/' . $image->image_path) }}" data-lightbox="checklist-images" data-title="Gambar dokumentasi checklist">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Checklist Image">
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Action Buttons -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Aksi</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.checklists.edit', $checklist) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit Checklist
                    </a>
                    
                    @if($completion_status != 'Selesai')
                    <form action="{{ route('admin.checklists.complete', $checklist) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Tandai Data Lengkap
                        </button>
                    </form>
                    @endif
                    
                    @if($checklist->status === 'draft')
                    <form action="{{ route('admin.checklists.publish', $checklist) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i> Publikasikan
                        </button>
                    </form>
                    @endif
                    
                    <form action="{{ route('admin.checklists.destroy', $checklist) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus checklist ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Hapus Checklist
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Informasi User</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('assets/admin/img/default-user.png') }}" alt="User Profile" class="rounded-circle" style="width: 80px; height: 80px;">
                    <h5 class="mt-2">{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
                
                <div class="mb-2">
                    <strong>Username:</strong> {{ $user->username }}
                </div>
                @if($user->phone_number)
                <div class="mb-2">
                    <strong>Telepon:</strong> {{ $user->phone_number }}
                </div>
                @endif
                @if($user->organisasi)
                <div class="mb-2">
                    <strong>Organisasi:</strong> {{ $user->organisasi }}
                </div>
                @endif
                
                <div class="mt-3 text-center">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-person me-1"></i> Lihat Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
@if($checklist->latitude && $checklist->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('map').setView([{{ $checklist->latitude }}, {{ $checklist->longitude }}], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add marker
        L.marker([{{ $checklist->latitude }}, {{ $checklist->longitude }}])
            .addTo(map)
            .bindPopup("<b>{{ $checklist->nama_lokasi }}</b><br>{{ $type_text }}")
            .openPopup();
    });
</script>
@endif
@endsection 