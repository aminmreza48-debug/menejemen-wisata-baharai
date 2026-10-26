<?php
$pageTitle = "Tentang Aplikasi & Dokumentasi Sistem";
require_once __DIR__ . '/config/db.php';
include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="bg-ocean-primary text-white py-5">
    <div class="container text-center">
        <h2 class="font-serif fw-bold mb-2">Tentang BahariGIS</h2>
        <p class="text-white-50 mb-0">Dokumentasi Perancangan Sistem Informasi Geografis Wisata Bahari — Semester 5</p>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4">
        <!-- Kolom Navigasi Cepat Samping -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 sticky-top" style="top: 100px;">
                <h6 class="fw-bold text-ocean-primary mb-3">Daftar Isi Dokumentasi</h6>
                <div class="nav flex-column nav-pills gap-1">
                    <a href="#profil" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light active">1. Profil Aplikasi</a>
                    <a href="#kebutuhan" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light">2. Analisis Kebutuhan</a>
                    <a href="#erd" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light">3. Perancangan ERD</a>
                    <a href="#dfd" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light">4. Aliran Data (DFD)</a>
                    <a href="#aturan-bisnis" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light">5. Aturan Bisnis (BR)</a>
                    <a href="#teknologi" class="nav-link text-dark py-2 px-3 small rounded-3 hover-bg-light">6. Spesifikasi Teknologi</a>
                </div>
            </div>
        </div>

        <!-- Kolom Konten Utama -->
        <div class="col-lg-9">
            <!-- 1. Profil Aplikasi -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" id="profil">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Identitas & Tujuan Sistem
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">1. Profil & Tujuan BahariGIS</h3>
                <p class="text-secondary" style="line-height: 1.8;">
                    <strong>BahariGIS</strong> merupakan aplikasi sistem informasi geografis berbasis web (WEBGIS) yang dirancang khusus untuk memetakan dan menyajikan informasi komprehensif mengenai objek wisata bahari di wilayah perairan Indonesia. Melalui peta interaktif digital, wisatawan dan pengambil kebijakan dapat mengetahui persebaran spasial, daya tarik pantai, terumbu karang, fasilitas pendukung, jam operasional, serta petunjuk rute perjalanan.
                </p>

                <h6 class="fw-bold text-dark mt-4 mb-2">Tujuan Utama Aplikasi:</h6>
                <ul class="text-secondary small" style="line-height: 1.9;">
                    <li>Memudahkan wisatawan menemukan lokasi wisata bahari melalui peta digital interaktif.</li>
                    <li>Menyediakan informasi akurat mengenai daya tarik wisata, fasilitas, dan tiket masuk.</li>
                    <li>Menampilkan persebaran geografis objek wisata bahari secara terpadu.</li>
                    <li>Membantu pengelola dan dinas pariwisata dalam memperbarui data spasial objek wisata.</li>
                    <li>Mendukung promosi pariwisata bahari dan pelestarian ekosistem pesisir daerah.</li>
                </ul>

                <h6 class="fw-bold text-dark mt-4 mb-2">Target Pengguna:</h6>
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <strong><i class="bi bi-people-fill text-primary me-2"></i>Wisatawan</strong>
                            <p class="text-muted small mb-0 mt-1">Mencari lokasi pantai, pulau eksotis, spot diving, tiket, dan rute navigasi.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <strong><i class="bi bi-shield-lock-fill text-success me-2"></i>Admin & Dinas Pariwisata</strong>
                            <p class="text-muted small mb-0 mt-1">Mengelola master data wisata, fasilitas, foto dokumentasi, dan titik koordinat spasial.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Analisis Kebutuhan Sistem -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" id="kebutuhan">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Requirement Analysis
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">2. Analisis Kebutuhan Sistem</h3>
                
                <h6 class="fw-bold text-dark mb-3">2.1 Kebutuhan Fungsional (KF):</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">Kode</th>
                                <th style="width: 200px;">Kebutuhan</th>
                                <th>Implementasi dalam Kode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">KF-01</span></td>
                                <td><strong>Menampilkan Peta Wisata</strong></td>
                                <td>Peta Leaflet.js dengan layer OpenStreetMap & Esri Satellite, marker custom oceanic pin.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-02</span></td>
                                <td><strong>Pencarian Wisata</strong></td>
                                <td>Realtime filter input kata kunci nama objek wisata dan alamat.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-03</span></td>
                                <td><strong>Filter Kategori</strong></td>
                                <td>Pills filter kategori (Pantai, Pulau, Snorkeling, Diving, Konservasi Mangrove).</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-04</span></td>
                                <td><strong>Detail Wisata</strong></td>
                                <td>Pop-up interaktif & modal/halaman detail lengkap dengan galeri foto, fasilitas, dan tiket.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-05</span></td>
                                <td><strong>Informasi Koordinat</strong></td>
                                <td>Penyajian data latitude & longitude presisi, fly-to zoom ke titik marker terpilih.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-06</span></td>
                                <td><strong>Navigasi Lokasi</strong></td>
                                <td>Integrasi tombol langsung ke Google Maps Direction API dengan koordinat tujuan.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-07</span></td>
                                <td><strong>Login Admin</strong></td>
                                <td>Autentikasi sesi PHP aman berbasis <code>password_verify()</code> dan enkripsi BCRYPT.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-08</span></td>
                                <td><strong>Pengelolaan Data Wisata</strong></td>
                                <td>CRUD lengkap data wisata dengan fitur <em>Click-to-Pick Coordinate</em> pada peta interaktif.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-09</span></td>
                                <td><strong>Pengelolaan Fasilitas</strong></td>
                                <td>CRUD master fasilitas dan relasi many-to-many pada tabel <code>wisata_fasilitas</code>.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">KF-10</span></td>
                                <td><strong>Pengelolaan Foto</strong></td>
                                <td>Multi-foto galeri per destinasi melalui tabel relasi <code>foto_wisata</code>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Perancangan ERD -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" id="erd">
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Entity Relationship Diagram
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">3. Perancangan Basis Data & ERD</h3>
                <p class="text-secondary small">
                    Basis data dirancang dengan nama <code>baharigis_db</code> menggunakan 6 entitas yang saling terhubung untuk memastikan integritas referensial dan normalisasi relasional yang optimal:
                </p>

                <div class="bg-dark text-white p-4 rounded-4 font-monospace small mb-4" style="line-height: 1.8;">
USERS (id_user PK) 1 ────────────< WISATA (id_user FK)<br>
KATEGORI_WISATA (id_kategori PK) 1 ────< WISATA (id_kategori FK)<br>
WISATA (id_wisata PK) 1 ─────────< FOTO_WISATA (id_wisata FK)<br>
WISATA (id_wisata PK) 1 ─────────< WISATA_FASILITAS >──────── 1 FASILITAS (id_fasilitas PK)
                </div>

                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>1. USERS</strong>
                            <p class="text-muted mb-0">Menyimpan identitas administrator dan operator pengelola sistem.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>2. KATEGORI_WISATA</strong>
                            <p class="text-muted mb-0">Mengelompokkan destinasi berdasarkan karakteristik bahari.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>3. WISATA</strong>
                            <p class="text-muted mb-0">Entitas inti spasial: nama, deskripsi, tiket, status, lat, long.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>4. FASILITAS</strong>
                            <p class="text-muted mb-0">Master data fasilitas yang dapat digunakan kembali.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>5. WISATA_FASILITAS</strong>
                            <p class="text-muted mb-0">Tabel persimpangan relasi M:N dengan catatan kondisi.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light">
                            <strong>6. FOTO_WISATA</strong>
                            <p class="text-muted mb-0">Menyimpan multi-foto dan keterangan setiap objek wisata.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Perancangan DFD -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" id="dfd">
                <span class="badge bg-warning bg-opacity-10 text-dark px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Data Flow Diagram
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">4. Perancangan Aliran Data (DFD)</h3>

                <h6 class="fw-bold text-dark mb-2">DFD Level 0 — Diagram Konteks:</h6>
                <div class="bg-light border rounded-3 p-3 font-monospace small mb-4" style="line-height: 1.8;">
[Wisatawan] ── pencarian wisata / filter ──> (Sistem WEBGIS Wisata Bahari)<br>
[Wisatawan] &lt;── peta, detail wisata, fasilitas ── (Sistem WEBGIS Wisata Bahari)<br>
<br>
[Admin] ── login & pengelolaan data wisata ──> (Sistem WEBGIS Wisata Bahari)<br>
[Admin] &lt;── status proses & data spasial ── (Sistem WEBGIS Wisata Bahari)
                </div>

                <h6 class="fw-bold text-dark mb-2">DFD Level 1 — Rincian Proses Sistem:</h6>
                <div class="bg-light border rounded-3 p-3 font-monospace small" style="line-height: 1.8;">
Proses 1.0 (Pencarian & Filter)  &lt;--&gt; D1 Data Wisata, D2 Data Kategori<br>
Proses 2.0 (Tampilkan Detail)    &lt;-- D1 Data Wisata, D3 Data Fasilitas, D5 Data Foto<br>
Proses 3.0 (Autentikasi Admin)   &lt;--&gt; D4 Data Pengguna (Users)<br>
Proses 4.0 (Pengelolaan Wisata)  --&gt; D1 Data Wisata, D2 Kategori, D3 Fasilitas, D5 Foto
                </div>
            </div>

            <!-- 5. Aturan Bisnis -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" id="aturan-bisnis">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Business Rules Implementation
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">5. Penerapan Aturan Bisnis (BR-01 s/d BR-15)</h3>
                
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3">
                            <span class="badge bg-danger mb-2">BR-01 s/d BR-04</span>
                            <ul class="mb-0 ps-3">
                                <li>Setiap wisata memiliki Primary Key unik.</li>
                                <li>Nama wisata wajib diisi saat validasi form.</li>
                                <li>Objek wisata wajib terhubung dengan kategori valid.</li>
                                <li>Koordinat Latitude (-90 s/d 90) & Longitude (-180 s/d 180).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3">
                            <span class="badge bg-danger mb-2">BR-05 s/d BR-08</span>
                            <ul class="mb-0 ps-3">
                                <li>Wisata aktif wajib memiliki deskripsi dan alamat lengkap.</li>
                                <li>Aksi CRUD hanya dapat diakses admin dengan sesi terverifikasi.</li>
                                <li>Password admin di-hash dengan enkripsi <code>password_hash()</code>.</li>
                                <li>Foto wajib terhubung ke objek wisata yang ada.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3">
                            <span class="badge bg-danger mb-2">BR-09 s/d BR-12</span>
                            <ul class="mb-0 ps-3">
                                <li>Relasi fasilitas valid via Foreign Key.</li>
                                <li>Pencarian publik hanya memfilter status 'aktif'.</li>
                                <li>Harga tiket tidak boleh bernilai negatif (&ge; 0).</li>
                                <li>Penghapusan data menjaga integritas (Cascade foto & fasilitas).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3">
                            <span class="badge bg-danger mb-2">BR-13 s/d BR-15</span>
                            <ul class="mb-0 ps-3">
                                <li>Email akun bersifat unik di seluruh tabel pengguna.</li>
                                <li>Validasi data sebelum proses penyimpanan ke basis data.</li>
                                <li>Navigasi eksternal memiliki pranala langsung ke Google Maps.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Spesifikasi Teknologi -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" id="teknologi">
                <span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2 rounded-pill small fw-bold mb-3 d-inline-block" style="width: fit-content;">
                    Technology Stack
                </span>
                <h3 class="font-serif fw-bold text-ocean-primary mb-3">6. Komponen Perangkat Lunak</h3>
                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <strong>Frontend</strong>
                            <p class="text-muted mb-0">HTML5, CSS3 Oceanic Theme, JavaScript ES6+, Bootstrap 5.3.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <strong>Peta Spasial (GIS)</strong>
                            <p class="text-muted mb-0">Leaflet.js 1.9.4 & OpenStreetMap + Esri World Imagery.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <strong>Backend & Database</strong>
                            <p class="text-muted mb-0">PHP <?= PHP_VERSION ?> dengan PDO, MySQL & SQLite Dual-Mode.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
