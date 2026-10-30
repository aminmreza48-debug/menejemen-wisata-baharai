    </main>
</div>

<!-- Backdrop Gelap untuk Sidebar Mobile -->
<div class="admin-sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Bottom Navigation Bar Mobile untuk Admin -->
<div class="admin-mobile-nav d-lg-none">
    <a href="index.php" class="admin-mobile-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>
    <a href="wisata.php" class="admin-mobile-nav-item <?= $currentPage === 'wisata.php' ? 'active' : '' ?>">
        <i class="bi bi-map-fill"></i>
        <span>Wisata</span>
    </a>
    <a href="kategori.php" class="admin-mobile-nav-item <?= $currentPage === 'kategori.php' ? 'active' : '' ?>">
        <i class="bi bi-tags-fill"></i>
        <span>Kategori</span>
    </a>
    <a href="fasilitas.php" class="admin-mobile-nav-item <?= $currentPage === 'fasilitas.php' ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i>
        <span>Fasilitas</span>
    </a>
    <a href="../index.php" target="_blank" class="admin-mobile-nav-item">
        <i class="bi bi-globe"></i>
        <span>Web</span>
    </a>
</div>

<!-- JS Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="../assets/js/admin.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin.js') ?>"></script>

<script>
// Sidebar Toggle & Backdrop Responsive
document.addEventListener('DOMContentLoaded', function () {
    const btnToggle = document.getElementById('btnToggleSidebar');
    const btnClose = document.getElementById('btnCloseSidebar');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (backdrop) backdrop.classList.add('show');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
    }

    if (btnToggle) btnToggle.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
});
</script>

</body>
</html>
