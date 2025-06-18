@extends('admin.layouts.app')

@section('title', 'Edit Admin')

@section('styles')
<style>
    .form-card {
        background-color: #fff;
    }
    
    .required:after {
        content: ' *';
        color: red;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Admin: {{ $admin->name }}</h1>
    <div>
        <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-info">
            <i class="bi bi-eye me-1"></i> Lihat Detail
        </a>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ms-2">
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
        <h6 class="m-0 fw-bold">Form Edit Admin</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label required">Nama</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter jika diisi.</small>
            </div>
            
            @if($admin->user)
            <div class="mb-4">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>
                            <strong>Perhatian:</strong> Admin ini terkait dengan user <a href="{{ route('admin.users.show', $admin->user->id) }}">{{ $admin->user->name }}</a>. 
                            Perubahan pada data admin akan memengaruhi data user terkait.
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <hr>
            
            <div class="d-flex justify-content-end">
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