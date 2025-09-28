@extends('admin.layouts.app')

@section('title', 'Detail Badge Akar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-award"></i> Detail Badge: {{ $badge->title }}
                        </h4>
                        <div class="btn-group">
                            <a href="{{ route('admin.badges.edit', $badge->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Badge Information -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informasi Badge</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="120"><strong>ID Badge:</strong></td>
                                                    <td>{{ $badge->id }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Judul:</strong></td>
                                                    <td>{{ $badge->title }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tipe Badge:</strong></td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $badge->type_name ?? 'N/A' }}</span>
                                                        @if($badge->type_description)
                                                            <br><small class="text-muted">{{ $badge->type_description }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Target Total:</strong></td>
                                                    <td>
                                                        @if($badge->total)
                                                            <span class="badge bg-success">{{ number_format($badge->total) }}</span>
                                                            @if($badge->requires_total)
                                                                <small class="text-info">(Wajib)</small>
                                                            @else
                                                                <small class="text-muted">(Opsional)</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Tidak ada target</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="120"><strong>Aplikasi:</strong></td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-tree"></i> Akar
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td>{{ \Carbon\Carbon::parse($badge->created_at)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Diupdate:</strong></td>
                                                    <td>{{ \Carbon\Carbon::parse($badge->updated_at)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Congratulations Text -->
                                    @if($badge->text_congrats_1 || $badge->text_congrats_2 || $badge->text_congrats_3)
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">Teks Ucapan Selamat</h6>
                                        </div>
                                        <div class="card-body">
                                            @if($badge->text_congrats_1)
                                                <div class="mb-3">
                                                    <strong>Ucapan 1:</strong>
                                                    <div class="border rounded p-2 bg-light">
                                                        {!! $badge->text_congrats_1 !!}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($badge->text_congrats_2)
                                                <div class="mb-3">
                                                    <strong>Ucapan 2:</strong>
                                                    <div class="border rounded p-2 bg-light">
                                                        {!! $badge->text_congrats_2 !!}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($badge->text_congrats_3)
                                                <div class="mb-3">
                                                    <strong>Ucapan 3:</strong>
                                                    <div class="border rounded p-2 bg-light">
                                                        {!! $badge->text_congrats_3 !!}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Badge Images -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Gambar Badge</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Icon Active -->
                                    <div class="mb-4">
                                        <h6>Icon Active</h6>
                                        @if($badge->icon_active)
                                            @php
                                                $iconActivePath = str_starts_with($badge->icon_active, 'storage/') 
                                                    ? $badge->icon_active 
                                                    : 'storage/badges/' . $badge->icon_active;
                                            @endphp
                                            <div class="text-center">
                                                <img src="{{ asset($iconActivePath) }}" alt="Icon Active" 
                                                     class="img-fluid border rounded" style="max-width: 200px; max-height: 200px;">
                                                <div class="mt-2">
                                                    <a href="{{ asset($iconActivePath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Lihat Full Size
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi bi-image fs-1"></i>
                                                <p>Tidak ada gambar</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Icon Inactive -->
                                    <div class="mb-4">
                                        <h6>Icon Inactive</h6>
                                        @if($badge->icon_unactive)
                                            @php
                                                $iconInactivePath = str_starts_with($badge->icon_unactive, 'storage/') 
                                                    ? $badge->icon_unactive 
                                                    : 'storage/badges/' . $badge->icon_unactive;
                                            @endphp
                                            <div class="text-center">
                                                <img src="{{ asset($iconInactivePath) }}" alt="Icon Inactive" 
                                                     class="img-fluid border rounded" style="max-width: 200px; max-height: 200px;">
                                                <div class="mt-2">
                                                    <a href="{{ asset($iconInactivePath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Lihat Full Size
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi bi-image fs-1"></i>
                                                <p>Tidak ada gambar</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Congratulations Image -->
                                    <div class="mb-4">
                                        <h6>Gambar Ucapan</h6>
                                        @if($badge->images_congrats)
                                            @php
                                                $congratsPath = str_starts_with($badge->images_congrats, 'storage/') 
                                                    ? $badge->images_congrats 
                                                    : 'storage/badges/' . $badge->images_congrats;
                                            @endphp
                                            <div class="text-center">
                                                <img src="{{ asset($congratsPath) }}" alt="Gambar Ucapan" 
                                                     class="img-fluid border rounded" style="max-width: 200px; max-height: 200px;">
                                                <div class="mt-2">
                                                    <a href="{{ asset($congratsPath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Lihat Full Size
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi bi-image fs-1"></i>
                                                <p>Tidak ada gambar</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Aksi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.badges.edit', $badge->id) }}" class="btn btn-warning">
                                            <i class="bi bi-pencil"></i> Edit Badge
                                        </a>
                                        
                                        <form action="{{ route('admin.badges.destroy', $badge->id) }}" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus badge ini? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="bi bi-trash"></i> Hapus Badge
                                            </button>
                                        </form>
                                        
                                        <hr>
                                        
                                        <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                                        </a>
                                    </div>
                                </div>
                            </div>
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
    .img-fluid {
        transition: transform 0.2s;
    }
    
    .img-fluid:hover {
        transform: scale(1.05);
        cursor: pointer;
    }
    
    .table-borderless td {
        padding: 0.5rem 0.75rem;
        border: none;
    }
    
    .badge {
        font-size: 0.875rem;
    }
</style>
@endpush
