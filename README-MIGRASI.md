# Panduan Migrasi Data AKAR

## Pengenalan

Repository ini berisi code untuk migrasi data dari struktur database lama ke struktur database baru aplikasi AKAR. Proses migrasi ini dirancang untuk memastikan kelancaran perpindahan dari sistem lama ke sistem baru dengan meminimalkan kehilangan data.

## Perubahan Struktur Database

Migrasi ini melibatkan perubahan pada tiga tabel utama:

1. **members** → **users** 
2. **checklists_olds** → **checklists**
3. **checklist_fauna_olds** → **checklist_faunas**

## File yang Dimodifikasi

Berikut adalah file yang dimodifikasi/dibuat untuk mendukung migrasi:

1. **database/migrations/2014_10_12_000000_create_users_table.php**: Ditambahkan kolom tambahan untuk kompatibilitas dengan struktur member lama
2. **database/migrations/2024_07_01_000004_create_checklists_table.php**: Ditambahkan kolom tambahan untuk kompatibilitas dengan struktur checklist lama
3. **database/migrations/2024_07_01_000005_create_checklist_faunas_table.php**: Ditambahkan kolom tambahan untuk kompatibilitas dengan struktur fauna lama
4. **database/migrations/2024_07_10_000000_migrate_old_data.php**: Migrasi untuk memindahkan data lama ke struktur baru
5. **database/seeders/MigrateOldDataSeeder.php**: Seeder untuk memindahkan data dari struktur lama ke struktur baru
6. **database/seeders/DatabaseSeeder.php**: Ditambahkan referensi ke MigrateOldDataSeeder (dikomentari)
7. **app/Models/User.php**: Ditambahkan kolom fillable untuk kolom tambahan
8. **app/Models/Checklist.php**: Ditambahkan kolom fillable untuk kolom tambahan
9. **app/Models/ChecklistFauna.php**: Ditambahkan kolom fillable untuk kolom tambahan
10. **app/Console/Commands/MigrateOldData.php**: Command Artisan untuk menjalankan migrasi
11. **database/MIGRASI_DATA.md**: Dokumentasi detail proses migrasi
12. **README-MIGRASI.md**: Ringkasan migrasi (file ini)

## Cara Melakukan Migrasi

### Persiapan

1. Pastikan Anda memiliki backup database
2. Pastikan tabel lama tersedia (`members`, `checklists_olds`, `checklist_fauna_olds`)
3. Jalankan migrasi baru untuk membuat tabel-tabel baru:

```
php artisan migrate
```

### Migrasi Data

Ada 3 cara untuk melakukan migrasi data:

#### 1. Menggunakan Artisan Command (Direkomendasikan)

```
php artisan akar:migrate-old-data
```

Atau dengan paksa tanpa konfirmasi:

```
php artisan akar:migrate-old-data --force
```

#### 2. Menggunakan Seeder

```
php artisan db:seed --class=MigrateOldDataSeeder
```

#### 3. Melalui Migrasi Otomatis

Jika Anda menjalankan migrasi setelah migrasi awal, migrasi data akan otomatis dijalankan:

```
php artisan migrate
```

## Verifikasi Migrasi

Setelah migrasi selesai, pastikan untuk memeriksa:

1. Jumlah data dalam tabel baru seharusnya sama atau mendekati jumlah data di tabel lama
2. Sampel beberapa data untuk memastikan data dipindahkan dengan benar
3. Periksa log di `storage/logs/laravel.log` untuk melihat pesan error atau warning

## Troubleshooting

### Data Tidak Lengkap

Jika ada data yang tidak terimport dengan benar, periksa:
- Perbedaan struktur data
- Data yang tidak valid di sistem lama
- Relasi yang rusak (misalnya fauna merujuk ke checklist yang tidak ada)

### Error Migrasi

Jika terjadi error saat migrasi, pastikan:
- Tabel lama ada dan dapat diakses
- Format data pada tabel lama sesuai dengan yang diharapkan
- Ruang disk dan memori cukup untuk proses migrasi

## Perbedaan Utama Antara Struktur Lama dan Baru

### Users vs Members
- Field `level` ditambahkan untuk membedakan user dan admin
- Field `status` diubah maknanya
- Format penyimpanan gambar profil berubah

### Checklists
- Field `type` digunakan untuk menggantikan `category_id`
- Field `is_completed` dan `status` menggantikan `confirmed`
- Field `tanggal` menggantikan `record_at`

### Checklist Fauna
- Field `nama_spesies` ditambahkan untuk identifikasi langsung
- Field `jumlah` menggantikan `total`
- Field `status_buruan` sebagai enum menggantikan `kondisi` numerik
- Field `gender` sebagai string menggantikan kode numerik

## Lisensi

Migrasi ini merupakan bagian dari aplikasi AKAR. Silakan lihat file LICENSE untuk informasi lebih lanjut. 