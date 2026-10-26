<?php
/**
 * BahariGIS — API Kategori Wisata (KF-03, Menu Admin)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    try {
        $stmt = $db->query("
            SELECT k.*, COUNT(w.id_wisata) AS total_wisata
            FROM kategori_wisata k
            LEFT JOIN wisata w ON k.id_kategori = w.id_kategori
            GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi
            ORDER BY k.id_kategori ASC
        ");
        $categories = $stmt->fetchAll();
        jsonResponse(['status' => true, 'data' => $categories]);
    } catch (Throwable $e) {
        jsonResponse(['status' => false, 'message' => 'Gagal memuat kategori: ' . $e->getMessage()], 500);
    }
}

if ($method === 'POST') {
    checkAdminAuth();

    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');
        $deskripsi     = trim($_POST['deskripsi'] ?? '');

        if (empty($nama_kategori)) {
            jsonResponse(['status' => false, 'message' => 'Nama kategori wajib diisi!'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO kategori_wisata (nama_kategori, deskripsi) VALUES (?, ?)");
            $stmt->execute([$nama_kategori, $deskripsi]);
            jsonResponse(['status' => true, 'message' => 'Kategori berhasil ditambahkan!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal menambah kategori: ' . $e->getMessage()], 500);
        }

    } elseif ($action === 'update') {
        $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');
        $deskripsi     = trim($_POST['deskripsi'] ?? '');

        if ($id_kategori <= 0 || empty($nama_kategori)) {
            jsonResponse(['status' => false, 'message' => 'ID dan nama kategori wajib diisi!'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE kategori_wisata SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
            $stmt->execute([$nama_kategori, $deskripsi, $id_kategori]);
            jsonResponse(['status' => true, 'message' => 'Kategori berhasil diperbarui!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal mengubah kategori: ' . $e->getMessage()], 500);
        }

    } elseif ($action === 'delete') {
        $id_kategori = (int)($_POST['id_kategori'] ?? 0);
        if ($id_kategori <= 0) {
            jsonResponse(['status' => false, 'message' => 'ID kategori tidak valid!'], 400);
        }

        // Cek apakah kategori sedang digunakan objek wisata
        $check = $db->prepare("SELECT COUNT(*) AS total FROM wisata WHERE id_kategori = ?");
        $check->execute([$id_kategori]);
        $used = $check->fetch()['total'] ?? 0;

        if ($used > 0) {
            jsonResponse(['status' => false, 'message' => "Kategori tidak dapat dihapus karena masih digunakan oleh $used objek wisata."], 400);
        }

        try {
            $stmt = $db->prepare("DELETE FROM kategori_wisata WHERE id_kategori = ?");
            $stmt->execute([$id_kategori]);
            jsonResponse(['status' => true, 'message' => 'Kategori berhasil dihapus!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal menghapus kategori: ' . $e->getMessage()], 500);
        }
    }
}
