<?php
$pageTitle = "Katalog Spot & Fasilitas Wisata Pantai Jumiang Madura";
require_once __DIR__ . '/config/db.php';

$db = getDB();

// Parameter filter dari URL
$search = trim($_GET['search'] ?? '');
$kategoriId = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$sortBy = trim($_GET['sort'] ?? 'terbaru');

// Query data kategori
$stmtKat = $db->query("SELECT * FROM kategori_wisata ORDER BY nama_kategori ASC");
$kategoriList = $stmtKat->fetchAll();

// Bangun query data wisata
$sql = "
    SELECT w.*, k.nama_kategori,
        (SELECT fw.nama_file FROM foto_wisata fw WHERE fw.id_wisata = w.id_wisata LIMIT 1) AS foto_utama,
        (SELECT COUNT(*) FROM wisata_fasilitas wf WHERE wf.id_wisata = w.id_wisata) AS total_fasilitas
    FROM wisata w
    JOIN kategori_wisata k ON w.id_kategori = k.id_kategori
    WHERE w.status = 'aktif'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (w.nama_wisata LIKE ? OR w.alamat LIKE ? OR w.deskripsi LIKE ?)";
    $st = "%{$search}%";
    $params[] = $st;
    $params[] = $st;
    $params[] = $st;
}

if ($kategoriId > 0) {
    $sql .= " AND w.id_kategori = ?";
    $params[] = $kategoriId;
}

// Sorting
switch ($sortBy) {
    case 'termurah':
        $sql .= " ORDER BY w.harga_tiket ASC";
        break;
    case 'termahal':
        $sql .= " ORDER BY w.harga_tiket DESC";
        break;
    case 'nama_asc':
        $sql .= " ORDER BY w.nama_wisata ASC";
        break;
    case 'nama_desc':
        $sql .= " ORDER BY w.nama_wisata DESC";
        break;
    case 'terbaru':
    default:
        $sql .= " ORDER BY w.id_wisata DESC";
        break;
}

$stmtWisata = $db->prepare($sql);
$stmtWisata->execute($params);
$wisataList = $stmtWisata->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="bg-ocean-primary text-white py-5">
    <div class="container text-center">
        <h2 class="font-serif fw-bold mb-2">Katalog Spot & Fasilitas Wisata Pantai Jumiang</h2>
        <p class="text-white-50 mb-0">Eksplorasi seluruh spot panorama tebing, pasir putih, hutan cemara, kuliner rujak petis Madura, dan sarana kawasan Pantai Jumiang.</p>
    </div>
</section>

<!-- Filter & Search Controls Bar -->
<section class="container mt-4">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <form method="GET" action="daftar-wisata.php" class="row g-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Cari Kata Kunci</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 bg-light" 
                           placeholder="Nama wisata atau alamat..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Kategori Wisata</label>
                <select name="kategori" class="form-select bg-light">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= $kategoriId == $kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Urutkan</label>
                <select name="sort" class="form-select bg-light">
                    <option value="terbaru" <?= $sortBy == 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="termurah" <?= $sortBy == 'termurah' ? 'selected' : '' ?>>Tiket Termurah</option>
                    <option value="termahal" <?= $sortBy == 'termahal' ? 'selected' : '' ?>>Tiket Termahal</option>
                    <option value="nama_asc" <?= $sortBy == 'nama_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                    <option value="nama_desc" <?= $sortBy == 'nama_desc' ? 'selected' : '' ?>>Nama (Z-A)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
                <a href="daftar-wisata.php" class="btn btn-outline-secondary rounded-pill" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Info Jumlah Hasil -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">
            Menemukan <strong><?= count($wisataList) ?></strong> objek wisata bahari.
        </span>
        <a href="index.php#sectionPeta" class="btn btn-sm btn-outline-primary rounded-pill">
            <i class="bi bi-map me-1"></i> Buka dalam Tampilan Peta
        </a>
    </div>

    <!-- Cards Grid -->
    <div class="row">
        <?php if (empty($wisataList)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 border">
                    <i class="bi bi-compass text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 font-serif">Tidak Ada Destinasi Ditemukan</h5>
                    <p class="text-muted">Tidak ada data wisata yang sesuai dengan filter pencarian Anda.</p>
                    <a href="daftar-wisata.php" class="btn btn-primary rounded-pill px-4">
                        Lihat Semua Destinasi
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($wisataList as $w): 
                $foto = !empty($w['foto_utama']) ? $w['foto_utama'] : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80';
                $gmaps = "https://www.google.com/maps/dir/?api=1&destination=" . $w['latitude'] . "," . $w['longitude'];
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card-wisata">
                        <div class="card-wisata-img-wrapper">
                            <img src="<?= htmlspecialchars($foto) ?>" class="card-wisata-img" alt="<?= htmlspecialchars($w['nama_wisata']) ?>" loading="lazy">
                            <span class="card-wisata-category"><?= htmlspecialchars($w['nama_kategori']) ?></span>
                            <span class="card-wisata-price"><?= formatRupiah((float)$w['harga_tiket']) ?></span>
                        </div>
                        <div class="card-wisata-body">
                            <h5 class="card-wisata-title"><?= htmlspecialchars($w['nama_wisata']) ?></h5>
                            <div class="card-wisata-location">
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                <span><?= htmlspecialchars(mb_strimwidth($w['alamat'], 0, 48, '...')) ?></span>
                            </div>
                            <p class="card-wisata-desc"><?= htmlspecialchars($w['deskripsi']) ?></p>
                            <div class="card-wisata-footer">
                                <a href="detail-wisata.php?id=<?= $w['id_wisata'] ?>" class="btn-card-detail">
                                    <i class="bi bi-eye me-1"></i> Detail Lengkap
                                </a>
                                <button type="button" onclick="window.viewDetailWisata(<?= $w['id_wisata'] ?>)" class="btn-card-map" title="Buka Pop-up Detail Cepat">
                                    <i class="bi bi-window-fullscreen"></i>
                                </button>
                                <a href="<?= $gmaps ?>" target="_blank" rel="noopener" class="btn-card-map text-success" title="Petunjuk Arah Navigasi">
                                    <i class="bi bi-sign-turn-right-fill"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
