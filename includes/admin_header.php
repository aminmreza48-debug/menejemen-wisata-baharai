<?php
/**
 * BahariGIS — Admin Header Template
 */
require_once __DIR__ . '/../config/db.php';
checkAdminAuth();

$currentPage = basename($_SERVER['PHP_SELF']);
$userName = $_SESSION['nama'] ?? 'Admin';
$userRole = $_SESSION['role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Admin Wisata Madura Pantai Jumiang</title>

    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS 1.9.4 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Favicon Pantai Jumiang -->
    <link rel="icon" type="image/jpeg" href="../assets/img/logo_pantai_jumiang.jpg">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Sidebar Navigasi Admin -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header d-flex align-items-center">
        <img src="../assets/img/logo_pantai_jumiang.png" alt="Logo Wisata Pantai Jumiang" class="brand-logo-img me-2">
        <div class="flex-grow-1">
            <h5 class="mb-0 text-white font-serif fw-bold">Pantai<span class="text-info">Jumiang</span></h5>
            <small class="text-white-50 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <i class="bi bi-geo-alt-fill text-warning"></i> Pademawu, Pamekasan
            </small>
        </div>
        <button type="button" class="btn-close btn-close-white d-lg-none ms-2" id="btnCloseSidebar" aria-label="Tutup"></button>
    </div>

    <div class="admin-sidebar-nav">
        <a href="index.php" class="admin-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="wisata.php" class="admin-nav-item <?= $currentPage === 'wisata.php' ? 'active' : '' ?>">
            <i class="bi bi-map-fill"></i>
            <span>Kelola Wisata</span>
        </a>
        <a href="kategori.php" class="admin-nav-item <?= $currentPage === 'kategori.php' ? 'active' : '' ?>">
            <i class="bi bi-tags-fill"></i>
            <span>Kategori Wisata</span>
        </a>
        <a href="fasilitas.php" class="admin-nav-item <?= $currentPage === 'fasilitas.php' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span>Master Fasilitas</span>
        </a>

        <div class="px-4 py-2 mt-3 text-uppercase text-white-50 small fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">
            Akses Cepat
        </div>
        <a href="../index.php" target="_blank" class="admin-nav-item">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>Lihat Web Publik</span>
        </a>
        <a href="logout.php" class="admin-nav-item text-danger">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar (Logout)</span>
        </a>
    </div>
</aside>

<!-- Konten Utama Admin -->
<div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light d-lg-none border-0 p-1 rounded-3" id="btnToggleSidebar" aria-label="Buka Menu">
                <i class="bi bi-list fs-3 text-ocean-primary"></i>
            </button>
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <img src="../assets/img/logo_pantai_jumiang.png" alt="Logo" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                <span class="fw-bold font-serif text-ocean-primary lh-1" style="font-size: 1.05rem;">Pantai<span class="text-ocean-accent">Jumiang</span></span>
            </div>
            <h5 class="mb-0 fw-bold font-serif text-ocean-primary d-none d-lg-block">
                <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Wisata Pantai Jumiang' ?>
            </h5>
        </div>

        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1 rounded-pill small d-none d-sm-inline-flex align-items-center" style="font-size: 0.75rem;">
                <i class="bi bi-database me-1 text-primary"></i> <?= strtoupper(getDBDriver()) ?>
            </span>

            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 34px; height: 34px; font-size: 0.9rem;" title="<?= htmlspecialchars($userName) ?>">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </div>
                <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                    <div class="fw-bold small"><?= htmlspecialchars($userName) ?></div>
                    <small class="text-muted text-capitalize"><?= htmlspecialchars($userRole) ?></small>
                </div>
            </div>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 34px; height: 34px;" title="Keluar">
                <i class="bi bi-power"></i>
            </a>
        </div>
    </header>

    <!-- Konten Halaman -->
    <main class="admin-content">
