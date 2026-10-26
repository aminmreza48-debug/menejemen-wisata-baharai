<?php
$pageTitle = "Kelola Data Wisata Bahari";
require_once __DIR__ . '/../config/db.php';

$db = getDB();

// Ambil master kategori
$stmtKat = $db->query("SELECT * FROM kategori_wisata ORDER BY nama_kategori ASC");
$kategoriList = $stmtKat->fetchAll();

// Ambil master fasilitas
$stmtFas = $db->query("SELECT * FROM fasilitas ORDER BY nama_fasilitas ASC");
$fasilitasList = $stmtFas->fetchAll();

// Filter list
$search = trim($_GET['search'] ?? '');
$kategoriFilter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT w.*, k.nama_kategori,
        (SELECT COUNT(*) FROM wisata_fasilitas wf WHERE wf.id_wisata = w.id_wisata) AS total_fasilitas,
        (SELECT COUNT(*) FROM foto_wisata fw WHERE fw.id_wisata = w.id_wisata) AS total_foto
    FROM wisata w
    JOIN kategori_wisata k ON w.id_kategori = k.id_kategori
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (w.nama_wisata LIKE ? OR w.alamat LIKE ?)";
    $st = "%{$search}%";
    $params[] = $st;
    $params[] = $st;
}

if ($kategoriFilter > 0) {
    $sql .= " AND w.id_kategori = ?";
    $params[] = $kategoriFilter;
}

if (!empty($statusFilter)) {
    $sql .= " AND w.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY w.id_wisata DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$wisataList = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<!-- Header & Action Button -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="font-serif fw-bold text-ocean-primary mb-1">Manajemen Data Wisata Bahari</h3>
        <p class="text-muted small mb-0">Tambah, ubah, perbarui koordinat peta, dan kelola foto maupun fasilitas (KF-08).</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="window.openAddWisataModal()">
        <i class="bi bi-plus-lg me-1"></i> Tambah Wisata Baru
    </button>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="wisata.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" 
                       placeholder="Cari nama atau alamat..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="kategori" class="form-select bg-light">
                <option value="0">Semua Kategori</option>
                <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= $kategoriFilter == $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select bg-light">
                <option value="">Semua Status</option>
                <option value="aktif" <?= $statusFilter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="nonaktif" <?= $statusFilter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold">Filter</button>
            <a href="wisata.php" class="btn btn-outline-secondary rounded-pill" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Table Data Wisata -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Objek Wisata</th>
                    <th>Kategori</th>
                    <th>Koordinat (Lat, Long)</th>
                    <th>Tiket Masuk</th>
                    <th>Relasi</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($wisataList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            Tidak ada data wisata bahari yang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($wisataList as $idx => $w): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($w['nama_wisata']) ?></div>
                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(mb_strimwidth($w['alamat'], 0, 38, '...')) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($w['nama_kategori']) ?></span>
                            </td>
                            <td>
                                <code><?= $w['latitude'] ?>, <?= $w['longitude'] ?></code>
                            </td>
                            <td>
                                <span class="fw-semibold text-success"><?= formatRupiah((float)$w['harga_tiket']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" title="Total Fasilitas Terhubung">
                                    <i class="bi bi-box-seam me-1"></i><?= $w['total_fasilitas'] ?>
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="Total Foto Galeri">
                                    <i class="bi bi-images me-1"></i><?= $w['total_foto'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $w['status'] === 'aktif' ? 'bg-success' : ($w['status'] === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                    <?= htmlspecialchars(ucfirst($w['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" onclick="window.editWisata(<?= $w['id_wisata'] ?>)" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Edit Wisata">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" onclick="window.deleteWisata(<?= $w['id_wisata'] ?>, '<?= htmlspecialchars(addslashes($w['nama_wisata'])) ?>')" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Hapus Wisata">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL FORM TAMBAH / EDIT WISATA (KF-08, KF-09, KF-10) -->
<div class="modal fade" id="modalFormWisata" tabindex="-1" aria-labelledby="modalWisataTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header modal-wisata-header border-0 py-3">
                <h5 class="modal-title font-serif text-white fw-bold" id="modalWisataTitle">Tambah Wisata Bahari Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formWisata">
                <input type="hidden" name="action" id="inputWisataAction" value="create">
                <input type="hidden" name="id_wisata" id="inputIdWisata" value="">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Kolom Data Dasar -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-ocean-primary mb-3">Informasi Utama Destinasi</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Objek Wisata <span class="text-danger">*</span></label>
                                <input type="text" name="nama_wisata" id="inputNamaWisata" class="form-control" placeholder="Contoh: Pantai Tanjung Tinggi" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori Wisata Bahari <span class="text-danger">*</span></label>
                                <select name="id_kategori" id="selectKategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategoriList as $k): ?>
                                        <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Alamat Lengkap / Wilayah Administratif <span class="text-danger">*</span></label>
                                <textarea name="alamat" id="inputAlamat" class="form-control" rows="2" placeholder="Desa, Kecamatan, Kabupaten, Provinsi" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi Wisata Bahari <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" id="inputDeskripsi" class="form-control" rows="4" placeholder="Jelaskan daya tarik, keindahan laut, sejarah singkat atau aktivitas..." required></textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Harga Tiket (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" step="1000" min="0" name="harga_tiket" id="inputHargaTiket" class="form-control" value="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Status Publikasi <span class="text-danger">*</span></label>
                                    <select name="status" id="selectStatus" class="form-select">
                                        <option value="aktif">Aktif (Dipublikasikan)</option>
                                        <option value="draft">Draft (Disembunyikan)</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Jam Operasional</label>
                                <input type="text" name="jam_operasional" id="inputJamOperasional" class="form-control" value="08:00 - 17:00 WIB">
                            </div>

                            <!-- Relasi Fasilitas (KF-09) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-block">Pilihan Fasilitas yang Tersedia (KF-09)</label>
                                <div class="p-3 border rounded-3 bg-light" style="max-height: 140px; overflow-y: auto;">
                                    <div class="row g-2">
                                        <?php foreach ($fasilitasList as $fas): ?>
                                            <div class="col-6">
                                                <div class="form-check small">
                                                    <input class="form-check-input check-fasilitas" type="checkbox" name="fasilitas[]" value="<?= $fas['id_fasilitas'] ?>" id="fas_<?= $fas['id_fasilitas'] ?>">
                                                    <label class="form-check-label" for="fas_<?= $fas['id_fasilitas'] ?>">
                                                        <?= htmlspecialchars($fas['nama_fasilitas']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Koordinat Spasial (Peta Picker) & Foto -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-ocean-primary mb-3">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Penetapan Koordinat Spasial (Peta Interaktif)
                            </h6>
                            <p class="text-muted small mb-2">
                                Klik pada peta di bawah ini atau geser pin marker untuk menentukan koordinat secara akurat (KF-05, KF-08).
                            </p>

                            <!-- Peta Picker -->
                            <div id="mapPicker"></div>
                            <div class="coordinate-helper d-flex justify-content-between">
                                <span><i class="bi bi-info-circle me-1"></i>Klik pada peta untuk menaruh marker</span>
                                <span class="text-primary fw-bold">Presisi: 7 Desimal</span>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Latitude <span class="text-danger">*</span></label>
                                    <input type="text" name="latitude" id="inputLatitude" class="form-control" placeholder="-2.5701389" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Longitude <span class="text-danger">*</span></label>
                                    <input type="text" name="longitude" id="inputLongitude" class="form-control" placeholder="107.7121667" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Pengelolaan Foto (KF-10) -->
                            <h6 class="fw-bold text-ocean-primary mb-2">Foto Destinasi (KF-10)</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tambah URL Foto Baru</label>
                                <input type="url" name="new_photo_url" class="form-control" placeholder="https://images.unsplash.com/...">
                                <small class="text-muted">Masukkan tautan URL foto beresolusi tinggi (Unsplash/Imgur/dsb).</small>
                            </div>

                            <div id="existingPhotosSection">
                                <label class="form-label small fw-semibold">Galeri Foto Tersimpan:</label>
                                <div id="existingPhotosContainer" class="p-2 border rounded-3 bg-light min-height-50">
                                    <small class="text-muted">Foto tersimpan akan ditampilkan di sini.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Data Wisata
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
