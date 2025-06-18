@extends('admin.layouts.app')

@section('title', 'Manajemen User')

@section('styles')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .filter-card {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen User</h1>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah User
        </a>
        <a href="{{ route('admin.users.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success ms-2">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 filter-card">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, username...">
            </div>
            <div class="col-md-3">
                <label for="level" class="form-label">Level</label>
                <select class="form-select" id="level" name="level">
                    <option value="">Semua Level</option>
                    <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>User</option>
                    <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort_by" class="form-label">Urutkan</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Registrasi</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama</option>
                    <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="username" {{ request('sort_by') == 'username' ? 'selected' : '' }}>Username</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort_order" class="form-label">Urutan</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Naik</option>
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Turun</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            </div>
        </form>
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

<!-- User List Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">Daftar User</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="userTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">Profil</th>
                        <th width="15%">Username</th>
                        <th width="15%">Nama</th>
                        <th width="20%">Email</th>
                        <th width="15%">Level</th>
                        <th width="10%">Terdaftar</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td class="text-center">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" class="user-avatar" alt="{{ $user->name }}">
                            @else
                                <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto user-avatar">
                                    <span>{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->level == 2)
                                <span class="badge rounded-pill bg-primary">Admin</span>
                            @else
                                <span class="badge rounded-pill bg-secondary">User</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="dropdown-item">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="dropdown-item">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    @if($user->level == 1)
                                        <li>
                                            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="username" value="{{ $user->username }}">
                                                <input type="hidden" name="name" value="{{ $user->name }}">
                                                <input type="hidden" name="email" value="{{ $user->email }}">
                                                <input type="hidden" name="level" value="2">
                                                <button type="submit" class="dropdown-item text-primary">
                                                    <i class="bi bi-arrow-up-circle me-1"></i> Promosikan ke Admin
                                                </button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="username" value="{{ $user->username }}">
                                                <input type="hidden" name="name" value="{{ $user->name }}">
                                                <input type="hidden" name="email" value="{{ $user->email }}">
                                                <input type="hidden" name="level" value="1">
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="bi bi-arrow-down-circle me-1"></i> Turunkan ke User
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item text-danger" 
                                           onclick="event.preventDefault(); document.getElementById('delete-form-{{ $user->id }}').submit();">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Confirmation dialog for delete
    function confirmDelete(formId) {
        if(confirm('Apakah Anda yakin ingin menghapus user ini?')) {
            document.getElementById(formId).submit();
        }
    }
</script>
@endsection 