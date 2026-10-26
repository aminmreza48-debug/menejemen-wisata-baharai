<?php
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: daftar-wisata.php');
    exit;
}

$db = getDB();

try {
    $stmt = $db->prepare("
        SELECT w.*, k.nama_kategori, k.deskripsi AS deskripsi_kategori, u.nama AS nama_pengelola
        FROM wisata w
        JOIN kategori_wisata k ON w.id_kategori = k.id_kategori
        JOIN users u ON w.id_user = u.id_user
        WHERE w.id_wisata = ?
    ");
    $stmt->execute([$id]);
    $wisata = $stmt->fetch();

    if (!$wisata) {
        die("Destinasi wisata tidak ditemukan. <a href='daftar-wisata.php'>Kembali ke katalog</a>");
    }

    // Ambil fasilitas
    $stmtFas = $db->prepare("
        SELECT f.nama_fasilitas, f.deskripsi, wf.keterangan
        FROM wisata_fasilitas wf
        JOIN fasilitas f ON wf.id_fasilitas = f.id_fasilitas
        WHERE wf.id_wisata = ?
    ");
    $stmtFas->execute([$id]);
    $fasilitas = $stmtFas->fetchAll();

    // Ambil foto
    $stmtFoto = $db->prepare("SELECT * FROM foto_wisata WHERE id_wisata = ? ORDER BY id_foto ASC");
    $stmtFoto->execute([$id]);
    $fotoList = $stmtFoto->fetchAll();

    // Wisata terkait (kategori yang sama)
    $stmtRelated = $db->prepare("
        SELECT id_wisata, nama_wisata, harga_tiket,
            (SELECT nama_file FROM foto_wisata WHERE id_wisata = w.id_wisata LIMIT 1) AS foto
        FROM wisata w
        WHERE id_kategori = ? AND id_wisata != ? AND status = 'aktif'
        LIMIT 3
    ");
    $stmtRelated->execute([$wisata['id_kategori'], $id]);
    $relatedWisata = $stmtRelated->fetchAll();

} catch (Throwable $e) {
    die("Kesalahan database: " . htmlspecialchars($e->getMessage()));
}

$pageTitle = $wisata['nama_wisata'];
$gmapsUrl = "https://www.google.com/maps/dir/?api=1&destination=" . $wisata['latitude'] . "," . $wisata['longitude'];

include __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="daftar-wisata.php" class="text-decoration-none">Daftar Wisata</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($wisata['nama_wisata']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Kolom Kiri: Galeri Foto & Deskripsi -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <!-- Foto Carousel -->
                <div id="detailCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php if (!empty($fotoList)): ?>
                            <?php foreach ($fotoList as $idx => $f): ?>
                                <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?> gallery-carousel-item" style="height: 440px;">
                                    <img src="<?= htmlspecialchars($f['nama_file']) ?>" class="d-block w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($f['keterangan'] ?: $wisata['nama_wisata']) ?>">
                                    <?php if (!empty($f['keterangan'])): ?>
                                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-pill px-4 py-1" style="max-width: 80%; margin: 0 auto;">
                                            <small><?= htmlspecialchars($f['keterangan']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active gallery-carousel-item" style="height: 440px;">
                                <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80" class="d-block w-100 h-100" style="object-fit: cover;" alt="Placeholder">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($fotoList) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#detailCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#detailCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                            <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($wisata['nama_kategori']) ?>
                        </span>
                        <h3 class="text-success fw-bold mb-0">
                            <?= formatRupiah((float)$wisata['harga_tiket']) ?>
                        </h3>
                    </div>

                    <h1 class="font-serif fw-bold text-ocean-primary mb-2"><?= htmlspecialchars($wisata['nama_wisata']) ?></h1>
                    <p class="text-muted mb-4">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($wisata['alamat']) ?>
                    </p>

                    <hr class="my-4 text-muted">

                    <h5 class="fw-bold text-ocean-primary mb-3">
                        <i class="bi bi-card-text me-2"></i>Tentang Objek Wisata
                    </h5>
                    <p class="text-secondary" style="line-height: 1.9; font-size: 1.02rem;">
                        <?= nl2br(htmlspecialchars($wisata['deskripsi'])) ?>
                    </p>

                    <h5 class="fw-bold text-ocean-primary mt-5 mb-3">
                        <i class="bi bi-box-seam me-2"></i>Fasilitas Wisata Bahari
                    </h5>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php if (!empty($fasilitas)): ?>
                            <?php foreach ($fasilitas as $fas): ?>
                                <span class="badge-fasilitas" title="<?= htmlspecialchars($fas['keterangan'] ?: $fas['deskripsi']) ?>">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span><?= htmlspecialchars($fas['nama_fasilitas']) ?></span>
                                    <?php if (!empty($fas['keterangan'])): ?>
                                        <small class="text-muted">(<?= htmlspecialchars($fas['keterangan']) ?>)</small>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">Belum ada data fasilitas yang ditambahkan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Mini Peta, Navigasi & Info Spasial -->
        <div class="col-lg-4">
            <!-- Peta Lokasi Mini -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold text-ocean-primary mb-3">
                    <i class="bi bi-pin-map-fill text-danger me-2"></i>Lokasi Geografis (Peta)
                </h6>
                <div id="detailMiniMap" style="height: 250px; border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1;"></div>

                <div class="mt-3">
                    <table class="table table-sm table-borderless small mb-3">
                        <tr>
                            <td class="text-muted">Latitude:</td>
                            <td class="fw-semibold text-end"><code><?= $wisata['latitude'] ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Longitude:</td>
                            <td class="fw-semibold text-end"><code><?= $wisata['longitude'] ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jam Buka:</td>
                            <td class="fw-semibold text-end"><?= htmlspecialchars($wisata['jam_operasional']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pengelola:</td>
                            <td class="fw-semibold text-end"><?= htmlspecialchars($wisata['nama_pengelola']) ?></td>
                        </tr>
                    </table>

                    <a href="<?= $gmapsUrl ?>" target="_blank" rel="noopener" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm">
                        <i class="bi bi-sign-turn-right-fill me-1"></i> Buka Rute Google Maps
                    </a>
                </div>
            </div>

            <!-- Rekomendasi Terkait -->
            <?php if (!empty($relatedWisata)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold text-ocean-primary mb-3">Wisata Bahari Terkait</h6>
                    <?php foreach ($relatedWisata as $rel): 
                        $relFoto = $rel['foto'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80';
                    ?>
                        <a href="detail-wisata.php?id=<?= $rel['id_wisata'] ?>" class="d-flex align-items-center gap-3 text-decoration-none text-dark mb-3 p-2 rounded-3 hover-bg-light">
                            <img src="<?= htmlspecialchars($relFoto) ?>" class="rounded-3" style="width: 65px; height: 65px; object-fit: cover;" alt="<?= htmlspecialchars($rel['nama_wisata']) ?>">
                            <div>
                                <h6 class="mb-1 fw-bold font-serif small"><?= htmlspecialchars($rel['nama_wisata']) ?></h6>
                                <small class="text-success fw-bold"><?= formatRupiah((float)$rel['harga_tiket']) ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const miniMapEl = document.getElementById('detailMiniMap');
    if (miniMapEl) {
        const lat = <?= (float)$wisata['latitude'] ?>;
        const lng = <?= (float)$wisata['longitude'] ?>;
        const miniMap = L.map('detailMiniMap', {
            center: [lat, lng],
            zoom: 12,
            zoomControl: true,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(miniMap);

        const pinIcon = L.divIcon({
            className: 'custom-ocean-pin-container',
            html: '<div class="custom-ocean-pin"><div class="custom-ocean-pin-inner"><i class="bi bi-water"></i></div></div>',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -34]
        });

        L.marker([lat, lng], { icon: pinIcon })
            .addTo(miniMap)
            .bindPopup('<b><?= htmlspecialchars(addslashes($wisata['nama_wisata'])) ?></b>')
            .openPopup();
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
