# SIG Wisata Madura Pantai Jumiang (Pamekasan)

> **Tugas Mata Kuliah: Manajemen Web GIS (Semester 5)**  
> **Aplikasi Berbasis:** Web GIS (Leaflet.js + OpenStreetMap + Esri Satellite + PHP PDO + SQLite / MySQL Dual-Mode)

---

## 1. Identitas & Deskripsi Aplikasi
**SIG Wisata Madura Pantai Jumiang** adalah aplikasi sistem informasi geografis berbasis web yang dirancang untuk memetakan persebaran objek wisata, spot daya tarik panorama alam (tebing karang, pantai pasir putih, hutan cemara udang pesisir), wahana foto sunset, sentra kuliner rujak petis Madura, serta fasilitas publik di kawasan **Wisata Pantai Jumiang, Desa Tanjung, Kecamatan Pademawu, Kabupaten Pamekasan, Pulau Madura, Jawa Timur**. 

### Tujuan Aplikasi:
1. **Memudahkan Wisatawan**: Menemukan lokasi spot-spot menarik dan fasilitas di kawasan Pantai Jumiang melalui peta digital interaktif.
2. **Menyediakan Informasi Terpadu**: Menyajikan deskripsi keindahan tebing karang, daya tarik pasir putih, tiket masuk kawasan, jam operasional, dan fasilitas pendukung.
3. **Analisis Persebaran Spasial**: Menampilkan visualisasi persebaran geografis titik objek daya tarik dan delimitasi kawasan wisata.
4. **Membantu Pengelola & Pokdarwis**: Memperbarui informasi objek wisata, fasilitas, dan dokumentasi foto secara mandiri melalui panel dashboard admin tanpa perlu koding ulang.
5. **Mendukung Promosi Pariwisata Madura**: Mendorong sektor pariwisata daerah Kabupaten Pamekasan dan Pulau Madura.

### Target Pengguna:
- **Wisatawan**: Pengunjung yang mencari lokasi spot tebing, pantai, kuliner, tiket, dan rute navigasi ke Pantai Jumiang.
- **Pengelola Objek Wisata (Pokdarwis Desa Tanjung)**: Meninjau status fasilitas dan data objek wisata.
- **Administrator / Dinas Pariwisata Pamekasan**: Mengelola titik koordinat, foto, dan master kategori.

---

## 2. Analisis Kebutuhan Sistem (System Requirements)

### 2.1 Kebutuhan Fungsional (KF)
| Kode | Kebutuhan | Penjelasan & Implementasi |
| :--- | :--- | :--- |
| **KF-01** | Menampilkan peta wisata | Peta interaktif Leaflet.js dengan tile OpenStreetMap, Esri World Imagery (Satelit), dan CartoDB Positron dengan custom oceanic marker. |
| **KF-02** | Pencarian wisata | Input pencarian langsung (*realtime filter*) berdasarkan nama wisata, wilayah administratif, atau kata kunci. |
| **KF-03** | Filter kategori | Filter kategori cepat (*Pills*) untuk Pantai, Pulau, Snorkeling, Diving, dan Mangrove. |
| **KF-04** | Detail wisata | Modal dan halaman khusus detail yang menampilkan galeri foto, alamat, tiket, jam buka, dan fasilitas. |
| **KF-05** | Informasi koordinat | Koordinat lintang (*Latitude*) dan bujur (*Longitude*) presisi tinggi dengan aksi *fly-to* di peta. |
| **KF-06** | Navigasi lokasi | Terintegrasi langsung dengan tautan Google Maps Direction untuk rute navigasi dari lokasi wisatawan. |
| **KF-07** | Login admin | Autentikasi sesi aman dengan verifikasi hash password BCRYPT (`password_verify`). |
| **KF-08** | Pengelolaan data wisata | CRUD lengkap data wisata yang dilengkapi **Peta Picker Koordinat Interaktif** (*Click to pick / Drag pin*). |
| **KF-09** | Pengelolaan fasilitas | CRUD master data fasilitas dan pencatatan relasi *Many-to-Many* pada tabel `wisata_fasilitas`. |
| **KF-10** | Pengelolaan foto | Multi-foto per objek wisata pada tabel `foto_wisata` dengan opsi tambah dan hapus foto individual. |

### 2.2 Kebutuhan Nonfungsional
- **Kemudahan Penggunaan**: Antarmuka bertema maritim modern dengan perpaduan warna biru laut (*oceanic blue*), putih bersih, dan aksen *seafoam green*.
- **Kinerja**: Pemuatan data cepat dengan optimasi JSON API dan library CDN resmi.
- **Keamanan**: Proteksi prepared statements PDO (mencegah SQL Injection), sanitasi XSS (`htmlspecialchars`), pembatasan otorisasi admin (`checkAdminAuth()`).
- **Kompatibilitas & Responsivitas**: Tampilan adaptif di komputer desktop, laptop, tablet, dan smartphone (dilengkapi bilah navigasi bawah *mobile bottom navigation*).
- **Keakuratan Data**: Koordinat nyata dari destinasi bahari terkemuka di Indonesia (misal: Tanjung Tinggi Belitung, Karimunjawa, Bunaken, Raja Ampat, Pink Beach Lombok, Wakatobi, Derawan, Mandalika).

---

## 3. Perancangan Basis Data (Database Design)

Basis data bernama **`baharigis_db`**. Terdiri atas 6 entitas yang saling terhubung:

1. **`users`**: Menyimpan akun administrator dan operator sistem.
   - `id_user` (INT PK, Auto Increment)
   - `nama` (VARCHAR 100)
   - `email` (VARCHAR 100, UNIQUE)
   - `password` (VARCHAR 255, Bcrypt Hash)
   - `role` (ENUM: 'admin', 'operator')
   - `created_at` (DATETIME)

2. **`kategori_wisata`**: Mengelompokkan jenis objek wisata bahari.
   - `id_kategori` (INT PK, Auto Increment)
   - `nama_kategori` (VARCHAR 100)
   - `deskripsi` (TEXT)

3. **`fasilitas`**: Master data sarana dan prasarana.
   - `id_fasilitas` (INT PK, Auto Increment)
   - `nama_fasilitas` (VARCHAR 100)
   - `deskripsi` (TEXT)

4. **`wisata`**: Entitas utama penyimpan data spasial dan atribut destinasi.
   - `id_wisata` (INT PK, Auto Increment)
   - `id_kategori` (INT FK ke `kategori_wisata`)
   - `nama_wisata` (VARCHAR 150)
   - `deskripsi` (TEXT)
   - `alamat` (TEXT)
   - `latitude` (DECIMAL 10,7)
   - `longitude` (DECIMAL 10,7)
   - `harga_tiket` (DECIMAL 12,2)
   - `jam_operasional` (VARCHAR 100)
   - `status` (ENUM: 'aktif', 'draft', 'nonaktif')
   - `id_user` (INT FK ke `users`)
   - `created_at` (DATETIME)

5. **`wisata_fasilitas`**: Tabel penghubung relasi banyak-ke-banyak (*Many-to-Many*).
   - `id_wisata` (INT PK/FK)
   - `id_fasilitas` (INT PK/FK)
   - `keterangan` (VARCHAR 255)

6. **`foto_wisata`**: Menyimpan dokumentasi visual destinasi.
   - `id_foto` (INT PK, Auto Increment)
   - `id_wisata` (INT FK ke `wisata`)
   - `nama_file` (VARCHAR 255)
   - `keterangan` (VARCHAR 255)

---

## 4. Entity Relationship Diagram (ERD)

```text
USERS (id_user PK) 1 ────────────< WISATA (id_user FK)
KATEGORI_WISATA (id_kategori PK) 1 ────< WISATA (id_kategori FK)
WISATA (id_wisata PK) 1 ─────────< FOTO_WISATA (id_wisata FK)
WISATA (id_wisata PK) 1 ─────────< WISATA_FASILITAS >──────── 1 FASILITAS (id_fasilitas PK)
```

---

## 5. Perancangan Aliran Data (Data Flow Diagram - DFD)

### 5.1 DFD Level 0 — Diagram Konteks
```text
[Wisatawan] ─── pencarian wisata / filter ───> (Sistem WEBGIS Wisata Bahari)
[Wisatawan] <─── peta interaktif, detail, rute ─ (Sistem WEBGIS Wisata Bahari)

[Admin] ─────── login & pengelolaan data ────> (Sistem WEBGIS Wisata Bahari)
[Admin] <────── status proses & data spasial ─ (Sistem WEBGIS Wisata Bahari)
```

### 5.2 DFD Level 1 — Rincian Proses
- **Proses 1.0 (Pencarian & Filter)**: Membaca D1 (Wisata) & D2 (Kategori).
- **Proses 2.0 (Menampilkan Detail)**: Membaca D1 (Wisata), D3 (Fasilitas), dan D5 (Foto).
- **Proses 3.0 (Autentikasi Admin)**: Memverifikasi D4 (Pengguna/Users).
- **Proses 4.0 (Pengelolaan Data Spasial)**: Menulis dan memperbarui D1 (Wisata), D2 (Kategori), D3 (Fasilitas), dan D5 (Foto).

---

## 6. Penerapan Aturan Bisnis (Business Rules BR-01 s/d BR-15)

1. **BR-01**: Setiap wisata memiliki ID unik (Primary Key Auto Increment).
2. **BR-02**: Nama wisata wajib diisi saat validasi form.
3. **BR-03**: Setiap wisata wajib terhubung dengan kategori yang valid.
4. **BR-04**: Koordinat harus dalam rentang geografis valid (-90 s/d 90 untuk latitude, -180 s/d 180 untuk longitude).
5. **BR-05**: Wisata berstatus 'aktif' wajib memiliki deskripsi dan alamat lengkap sebelum dipublikasikan.
6. **BR-06**: Akses penambahan, pengubahan, dan penghapusan data hanya diizinkan untuk admin terautentikasi.
7. **BR-07**: Password akun tersimpan dalam bentuk enkripsi hash BCRYPT.
8. **BR-08**: Foto wisata wajib memiliki foreign key ke objek wisata yang valid.
9. **BR-09**: Relasi fasilitas dan objek wisata harus berpasangan secara valid.
10. **BR-10**: Wisatawan umum hanya dapat melihat data wisata berstatus 'aktif'.
11. **BR-11**: Harga tiket masuk tidak boleh bernilai negatif (>= 0).
12. **BR-12**: Penghapusan wisata secara otomatis menghapus relasi foto dan fasilitas terkait (*Cascade Delete*) untuk menjaga integritas data.
13. **BR-13**: Alamat email akun admin bersifat unik di basis data.
14. **BR-14**: Data yang diinputkan tervalidasi di sisi klien dan server sebelum disimpan.
15. **BR-15**: Navigasi eksternal disediakan sebagai panduan rute melalui Google Maps Direction.

---

## 7. Cara Menjalankan Aplikasi

Aplikasi dirancang dengan **Dual-Mode Database**:
- **Mode 1 (MySQL XAMPP)**: Menggunakan database MySQL standar (`baharigis_db`).
- **Mode 2 (SQLite Standalone Fallback)**: Jika MySQL belum dinyalakan di XAMPP, aplikasi **tetap langsung berjalan secara mulus** menggunakan SQLite otomatis tanpa error!

### Metode A: Menjalankan Cepat (1-Klik via Batch File)
1. Cukup klik ganda (*double click*) file **`run-server.bat`** di folder proyek ini.
2. Server lokal akan otomatis berjalan di **`http://localhost:8000`** dan membuka browser Anda secara langsung.

### Metode B: Menjalankan via Command Line
Buka PowerShell atau Terminal di direktori proyek ini, jalankan:
```bash
"C:\xampp\php\php.exe" -S localhost:8000
```
Lalu buka browser di: `http://localhost:8000`

### Metode C: Menjalankan via XAMPP Apache
1. Pindahkan atau salin folder proyek ini ke dalam folder `C:\xampp\htdocs\baharigis`.
2. Buka **XAMPP Control Panel**, lalu tekan **Start** pada Apache dan MySQL.
3. Buka browser dan akses: `http://localhost/baharigis/`
4. (Opsional) Jika ingin mengimpor database manual di phpMyAdmin:
   - Buka `http://localhost/phpmyadmin/`
   - Buat database baru bernama `baharigis_db`
   - Pilih tab **Import**, pilih file `database/baharigis_db.sql`, lalu klik **Import**.

---

## 8. Kredensial Login Administrator

| Peran (Role) | Alamat Email | Kata Sandi (Password) |
| :--- | :--- | :--- |
| **Administrator** | `admin@baharigis.id` | `admin123` |
| **Operator Dinas** | `operator@baharigis.id` | `admin123` |

*Catatan: Pada halaman login admin (`admin/login.php`), tersedia tombol pintas "Gunakan Akun Demo" untuk mengisi form otomatis.*

---

## 9. Struktur Berkas Proyek

```text
menejemen web gis/
├── admin/
│   ├── index.php             # Dashboard ringkasan, peta sebaran admin & KPI
│   ├── login.php             # Halaman login administrator (KF-07)
│   ├── logout.php            # Sesi logout admin
│   ├── wisata.php            # Manajemen CRUD Wisata + Leaflet Map Picker (KF-08)
│   ├── kategori.php          # CRUD Kategori Wisata
│   └── fasilitas.php         # CRUD Master Fasilitas (KF-09)
├── api/
│   ├── auth.php              # API login/logout verifikasi BCRYPT
│   ├── wisata.php            # REST API pencarian, filter, detail, dan CRUD wisata
│   ├── kategori.php          # REST API kategori wisata
│   └── fasilitas.php         # REST API fasilitas wisata
├── assets/
│   ├── css/
│   │   ├── style.css         # Desain bertema maritim/pantai, glassmorphism, responsive
│   │   └── admin.css         # Desain dashboard dan panel admin
│   └── js/
│       ├── app.js            # Logika Leaflet.js, popup interaktif, filter realtime
│       └── admin.js          # Skrip CRUD admin & interactive coordinate picker
├── config/
│   └── db.php                # Koneksi PDO (MySQL & SQLite Dual-Mode fallback)
├── database/
│   ├── baharigis_db.sql      # Skema DDL & seed data lengkap untuk phpMyAdmin
│   └── baharigis_db.sqlite   # File basis data fallback lokal
├── includes/
│   ├── header.php            # Header & navigasi publik
│   ├── footer.php            # Footer, mobile bottom bar, modal detail
│   ├── admin_header.php      # Sidebar & header panel admin
│   └── admin_footer.php      # Footer & script panel admin
├── index.php                 # Halaman utama (Hero, Peta Interaktif, Rekomendasi)
├── daftar-wisata.php         # Katalog seluruh destinasi dengan pencarian & sorting
├── detail-wisata.php         # Halaman khusus detail destinasi & rute navigasi
├── tentang.php               # Dokumentasi sistem, profil, ERD, DFD, & tim pengembang
├── run-server.bat            # Script batch peluncur server 1-klik
└── README.md                 # Dokumentasi panduan lengkap tugas Semester 5
```
