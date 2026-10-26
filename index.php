<?php
$pageTitle = "Beranda & Peta Wisata Interaktif";
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
        <span class="badge bg-info bg-opacity-25 text-white border border-info border-opacity-50 px-3 py-2 rounded-pill mb-3">
            <i class="bi bi-compass me-1"></i> Web Geographic Information System — Madura & Jawa Timur
        </span>
        <h1 class="hero-title">Jelajahi Pesona Wisata Bahari Madura & Jawa Timur</h1>
        <p class="hero-subtitle">
            Temukan keindahan Pantai Jumiang Pamekasan, panorama tebing karang Selat Madura, pulau eksotis, dan pesona pesisir Jawa Timur melalui peta spasial interaktif BahariGIS.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#sectionPeta" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow">
                <i class="bi bi-map-fill me-1"></i> Buka Peta Spasial
            </a>
            <a href="daftar-wisata.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-grid-fill me-1"></i> Jelajahi Semua Destinasi
            </a>
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
                       placeholder="Cari wisata... (misal: Pantai Jumiang, Pamekasan, Madura)">
            </div>

            <!-- Category Filter Pills -->
            <div class="col-lg-7">
                <div class="category-pills-container">
                    <button type="button" class="category-pill active" data-category="0">
                        <span>Semua Kategori</span>
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
                <i class="bi bi-pin-map-fill text-danger me-2"></i>Peta Persebaran Wisata Bahari
            </h3>
            <p class="text-muted small mb-0">Klik pada penanda (marker) untuk melihat foto, harga tiket, dan rute navigasi.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2" id="totalWisataBadge">
                <?= $totalWisata ?> Destinasi
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
            <button id="btnResetMap" class="map-control-btn" title="Fokuskan Kembali ke Seluruh Wilayah">
                <i class="bi bi-arrows-fullscreen"></i> Reset Tampilan
            </button>
        </div>

        <!-- Live Pulse Stats Indicator -->
        <div class="map-stats-badge">
            <div class="pulse-indicator"></div>
            <span>Peta Interaktif Spasial Aktif</span>
            <span id="mapLoadingIndicator" class="spinner-border spinner-border-sm text-primary ms-2" style="display: none;"></span>
        </div>
    </div>
</section>

<!-- Destination Cards Section (KF-04) -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="font-serif fw-bold text-ocean-primary mb-1">Daftar Destinasi Bahari</h3>
            <p class="text-muted small mb-0">Menampilkan destinasi yang sesuai dengan pencarian dan filter aktif di atas.</p>
        </div>
        <a href="daftar-wisata.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
            Lihat Semua di Katalog <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row" id="destinationCardsContainer">
        <!-- Rendered dynamically by app.js via fetch('api/wisata.php') -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat destinasi wisata bahari...</p>
        </div>
    </div>
</section>

<!-- Features Highlights -->
<section class="bg-ocean-soft py-5 mt-5 border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="font-serif fw-bold text-ocean-primary">Keunggulan Sistem BahariGIS</h3>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                Solusi digital terintegrasi untuk pemetaan potensi pariwisata laut dan pesisir Indonesia.
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
