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
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="font-serif fw-bold text-ocean-primary mb-1">Selamat Datang, <?= htmlspecialchars($userName) ?>!</h3>
        <p class="text-muted small mb-0">Kelola titik koordinat spasial, fasilitas, dan galeri kawasan Wisata Pantai Jumiang Madura.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="wisata.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Kelola Wisata
        </a>
        <a href="../index.php" target="_blank" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-eye me-1"></i> Tinjau Publik
        </a>
    </div>
</div>

<!-- KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Total Spot & Fasilitas</small>
                <h3 class="fw-bold text-ocean-primary my-1"><?= $totalWisata ?></h3>
                <span class="badge bg-success bg-opacity-10 text-success small"><?= $totalAktif ?> Aktif</span>
                <span class="badge bg-warning bg-opacity-10 text-warning small"><?= $totalDraft ?> Draft</span>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-pin-map-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Kategori Wisata</small>
                <h3 class="fw-bold text-ocean-primary my-1"><?= $totalKategori ?></h3>
                <small class="text-muted">Jenis kegiatan bahari</small>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-tags-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Master Fasilitas</small>
                <h3 class="fw-bold text-ocean-primary my-1"><?= $totalFasilitas ?></h3>
                <small class="text-muted">Fasilitas terdaftar</small>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Galeri Foto</small>
                <h3 class="fw-bold text-ocean-primary my-1"><?= $totalFoto ?></h3>
                <small class="text-muted">Dokumentasi tersimpan</small>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-images"></i>
            </div>
        </div>
    </div>
</div>

<!-- Peta Sebaran Admin & Tabel Data Terbaru -->
<div class="row g-4 mb-4">
    <!-- Peta Ringkasan Admin -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-ocean-primary mb-0">
                    <i class="bi bi-map me-1"></i> Persebaran Spasial Wisata Terdaftar
                </h6>
                <span class="badge bg-light text-dark border small"><?= $totalWisata ?> Titik</span>
            </div>
            <div id="adminOverviewMap" style="height: 380px; border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1;"></div>
        </div>
    </div>

    <!-- Data Wisata Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-ocean-primary mb-0">
                    <i class="bi bi-clock-history me-1"></i> Destinasi Terbaru Ditambahkan
                </h6>
                <a href="wisata.php" class="small text-decoration-none">Kelola Semua &raquo;</a>
            </div>

            <div class="table-responsive">
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

    const map = L.map('adminOverviewMap').setView([-2.5489, 118.0149], 4);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

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
