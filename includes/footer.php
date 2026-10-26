<?php
/**
 * BahariGIS — Public Footer Template
 */
?>
<!-- Footer Bahari -->
<footer class="footer-bahari">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <i class="bi bi-geo-alt-fill me-2 text-info"></i>Pantai<span class="text-info">Jumiang</span> <small class="text-white-50 fs-6">GIS</small>
                </div>
                <p class="text-secondary small" style="line-height: 1.8;">
                    Sistem Informasi Geografis Wisata Madura Pantai Jumiang interaktif berbasis web untuk memetakan panorama tebing karang, pantai pasir putih, hutan cemara, kuliner rujak petis Madura, dan fasilitas di Desa Tanjung, Pademawu, Pamekasan.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <span class="badge bg-secondary bg-opacity-25 text-light px-3 py-2 rounded-pill small">
                        <i class="bi bi-database me-1"></i> <?= getDBStatus() ?>
                    </span>
                </div>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="text-white fw-bold mb-3">Eksplorasi</h6>
                <a href="index.php" class="footer-link">Beranda</a>
                <a href="index.php#sectionPeta" class="footer-link">Peta Jumiang</a>
                <a href="daftar-wisata.php" class="footer-link">Katalog Spot</a>
                <a href="tentang.php" class="footer-link">Dokumentasi</a>
            </div>

            <div class="col-lg-3 col-md-3 col-6">
                <h6 class="text-white fw-bold mb-3">Kategori Spot</h6>
                <a href="daftar-wisata.php?kategori=1" class="footer-link">Tebing Karang & Panorama</a>
                <a href="daftar-wisata.php?kategori=2" class="footer-link">Pantai Pasir Putih</a>
                <a href="daftar-wisata.php?kategori=3" class="footer-link">Hutan Cemara Udang</a>
                <a href="daftar-wisata.php?kategori=4" class="footer-link">Spot Foto & Sunset</a>
                <a href="daftar-wisata.php?kategori=5" class="footer-link">Kuliner Khas Madura</a>
            </div>

            <div class="col-lg-3 col-md-12">
                <h6 class="text-white fw-bold mb-3">Teknologi Web GIS</h6>
                <p class="text-secondary small mb-2">Dibangun untuk Tugas Semester 5 — Manajemen Web GIS.</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-dark border border-secondary text-info">Leaflet.js 1.9</span>
                    <span class="badge bg-dark border border-secondary text-light">OpenStreetMap</span>
                    <span class="badge bg-dark border border-secondary text-warning">PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?></span>
                    <span class="badge bg-dark border border-secondary text-primary">MySQL / SQLite</span>
                    <span class="badge bg-dark border border-secondary text-danger">Bootstrap 5</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>
                &copy; <?= date('Y') ?> <strong>Wisata Madura Pantai Jumiang GIS</strong>. Hak Cipta Dilindungi.
            </div>
            <div class="text-secondary small">
                Pokdarwis Dusun Jumiang, Desa Tanjung, Pademawu, Pamekasan, Madura
            </div>
        </div>
    </div>
</footer>

<!-- Modal Detail Wisata (KF-04) -->
<div class="modal fade" id="detailWisataModal" tabindex="-1" aria-labelledby="detailWisataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header modal-wisata-header border-0 py-3">
                <h5 class="modal-title font-serif text-white d-flex align-items-center gap-2" id="detailWisataModalLabel">
                    <i class="bi bi-geo-alt-fill text-warning"></i> Detail Spot Wisata Pantai Jumiang Madura
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailWisataContent">
                <!-- Konten dinamis dimuat via app.js -->
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation (Responsive Bar) -->
<div class="mobile-bottom-nav">
    <div class="container-fluid d-flex justify-content-around">
        <a href="index.php" class="mobile-bottom-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i>
            <span>Beranda</span>
        </a>
        <a href="index.php#sectionPeta" class="mobile-bottom-nav-item">
            <i class="bi bi-map"></i>
            <span>Peta</span>
        </a>
        <a href="daftar-wisata.php" class="mobile-bottom-nav-item <?= $currentPage === 'daftar-wisata.php' ? 'active' : '' ?>">
            <i class="bi bi-compass"></i>
            <span>Wisata</span>
        </a>
        <a href="tentang.php" class="mobile-bottom-nav-item <?= $currentPage === 'tentang.php' ? 'active' : '' ?>">
            <i class="bi bi-info-circle"></i>
            <span>Tentang</span>
        </a>
        <a href="admin/index.php" class="mobile-bottom-nav-item">
            <i class="bi bi-person-lock"></i>
            <span>Admin</span>
        </a>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>

</body>
</html>
