@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('styles')
<style>
    .form-card {
        background-color: #fff;
    }
    
    .required:after {
        content: ' *';
        color: red;
    }
    
    .preview-image {
        max-width: 150px;
        max-height: 150px;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit User: {{ $user->name }}</h1>
    <div>
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info">
            <i class="bi bi-eye me-1"></i> Lihat Detail
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Form Card -->
<div class="card shadow mb-4 form-card">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">Form Edit User</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <!-- Username -->
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label required">Username</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Name -->
                <div class="col-md-12 mb-3">
                    <label for="name" class="form-label required">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Firstname -->
                <div class="col-md-6 mb-3">
                    <label for="firstname" class="form-label">Nama Depan</label>
                    <input type="text" class="form-control @error('firstname') is-invalid @enderror" id="firstname" name="firstname" value="{{ old('firstname', $user->firstname) }}">
                    @error('firstname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Lastname -->
                <div class="col-md-6 mb-3">
                    <label for="lastname" class="form-label">Nama Belakang</label>
                    <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname" name="lastname" value="{{ old('lastname', $user->lastname) }}">
                    @error('lastname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Password -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter jika diisi.</small>
                </div>
                
                <!-- Level -->
                <div class="col-md-6 mb-3">
                    <label for="level" class="form-label required">Level</label>
                    <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                        <option value="1" {{ old('level', $user->level) == '1' ? 'selected' : '' }}>User</option>
                        <option value="2" {{ old('level', $user->level) == '2' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Organization -->
                <div class="col-md-6 mb-3">
                    <label for="organisasi" class="form-label">Organisasi</label>
                    <input type="text" class="form-control @error('organisasi') is-invalid @enderror" id="organisasi" name="organisasi" value="{{ old('organisasi', $user->organisasi) }}">
                    @error('organisasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Alias Name -->
                <div class="col-md-6 mb-3">
                    <label for="alias_name" class="form-label">Nama Alias</label>
                    <input type="text" class="form-control @error('alias_name') is-invalid @enderror" id="alias_name" name="alias_name" value="{{ old('alias_name', $user->alias_name) }}">
                    @error('alias_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Phone Number -->
                <div class="col-md-6 mb-3">
                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Social Media -->
                <div class="col-md-6 mb-3">
                    <label for="social_media" class="form-label">Social Media</label>
                    <input type="text" class="form-control @error('social_media') is-invalid @enderror" id="social_media" name="social_media" value="{{ old('social_media', $user->social_media) }}">
                    @error('social_media')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <!-- Profile Picture -->
                <div class="col-md-6 mb-3">
                    <label for="profile_picture" class="form-label">Foto Profil</label>
                    <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" id="profile_picture" name="profile_picture">
                    @error('profile_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    
                    @if($user->profile_picture)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="preview-image">
                        <p class="text-muted mt-1">Foto profil saat ini</p>
                    </div>
                    @endif
                </div>
                
                <!-- Email Verified -->
                <div class="col-md-6 mb-3">
                    <label for="email_verified_at" class="form-label">Status Email</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="email_verified_at" name="email_verified_at" value="1" {{ old('email_verified_at', $user->email_verified_at) ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_verified_at">
                            Email sudah terverifikasi
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <!-- Reason -->
                <label for="reason" class="form-label">Alasan Pendaftaran</label>
                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3">{{ old('reason', $user->reason) }}</textarea>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="reset" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Preview uploaded image
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Remove old preview if exists
                const oldPreview = document.querySelector('.preview-image');
                if (oldPreview) {
                    oldPreview.remove();
                }
                
                // Create new preview image
                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'preview-image';
                img.alt = 'Preview';
                
                // Add preview after input
                const parent = document.getElementById('profile_picture').parentNode;
                parent.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection 