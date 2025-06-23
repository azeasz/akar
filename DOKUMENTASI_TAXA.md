# Dokumentasi Fitur Taxa

Fitur Taxa adalah sistem untuk mengelola dan menyinkronkan data taksonomi spesies dari database amaturalist ke database lokal aplikasi AKAR. Fitur ini memungkinkan admin untuk mencari, melihat, dan mengelola data taksonomi spesies yang digunakan dalam checklist fauna.

## Komponen Utama

### 1. Model

- **Taxa**: Model untuk mengakses data taksonomi dari database amaturalist.
- **TaxaLocal**: Model untuk menyimpan data taksonomi di database lokal aplikasi AKAR.

### 2. Controller

- **TaxaController**: Controller untuk mengelola operasi CRUD pada data taksonomi.
  - `index`: Menampilkan daftar taxa di database lokal.
  - `show`: Menampilkan detail taxa.
  - `search`: Menampilkan form pencarian taxa.
  - `searchResults`: Menampilkan hasil pencarian taxa.
  - `sync`: Menampilkan form sinkronisasi taxa.
  - `processSync`: Memproses sinkronisasi taxa dari database amaturalist ke database lokal.
  - `compare`: Membandingkan data taxa dari database amaturalist dan database lokal.
  - `import`: Mengimpor data taxa dari database amaturalist ke database lokal.
  - `syncSingle`: Menyinkronkan data taxa tunggal dari database amaturalist ke database lokal.
  - `updateIucnStatus`: Memperbarui status IUCN dari API eksternal.
  - `updateCitesStatus`: Memperbarui status CITES dari API eksternal.
  - `selectModal`: Menampilkan modal untuk memilih taxa.

- **ChecklistFaunaController**: Controller untuk operasi terkait fauna.
  - `findTaxa`: Mencari taxa berdasarkan ID fauna.

### 3. Command

- **SyncTaxaData**: Command untuk menyinkronkan data taxa dari database amaturalist ke database lokal.
  - Opsi: `--limit`, `--offset`, `--chunk`

### 4. View

- **index.blade.php**: Menampilkan daftar taxa di database lokal.
- **show.blade.php**: Menampilkan detail taxa.
- **search.blade.php**: Form pencarian taxa.
- **search_results.blade.php**: Menampilkan hasil pencarian taxa.
- **sync.blade.php**: Form sinkronisasi taxa.
- **compare.blade.php**: Membandingkan data taxa dari database amaturalist dan database lokal.
- **select_modal.blade.php**: Modal untuk memilih taxa.

### 5. Route

- `admin.taxas.index`: Menampilkan daftar taxa.
- `admin.taxas.show`: Menampilkan detail taxa.
- `admin.taxas.search`: Menampilkan form pencarian taxa.
- `admin.taxas.search.results`: Menampilkan hasil pencarian taxa.
- `admin.taxas.sync`: Menampilkan form sinkronisasi taxa.
- `admin.taxas.process_sync`: Memproses sinkronisasi taxa.
- `admin.taxas.compare`: Membandingkan data taxa.
- `admin.taxas.import`: Mengimpor data taxa.
- `admin.taxas.sync_single`: Menyinkronkan data taxa tunggal.
- `admin.taxas.update_iucn`: Memperbarui status IUCN.
- `admin.taxas.update_cites`: Memperbarui status CITES.
- `admin.checklist-faunas.find-taxa`: Mencari taxa berdasarkan ID fauna.

## Konfigurasi Database

Aplikasi AKAR menggunakan dua koneksi database:
1. **default**: Database lokal aplikasi AKAR.
2. **amaturalist**: Database amaturalist yang berisi data taksonomi spesies.

Konfigurasi koneksi database amaturalist ada di file `.env`:

```
AMATURALIST_DB_CONNECTION=mysql
AMATURALIST_DB_HOST=127.0.0.1
AMATURALIST_DB_PORT=3306
AMATURALIST_DB_DATABASE=amaturalist
AMATURALIST_DB_USERNAME=root
AMATURALIST_DB_PASSWORD=
```

## API Eksternal

Fitur Taxa juga menggunakan API eksternal untuk memperbarui status IUCN dan CITES:

1. **IUCN API**: Digunakan untuk memperbarui status IUCN spesies.
   - Konfigurasi: `IUCN_API_TOKEN` di `.env`

2. **CITES API**: Digunakan untuk memperbarui status CITES spesies.
   - Konfigurasi: `CITES_API_TOKEN` di `.env`

## Integrasi dengan Checklist Fauna

Fitur Taxa terintegrasi dengan Checklist Fauna melalui kolom `fauna_id` pada tabel `checklist_faunas`. Kolom ini mereferensikan `id` pada tabel `taxa_locals` atau `taxas`.

## Penggunaan

### Sinkronisasi Taxa

1. Buka halaman Sinkronisasi Taxa di menu Admin.
2. Isi form dengan jumlah data dan offset yang diinginkan.
3. Klik tombol "Mulai Sinkronisasi" untuk memulai proses sinkronisasi.

### Pencarian Taxa

1. Buka halaman Pencarian Taxa di menu Admin.
2. Isi form dengan kata kunci dan kingdom yang diinginkan.
3. Klik tombol "Cari" untuk menampilkan hasil pencarian.

### Perbandingan Taxa

1. Buka halaman Perbandingan Taxa di menu Admin.
2. Lihat perbandingan data taxa dari database amaturalist dan database lokal.
3. Klik tombol "Import" untuk mengimpor data taxa dari database amaturalist ke database lokal.
4. Klik tombol "Sinkronkan" untuk menyinkronkan data taxa yang sudah ada di database lokal.

### Mencari Taxa dari Checklist Fauna

1. Buka halaman Detail Checklist.
2. Pada bagian Data Fauna, klik tombol ID Taxa untuk mencari taxa berdasarkan ID fauna.

## Command Line

Untuk menyinkronkan data taxa melalui command line, gunakan perintah berikut:

```bash
php artisan taxa:sync --limit=100 --offset=0 --chunk=10
```

Opsi:
- `--limit`: Jumlah data yang akan disinkronkan (default: 100).
- `--offset`: Mulai dari data ke-n (default: 0).
- `--chunk`: Jumlah data yang akan disinkronkan dalam satu proses (default: 10). 