@extends('admin.layouts.app')

@section('title', 'Detail Fauna Prioritas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-eye text-info"></i>
            Detail Fauna Prioritas
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.priority-fauna.fauna') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            @if($fauna->needsApiSync())
            <form action="{{ route('admin.priority-fauna.fauna.sync', $fauna) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Sinkronisasi data fauna ini?')">
                    <i class="bi bi-arrow-clockwise"></i> Sync Data
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle"></i> Informasi Dasar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Nama Taksa:</td>
                                    <td>{{ $fauna->taxa_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nama Ilmiah:</td>
                                    <td>
                                        @if($fauna->scientific_name)
                                            <em>{{ $fauna->scientific_name }}</em>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nama Umum:</td>
                                    <td>{{ $fauna->common_name ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Taxa ID:</td>
                                    <td>
                                        @if($fauna->taxa_id)
                                            <code>{{ $fauna->taxa_id }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Kategori:</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $fauna->category->color_code }}">
                                            {{ $fauna->category->name }}
                                        </span>
                                        <br><small class="text-muted">{{ $fauna->category->description }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status IUCN:</td>
                                    <td>
                                        @if($fauna->iucn_status)
                                            <span class="badge bg-warning">{{ $fauna->iucn_status }}</span>
                                        @else
                                            <span class="text-muted">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status Perlindungan:</td>
                                    <td>
                                        @if($fauna->protection_status)
                                            @if($fauna->protection_status === 'Dilindungi')
                                                <span class="badge bg-success">{{ $fauna->protection_status }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $fauna->protection_status }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status Monitoring:</td>
                                    <td>
                                        @if($fauna->is_monitored)
                                            <span class="badge bg-success">
                                                <i class="bi bi-eye-fill"></i> Dipantau
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-eye-slash"></i> Tidak Dipantau
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($fauna->notes)
                    <div class="mt-3">
                        <h6 class="fw-bold">Catatan:</h6>
                        <div class="alert alert-light">
                            {{ $fauna->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- API Data -->
            @if($fauna->taxa_data)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-cloud-download"></i> Data API Amaturalist
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if(isset($fauna->taxa_data['rank']))
                            <p><strong>Rank:</strong> {{ $fauna->taxa_data['rank'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['kingdom']))
                            <p><strong>Kingdom:</strong> {{ $fauna->taxa_data['kingdom'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['phylum']))
                            <p><strong>Phylum:</strong> {{ $fauna->taxa_data['phylum'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['class']))
                            <p><strong>Class:</strong> {{ $fauna->taxa_data['class'] }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(isset($fauna->taxa_data['order']))
                            <p><strong>Order:</strong> {{ $fauna->taxa_data['order'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['family']))
                            <p><strong>Family:</strong> {{ $fauna->taxa_data['family'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['genus']))
                            <p><strong>Genus:</strong> {{ $fauna->taxa_data['genus'] }}</p>
                            @endif
                            
                            @if(isset($fauna->taxa_data['species']))
                            <p><strong>Species:</strong> {{ $fauna->taxa_data['species'] }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Raw JSON Data (Collapsible) -->
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawApiData">
                            <i class="bi bi-code"></i> Lihat Data JSON Lengkap
                        </button>
                        <div class="collapse mt-2" id="rawApiData">
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($fauna->taxa_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Related Checklist -->
            @if($fauna->checklist)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-list-check"></i> Checklist Terkait
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $fauna->checklist->title ?? 'Checklist #' . $fauna->checklist->id }}</h6>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar"></i> {{ $fauna->checklist->created_at->format('d M Y H:i') }}
                                <br>
                                <i class="bi bi-person"></i> {{ $fauna->checklist->user->name ?? 'Unknown User' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.checklists.show', $fauna->checklist) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> Lihat Checklist
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-gear"></i> Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Edit Form -->
                    <form action="{{ route('admin.priority-fauna.fauna.update', $fauna) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                @foreach(\App\Models\PriorityFaunaCategory::active()->get() as $category)
                                <option value="{{ $category->id }}" {{ $fauna->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} - {{ $category->description }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ $fauna->notes }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_monitored" 
                                       name="is_monitored" value="1" {{ $fauna->is_monitored ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_monitored">
                                    Aktifkan monitoring
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update
                            </button>
                        </div>
                    </form>

                    <hr>

                    <!-- Delete Form -->
                    <form action="{{ route('admin.priority-fauna.fauna.destroy', $fauna) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Yakin ingin menghapus fauna ini dari daftar prioritas?')">
                                <i class="bi bi-trash"></i> Hapus dari Prioritas
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sync Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-arrow-clockwise"></i> Informasi Sinkronisasi
                    </h6>
                </div>
                <div class="card-body">
                    @if($fauna->last_api_sync)
                        <p class="mb-2">
                            <strong>Sync Terakhir:</strong><br>
                            <small class="text-muted">{{ $fauna->last_api_sync->format('d M Y H:i:s') }}</small>
                            <br>
                            <small class="text-muted">{{ $fauna->last_api_sync->diffForHumans() }}</small>
                        </p>
                        
                        @if($fauna->needsApiSync())
                            <div class="alert alert-warning alert-sm">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Perlu Sync!</strong><br>
                                Data sudah lebih dari 7 hari.
                            </div>
                        @else
                            <div class="alert alert-success alert-sm">
                                <i class="bi bi-check-circle"></i>
                                Data masih fresh.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger alert-sm">
                            <i class="bi bi-x-circle"></i>
                            <strong>Belum pernah sync!</strong><br>
                            Data mungkin tidak lengkap.
                        </div>
                    @endif

                    @if($fauna->is_monitored)
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            Fauna ini akan disinkronisasi otomatis secara berkala.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="bi bi-clock-history"></i> Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Ditambahkan</h6>
                                <p class="timeline-text">{{ $fauna->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($fauna->updated_at != $fauna->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Terakhir Diupdate</h6>
                                <p class="timeline-text">{{ $fauna->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($fauna->last_api_sync)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Sync API</h6>
                                <p class="timeline-text">{{ $fauna->last_api_sync->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endpush
