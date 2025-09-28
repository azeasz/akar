@extends('admin.layouts.app')

@section('title', 'Kelola Kategori Fauna Prioritas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-tags-fill text-primary"></i>
            Kelola Kategori Fauna Prioritas
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.priority-fauna.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle"></i> Tambah Kategori
            </button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kategori</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="categoriesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Deskripsi</th>
                            <th>Warna</th>
                            <th>Jumlah Fauna</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>
                                <span class="badge me-2" style="background-color: {{ $category->color_code }}">
                                    {{ $category->name }}
                                </span>
                            </td>
                            <td>
                                @switch($category->type)
                                    @case('iucn')
                                        <span class="badge bg-danger">IUCN Red List</span>
                                        @break
                                    @case('protection_status')
                                        <span class="badge bg-success">Status Perlindungan</span>
                                        @break
                                    @case('custom')
                                        <span class="badge bg-info">Custom</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $category->description ?: '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: {{ $category->color_code }}; border-radius: 3px; border: 1px solid #ddd;"></div>
                                    <code>{{ $category->color_code }}</code>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $category->priority_faunas_count }}</span>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->type }}', '{{ $category->description }}', '{{ $category->color_code }}', {{ $category->is_active ? 'true' : 'false' }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($category->priority_faunas_count == 0)
                                    <form action="{{ route('admin.priority-fauna.categories.destroy', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada kategori</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.priority-fauna.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">Contoh: CR, EN, VU, Dilindungi, dll.</div>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipe Kategori</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="iucn">IUCN Red List</option>
                            <option value="protection_status">Status Perlindungan</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="color_code" class="form-label">Warna Badge</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color_code" name="color_code" value="#dc3545" required>
                            <input type="text" class="form-control" id="color_code_text" value="#dc3545" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Tipe Kategori</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="iucn">IUCN Red List</option>
                            <option value="protection_status">Status Perlindungan</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_color_code" class="form-label">Warna Badge</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="edit_color_code" name="color_code" required>
                            <input type="text" class="form-control" id="edit_color_code_text" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Kategori Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color picker sync
    const colorPicker = document.getElementById('color_code');
    const colorText = document.getElementById('color_code_text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });

    const editColorPicker = document.getElementById('edit_color_code');
    const editColorText = document.getElementById('edit_color_code_text');
    
    editColorPicker.addEventListener('input', function() {
        editColorText.value = this.value;
    });
});

function editCategory(id, name, type, description, colorCode, isActive) {
    const form = document.getElementById('editCategoryForm');
    form.action = `/admin/priority-fauna/categories/${id}`;
    
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_color_code').value = colorCode;
    document.getElementById('edit_color_code_text').value = colorCode;
    document.getElementById('edit_is_active').checked = isActive;
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
@endpush
