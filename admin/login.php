<?php
/**
 * BahariGIS — Halaman Login Admin (KF-07, BR-06, BR-07)
 */
require_once __DIR__ . '/../config/db.php';

// Jika sudah login, langsung ke dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errorMsg = 'Email dan password wajib diisi!';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // BR-07: Verifikasi hash password
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                header('Location: index.php');
                exit;
            } else {
                $errorMsg = 'Email atau password yang Anda masukkan salah!';
            }
        } catch (Throwable $e) {
            $errorMsg = 'Kesalahan database: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator — Wisata Pantai Jumiang</title>
    <link rel="icon" type="image/jpeg" href="../assets/img/logo_pantai_jumiang.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: 
                linear-gradient(135deg, rgba(4, 28, 50, 0.65) 0%, rgba(8, 38, 66, 0.42) 50%, rgba(4, 28, 50, 0.78) 100%),
                url('../assets/img/pantai_jumiang_hero.jpg') center center / cover no-repeat fixed;
            padding: 24px 16px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px rgba(4, 28, 50, 0.35);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            position: relative;
            background: 
                linear-gradient(180deg, rgba(4, 28, 50, 0.85) 0%, rgba(15, 76, 129, 0.92) 100%),
                url('../assets/img/pantai_jumiang_hero.jpg') center top / cover no-repeat;
            padding: 2.2rem 2rem 1.8rem;
            color: #fff;
            text-align: center;
        }
        .login-logo {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            background: #ffffff;
            transition: transform 0.25s ease;
        }
        .login-logo:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <img src="../assets/img/logo_pantai_jumiang.png" alt="Logo Wisata Pantai Jumiang" class="login-logo mx-auto mb-2 d-block">
        <h4 class="font-serif fw-bold mb-1 text-white">Masuk Panel Admin</h4>
        <p class="small text-white-50 mb-0 d-inline-flex align-items-center gap-1 bg-dark bg-opacity-25 px-3 py-1 rounded-pill" style="font-size: 0.78rem;">
            <i class="bi bi-geo-alt-fill text-warning"></i> Pantai Jumiang — Pademawu, Pamekasan
        </p>
    </div>

    <div class="p-4 p-md-5">
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="inputEmail" class="form-label small fw-semibold text-muted">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" id="inputEmail" class="form-control border-start-0 bg-light" 
                           placeholder="admin@baharigis.id" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="inputPassword" class="form-label small fw-semibold text-muted">Kata Sandi (Password)</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                    <input type="password" name="password" id="inputPassword" class="form-control border-start-0 bg-light" 
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
            </button>
        </form>

        <!-- Akun Demo Cepat Helper -->
        <div class="p-3 bg-light rounded-3 border text-center small mb-3">
            <div class="fw-bold text-ocean-primary mb-1">
                <i class="bi bi-info-circle me-1"></i> Akun Pengujian Default:
            </div>
            <div class="text-muted">Email: <code>admin@baharigis.id</code></div>
            <div class="text-muted">Password: <code>admin123</code></div>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2 py-1 px-3" onclick="fillDemoCredentials()">
                Gunakan Akun Demo
            </button>
        </div>

        <div class="text-center">
            <a href="../index.php" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda Publik
            </a>
        </div>
    </div>
</div>

<script>
function fillDemoCredentials() {
    document.getElementById('inputEmail').value = 'admin@baharigis.id';
    document.getElementById('inputPassword').value = 'admin123';
}
</script>

</body>
</html>
