@extends('admin.layouts.app')

@section('title', 'Tambah Badge Akar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-plus-circle"></i> Tambah Badge Akar
                        </h4>
                        <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.badges.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Informasi Badge</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="title" class="form-label">Judul Badge <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                       id="title" name="title" value="{{ old('title') }}" required maxlength="255">
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Nama badge yang akan ditampilkan kepada pengguna</div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="type" class="form-label">Tipe Badge <span class="text-danger">*</span></label>
                                                <select class="form-select @error('type') is-invalid @enderror" 
                                                        id="type" name="type" required onchange="toggleTotalField()">
                                                    <option value="">Pilih Tipe Badge</option>
                                                    @php
                                                        $typesWithTotal = $badgeTypes->where('requires_total', true);
                                                        $typesWithoutTotal = $badgeTypes->where('requires_total', false);
                                                    @endphp

                                                    @if($typesWithTotal->count() > 0)
                                                        <optgroup label="Badge dengan Target Angka">
                                                            @foreach($typesWithTotal as $badgeType)
                                                                <option value="{{ $badgeType->id }}" 
                                                                        data-requires-total="true"
                                                                        {{ old('type') == $badgeType->id ? 'selected' : '' }}>
                                                                    {{ $badgeType->name }}
                                                                    @if($badgeType->description)
                                                                        ({{ Str::limit($badgeType->description, 30) }})
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif

                                                    @if($typesWithoutTotal->count() > 0)
                                                        <optgroup label="Badge Tanpa Target Angka">
                                                            @foreach($typesWithoutTotal as $badgeType)
                                                                <option value="{{ $badgeType->id }}" 
                                                                        data-requires-total="false"
                                                                        {{ old('type') == $badgeType->id ? 'selected' : '' }}>
                                                                    {{ $badgeType->name }}
                                                                    @if($badgeType->description)
                                                                        ({{ Str::limit($badgeType->description, 30) }})
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                </select>
                                                @error('type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    Pilih tipe badge berdasarkan kriteria pencapaian
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3" id="totalField" style="display: none;">
                                                <label for="total" class="form-label">
                                                    Target Total <span class="text-danger" id="totalRequired" style="display: none;">*</span>
                                                </label>
                                                <input type="number" class="form-control @error('total') is-invalid @enderror" 
                                                       id="total" name="total" value="{{ old('total') }}" min="1" max="10000">
                                                @error('total')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted" id="totalHelpText">
                                                    Jumlah target untuk mendapatkan badge ini (opsional)
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Congratulations Text -->
                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Teks Ucapan Selamat</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="text_congrats_1" class="form-label">Teks Ucapan 1</label>
                                                        <textarea class="form-control tinymce-simple @error('text_congrats_1') is-invalid @enderror" 
                                                                  id="text_congrats_1" name="text_congrats_1" rows="2" 
                                                                  maxlength="500" placeholder="Teks ucapan selamat pertama...">{{ old('text_congrats_1') }}</textarea>
                                                        @error('text_congrats_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div class="form-text">Maksimal 500 karakter</div>
                                                    </div>

                                                    <div class="col-md-12 mb-3">
                                                        <label for="text_congrats_2" class="form-label">Teks Ucapan 2</label>
                                                        <textarea class="form-control tinymce-simple @error('text_congrats_2') is-invalid @enderror" 
                                                                  id="text_congrats_2" name="text_congrats_2" rows="2" 
                                                                  maxlength="500" placeholder="Teks ucapan selamat kedua...">{{ old('text_congrats_2') }}</textarea>
                                                        @error('text_congrats_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-12 mb-3">
                                                        <label for="text_congrats_3" class="form-label">Teks Ucapan 3</label>
                                                        <textarea class="form-control tinymce-simple @error('text_congrats_3') is-invalid @enderror" 
                                                                  id="text_congrats_3" name="text_congrats_3" rows="2" 
                                                                  maxlength="500" placeholder="Teks ucapan selamat ketiga...">{{ old('text_congrats_3') }}</textarea>
                                                        @error('text_congrats_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Uploads -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Upload Gambar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="icon_active" class="form-label">Icon Active</label>
                                            <input type="file" class="form-control @error('icon_active') is-invalid @enderror" 
                                                   id="icon_active" name="icon_active" accept="image/*" onchange="previewImage(this, 'icon_active_preview')">
                                            @error('icon_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG, GIF, SVG. Maksimal 2MB</div>
                                            <div class="mt-2">
                                                <img id="icon_active_preview" src="#" alt="Preview" 
                                                     class="img-thumbnail d-none" style="max-width: 150px;">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="icon_unactive" class="form-label">Icon Inactive</label>
                                            <input type="file" class="form-control @error('icon_unactive') is-invalid @enderror" 
                                                   id="icon_unactive" name="icon_unactive" accept="image/*" onchange="previewImage(this, 'icon_unactive_preview')">
                                            @error('icon_unactive')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG, GIF, SVG. Maksimal 2MB</div>
                                            <div class="mt-2">
                                                <img id="icon_unactive_preview" src="#" alt="Preview" 
                                                     class="img-thumbnail d-none" style="max-width: 150px;">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="images_congrats" class="form-label">Gambar Ucapan</label>
                                            <input type="file" class="form-control @error('images_congrats') is-invalid @enderror" 
                                                   id="images_congrats" name="images_congrats" accept="image/*" onchange="previewImage(this, 'images_congrats_preview')">
                                            @error('images_congrats')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG, GIF, SVG. Maksimal 2MB</div>
                                            <div class="mt-2">
                                                <img id="images_congrats_preview" src="#" alt="Preview" 
                                                     class="img-thumbnail d-none" style="max-width: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Application Info -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Informasi Aplikasi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            Badge ini akan aktif untuk aplikasi <strong>Akar</strong> saja.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Simpan Badge
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTotalField() {
    const typeSelect = document.getElementById('type');
    const totalField = document.getElementById('totalField');
    const totalInput = document.getElementById('total');
    const totalRequired = document.getElementById('totalRequired');
    const totalHelpText = document.getElementById('totalHelpText');

    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const requiresTotal = selectedOption ? selectedOption.getAttribute('data-requires-total') === 'true' : false;

    if (requiresTotal) {
        totalField.style.display = 'block';
        totalInput.required = true;
        totalRequired.style.display = 'inline';
        totalHelpText.textContent = 'Jumlah target untuk mendapatkan badge ini (wajib untuk tipe ini)';
        totalHelpText.className = 'form-text text-info';
    } else if (typeSelect.value && !requiresTotal) {
        totalField.style.display = 'none';
        totalInput.required = false;
        totalInput.value = '';
    } else {
        totalField.style.display = 'block';
        totalInput.required = false;
        totalRequired.style.display = 'none';
        totalHelpText.textContent = 'Jumlah target untuk mendapatkan badge ini (opsional)';
        totalHelpText.className = 'form-text text-muted';
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.classList.add('d-none');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTotalField();
});
</script>
@endpush
