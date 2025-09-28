@extends('admin.layouts.app')

@section('title', 'Dashboard Fauna Prioritas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-exclamation text-warning"></i>
            Dashboard Fauna Prioritas
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.priority-fauna.fauna.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Fauna
            </a>
            <form action="{{ route('admin.priority-fauna.sync-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Sinkronisasi semua fauna yang perlu update?')">
                    <i class="bi bi-arrow-clockwise"></i> Sync All
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Kategori
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_categories'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tags-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Fauna Dipantau
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_monitored_fauna'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-eye-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Status CR
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cr_fauna_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Dilindungi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['protected_fauna_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-check-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Categories Overview -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Kategori Prioritas</h6>
                    <a href="{{ route('admin.priority-fauna.categories') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear"></i> Kelola
                    </a>
                </div>
                <div class="card-body">
                    @forelse($categoriesWithCount as $category)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="badge me-2" style="background-color: {{ $category->color_code }}">
                                {{ $category->name }}
                            </div>
                            <small class="text-muted">{{ $category->type }}</small>
                        </div>
                        <span class="badge bg-secondary">{{ $category->active_priority_faunas_count }} fauna</span>
                    </div>
                    @empty
                    <p class="text-muted text-center">Belum ada kategori</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Fauna -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Fauna Terbaru</h6>
                    <a href="{{ route('admin.priority-fauna.fauna') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list"></i> Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentFauna as $fauna)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-bold">{{ $fauna->display_name }}</div>
                            <small class="text-muted">
                                <span class="badge" style="background-color: {{ $fauna->category->color_code }}">
                                    {{ $fauna->category->name }}
                                </span>
                                @if($fauna->iucn_status)
                                    <span class="badge bg-warning">{{ $fauna->iucn_status }}</span>
                                @endif
                                @if($fauna->protection_status)
                                    <span class="badge bg-success">{{ $fauna->protection_status }}</span>
                                @endif
                            </small>
                        </div>
                        <small class="text-muted">{{ $fauna->created_at->diffForHumans() }}</small>
                    </div>
                    @empty
                    <p class="text-muted text-center">Belum ada fauna prioritas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Priority Fauna Observations -->
    @if(isset($recentObservations) && $recentObservations->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-binoculars me-2"></i>
                        Laporan Fauna Prioritas Terbaru
                    </h6>
                    <span class="badge bg-primary">{{ $stats['new_observations'] ?? 0 }} Baru</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Spesies</th>
                                    <th>Kategori</th>
                                    <th>Pelapor</th>
                                    <th>Lokasi</th>
                                    <th>Jumlah</th>
                                    <th>Waktu Laporan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentObservations as $observation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($observation->priorityFauna && $observation->priorityFauna->category)
                                                <div class="badge me-2" style="background-color: {{ $observation->priorityFauna->category->color_code }}; width: 12px; height: 12px;"></div>
                                            @endif
                                            <div>
                                                <div class="font-weight-bold">{{ $observation->common_name ?: $observation->scientific_name }}</div>
                                                @if($observation->common_name)
                                                    <small class="text-muted fst-italic">{{ $observation->scientific_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($observation->priorityFauna && $observation->priorityFauna->category)
                                            <span class="badge" style="background-color: {{ $observation->priorityFauna->category->color_code }}">
                                                {{ $observation->priorityFauna->category->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="font-weight-bold">{{ $observation->user->name ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $observation->user->email ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $observation->formatted_location }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $observation->individual_count }} individu</span>
                                    </td>
                                    <td>
                                        <small>{{ $observation->observed_at->format('d M Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $observation->observed_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $observation->status_color }}">{{ $observation->status_label }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($observation->checklist)
                                                <a href="{{ route('admin.checklists.show', $observation->checklist) }}" class="btn btn-outline-primary btn-sm" title="Lihat Checklist">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            @if($observation->photos && count($observation->photos) > 0)
                                                <button class="btn btn-outline-success btn-sm" title="Lihat Foto" onclick="showPhotos({{ json_encode($observation->photos) }})">
                                                    <i class="bi bi-camera"></i>
                                                </button>
                                            @endif
                                            @if($observation->status === 'new')
                                                <button class="btn btn-outline-warning btn-sm" title="Review" onclick="reviewObservation({{ $observation->id }})">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($stats['total_observations'] > 10)
                    <div class="text-center mt-3">
                        <p class="text-muted">Menampilkan 10 laporan terbaru dari {{ $stats['total_observations'] }} total laporan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="bi bi-binoculars display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Ada Laporan Fauna Prioritas</h5>
                    <p class="text-muted">Laporan akan muncul ketika pengguna melaporkan fauna yang masuk dalam daftar prioritas</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Sync Status Alert -->
    @if($stats['needs_sync_count'] > 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Perhatian!</strong> Ada {{ $stats['needs_sync_count'] }} fauna yang perlu sinkronisasi data API.
                <form action="{{ route('admin.priority-fauna.sync-all') }}" method="POST" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Sinkronisasi semua fauna yang perlu update?')">
                        <i class="bi bi-arrow-clockwise"></i> Sync Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto refresh stats every 5 minutes
    setInterval(function() {
        window.location.reload();
    }, 300000);
});

// Function to show photos in modal
function showPhotos(photos) {
    if (!photos || photos.length === 0) {
        alert('Tidak ada foto tersedia');
        return;
    }
    
    let modalHtml = `
        <div class="modal fade" id="photoModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Foto Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
    `;
    
    photos.forEach((photo, index) => {
        modalHtml += `
            <div class="col-md-6 mb-3">
                <img src="/storage/${photo}" class="img-fluid rounded" alt="Foto ${index + 1}">
            </div>
        `;
    });
    
    modalHtml += `
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('photoModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    modal.show();
    
    // Remove modal from DOM when hidden
    document.getElementById('photoModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Function to review observation
function reviewObservation(observationId) {
    if (confirm('Tandai Laporan ini sebagai sudah direview?')) {
        // Get CSRF token with fallback
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        if (!csrfToken) {
            alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
            return;
        }
        
        fetch(`/admin/priority-fauna/observations/${observationId}/review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal mengupdate status Laporan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengupdate status');
        });
    }
}
</script>
@endsection
