@extends('admin.layouts.app')

@section('title', 'Edit Checklist')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .fauna-item {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }
    
    .remove-fauna {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        cursor: pointer;
        color: #dc3545;
        font-size: 1.25rem;
    }
    
    .required-field::after {
        content: " *";
        color: red;
    }
    
    .existing-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .existing-image {
        position: relative;
        width: 100px;
        height: 100px;
    }
    
    .existing-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #dc3545;
    }
    
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    
    .preview-item {
        position: relative;
        width: 100px;
        height: 100px;
    }
    
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Checklist</h1>
    <div>
        <a href="{{ route('admin.checklists.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Error!</strong> Ada kesalahan dalam pengisian form.<br>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.checklists.update', $checklist) }}" method="POST" enctype="multipart/form-data" id="checklist-edit-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Informasi Dasar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi Dasar</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label required-field">User</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Pilih User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (old('user_id', $checklist->user_id) == $user->id) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label required-field">Tipe</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Pilih Tipe</option>
                                <option value="pemeliharaan" {{ (old('type', $checklist->type) == 'pemeliharaan') ? 'selected' : '' }}>Pemeliharaan</option>
                                <option value="penangkaran" {{ (old('type', $checklist->type) == 'penangkaran') ? 'selected' : '' }}>Penangkaran</option>
                                <option value="perburuan" {{ (old('type', $checklist->type) == 'perburuan') ? 'selected' : '' }}>Perburuan</option>
                                <option value="lomba" {{ (old('type', $checklist->type) == 'lomba') ? 'selected' : '' }}>Lomba</option>
                                <option value="perdagangan" {{ (old('type', $checklist->type) == 'perdagangan') ? 'selected' : '' }}>Perdagangan</option>
                                <option value="pemeliharaan & penangkaran" {{ (old('type', $checklist->type) == 'pemeliharaan & penangkaran' || old('type', $checklist->type) == 'lainnya') ? 'selected' : '' }}>Pemeliharaan & Penangkaran</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal" class="form-label required-field">Tanggal</label>
                            <input type="text" class="form-control datepicker" id="tanggal" name="tanggal" value="{{ old('tanggal', $checklist->tanggal->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label required-field">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft" {{ (old('status', $checklist->status) == 'draft') ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ (old('status', $checklist->status) == 'published') ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama_lokasi" class="form-label required-field">Nama Lokasi</label>
                            <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $checklist->nama_lokasi) }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $checklist->latitude) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $checklist->longitude) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="pemilik" class="form-label">Pemilik</label>
                            <input type="text" class="form-control" id="pemilik" name="pemilik" value="{{ old('pemilik', $checklist->pemilik) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ old('catatan', $checklist->catatan) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_completed" name="is_completed" value="1" {{ (old('is_completed', $checklist->is_completed) ? 'checked' : '') }}>
                        <label class="form-check-label" for="is_completed">
                            Tandai data lengkap
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Data Fauna -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Data Fauna</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="add-fauna">
                        <i class="bi bi-plus-circle"></i> Tambah Fauna
                    </button>
                </div>
                <div class="card-body">
                    <div id="fauna-container">
                        @forelse($checklist->faunas as $index => $fauna)
                        <div class="fauna-item existing-fauna">
                            <span class="remove-fauna"><i class="bi bi-x-circle"></i></span>
                            <input type="hidden" name="faunas[{{ $index }}][id]" value="{{ $fauna->id }}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">Nama Spesies</label>
                                    <input type="text" class="form-control" name="faunas[{{ $index }}][nama_spesies]" value="{{ old('faunas.'.$index.'.nama_spesies', $fauna->nama_spesies) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required-field">Jumlah</label>
                                    <input type="number" class="form-control" name="faunas[{{ $index }}][jumlah]" min="1" value="{{ old('faunas.'.$index.'.jumlah', $fauna->jumlah) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="faunas[{{ $index }}][gender]">
                                        <option value="">Pilih Gender</option>
                                        <option value="jantan" {{ old('faunas.'.$index.'.gender', $fauna->gender) == 'jantan' ? 'selected' : '' }}>Jantan</option>
                                        <option value="betina" {{ old('faunas.'.$index.'.gender', $fauna->gender) == 'betina' ? 'selected' : '' }}>Betina</option>
                                        <option value="tidak diketahui" {{ old('faunas.'.$index.'.gender', $fauna->gender) == 'tidak diketahui' ? 'selected' : '' }}>Tidak Diketahui</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="faunas[{{ $index }}][cincin]" value="1" {{ old('faunas.'.$index.'.cincin', $fauna->cincin) ? 'checked' : '' }}>
                                        <label class="form-check-label">Ada Cincin</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="faunas[{{ $index }}][tagging]" value="1" {{ old('faunas.'.$index.'.tagging', $fauna->tagging) ? 'checked' : '' }}>
                                        <label class="form-check-label">Ada Tagging</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 status-buruan-container" style="{{ $checklist->type === 'perburuan' ? 'display: block;' : 'display: none;' }}">
                                    <label class="form-label">Status Buruan</label>
                                    <select class="form-select status-buruan-select" name="faunas[{{ $index }}][status_buruan]">
                                        <option value="">Pilih Status</option>
                                        <option value="hidup" {{ old('faunas.'.$index.'.status_buruan', $fauna->status_buruan) == 'hidup' ? 'selected' : '' }}>Hidup</option>
                                        <option value="mati" {{ old('faunas.'.$index.'.status_buruan', $fauna->status_buruan) == 'mati' ? 'selected' : '' }}>Mati</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row alat-buru-row" style="{{ $fauna->status_buruan == 'mati' ? 'display: flex;' : 'display: none;' }}">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Alat Buru</label>
                                    <input type="text" class="form-control" name="faunas[{{ $index }}][alat_buru]" value="{{ old('faunas.'.$index.'.alat_buru', $fauna->alat_buru) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="faunas[{{ $index }}][catatan]" rows="2">{{ old('faunas.'.$index.'.catatan', $fauna->catatan) }}</textarea>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4" id="no-fauna-message">
                            <i class="bi bi-emoji-neutral" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <h5 class="mt-2">Belum ada data fauna</h5>
                            <p class="text-muted">Klik tombol "Tambah Fauna" untuk menambahkan data fauna</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Gambar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Gambar Dokumentasi</h6>
                </div>
                <div class="card-body">
                    <!-- Existing Images -->
                    @if($checklist->images->isNotEmpty())
                    <div class="mb-4">
                        <h6>Gambar Saat Ini</h6>
                        <div class="existing-images">
                            @foreach($checklist->images as $image)
                            <div class="existing-image">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Checklist Image">
                                <span class="remove-image" data-id="{{ $image->id }}">
                                    <i class="bi bi-x-circle-fill"></i>
                                </span>
                                <input type="hidden" name="removed_images[]" value="" class="removed-image-input" data-id="{{ $image->id }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Upload New Images -->
                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Gambar Baru (Opsional)</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <small class="form-text text-muted">Maksimal ukuran file: 5MB per gambar. Format: JPG, PNG, GIF</small>
                    </div>
                    <div class="image-preview" id="image-preview"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Submit Button -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Publikasi</h6>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Update Checklist
                    </button>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Aksi</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$checklist->is_completed)
                        <a href="{{ route('admin.checklists.complete', $checklist) }}" class="btn btn-success" onclick="event.preventDefault(); document.getElementById('complete-form').submit();">
                            <i class="bi bi-check-circle me-1"></i> Tandai Data Lengkap
                        </a>
                        <form id="complete-form" action="{{ route('admin.checklists.complete', $checklist) }}" method="POST" class="d-none">
                            @csrf
                            @method('PUT')
                        </form>
                        @endif
                        
                        @if($checklist->status === 'draft')
                        <a href="{{ route('admin.checklists.publish', $checklist) }}" class="btn btn-info" onclick="event.preventDefault(); document.getElementById('publish-form').submit();">
                            <i class="bi bi-send me-1"></i> Publikasikan
                        </a>
                        <form id="publish-form" action="{{ route('admin.checklists.publish', $checklist) }}" method="POST" class="d-none">
                            @csrf
                            @method('PUT')
                        </form>
                        @endif
                        
                        <a href="#" class="btn btn-danger" onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus checklist ini?')) document.getElementById('delete-form').submit();">
                            <i class="bi bi-trash me-1"></i> Hapus Checklist
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Form untuk delete checklist -->
<form id="delete-form" action="{{ route('admin.checklists.destroy', $checklist) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<!-- Template untuk fauna item baru -->
<template id="fauna-template">
    <div class="fauna-item">
        <span class="remove-fauna"><i class="bi bi-x-circle"></i></span>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label required-field">Nama Spesies</label>
                <input type="text" class="form-control" name="faunas[__index__][nama_spesies]" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label required-field">Jumlah</label>
                <input type="number" class="form-control" name="faunas[__index__][jumlah]" min="1" value="1" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Gender</label>
                <select class="form-select" name="faunas[__index__][gender]">
                    <option value="">Pilih Gender</option>
                    <option value="jantan">Jantan</option>
                    <option value="betina">Betina</option>
                    <option value="tidak diketahui">Tidak Diketahui</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="faunas[__index__][cincin]" value="1">
                    <label class="form-check-label">Ada Cincin</label>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="faunas[__index__][tagging]" value="1">
                    <label class="form-check-label">Ada Tagging</label>
                </div>
            </div>
            <div class="col-md-6 mb-3 status-buruan-container" style="display: none;">
                <label class="form-label">Status Buruan</label>
                <select class="form-select status-buruan-select" name="faunas[__index__][status_buruan]">
                    <option value="">Pilih Status</option>
                    <option value="hidup">Hidup</option>
                    <option value="mati">Mati</option>
                </select>
            </div>
        </div>
        <div class="row alat-buru-row" style="display: none;">
            <div class="col-md-12 mb-3">
                <label class="form-label">Alat Buru</label>
                <input type="text" class="form-control" name="faunas[__index__][alat_buru]">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Catatan</label>
                <textarea class="form-control" name="faunas[__index__][catatan]" rows="2"></textarea>
            </div>
        </div>
    </div>
</template>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize datepicker
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d'
        });
        
        // Set up fauna index for new items
        let faunaIndex = {{ count($checklist->faunas) }};
        const faunaContainer = document.getElementById('fauna-container');
        const noFaunaMessage = document.getElementById('no-fauna-message');
        const faunaTemplate = document.getElementById('fauna-template').innerHTML;
        
        // Hide no fauna message if we have faunas
        if (faunaContainer.querySelectorAll('.fauna-item').length > 0) {
            if (noFaunaMessage) noFaunaMessage.style.display = 'none';
        }
        
        // Add fauna
        document.getElementById('add-fauna').addEventListener('click', function() {
            if (noFaunaMessage) noFaunaMessage.style.display = 'none';
            
            let newFauna = faunaTemplate.replace(/__index__/g, faunaIndex);
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = newFauna;
            
            let faunaElement = tempDiv.firstChild;
            faunaContainer.appendChild(faunaElement);
            
            // Show/hide status buruan field based on checklist type
            updateStatusBuruanVisibility();
            
            // Remove fauna event
            faunaElement.querySelector('.remove-fauna').addEventListener('click', function() {
                faunaContainer.removeChild(faunaElement);
                if (faunaContainer.querySelectorAll('.fauna-item').length === 0) {
                    if (noFaunaMessage) noFaunaMessage.style.display = 'block';
                }
            });
            
            // Show/hide alat buru field based on status buruan
            const statusBuruanSelect = faunaElement.querySelector('.status-buruan-select');
            const alatBuruRow = faunaElement.querySelector('.alat-buru-row');
            
            if (statusBuruanSelect) {
                statusBuruanSelect.addEventListener('change', function() {
                    if (this.value === 'mati') {
                        alatBuruRow.style.display = 'flex';
                    } else {
                        alatBuruRow.style.display = 'none';
                    }
                });
            }
            
            faunaIndex++;
        });
        
        // Handle checklist type changes
        document.getElementById('type').addEventListener('change', function() {
            updateStatusBuruanVisibility();
        });
        
        function updateStatusBuruanVisibility() {
            const checklistType = document.getElementById('type').value;
            const statusBuruanContainers = document.querySelectorAll('.status-buruan-container');
            
            statusBuruanContainers.forEach(function(container) {
                if (checklistType === 'perburuan') {
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                    
                    // Reset status buruan value and hide alat buru field
                    const statusSelect = container.querySelector('.status-buruan-select');
                    if (statusSelect) statusSelect.value = '';
                    
                    const faunaItem = container.closest('.fauna-item');
                    if (faunaItem) {
                        const alatBuruRow = faunaItem.querySelector('.alat-buru-row');
                        if (alatBuruRow) alatBuruRow.style.display = 'none';
                    }
                }
            });
        }
        
        // Image preview for new uploads
        document.getElementById('images').addEventListener('change', function(event) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Check file type
                if (!file.type.startsWith('image/')) continue;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    div.appendChild(img);
                    
                    preview.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Remove existing fauna
        document.querySelectorAll('.existing-fauna .remove-fauna').forEach(function(button) {
            button.addEventListener('click', function() {
                const faunaItem = this.closest('.fauna-item');
                faunaContainer.removeChild(faunaItem);
                
                if (faunaContainer.querySelectorAll('.fauna-item').length === 0) {
                    if (noFaunaMessage) noFaunaMessage.style.display = 'block';
                }
            });
        });
        
        // Handle existing status buruan selects
        document.querySelectorAll('.existing-fauna .status-buruan-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const faunaItem = this.closest('.fauna-item');
                const alatBuruRow = faunaItem.querySelector('.alat-buru-row');
                
                if (this.value === 'mati') {
                    alatBuruRow.style.display = 'flex';
                } else {
                    alatBuruRow.style.display = 'none';
                }
            });
        });
        
        // Handle remove image
        document.querySelectorAll('.remove-image').forEach(function(button) {
            button.addEventListener('click', function() {
                const imageId = this.dataset.id;
                const input = document.querySelector('.removed-image-input[data-id="' + imageId + '"]');
                
                if (input) {
                    input.value = imageId;
                    this.closest('.existing-image').style.opacity = '0.3';
                }
            });
        });
    });
</script>
@endsection 