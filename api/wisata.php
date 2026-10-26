<?php
/**
 * BahariGIS — API Wisata Bahari
 * Menyediakan endpoint pencarian, filter, detail, dan pengelolaan CRUD (KF-01 s/d KF-06, KF-08, KF-09, KF-10, BR-01 s/d BR-15)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

// -------------------------------------------------------------
// GET: Ambil daftar wisata atau detail tunggal
// -------------------------------------------------------------
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // A. Detail Tunggal Wisata (KF-04, KF-05, KF-06)
    if ($id > 0) {
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
                jsonResponse(['status' => false, 'message' => 'Objek wisata tidak ditemukan.'], 404);
            }

            // Ambil Fasilitas Terkait (Relasi Many-to-Many)
            $stmtFasilitas = $db->prepare("
                SELECT f.id_fasilitas, f.nama_fasilitas, f.deskripsi, wf.keterangan
                FROM wisata_fasilitas wf
                JOIN fasilitas f ON wf.id_fasilitas = f.id_fasilitas
                WHERE wf.id_wisata = ?
                ORDER BY f.nama_fasilitas ASC
            ");
            $stmtFasilitas->execute([$id]);
            $wisata['fasilitas'] = $stmtFasilitas->fetchAll();

            // Ambil Foto Galeri
            $stmtFoto = $db->prepare("
                SELECT id_foto, nama_file, keterangan
                FROM foto_wisata
                WHERE id_wisata = ?
                ORDER BY id_foto ASC
            ");
            $stmtFoto->execute([$id]);
            $wisata['foto'] = $stmtFoto->fetchAll();

            // Format data pendukung
            $wisata['harga_tiket_format'] = formatRupiah((float)$wisata['harga_tiket']);
            $wisata['google_maps_url'] = "https://www.google.com/maps/dir/?api=1&destination=" . $wisata['latitude'] . "," . $wisata['longitude'];

            jsonResponse(['status' => true, 'data' => $wisata]);

        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Kesalahan saat mengambil data wisata: ' . $e->getMessage()], 500);
        }
    }

    // B. Daftar Wisata dengan Filter & Pencarian (KF-01, KF-02, KF-03, BR-10)
    try {
        $search   = trim($_GET['search'] ?? '');
        $kategori = isset($_GET['kategori']) && $_GET['kategori'] !== '' ? (int)$_GET['kategori'] : 0;
        $status   = trim($_GET['status'] ?? '');
        $isAdmin  = !empty($_SESSION['user_id']);

        $query = "
            SELECT w.*, k.nama_kategori,
                (SELECT fw.nama_file FROM foto_wisata fw WHERE fw.id_wisata = w.id_wisata LIMIT 1) AS foto_utama,
                (SELECT COUNT(*) FROM foto_wisata fw WHERE fw.id_wisata = w.id_wisata) AS total_foto
            FROM wisata w
            JOIN kategori_wisata k ON w.id_kategori = k.id_kategori
            WHERE 1=1
        ";
        $params = [];

        // Aturan BR-10: Publik hanya melihat wisata 'aktif'
        if (!$isAdmin) {
            $query .= " AND w.status = 'aktif'";
        } elseif (!empty($status)) {
            $query .= " AND w.status = ?";
            $params[] = $status;
        }

        // KF-02: Pencarian kata kunci
        if (!empty($search)) {
            $query .= " AND (w.nama_wisata LIKE ? OR w.alamat LIKE ? OR w.deskripsi LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // KF-03: Filter Kategori
        if ($kategori > 0) {
            $query .= " AND w.id_kategori = ?";
            $params[] = $kategori;
        }

        $query .= " ORDER BY w.id_wisata DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        // Tambahkan foto fallback & format mata uang
        foreach ($results as &$item) {
            if (empty($item['foto_utama'])) {
                $item['foto_utama'] = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80';
            }
            $item['harga_tiket_format'] = formatRupiah((float)$item['harga_tiket']);
            $item['latitude'] = (float)$item['latitude'];
            $item['longitude'] = (float)$item['longitude'];
            $item['google_maps_url'] = "https://www.google.com/maps/dir/?api=1&destination=" . $item['latitude'] . "," . $item['longitude'];
        }

        jsonResponse([
            'status' => true,
            'total' => count($results),
            'database_driver' => getDBDriver(),
            'data' => $results
        ]);

    } catch (Throwable $e) {
        jsonResponse(['status' => false, 'message' => 'Kesalahan saat mengambil daftar wisata: ' . $e->getMessage()], 500);
    }
}

// -------------------------------------------------------------
// POST: Tambah, Update, atau Hapus Wisata (Hanya Admin - BR-06)
// -------------------------------------------------------------
if ($method === 'POST') {
    checkAdminAuth();

    $action = $_POST['action'] ?? 'create';

    // 1. TAMBAH WISATA BARU (KF-08)
    if ($action === 'create') {
        $nama_wisata     = trim($_POST['nama_wisata'] ?? '');
        $id_kategori     = (int)($_POST['id_kategori'] ?? 0);
        $deskripsi       = trim($_POST['deskripsi'] ?? '');
        $alamat          = trim($_POST['alamat'] ?? '');
        $latitude        = trim($_POST['latitude'] ?? '');
        $longitude       = trim($_POST['longitude'] ?? '');
        $harga_tiket     = (float)($_POST['harga_tiket'] ?? 0);
        $jam_operasional = trim($_POST['jam_operasional'] ?? '08:00 - 17:00 WIB');
        $status          = trim($_POST['status'] ?? 'aktif');
        $fasilitas_ids   = $_POST['fasilitas'] ?? []; // Array id_fasilitas
        $foto_urls       = $_POST['foto_urls'] ?? []; // Array of photo URLs or single comma-separated

        // Validasi Aturan Bisnis
        if (empty($nama_wisata)) {
            jsonResponse(['status' => false, 'message' => 'Nama wisata wajib diisi! (BR-02)'], 400);
        }
        if ($id_kategori <= 0) {
            jsonResponse(['status' => false, 'message' => 'Kategori wisata wajib dipilih! (BR-03)'], 400);
        }
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            jsonResponse(['status' => false, 'message' => 'Koordinat Latitude dan Longitude harus berupa angka valid! (BR-04)'], 400);
        }
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            jsonResponse(['status' => false, 'message' => 'Koordinat di luar rentang valid! Latitude: -90 s/d 90, Longitude: -180 s/d 180 (BR-04)'], 400);
        }
        if ($harga_tiket < 0) {
            jsonResponse(['status' => false, 'message' => 'Harga tiket tidak boleh bernilai negatif! (BR-11)'], 400);
        }
        if ($status === 'aktif' && (empty($deskripsi) || empty($alamat))) {
            jsonResponse(['status' => false, 'message' => 'Data wisata berstatus aktif harus memiliki deskripsi dan alamat lengkap! (BR-05)'], 400);
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO wisata (id_kategori, nama_wisata, deskripsi, alamat, latitude, longitude, harga_tiket, jam_operasional, status, id_user, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $id_kategori,
                $nama_wisata,
                $deskripsi,
                $alamat,
                $lat,
                $lng,
                $harga_tiket,
                $jam_operasional,
                $status,
                $_SESSION['user_id']
            ]);

            $newId = (int)$db->lastInsertId();

            // Simpan Relasi Fasilitas (KF-09, BR-09)
            if (!empty($fasilitas_ids) && is_array($fasilitas_ids)) {
                $stmtFas = $db->prepare("INSERT INTO wisata_fasilitas (id_wisata, id_fasilitas, keterangan) VALUES (?, ?, ?)");
                foreach ($fasilitas_ids as $fid) {
                    $stmtFas->execute([$newId, (int)$fid, 'Tersedia di lokasi']);
                }
            }

            // Simpan Foto Wisata (KF-10, BR-08)
            $stmtFoto = $db->prepare("INSERT INTO foto_wisata (id_wisata, nama_file, keterangan) VALUES (?, ?, ?)");
            if (is_array($foto_urls)) {
                foreach ($foto_urls as $furl) {
                    $cleanUrl = trim($furl);
                    if (!empty($cleanUrl)) {
                        $stmtFoto->execute([$newId, $cleanUrl, 'Dokumentasi ' . $nama_wisata]);
                    }
                }
            } elseif (!empty($_POST['foto_utama_url'])) {
                $cleanUrl = trim($_POST['foto_utama_url']);
                $stmtFoto->execute([$newId, $cleanUrl, 'Foto Utama ' . $nama_wisata]);
            }

            $db->commit();
            jsonResponse(['status' => true, 'message' => 'Data wisata bahari berhasil ditambahkan!', 'id_wisata' => $newId]);

        } catch (Throwable $e) {
            $db->rollBack();
            jsonResponse(['status' => false, 'message' => 'Gagal menyimpan data wisata: ' . $e->getMessage()], 500);
        }

    // 2. EDIT / UPDATE WISATA (KF-08)
    } elseif ($action === 'update') {
        $id_wisata       = (int)($_POST['id_wisata'] ?? 0);
        $nama_wisata     = trim($_POST['nama_wisata'] ?? '');
        $id_kategori     = (int)($_POST['id_kategori'] ?? 0);
        $deskripsi       = trim($_POST['deskripsi'] ?? '');
        $alamat          = trim($_POST['alamat'] ?? '');
        $latitude        = trim($_POST['latitude'] ?? '');
        $longitude       = trim($_POST['longitude'] ?? '');
        $harga_tiket     = (float)($_POST['harga_tiket'] ?? 0);
        $jam_operasional = trim($_POST['jam_operasional'] ?? '08:00 - 17:00 WIB');
        $status          = trim($_POST['status'] ?? 'aktif');
        $fasilitas_ids   = $_POST['fasilitas'] ?? [];
        $foto_urls       = $_POST['foto_urls'] ?? [];

        if ($id_wisata <= 0) {
            jsonResponse(['status' => false, 'message' => 'ID wisata tidak valid!'], 400);
        }
        if (empty($nama_wisata)) {
            jsonResponse(['status' => false, 'message' => 'Nama wisata wajib diisi! (BR-02)'], 400);
        }
        if ($id_kategori <= 0) {
            jsonResponse(['status' => false, 'message' => 'Kategori wisata wajib dipilih! (BR-03)'], 400);
        }
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            jsonResponse(['status' => false, 'message' => 'Koordinat harus berupa angka valid! (BR-04)'], 400);
        }
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            jsonResponse(['status' => false, 'message' => 'Koordinat di luar rentang valid! (BR-04)'], 400);
        }
        if ($harga_tiket < 0) {
            jsonResponse(['status' => false, 'message' => 'Harga tiket tidak boleh negatif! (BR-11)'], 400);
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE wisata SET
                    id_kategori = ?,
                    nama_wisata = ?,
                    deskripsi = ?,
                    alamat = ?,
                    latitude = ?,
                    longitude = ?,
                    harga_tiket = ?,
                    jam_operasional = ?,
                    status = ?,
                    id_user = ?
                WHERE id_wisata = ?
            ");
            $stmt->execute([
                $id_kategori,
                $nama_wisata,
                $deskripsi,
                $alamat,
                $lat,
                $lng,
                $harga_tiket,
                $jam_operasional,
                $status,
                $_SESSION['user_id'],
                $id_wisata
            ]);

            // Sinkronisasi Relasi Fasilitas
            if (isset($_POST['fasilitas'])) {
                $db->prepare("DELETE FROM wisata_fasilitas WHERE id_wisata = ?")->execute([$id_wisata]);
                if (!empty($fasilitas_ids) && is_array($fasilitas_ids)) {
                    $stmtFas = $db->prepare("INSERT INTO wisata_fasilitas (id_wisata, id_fasilitas, keterangan) VALUES (?, ?, ?)");
                    foreach ($fasilitas_ids as $fid) {
                        $stmtFas->execute([$id_wisata, (int)$fid, 'Tersedia di lokasi']);
                    }
                }
            }

            // Tambah Foto Baru jika ada
            if (!empty($_POST['new_photo_url'])) {
                $pUrl = trim($_POST['new_photo_url']);
                $pKet = trim($_POST['new_photo_caption'] ?? 'Dokumentasi ' . $nama_wisata);
                $stmtPhoto = $db->prepare("INSERT INTO foto_wisata (id_wisata, nama_file, keterangan) VALUES (?, ?, ?)");
                $stmtPhoto->execute([$id_wisata, $pUrl, $pKet]);
            }

            $db->commit();
            jsonResponse(['status' => true, 'message' => 'Data wisata berhasil diperbarui!']);

        } catch (Throwable $e) {
            $db->rollBack();
            jsonResponse(['status' => false, 'message' => 'Gagal memperbarui data wisata: ' . $e->getMessage()], 500);
        }

    // 3. HAPUS WISATA (KF-08, BR-12)
    } elseif ($action === 'delete') {
        $id_wisata = (int)($_POST['id_wisata'] ?? 0);

        if ($id_wisata <= 0) {
            jsonResponse(['status' => false, 'message' => 'ID wisata tidak valid!'], 400);
        }

        try {
            $db->beginTransaction();

            // BR-12: Hapus relasi foto dan fasilitas terlebih dahulu untuk menjaga integritas data
            $db->prepare("DELETE FROM foto_wisata WHERE id_wisata = ?")->execute([$id_wisata]);
            $db->prepare("DELETE FROM wisata_fasilitas WHERE id_wisata = ?")->execute([$id_wisata]);

            // Hapus objek wisata utama
            $stmt = $db->prepare("DELETE FROM wisata WHERE id_wisata = ?");
            $stmt->execute([$id_wisata]);

            $db->commit();
            jsonResponse(['status' => true, 'message' => 'Data wisata berhasil dihapus secara bersih (Integritas terjaga).']);

        } catch (Throwable $e) {
            $db->rollBack();
            jsonResponse(['status' => false, 'message' => 'Gagal menghapus data wisata: ' . $e->getMessage()], 500);
        }

    // 4. HAPUS FOTO TUNGGAL DARI WISATA (KF-10)
    } elseif ($action === 'delete_photo') {
        $id_foto = (int)($_POST['id_foto'] ?? 0);
        if ($id_foto <= 0) {
            jsonResponse(['status' => false, 'message' => 'ID foto tidak valid!'], 400);
        }
        try {
            $stmt = $db->prepare("DELETE FROM foto_wisata WHERE id_foto = ?");
            $stmt->execute([$id_foto]);
            jsonResponse(['status' => true, 'message' => 'Foto berhasil dihapus!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal menghapus foto: ' . $e->getMessage()], 500);
        }

    } else {
        jsonResponse(['status' => false, 'message' => 'Action tidak dikenali!'], 400);
    }
}
