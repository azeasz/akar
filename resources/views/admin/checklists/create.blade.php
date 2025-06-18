@extends('admin.layouts.app')

@section('title', 'Tambah Checklist')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .fauna-item {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f9f9f9;
        position: relative;
    }
    
    .remove-fauna {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        color: #e74a3b;
    }
    
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 3px;
    }
    
    .custom-file-input {
        cursor: pointer;
    }
    
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .preview-item {
        position: relative;
        width: 100px;
        height: 100px;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .remove-preview {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Checklist</h1>
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

<form action="{{ route('admin.checklists.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
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
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label required-field">Tipe</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Pilih Tipe</option>
                                <option value="pemeliharaan" {{ old('type') == 'pemeliharaan' ? 'selected' : '' }}>Pemeliharaan</option>
                                <option value="penangkaran" {{ old('type') == 'penangkaran' ? 'selected' : '' }}>Penangkaran</option>
                                <option value="perburuan" {{ old('type') == 'perburuan' ? 'selected' : '' }}>Perburuan</option>
                                <option value="lomba" {{ old('type') == 'lomba' ? 'selected' : '' }}>Lomba</option>
                                <option value="perdagangan" {{ old('type') == 'perdagangan' ? 'selected' : '' }}>Perdagangan</option>
                                <option value="lainnya" {{ old('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal" class="form-label required-field">Tanggal</label>
                            <input type="text" class="form-control datepicker" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" placeholder="Pilih tanggal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label required-field">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama_lokasi" class="form-label required-field">Nama Lokasi</label>
                            <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi') }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Contoh: -6.2088">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Contoh: 106.8456">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="pemilik" class="form-label">Pemilik</label>
                            <input type="text" class="form-control" id="pemilik" name="pemilik" value="{{ old('pemilik') }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_completed" name="is_completed" value="1" {{ old('is_completed') ? 'checked' : '' }}>
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
                        <!-- Fauna items will be added here -->
                        <div class="text-center py-4" id="no-fauna-message">
                            <i class="bi bi-emoji-neutral" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <h5 class="mt-2">Belum ada data fauna</h5>
                            <p class="text-muted">Klik tombol "Tambah Fauna" untuk menambahkan data fauna</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gambar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Gambar Dokumentasi</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Gambar (Multiple)</label>
                        <input type="file" class="form-control custom-file-input" id="images" name="images[]" multiple accept="image/*">
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
                        <i class="bi bi-save me-1"></i> Simpan Checklist
                    </button>
                </div>
            </div>
            
            <!-- Informasi -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi</h6>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-info-circle"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
                    <p><i class="bi bi-info-circle"></i> Status draft berarti checklist belum dipublikasikan.</p>
                    <p><i class="bi bi-info-circle"></i> Anda dapat menambahkan beberapa fauna untuk satu checklist.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template untuk fauna item -->
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
            dateFormat: 'Y-m-d',
            defaultDate: new Date()
        });
        
        let faunaIndex = 0;
        const faunaContainer = document.getElementById('fauna-container');
        const noFaunaMessage = document.getElementById('no-fauna-message');
        const faunaTemplate = document.getElementById('fauna-template').innerHTML;
        
        // Add fauna
        document.getElementById('add-fauna').addEventListener('click', function() {
            noFaunaMessage.style.display = 'none';
            
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
                    noFaunaMessage.style.display = 'block';
                }
            });
            
            // Show/hide alat buru field based on status buruan
            const statusBuruanSelect = faunaElement.querySelector('.status-buruan-select');
            const alatBuruRow = faunaElement.querySelector('.alat-buru-row');
            
            statusBuruanSelect.addEventListener('change', function() {
                if (this.value === 'mati') {
                    alatBuruRow.style.display = 'flex';
                } else {
                    alatBuruRow.style.display = 'none';
                }
            });
            
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
        
        // Image preview
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
    });
</script>
@endsection 