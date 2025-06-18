# Panduan Migrasi Data AKAR

Dokumen ini berisi panduan untuk melakukan migrasi data dari struktur database lama ke struktur database baru pada aplikasi AKAR.

## Struktur Database

### Struktur Lama
- **members**: Tabel pengguna lama
- **checklists_olds**: Tabel checklist lama
- **checklist_fauna_olds**: Tabel fauna checklist lama

### Struktur Baru
- **users**: Tabel pengguna baru
- **checklists**: Tabel checklist baru
- **checklist_faunas**: Tabel fauna checklist baru

## Langkah Migrasi

### 1. Persiapan

Pastikan Anda memiliki backup database sebelum melakukan migrasi. Kemungkinan ada data yang tidak lengkap atau tidak sesuai yang mungkin perlu ditangani secara manual.

### 2. Jalankan Migrasi untuk Membuat Tabel Baru

```
php artisan migrate
```

### 3. Ada Dua Cara Migrasi Data

#### Cara 1: Menggunakan Migrasi Otomatis

Migrasi otomatis akan dijalankan saat Anda menjalankan migrasi berikutnya:

```
php artisan migrate
```

#### Cara 2: Menggunakan Seeder (Direkomendasikan)

Seeder memberikan feedback lebih detail dan kontrol lebih baik:

```
php artisan db:seed --class=MigrateOldDataSeeder
```

Atau aktifkan di DatabaseSeeder dan jalankan:

```
php artisan db:seed
```

### 4. Verifikasi Data

Setelah migrasi selesai, periksa data untuk memastikan:

- Semua member telah dimigrasi ke users
- Semua checklist telah dimigrasi dengan benar
- Semua fauna checklist telah dimigrasi dengan benar

## Mapping Data

### Mapping User
| Lama (members)       | Baru (users)        | Keterangan                            |
|----------------------|---------------------|----------------------------------------------------|
| id                   | id                  |                                                    |
| firstname            | firstname           |                                                    |
| lastname             | lastname            |                                                    |
| name                 | name                |                                                    |
| avatar               | profile_picture dan avatar | Disimpan di kedua field untuk kompatibilitas |
| email                | email               |                                                    |
| username             | username            |                                                    |
| alias_name           | alias_name          |                                                    |
| organisasi           | organisasi          |                                                    |
| domisili             | domisili            | Field tambahan untuk kompatibilitas                |
| sosial_media         | social_media        |                                                    |
| pengamatan_satwa     | pengamatan_satwa    | Field tambahan untuk kompatibilitas                |
| reason               | reason              |                                                    |
| phone                | phone_number dan phone | Disimpan di kedua field untuk kompatibilitas   |
| email_verified_at    | email_verified_at   |                                                    |
| password             | password            |                                                    |
| status               | status              | Field tambahan untuk kompatibilitas                |
| -                    | level               | Default 1 (user biasa)                             |

### Mapping Checklist
| Lama (checklists_olds) | Baru (checklists)  | Keterangan                            |
|------------------------|--------------------|----------------------------------------------------|
| id                     | id                 |                                                    |
| member_id              | user_id            |                                                    |
| app_id                 | app_id             | Field tambahan untuk kompatibilitas                |
| category_id            | category_id, type  | Disimpan di category_id dan dikonversi ke type     |
| name                   | name               | Field tambahan untuk kompatibilitas                |
| record_at              | tanggal            |                                                    |
| latitude               | latitude           |                                                    |
| longitude              | longitude          |                                                    |
| nama_lokasi            | nama_lokasi        |                                                    |
| nama_pemilik           | pemilik            |                                                    |
| notes                  | catatan            |                                                    |
| confirmed              | is_completed, status | confirmed=1 → is_completed=true, status=published |
| nama_event             | nama_event         | Field tambahan untuk kompatibilitas                |
| nama_arena             | nama_arena         | Field tambahan untuk kompatibilitas                |
| ... dan lain-lain      | ... dan lain-lain  | Semua field lama tetap disimpan untuk kompatibilitas|

### Mapping Checklist Fauna
| Lama (checklist_fauna_olds) | Baru (checklist_faunas) | Keterangan                            |
|-----------------------------|-------------------------|----------------------------------------------------|
| id                          | id                      |                                                    |
| checklist_id                | checklist_id            | ID checklist baru (bukan ID lama)                  |
| fauna_id                    | fauna_id                | Field tambahan untuk kompatibilitas                |
| total                       | jumlah                  |                                                    |
| gender                      | gender                  | Dikonversi dari kode ke teks (1→'jantan', 2→'betina')|
| cincin                      | cincin                  | Dikonversi dari int ke boolean                     |
| notes                       | catatan                 |                                                    |
| kondisi                     | status_buruan           | Dikonversi dari kode ke enum ('hidup'/'mati')      |
| asal                        | asal                    | Field tambahan untuk kompatibilitas                |
| harga                       | harga                   | Field tambahan untuk kompatibilitas                |
| ... dan lain-lain           | ... dan lain-lain       | Semua field lama tetap disimpan untuk kompatibilitas|

## Troubleshooting

### Data tidak terimport

Beberapa kemungkinan penyebab:
- Tabel lama tidak ditemukan
- Format data tidak sesuai
- Relasi tidak ditemukan (misalnya member_id tidak ada di tabel users)

Lihat file log Laravel di `storage/logs/laravel.log` untuk detail error.

### Duplikat Data

Migrasi dirancang untuk tidak memasukkan data duplikat berdasarkan:
- Email untuk users
- ID checklist untuk checklists
- Migrasi dapat dijalankan ulang dengan aman

## Catatan Khusus

1. Field `status` dan `level` pada tabel users sedikit berbeda dari struktur lama:
   - `status` di struktur baru digunakan untuk menandai status user
   - `level` di struktur baru digunakan untuk membedakan user dan admin (1=user, 2=admin)

2. Field `is_completed` dan `status` pada tabel checklists berbeda dari field `confirmed` di struktur lama:
   - `is_completed` menandakan apakah checklist sudah lengkap
   - `status` menandakan status draft/published

3. Field `gender` di struktur baru adalah string, sementara di struktur lama berupa integer. 