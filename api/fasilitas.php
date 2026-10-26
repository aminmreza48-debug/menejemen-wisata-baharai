<?php
/**
 * BahariGIS — API Fasilitas Wisata (KF-09, Menu Admin)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    try {
        $stmt = $db->query("
            SELECT f.*, COUNT(wf.id_wisata) AS total_wisata
            FROM fasilitas f
            LEFT JOIN wisata_fasilitas wf ON f.id_fasilitas = wf.id_fasilitas
            GROUP BY f.id_fasilitas, f.nama_fasilitas, f.deskripsi
            ORDER BY f.nama_fasilitas ASC
        ");
        $facilities = $stmt->fetchAll();
        jsonResponse(['status' => true, 'data' => $facilities]);
    } catch (Throwable $e) {
        jsonResponse(['status' => false, 'message' => 'Gagal memuat fasilitas: ' . $e->getMessage()], 500);
    }
}

if ($method === 'POST') {
    checkAdminAuth();

    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
        $deskripsi      = trim($_POST['deskripsi'] ?? '');

        if (empty($nama_fasilitas)) {
            jsonResponse(['status' => false, 'message' => 'Nama fasilitas wajib diisi!'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO fasilitas (nama_fasilitas, deskripsi) VALUES (?, ?)");
            $stmt->execute([$nama_fasilitas, $deskripsi]);
            jsonResponse(['status' => true, 'message' => 'Fasilitas berhasil ditambahkan!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal menambah fasilitas: ' . $e->getMessage()], 500);
        }

    } elseif ($action === 'update') {
        $id_fasilitas   = (int)($_POST['id_fasilitas'] ?? 0);
        $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
        $deskripsi      = trim($_POST['deskripsi'] ?? '');

        if ($id_fasilitas <= 0 || empty($nama_fasilitas)) {
            jsonResponse(['status' => false, 'message' => 'ID dan nama fasilitas wajib diisi!'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE fasilitas SET nama_fasilitas = ?, deskripsi = ? WHERE id_fasilitas = ?");
            $stmt->execute([$nama_fasilitas, $deskripsi, $id_fasilitas]);
            jsonResponse(['status' => true, 'message' => 'Fasilitas berhasil diperbarui!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal mengubah fasilitas: ' . $e->getMessage()], 500);
        }

    } elseif ($action === 'delete') {
        $id_fasilitas = (int)($_POST['id_fasilitas'] ?? 0);
        if ($id_fasilitas <= 0) {
            jsonResponse(['status' => false, 'message' => 'ID fasilitas tidak valid!'], 400);
        }

        try {
            $stmt = $db->prepare("DELETE FROM fasilitas WHERE id_fasilitas = ?");
            $stmt->execute([$id_fasilitas]);
            jsonResponse(['status' => true, 'message' => 'Fasilitas berhasil dihapus!']);
        } catch (Throwable $e) {
            jsonResponse(['status' => false, 'message' => 'Gagal menghapus fasilitas: ' . $e->getMessage()], 500);
        }
    }
}
