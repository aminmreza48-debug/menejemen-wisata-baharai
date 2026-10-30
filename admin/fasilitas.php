<?php
$pageTitle = "Kelola Master Fasilitas";
require_once __DIR__ . '/../config/db.php';

$db = getDB();

$stmt = $db->query("
    SELECT f.*, COUNT(wf.id_wisata) AS total_wisata
    FROM fasilitas f
    LEFT JOIN wisata_fasilitas wf ON f.id_fasilitas = wf.id_fasilitas
    GROUP BY f.id_fasilitas, f.nama_fasilitas, f.deskripsi
    ORDER BY f.nama_fasilitas ASC
");
$fasilitasList = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="font-serif fw-bold text-ocean-primary mb-1">Master Fasilitas Wisata</h3>
        <p class="text-muted small mb-0">Kelola daftar fasilitas yang dapat ditautkan ke berbagai objek wisata bahari (KF-09).</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="window.openAddFasilitasModal()">
        <i class="bi bi-plus-lg me-1"></i> Tambah Fasilitas
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <!-- Tampilan Khusus Mobile: List Card Rapi (d-md-none) -->
    <div class="d-md-none p-3">
        <?php if (empty($fasilitasList)): ?>
            <div class="text-center py-4 text-muted small">Belum ada master fasilitas.</div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($fasilitasList as $f): ?>
                    <div class="p-3 bg-light bg-opacity-50 rounded-3 border">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div class="fw-bold text-ocean-primary">
                                <i class="bi bi-check2-circle text-success me-1"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.70rem;">
                                <?= $f['total_wisata'] ?> Wisata
                            </span>
                        </div>
                        <?php if ($f['deskripsi']): ?>
                            <small class="text-muted d-block mb-2" style="font-size: 0.78rem;"><?= htmlspecialchars($f['deskripsi']) ?></small>
                        <?php endif; ?>
                        <div class="d-flex justify-content-end gap-1 pt-2 border-top">
                            <button type="button" onclick="window.editFasilitas(<?= $f['id_fasilitas'] ?>, '<?= htmlspecialchars(addslashes($f['nama_fasilitas'])) ?>', '<?= htmlspecialchars(addslashes($f['deskripsi'] ?? '')) ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <button type="button" onclick="window.deleteFasilitas(<?= $f['id_fasilitas'] ?>, '<?= htmlspecialchars(addslashes($f['nama_fasilitas'])) ?>')" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-trash me-1"></i> Hapus
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
                    <th>Nama Fasilitas</th>
                    <th>Deskripsi Fasilitas</th>
                    <th>Digunakan Pada</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fasilitasList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada master fasilitas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($fasilitasList as $f): ?>
                        <tr>
                            <td><?= $f['id_fasilitas'] ?></td>
                            <td class="fw-bold text-ocean-primary">
                                <i class="bi bi-check2-circle text-success me-1"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($f['deskripsi'] ?: '-') ?></td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <?= $f['total_wisata'] ?> Objek Wisata
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" onclick="window.editFasilitas(<?= $f['id_fasilitas'] ?>, '<?= htmlspecialchars(addslashes($f['nama_fasilitas'])) ?>', '<?= htmlspecialchars(addslashes($f['deskripsi'] ?? '')) ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-2">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" onclick="window.deleteFasilitas(<?= $f['id_fasilitas'] ?>, '<?= htmlspecialchars(addslashes($f['nama_fasilitas'])) ?>')" class="btn btn-sm btn-outline-danger rounded-pill px-2">
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

<!-- Modal Form Fasilitas -->
<div class="modal fade" id="modalFormFasilitas" tabindex="-1" aria-labelledby="modalFasilitasTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header modal-wisata-header border-0 py-3">
                <h5 class="modal-title font-serif text-white fw-bold" id="modalFasilitasTitle">Tambah Fasilitas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFasilitas">
                <input type="hidden" name="action" id="inputFasilitasAction" value="create">
                <input type="hidden" name="id_fasilitas" id="inputIdFasilitas" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_fasilitas" id="inputNamaFasilitas" class="form-control" placeholder="Contoh: Sewa Alat Snorkeling" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" id="inputDeskripsiFasilitas" class="form-control" rows="3" placeholder="Penjelasan tentang ketersediaan dan kegunaan fasilitas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Simpan Fasilitas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
