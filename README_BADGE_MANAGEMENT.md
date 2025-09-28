# Badge Management System - Aplikasi Akar

## Overview
Sistem manajemen badge untuk aplikasi Akar yang terintegrasi dengan database aplikasi induk FOBi. Admin Akar dapat mengelola badge yang khusus aktif untuk aplikasi Akar saja.

## Features
- ✅ **CRUD Badge**: Create, Read, Update, Delete badge
- ✅ **Filter & Search**: Pencarian dan filter berdasarkan tipe, target, dll
- ✅ **File Upload**: Upload icon active, inactive, dan gambar ucapan
- ✅ **Rich Text Editor**: TinyMCE untuk teks ucapan selamat
- ✅ **Pagination**: Navigasi halaman yang enhanced
- ✅ **Validation**: Validasi form yang komprehensif
- ✅ **Integration**: Terintegrasi dengan database aplikasi induk

## File Structure
```
akar/
├── app/Http/Controllers/Admin/
│   └── BadgeController.php          # Controller untuk CRUD badge
├── resources/views/admin/badges/
│   ├── index.blade.php              # Halaman daftar badge
│   ├── create.blade.php             # Form tambah badge
│   ├── edit.blade.php               # Form edit badge
│   └── show.blade.php               # Detail badge
└── routes/
    └── admin.php                    # Routing badge management
```

## Database Integration
Badge data disimpan di database aplikasi induk dengan field:
- `akar = true` - Badge aktif untuk aplikasi Akar
- `fobi = false` - Tidak aktif untuk FOBi
- `burungnesia = false` - Tidak aktif untuk Burungnesia
- `kupunesia = false` - Tidak aktif untuk Kupunesia

## Routes
```php
// Badge Management Routes (admin.badges.*)
GET    /admin/badges           # Daftar badge
GET    /admin/badges/create    # Form tambah badge
POST   /admin/badges           # Simpan badge baru
GET    /admin/badges/{id}      # Detail badge
GET    /admin/badges/{id}/edit # Form edit badge
PUT    /admin/badges/{id}      # Update badge
DELETE /admin/badges/{id}      # Hapus badge
```

## Controller Methods

### BadgeController
- `index()` - Menampilkan daftar badge dengan filter dan pagination
- `create()` - Menampilkan form tambah badge
- `store()` - Menyimpan badge baru ke database
- `show()` - Menampilkan detail badge
- `edit()` - Menampilkan form edit badge
- `update()` - Mengupdate badge di database
- `destroy()` - Menghapus badge (soft delete)

## Database Connection
Controller menggunakan `DB::connection('mysql')` untuk mengakses database aplikasi induk.

## File Upload
File gambar disimpan di `public/uploads/badges/` dengan naming convention:
- Icon Active: `badge_icon_active_{timestamp}_{uniqid}.{ext}`
- Icon Inactive: `badge_icon_unactive_{timestamp}_{uniqid}.{ext}`
- Gambar Ucapan: `badge_congrats_{timestamp}_{uniqid}.{ext}`

## Validation Rules
```php
'title' => 'required|string|max:255',
'type' => 'required|integer|in:{badge_type_ids}',
'total' => 'nullable|integer|min:1|max:10000',
'icon_active' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
'icon_unactive' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
'images_congrats' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
'text_congrats_1' => 'nullable|string|max:500',
'text_congrats_2' => 'nullable|string|max:500',
'text_congrats_3' => 'nullable|string|max:500',
```

## Features Detail

### 1. Enhanced Pagination
- Smart navigation dengan first/last page buttons
- Range pagination (current ± 2 pages)
- Info display "Menampilkan X - Y dari Z data"
- Font Awesome icons untuk navigasi

### 2. Advanced Filtering
- **Search**: Pencarian berdasarkan judul badge
- **Type Filter**: Filter berdasarkan tipe badge
- **Target Filter**: Filter badge dengan/tanpa target
- **Sorting**: Urutkan berdasarkan tanggal, judul, atau tipe

### 3. Rich Text Editor
- TinyMCE integration untuk teks ucapan selamat
- Class `tinymce-simple` untuk editor minimal
- Auto-save functionality
- Bootstrap compatible

### 4. Dynamic Form Behavior
- Toggle field total berdasarkan tipe badge
- Preview gambar saat upload
- Validation real-time
- Responsive design

## Testing Guide

### 1. Setup Testing
```bash
# Pastikan aplikasi Akar dapat mengakses database induk
# Periksa koneksi database di config/database.php
```

### 2. Test Cases

#### A. Index Page
1. Akses `/admin/badges`
2. Verifikasi hanya badge dengan `akar = true` yang ditampilkan
3. Test filter dan search functionality
4. Test pagination navigation

#### B. Create Badge
1. Akses `/admin/badges/create`
2. Test form validation:
   - Title required
   - Type required
   - File upload validation
3. Test dynamic total field berdasarkan badge type
4. Test file upload dan preview
5. Submit form dan verifikasi data tersimpan

#### C. Edit Badge
1. Akses `/admin/badges/{id}/edit`
2. Verifikasi data ter-populate dengan benar
3. Test update functionality
4. Test file replacement

#### D. Show Badge
1. Akses `/admin/badges/{id}`
2. Verifikasi semua informasi ditampilkan
3. Test image display dan full-size view

#### E. Delete Badge
1. Test soft delete functionality
2. Verifikasi badge tidak muncul di index setelah dihapus

### 3. Database Verification
```sql
-- Verifikasi badge Akar
SELECT * FROM badges WHERE akar = 1 AND deleted_at IS NULL;

-- Verifikasi aplikasi flags
SELECT id, title, fobi, burungnesia, kupunesia, akar FROM badges;
```

## Security Considerations
- ✅ CSRF Protection pada semua form
- ✅ File upload validation
- ✅ SQL injection protection dengan Query Builder
- ✅ Authorization middleware
- ✅ Input sanitization

## Performance Optimization
- ✅ Pagination untuk large datasets
- ✅ Efficient database queries dengan joins
- ✅ Image optimization recommendations
- ✅ Caching considerations

## Error Handling
- ✅ Try-catch blocks pada semua methods
- ✅ Logging untuk debugging
- ✅ User-friendly error messages
- ✅ Graceful fallbacks

## Maintenance
- Badge types dikelola di aplikasi induk
- File cleanup untuk gambar yang dihapus
- Regular database maintenance
- Log monitoring

## Support
Untuk pertanyaan atau issues, hubungi tim development atau buat issue di repository.
