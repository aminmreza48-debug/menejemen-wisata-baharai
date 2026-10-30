<?php
$pageTitle = "Kelola Kategori Wisata Bahari";
require_once __DIR__ . '/../config/db.php';

$db = getDB();

$stmt = $db->query("
    SELECT k.*, COUNT(w.id_wisata) AS total_wisata
    FROM kategori_wisata k
    LEFT JOIN wisata w ON k.id_kategori = w.id_kategori
    GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi
    ORDER BY k.id_kategori ASC
");
$kategoriList = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-3 mb-md-4 gap-2">
    <div>
        <h3 class="font-serif fw-bold text-ocean-primary mb-1 admin-welcome-title">Kategori Wisata Bahari</h3>
        <p class="text-muted small mb-0 admin-welcome-sub">Kelola zona dan jenis kegiatan di kawasan Pantai Jumiang.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-3 btn-sm shadow-sm align-self-start align-self-sm-auto" onclick="window.openAddKategoriModal()">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <!-- Tampilan Khusus Mobile (d-md-none) -->
    <div class="d-md-none p-3">
        <?php if (empty($kategoriList)): ?>
            <div class="text-center py-4 text-muted small">Belum ada data kategori.</div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($kategoriList as $k): ?>
                    <div class="p-3 bg-light bg-opacity-25 rounded-3 border d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-bold text-ocean-primary small mb-1"><?= htmlspecialchars($k['nama_kategori']) ?></div>
                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars($k['deskripsi'] ?: 'Tanpa deskripsi') ?></small>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.65rem;">
                                <?= $k['total_wisata'] ?> Destinasi
                            </span>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button type="button" onclick="window.editKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>', '<?= htmlspecialchars(addslashes($k['deskripsi'] ?? '')) ?>')" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" onclick="window.deleteKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>')" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tampilan Desktop: Tabel Lengkap (d-none d-md-block) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi Kategori</th>
                    <th>Jumlah Wisata</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategoriList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada data kategori.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kategoriList as $k): ?>
                        <tr>
                            <td><?= $k['id_kategori'] ?></td>
                            <td class="fw-bold text-ocean-primary"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($k['deskripsi'] ?: '-') ?></td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <?= $k['total_wisata'] ?> Destinasi
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" onclick="window.editKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>', '<?= htmlspecialchars(addslashes($k['deskripsi'] ?? '')) ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-2">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" onclick="window.deleteKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>')" class="btn btn-sm btn-outline-danger rounded-pill px-2">
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

<!-- Modal Form Kategori -->
<div class="modal fade" id="modalFormKategori" tabindex="-1" aria-labelledby="modalKategoriTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header modal-wisata-header border-0 py-3">
                <h5 class="modal-title font-serif text-white fw-bold" id="modalKategoriTitle">Tambah Kategori</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formKategori">
                <input type="hidden" name="action" id="inputKategoriAction" value="create">
                <input type="hidden" name="id_kategori" id="inputIdKategori" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="inputNamaKategori" class="form-control" placeholder="Contoh: Spot Diving & Coral Reef" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" id="inputDeskripsiKategori" class="form-control" rows="3" placeholder="Penjelasan tentang kategori kegiatan bahari ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
