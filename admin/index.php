<?php
$pageTitle = "Dashboard Ringkasan Sistem";
require_once __DIR__ . '/../config/db.php';

$db = getDB();

// Query KPI Ringkasan
$totalWisata = (int)$db->query("SELECT COUNT(*) FROM wisata")->fetchColumn();
$totalAktif = (int)$db->query("SELECT COUNT(*) FROM wisata WHERE status = 'aktif'")->fetchColumn();
$totalDraft = (int)$db->query("SELECT COUNT(*) FROM wisata WHERE status = 'draft'")->fetchColumn();
$totalKategori = (int)$db->query("SELECT COUNT(*) FROM kategori_wisata")->fetchColumn();
$totalFasilitas = (int)$db->query("SELECT COUNT(*) FROM fasilitas")->fetchColumn();
$totalFoto = (int)$db->query("SELECT COUNT(*) FROM foto_wisata")->fetchColumn();

// 5 Wisata Terbaru
$stmtRecent = $db->query("
    SELECT w.*, k.nama_kategori,
        (SELECT fw.nama_file FROM foto_wisata fw WHERE fw.id_wisata = w.id_wisata LIMIT 1) AS foto_utama
    FROM wisata w
    JOIN kategori_wisata k ON w.id_kategori = k.id_kategori
    ORDER BY w.id_wisata DESC
    LIMIT 6
");
$recentWisata = $stmtRecent->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<!-- Sambutan & Tombol Cepat -->
<div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-3 mb-md-4 gap-2">
    <div>
        <h3 class="font-serif fw-bold text-ocean-primary mb-1 admin-welcome-title">Selamat Datang, <?= htmlspecialchars($userName) ?>! 👋</h3>
        <p class="text-muted small mb-0 admin-welcome-sub">Kelola koordinat, fasilitas, & galeri Wisata Pantai Jumiang.</p>
    </div>
    <div class="d-flex gap-2 mt-1 mt-sm-0">
        <a href="wisata.php" class="btn btn-primary rounded-pill px-3 btn-sm shadow-sm flex-fill flex-sm-grow-0 text-center">
            <i class="bi bi-plus-circle me-1"></i> Tambah Wisata
        </a>
        <a href="../index.php" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 btn-sm flex-fill flex-sm-grow-0 text-center">
            <i class="bi bi-eye me-1"></i> Web Publik
        </a>
    </div>
</div>

<!-- KPI Stat Cards (2x2 Grid di HP, 4 Kolom di Desktop) -->
<div class="row g-2 g-md-3 mb-3 mb-md-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="stat-label">TOTAL SPOT</small>
                <h3 class="stat-value"><?= $totalWisata ?></h3>
                <div class="stat-badge-wrap d-flex flex-wrap gap-1">
                    <span class="badge bg-success bg-opacity-10 text-success"><?= $totalAktif ?> Aktif</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning"><?= $totalDraft ?> Draft</span>
                </div>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-pin-map-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="stat-label">KATEGORI</small>
                <h3 class="stat-value"><?= $totalKategori ?></h3>
                <small class="text-muted stat-sub">Jenis kegiatan</small>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-tags-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="stat-label">FASILITAS</small>
                <h3 class="stat-value"><?= $totalFasilitas ?></h3>
                <small class="text-muted stat-sub">Sarana umum</small>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="stat-label">GALERI FOTO</small>
                <h3 class="stat-value"><?= $totalFoto ?></h3>
                <small class="text-muted stat-sub">Dokumentasi</small>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-images"></i>
            </div>
        </div>
    </div>
</div>

<!-- Peta Sebaran Admin & Tabel Data Terbaru -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Peta Ringkasan Admin -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <h6 class="fw-bold text-ocean-primary mb-0 small">
                    <i class="bi bi-map me-1 text-danger"></i> Persebaran Spasial Terdaftar
                </h6>
                <span class="badge bg-light text-dark border small"><?= $totalWisata ?> Titik</span>
            </div>
            <div id="adminOverviewMap" style="height: 260px; width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1;"></div>
        </div>
    </div>

    <!-- Data Wisata Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <h6 class="fw-bold text-ocean-primary mb-0 small">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Destinasi Terbaru Ditambahkan
                </h6>
                <a href="wisata.php" class="small text-decoration-none">Kelola Semua &raquo;</a>
            </div>

            <!-- Tampilan Khusus Mobile: List Card Rapi & Sederhana (d-md-none) -->
            <div class="d-md-none">
                <?php if (empty($recentWisata)): ?>
                    <div class="text-center py-4 text-muted small">Belum ada data wisata.</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($recentWisata as $w): 
                            $foto = $w['foto_utama'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=150&q=80';
                            if (!preg_match('/^https?:\/\//i', $foto) && !str_starts_with($foto, '../')) {
                                $foto = '../' . ltrim($foto, '/');
                            }
                        ?>
                            <div class="p-2 bg-light bg-opacity-50 rounded-3 border d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars($foto) ?>" class="rounded-3 flex-shrink-0" style="width: 48px; height: 48px; object-fit: cover;" alt="Foto">
                                <div class="flex-grow-1 min-w-0" style="overflow: hidden;">
                                    <div class="fw-bold text-dark text-truncate small mb-1"><?= htmlspecialchars($w['nama_wisata']) ?></div>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-light text-dark border py-1 px-2" style="font-size: 0.65rem;"><?= htmlspecialchars($w['nama_kategori']) ?></span>
                                        <span class="badge <?= $w['status'] === 'aktif' ? 'bg-success bg-opacity-10 text-success' : ($w['status'] === 'draft' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-secondary') ?> py-1 px-2" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars(ucfirst($w['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="wisata.php?edit=<?= $w['id_wisata'] ?>" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 32px; height: 32px;" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tampilan Desktop: Tabel Lengkap (d-none d-md-block) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Wisata</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentWisata)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data wisata.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentWisata as $w): 
                                $foto = $w['foto_utama'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=150&q=80';
                                if (!preg_match('/^https?:\/\//i', $foto) && !str_starts_with($foto, '../')) {
                                    $foto = '../' . ltrim($foto, '/');
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= htmlspecialchars($foto) ?>" class="rounded-2" style="width: 42px; height: 42px; object-fit: cover;" alt="Foto">
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($w['nama_wisata']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($w['alamat'], 0, 30, '...')) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($w['nama_kategori']) ?></span></td>
                                    <td>
                                        <span class="badge <?= $w['status'] === 'aktif' ? 'bg-success' : ($w['status'] === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= htmlspecialchars(ucfirst($w['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="wisata.php?edit=<?= $w['id_wisata'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('adminOverviewMap');
    if (!mapEl) return;

    const map = L.map('adminOverviewMap').setView([-7.2332, 113.5490], 14);

    const satAdmin = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Citra Satelit Google'
    }).addTo(map);

    const osmAdmin = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    });

    L.control.layers({
        "Satelit": satAdmin,
        "Peta Jalan": osmAdmin
    }, null, { position: 'topright' }).addTo(map);

    fetch('../api/wisata.php')
        .then(res => res.json())
        .then(res => {
            if (res.status && res.data.length > 0) {
                const group = [];
                res.data.forEach(item => {
                    if (item.latitude && item.longitude) {
                        const m = L.circleMarker([item.latitude, item.longitude], {
                            radius: 7,
                            fillColor: item.status === 'aktif' ? '#0f4c81' : '#f59e0b',
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.85
                        }).addTo(map);

                        m.bindPopup(`<b>${item.nama_wisata}</b><br><small>${item.nama_kategori}</small><br>Lat: ${item.latitude}, Long: ${item.longitude}`);
                        group.push([item.latitude, item.longitude]);
                    }
                });
                if (group.length > 0) {
                    map.fitBounds(group, { padding: [20, 20] });
                }
            }
        });
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
