<?php
$pageTitle = "Peta Kawasan Wisata Pantai Jumiang Madura";
require_once __DIR__ . '/config/db.php';

$db = getDB();

// Ambil kategori untuk filter pills
$categories = [];
try {
    $stmtKat = $db->query("
        SELECT k.*, COUNT(w.id_wisata) AS total 
        FROM kategori_wisata k
        LEFT JOIN wisata w ON k.id_kategori = w.id_kategori AND w.status = 'aktif'
        GROUP BY k.id_kategori
        ORDER BY k.id_kategori ASC
    ");
    $categories = $stmtKat->fetchAll();
} catch (Throwable $e) {
    // fallback empty
}

// Ambil total destinasi aktif
$totalWisata = 0;
try {
    $totalWisata = (int)$db->query("SELECT COUNT(*) FROM wisata WHERE status = 'aktif'")->fetchColumn();
} catch (Throwable $e) {}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<section class="hero-bahari text-center">
    <div class="container position-relative" style="z-index: 2;">
        <span class="badge hero-badge-pill text-white border px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-geo-alt-fill me-1 text-warning"></i> Kawasan Wisata Pantai Jumiang — Desa Tanjung, Pademawu, Pamekasan, Madura
        </span>
        <h1 class="hero-title">Wisata Madura: Pantai Jumiang Pamekasan</h1>
        <p class="hero-subtitle">
            Sistem Informasi Geografis pemetaan spasial dua zona utama: <strong>Jumiang Atas</strong> (tebing karang kokoh, gardu pandang sunset & sunrise, ayunan tebing) dan <strong>Jumiang Bawah</strong> (pantai pasir putih landai, perahu nelayan tradisional Madura, banana boat, hutan cemara udang, & warung rujak petis).
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#sectionPeta" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow">
                <i class="bi bi-map-fill me-1"></i> Buka Peta Spasial Jumiang
            </a>
            <a href="daftar-wisata.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-grid-fill me-1"></i> Katalog Spot & Fasilitas
            </a>
            <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark shadow" data-bs-toggle="modal" data-bs-target="#modalInfoTiket">
                <i class="bi bi-ticket-perforated-fill me-1"></i> Info Tiket: Rp 5.000
            </button>
        </div>
    </div>
</section>

<!-- Floating Search & Filter Section (KF-02, KF-03) -->
<section class="container">
    <div class="search-box-card">
        <div class="row g-3 align-items-center">
            <!-- Search Input -->
            <div class="col-lg-5 position-relative">
                <span class="search-icon-wrap">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchWisataInput" class="form-control input-search-bahari" 
                       placeholder="Cari spot di Pantai Jumiang... (misal: Tebing Karang, Pasir Putih, Sunset, Rujak)">
            </div>

            <!-- Category Filter Pills -->
            <div class="col-lg-7">
                <div class="category-pills-container">
                    <button type="button" class="category-pill active" data-category="0">
                        <span>Semua Spot</span>
                        <span class="badge bg-secondary"><?= $totalWisata ?></span>
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <button type="button" class="category-pill" data-category="<?= $cat['id_kategori'] ?>">
                            <span><?= htmlspecialchars($cat['nama_kategori']) ?></span>
                            <span class="badge bg-light text-dark"><?= $cat['total'] ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Map Section (KF-01, KF-05) -->
<section id="sectionPeta" class="container mt-4 pt-2">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="font-serif fw-bold text-ocean-primary mb-1">
                <i class="bi bi-pin-map-fill text-danger me-2"></i>Peta Persebaran Objek & Spot Wisata Pantai Jumiang Madura
            </h3>
            <p class="text-muted small mb-0">Klik pada penanda (marker) untuk melihat foto, tiket retribusi, jam buka, dan rute navigasi di kawasan Pantai Jumiang.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2" id="totalWisataBadge">
                <?= $totalWisata ?> Spot & Fasilitas
            </span>
            <span class="badge bg-dark rounded-pill px-3 py-2" id="dbDriverBadge">
                DB: <?= strtoupper(getDBDriver()) ?>
            </span>
        </div>
    </div>

    <!-- Map Canvas Container -->
    <div class="map-wrapper">
        <div id="mapWisata"></div>

        <!-- Floating Controls Inside Map -->
        <div class="map-floating-overlay">
            <button id="btnToggleSatellite" class="map-control-btn" title="Beralih Tampilan Satelit / Peta">
                <i class="bi bi-layers"></i> Satelit
            </button>
            <button id="btnLocateUser" class="map-control-btn" title="Deteksi Lokasi Anda Saat Ini">
                <i class="bi bi-crosshair"></i> Lokasi Saya
            </button>
            <button id="btnResetMap" class="map-control-btn" title="Fokuskan Kembali ke Kawasan Pantai Jumiang">
                <i class="bi bi-arrows-fullscreen"></i> Reset Kawasan
            </button>
        </div>

        <!-- Live Pulse Stats Indicator -->
        <div class="map-stats-badge">
            <div class="pulse-indicator"></div>
            <span>Peta Kawasan Wisata Pantai Jumiang Aktif</span>
            <span id="mapLoadingIndicator" class="spinner-border spinner-border-sm text-primary ms-2" style="display: none;"></span>
        </div>
    </div>
</section>

<!-- Destination Cards Section (KF-04) -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="font-serif fw-bold text-ocean-primary mb-1">Spot & Fasilitas Wisata Pantai Jumiang</h3>
            <p class="text-muted small mb-0">Menampilkan daya tarik wisata dan sarana pendukung di kawasan Pantai Jumiang sesuai filter.</p>
        </div>
        <a href="daftar-wisata.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
            Lihat Semua di Katalog <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row" id="destinationCardsContainer">
        <!-- Rendered dynamically by app.js via fetch('api/wisata.php') -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data spot wisata Pantai Jumiang...</p>
        </div>
    </div>
</section>

<!-- Features Highlights -->
<section class="bg-ocean-soft py-5 mt-5 border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="font-serif fw-bold text-ocean-primary">Keunggulan Web GIS Wisata Pantai Jumiang Madura</h3>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                Solusi digital berbasis spasial untuk pemetaan komprehensif seluruh spot panorama, daya tarik, sentra kuliner, dan fasilitas kawasan Wisata Pantai Jumiang Pamekasan.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Koordinat Akurat</h6>
                    <p class="text-muted small mb-0">Titik lintang dan bujur tervalidasi secara geografis sesuai posisi sebenarnya.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0096c7, #48cae4);">
                        <i class="bi bi-funnel"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Pencarian & Filter Cepat</h6>
                    <p class="text-muted small mb-0">Pencarian instan berdasarkan nama pantai, lokasi, maupun kategori kegiatan.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #06d6a0, #2ec4b6);">
                        <i class="bi bi-sign-turn-right"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Navigasi Langsung</h6>
                    <p class="text-muted small mb-0">Terhubung langsung dengan petunjuk arah Google Maps ke lokasi destinasi.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Kelola Data Mandiri</h6>
                    <p class="text-muted small mb-0">Admin dapat memperbarui informasi destinasi, fasilitas, dan foto secara real-time.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
