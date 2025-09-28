# 🦎 Sistem Manajemen Fauna Prioritas AKAR

Sistem komprehensif untuk pemantauan dan pengelolaan fauna prioritas berdasarkan status IUCN Red List dan status perlindungan Indonesia.

## 📋 Fitur Utama

### 🏷️ Manajemen Kategori Dinamis
- **Kategori IUCN**: CR (Critically Endangered), EN (Endangered), VU (Vulnerable)
- **Status Perlindungan**: Dilindungi, Tidak Dilindungi
- **Kategori Custom**: Fleksibel untuk kebutuhan khusus
- **Color Coding**: Setiap kategori memiliki warna badge yang dapat dikustomisasi

### 🔍 Integrasi API Amaturalist
- **Real-time Search**: Pencarian taksa langsung dari API amaturalist.com
- **Auto-complete**: Suggestion search dengan minimal 2 karakter
- **Fallback System**: Otomatis menggunakan database lokal jika API tidak tersedia
- **Data Sync**: Sinkronisasi berkala data taksa dengan API

### 📊 Dashboard Monitoring
- **Statistik Real-time**: Total kategori, fauna dipantau, status CR, dan dilindungi
- **Visual Cards**: Overview kategori dengan jumlah fauna per kategori
- **Recent Activity**: Daftar fauna yang baru ditambahkan
- **Sync Status**: Monitoring fauna yang perlu sinkronisasi data

## 🗄️ Struktur Database

### Tabel `priority_fauna_categories`
```sql
- id (Primary Key)
- name (Nama kategori: CR, EN, VU, Dilindungi, dll)
- type (Tipe: iucn, protection_status, custom)
- description (Deskripsi kategori)
- color_code (Kode warna hex untuk badge)
- is_active (Status aktif/nonaktif)
- timestamps
```

### Tabel `priority_faunas`
```sql
- id (Primary Key)
- checklist_id (Foreign Key ke tabel checklists)
- fauna_id (Foreign Key ke tabel checklist_faunas)
- taxa_id (ID taksa dari API amaturalist)
- taxa_name (Nama taksa)
- scientific_name (Nama ilmiah)
- common_name (Nama umum)
- taxa_data (JSON data lengkap dari API)
- iucn_status (Status IUCN: CR, EN, VU, dll)
- protection_status (Status perlindungi: Dilindungi/Tidak Dilindungi)
- category_id (Foreign Key ke priority_fauna_categories)
- notes (Catatan tambahan)
- is_monitored (Status monitoring aktif/nonaktif)
- last_api_sync (Timestamp sync terakhir dengan API)
- timestamps
```

## 🚀 Instalasi & Setup

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Seed Data Default
```bash
php artisan db:seed --class=PriorityFaunaCategorySeeder
```

### 3. Konfigurasi Database Kedua (Opsional)
Tambahkan konfigurasi database fallback di `.env`:
```env
DB_SECOND_CONNECTION=mysql
DB_SECOND_HOST=127.0.0.1
DB_SECOND_PORT=3306
DB_SECOND_DATABASE=backup_db
DB_SECOND_USERNAME=root
DB_SECOND_PASSWORD=
```

## 🎯 Cara Penggunaan

### Akses Admin Panel
1. Login sebagai admin
2. Navigasi ke **Fauna Prioritas** di sidebar
3. Pilih menu yang diinginkan:
   - **Dashboard**: Overview dan statistik
   - **Kelola Kategori**: CRUD kategori prioritas
   - **Kelola Fauna**: CRUD fauna prioritas

### Menambah Kategori Baru
1. Masuk ke **Kelola Kategori**
2. Klik **Tambah Kategori**
3. Isi form:
   - **Nama**: Singkatan kategori (contoh: CR, EN, VU)
   - **Tipe**: Pilih IUCN Red List, Status Perlindungan, atau Custom
   - **Deskripsi**: Penjelasan kategori
   - **Warna Badge**: Pilih warna untuk visual badge
4. Klik **Simpan**

### Menambah Fauna Prioritas
1. Masuk ke **Kelola Fauna**
2. Klik **Tambah Fauna**
3. Cari taksa menggunakan search box (minimal 2 karakter)
4. Pilih taksa dari hasil pencarian
5. Pilih kategori prioritas
6. Tambahkan catatan jika diperlukan
7. Aktifkan monitoring untuk auto-sync
8. Klik **Simpan Fauna**

### Sinkronisasi Data
- **Manual**: Klik tombol sync pada fauna individual
- **Bulk**: Gunakan tombol "Sync All" untuk semua fauna yang perlu update
- **Otomatis**: Fauna dengan monitoring aktif akan disync berkala

## 🔧 API Endpoints

### Pencarian Taksa (Internal)
```
GET /admin/priority-fauna/api/taxa-suggestions?q={query}&limit={limit}
```

### Response Format
```json
{
  "success": true,
  "data": [
    {
      "id": 12345,
      "name": "Varanus komodoensis",
      "common_name": "Komodo Dragon",
      "display_name": "Komodo Dragon (Varanus komodoensis)",
      "iucn_status": "EN",
      "protection_status": "Dilindungi"
    }
  ],
  "source": "api"
}
```

## 📁 File Structure

```
app/
├── Http/Controllers/Admin/
│   └── AdminPriorityFaunaController.php
├── Models/
│   ├── PriorityFauna.php
│   └── PriorityFaunaCategory.php
└── Services/
    └── AmaturalistApiService.php

database/
├── migrations/
│   ├── 2024_01_01_000001_create_priority_fauna_categories_table.php
│   └── 2024_01_01_000002_create_priority_faunas_table.php
└── seeders/
    └── PriorityFaunaCategorySeeder.php

resources/views/admin/priority-fauna/
├── index.blade.php (Dashboard)
├── categories.blade.php (Kelola Kategori)
├── fauna.blade.php (Daftar Fauna)
├── create-fauna.blade.php (Tambah Fauna)
└── show-fauna.blade.php (Detail Fauna)

routes/
└── admin.php (Route definitions)
```

## 🎨 UI Components

### Dashboard Cards
- **Total Kategori**: Jumlah kategori aktif
- **Fauna Dipantau**: Jumlah fauna dengan monitoring aktif
- **Status CR**: Jumlah fauna dengan status Critically Endangered
- **Dilindungi**: Jumlah fauna dengan status perlindungan

### Color Coding
- **CR (Critically Endangered)**: `#dc3545` (Merah)
- **EN (Endangered)**: `#fd7e14` (Oranye)
- **VU (Vulnerable)**: `#ffc107` (Kuning)
- **Dilindungi**: `#198754` (Hijau)
- **Tidak Dilindungi**: `#6c757d` (Abu-abu)

## 🔄 Workflow Sistem

1. **Admin menambah kategori** → Sistem menyimpan dengan color coding
2. **Admin mencari taksa** → API amaturalist.com dipanggil
3. **Taksa dipilih** → Data lengkap diambil dari API
4. **Fauna disimpan** → Status IUCN dan perlindungan otomatis terisi
5. **Monitoring aktif** → Sistem akan sync data berkala
6. **Dashboard update** → Statistik real-time terupdate

## 🛡️ Error Handling

### API Fallback System
- **Primary**: API amaturalist.com
- **Secondary**: Database lokal (koneksi 'second')
- **Tertiary**: Database utama

### Logging
- Semua aktivitas API dicatat di Laravel log
- Error handling dengan informasi detail
- Monitoring status sync untuk troubleshooting

## 🚨 Monitoring & Alerts

### Sync Status
- **Fresh**: Data sync dalam 7 hari terakhir
- **Needs Sync**: Data lebih dari 7 hari
- **Never Synced**: Belum pernah sync dengan API

### Visual Indicators
- **Badge warna**: Status kategori
- **Icon monitoring**: Status pemantauan aktif/nonaktif
- **Timeline**: Riwayat aktivitas fauna

## 📈 Statistik & Reporting

### Dashboard Metrics
- Total fauna per kategori
- Trend penambahan fauna prioritas
- Status sinkronisasi API
- Distribusi status IUCN dan perlindungan

### Export Features
- Data fauna prioritas dapat diekspor
- Filter berdasarkan kategori dan status
- Format CSV/Excel untuk analisis lanjutan

## 🔐 Security & Permissions

### Admin Access Only
- Hanya admin yang dapat mengakses sistem
- CRUD operations memerlukan autentikasi admin
- Logging semua aktivitas admin

### Data Validation
- Validasi input form yang ketat
- Sanitasi data dari API eksternal
- Protection terhadap SQL injection

## 🎯 Future Enhancements

### Planned Features
- **Notification System**: Alert untuk fauna yang perlu perhatian khusus
- **Reporting Dashboard**: Grafik dan chart untuk analisis trend
- **Export Integration**: Integrasi dengan sistem pelaporan eksternal
- **Mobile App**: Akses mobile untuk field monitoring
- **GIS Integration**: Pemetaan distribusi fauna prioritas

### API Improvements
- **Caching System**: Cache hasil API untuk performa lebih baik
- **Rate Limiting**: Kontrol frekuensi panggilan API
- **Webhook Support**: Real-time update dari API eksternal

---

## 📞 Support & Maintenance

Untuk pertanyaan teknis atau bug report, silakan hubungi tim development AKAR.

**Dibuat dengan ❤️ untuk konservasi fauna Indonesia**
