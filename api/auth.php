<?php
/**
 * BahariGIS — API Autentikasi Pengguna & Admin (KF-07, BR-06, BR-07, BR-13)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['status' => false, 'message' => 'Metode request tidak valid'], 405);
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        jsonResponse(['status' => false, 'message' => 'Email dan password wajib diisi!'], 400);
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(['status' => false, 'message' => 'Akun tidak ditemukan dengan email tersebut.'], 401);
        }

        // BR-07: Verifikasi hash password
        if (!password_verify($password, $user['password'])) {
            jsonResponse(['status' => false, 'message' => 'Password yang Anda masukkan salah.'], 401);
        }

        // Set session
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        jsonResponse([
            'status' => true,
            'message' => 'Login berhasil! Selamat datang, ' . $user['nama'],
            'user' => [
                'id_user' => $user['id_user'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role']
            ],
            'redirect' => 'admin/index.php'
        ]);

    } catch (Throwable $e) {
        jsonResponse(['status' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
    }

} elseif ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    jsonResponse(['status' => true, 'message' => 'Berhasil logout. Sampai jumpa kembali!', 'redirect' => '../index.php']);

} elseif ($action === 'check') {
    $isLoggedIn = !empty($_SESSION['user_id']);
    jsonResponse([
        'status' => true,
        'logged_in' => $isLoggedIn,
        'user' => $isLoggedIn ? [
            'id_user' => $_SESSION['user_id'],
            'nama' => $_SESSION['nama'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ] : null
    ]);

} else {
    jsonResponse(['status' => false, 'message' => 'Action tidak dikenali'], 400);
}
