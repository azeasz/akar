# UpdateChecklistImagesPathSeeder - Update Path Checklist Images

Seeder ini digunakan untuk mengubah format path pada kolom `images` di tabel `checklist_images` dari format lama `checklistimg/` menjadi format baru `checklist_images/`.

## 🎯 Tujuan

Mengubah path file gambar dari:
- **Format Lama**: `checklistimg/20240723_034759.jpeg`
- **Format Baru**: `checklist_images/20240723_034759.jpeg`

## 🔧 Cara Kerja

### 1. Pencarian Records
- Mencari semua record di tabel `checklist_images` yang memiliki path dengan awalan `checklistimg/`
- Menggunakan query: `WHERE images LIKE 'checklistimg/%'`

### 2. Update Path
- Mengganti `checklistimg/` dengan `checklist_images/` menggunakan `str_replace()`
- Update kolom `updated_at` dengan timestamp saat ini

### 3. Logging Detail
- Log setiap record yang diproses
- Tracking status: SUCCESS, FAILED, ERROR
- Progress report setiap 100 record

## 🚀 Cara Menjalankan

```bash
php artisan db:seed --class=UpdateChecklistImagesPathSeeder
```

## 📊 Output yang Dihasilkan

### Console Output
```
Memulai proses update path checklist_images...
Total records ditemukan dengan path 'checklistimg/': 1250
Progress: 100/1250 diproses, 100 berhasil diupdate
Progress: 200/1250 diproses, 200 berhasil diupdate
...
Progress: 1250/1250 diproses, 1250 berhasil diupdate

=== RINGKASAN HASIL ===
Total records diproses: 1250
Berhasil diupdate: 1250
Error: 0
Success rate: 100%

=======================================================================
           LAPORAN AKHIR UPDATE CHECKLIST IMAGES PATH
=======================================================================

RINGKASAN HASIL:
- Total records diproses: 1250
- Berhasil (SUCCESS): 1250
- Gagal (FAILED): 0
- Error: 0
- Success rate: 100%

PERUBAHAN YANG DILAKUKAN:
- Path 'checklistimg/' → 'checklist_images/'
- Update kolom 'updated_at' dengan timestamp saat ini

CONTOH PERUBAHAN:
- checklistimg/20240723_034759.jpeg → checklist_images/20240723_034759.jpeg
- checklistimg/20240801_123456.jpg → checklist_images/20240801_123456.jpg
```

### File CSV Log
Kolom yang disimpan:
- `ID`: ID record checklist_images
- `Path_Lama`: Path sebelum diubah
- `Path_Baru`: Path setelah diubah
- `Status`: SUCCESS/FAILED/ERROR
- `Keterangan`: Detail hasil proses
- `Timestamp`: Waktu proses

### File TXT Log
Log detail proses dengan format:
```
[2024-09-23 23:35:15] [INFO] === MULAI PROSES UPDATE PATH CHECKLIST_IMAGES ===
[2024-09-23 23:35:15] [INFO] Total records ditemukan dengan path 'checklistimg/': 1250
[2024-09-23 23:35:16] [INFO] Progress: 100/1250 diproses, 100 berhasil diupdate
...
```

## 📋 Validasi Hasil

### Query untuk Cek Sebelum Seeder
```sql
-- Cek jumlah record dengan path lama
SELECT COUNT(*) as total_checklistimg 
FROM checklist_images 
WHERE images LIKE 'checklistimg/%';

-- Lihat contoh record
SELECT id, images 
FROM checklist_images 
WHERE images LIKE 'checklistimg/%' 
LIMIT 10;
```

### Query untuk Cek Setelah Seeder
```sql
-- Cek jumlah record dengan path baru
SELECT COUNT(*) as total_checklist_images 
FROM checklist_images 
WHERE images LIKE 'checklist_images/%';

-- Pastikan tidak ada lagi path lama
SELECT COUNT(*) as sisa_checklistimg 
FROM checklist_images 
WHERE images LIKE 'checklistimg/%';

-- Lihat contoh hasil update
SELECT id, images 
FROM checklist_images 
WHERE images LIKE 'checklist_images/%' 
LIMIT 10;
```

## ⚠️ Catatan Penting

### Sebelum Menjalankan
1. **Backup Database**: Selalu backup tabel `checklist_images` sebelum menjalankan
2. **Cek Path File**: Pastikan file fisik juga sudah dipindah ke folder `checklist_images/`
3. **Test Environment**: Jalankan di environment testing terlebih dahulu

### Setelah Menjalankan
1. **Validasi Data**: Cek bahwa semua path sudah berubah dengan benar
2. **Test Aplikasi**: Pastikan gambar masih bisa ditampilkan dengan path baru
3. **Update Konfigurasi**: Update konfigurasi upload path jika diperlukan

## 🔄 Rollback (Jika Diperlukan)

Jika perlu mengembalikan ke format lama, buat seeder rollback:

```php
// Update kembali ke format lama
DB::table('checklist_images')
    ->where('images', 'LIKE', 'checklist_images/%')
    ->update([
        'images' => DB::raw("REPLACE(images, 'checklist_images/', 'checklistimg/')")
    ]);
```

## 🛠️ Troubleshooting

### Jika Ada Error "File Not Found"
- Pastikan folder `storage/app/logs` ada dan writable
- Cek permission folder storage

### Jika Update Gagal
- Cek constraint database
- Pastikan kolom `images` tidak memiliki unique constraint yang konflik
- Cek apakah ada foreign key constraint

### Jika Path Tidak Berubah
- Cek apakah benar-benar ada record dengan path `checklistimg/`
- Pastikan query LIKE case-sensitive sesuai dengan data

## 📈 Monitoring

Setelah seeder berjalan, monitor:
1. **Aplikasi Web**: Pastikan gambar masih tampil normal
2. **API Response**: Cek response API yang mengembalikan path gambar
3. **Mobile App**: Test di aplikasi mobile jika ada
4. **Storage Usage**: Pastikan tidak ada duplikasi file

## 🔍 Analisis Hasil

File CSV dapat digunakan untuk:
- Audit trail perubahan path
- Identifikasi record yang gagal diupdate
- Dokumentasi untuk compliance
- Troubleshooting jika ada masalah

Seeder ini aman dijalankan berulang kali karena hanya akan memproses record yang masih menggunakan format lama (`checklistimg/`).
