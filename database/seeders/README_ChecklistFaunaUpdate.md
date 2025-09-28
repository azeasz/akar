# ChecklistFaunaUpdateSeeder

Seeder ini dibuat untuk mengupdate data pada tabel `checklist_faunas` dalam 2 tahap:

## Tahap 1: Update dari Tabel Faunas
- **Tujuan**: Mengisi kolom `nama_spesies` dan `nama_latin` pada tabel `checklist_faunas`
- **Proses**: 
  - Cocokkan `faunas.id` dengan `checklist_faunas.fauna_id`
  - Jika cocok, update:
    - `checklist_faunas.nama_spesies` = `faunas.nameId`
    - `checklist_faunas.nama_latin` = `faunas.nameLat`

## Tahap 2: Update dari Tabel Taxas
- **Tujuan**: Mengupdate `fauna_id` berdasarkan matching dengan tabel `taxas`
- **Proses**:
  - Cocokkan `checklist_faunas.nama_latin` dengan `taxas.species`
  - Jika ada match, update `checklist_faunas.fauna_id` = `taxas.id`

## Cara Menjalankan

### 1. Melalui Artisan Command
```bash
php artisan db:seed --class=ChecklistFaunaUpdateSeeder
```

### 2. Menambahkan ke DatabaseSeeder (Opsional)
Tambahkan baris berikut di file `database/seeders/DatabaseSeeder.php`:
```php
public function run()
{
    // ... seeder lainnya
    $this->call(ChecklistFaunaUpdateSeeder::class);
}
```

Kemudian jalankan:
```bash
php artisan db:seed
```

## Fitur Seeder

### Logging
- Seeder akan mencatat progress dan hasil di log Laravel
- Error juga akan dicatat untuk debugging

### Progress Tracking
- Menampilkan jumlah record yang diproses
- Progress info setiap 100 record pada tahap 2
- Summary lengkap di akhir proses

### Error Handling
- Try-catch untuk menangani error
- Proses akan tetap berlanjut meskipun ada error pada salah satu tahap

## Output yang Diharapkan

```
Memulai proses update checklist fauna...
Tahap 1: Mengupdate nama_spesies dan nama_latin dari tabel faunas...
Tahap 1 selesai: X record berhasil diupdate
Tahap 2: Mengupdate fauna_id berdasarkan matching dengan tabel taxas...
Progress: 100 matches ditemukan, 100 record diupdate
Progress: 200 matches ditemukan, 200 record diupdate
...
Tahap 2 selesai:
- Total checklist fauna diproses: Y
- Total matches ditemukan: Z
- Total record diupdate: W
Proses update checklist fauna selesai!
```

## Catatan Penting

1. **Backup Database**: Selalu backup database sebelum menjalankan seeder ini
2. **Testing**: Test di environment development terlebih dahulu
3. **Performance**: Untuk dataset besar, pertimbangkan untuk menjalankan di background
4. **Validasi**: Periksa hasil setelah seeder selesai dijalankan

## Troubleshooting

### Jika Tahap 1 Gagal
- Pastikan tabel `faunas` dan `checklist_faunas` ada
- Periksa apakah ada data di `checklist_faunas` yang memiliki `fauna_id`

### Jika Tahap 2 Gagal
- Pastikan tabel `taxas` ada dan memiliki kolom `species`
- Periksa format data di `checklist_faunas.nama_latin` dan `taxas.species`
