<?php
/**
 * BahariGIS — Public Header Template
 */
require_once __DIR__ . '/../config/db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>BahariGIS — Sistem Informasi Geografis Wisata Bahari</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="BahariGIS adalah sistem informasi geografis interaktif berbasis web untuk eksplorasi destinasi wisata bahari, pantai, pulau eksotis, spot snorkeling, dan diving di Indonesia.">
    <meta name="keywords" content="baharigis, webgis, wisata bahari, peta pantai, leaflet js, openstreetmap, gis kelautan">
    <meta name="author" content="Tim Manajemen Web GIS">

    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS 1.9.4 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Custom Oceanic Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar Bahari -->
<nav class="navbar navbar-expand-lg navbar-bahari sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span class="brand-icon">
                <i class="bi bi-water"></i>
            </span>
            <span>Bahari<span class="text-ocean-accent">GIS</span></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <i class="bi bi-list fs-2 text-ocean-primary"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#sectionPeta">
                        <i class="bi bi-map me-1"></i> Peta Wisata
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'daftar-wisata.php' ? 'active' : '' ?>" href="daftar-wisata.php">
                        <i class="bi bi-compass me-1"></i> Daftar Wisata
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'tentang.php' ? 'active' : '' ?>" href="tentang.php">
                        <i class="bi bi-info-circle me-1"></i> Tentang
                    </a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/index.php" class="btn btn-nav-admin">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
                        </a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn btn-nav-admin">
                            <i class="bi bi-lock-fill me-1"></i> Login Admin
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
