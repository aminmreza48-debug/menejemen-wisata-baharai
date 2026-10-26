/**
 * BahariGIS — Client Script (Leaflet Map, Filter, Search, Details)
 * Menangani kebutuhan KF-01, KF-02, KF-03, KF-04, KF-05, KF-06
 */

document.addEventListener('DOMContentLoaded', function () {
    let map = null;
    let markersLayer = null;
    let currentData = [];
    let activeCategory = 0;
    let baseLayers = {};

    // 1. Inisialisasi Peta Leaflet (KF-01, KF-05)
    function initMap() {
        const mapContainer = document.getElementById('mapWisata');
        if (!mapContainer) return;

        // Titik tengah default: Pantai Jumiang, Pamekasan, Madura, Jawa Timur
        map = L.map('mapWisata', {
            center: [-7.2335225, 113.5492742],
            zoom: 13,
            zoomControl: false,
            minZoom: 4,
            maxZoom: 18
        });

        // Kontrol Zoom di sudut kanan bawah
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Tile Provider 1: OpenStreetMap Standard (Bebas Watermark & API Key)
        const osmStandard = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        // Tile Provider 2: Esri World Imagery (Satelit Presisi Tinggi)
        const esriSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        // Default pakai OpenStreetMap Standard
        osmStandard.addTo(map);

        baseLayers = {
            "Peta Standar (OSM)": osmStandard,
            "Peta Satelit (Esri)": esriSatellite
        };

        // Layer Group untuk Marker Wisata
        markersLayer = L.layerGroup().addTo(map);

        // Event Tombol Peta Khusus
        setupMapControls();

        // Muat Data Wisata dari API
        loadWisataData();
    }

    // 2. Setup Tombol Kontrol Peta
    function setupMapControls() {
        // Toggle Satelit / Standard
        const btnTile = document.getElementById('btnToggleSatellite');
        let isSatellite = false;
        if (btnTile) {
            btnTile.addEventListener('click', function () {
                if (isSatellite) {
                    map.removeLayer(baseLayers["Peta Satelit (Esri)"]);
                    baseLayers["Peta Standar (OSM)"].addTo(map);
                    this.innerHTML = '<i class="bi bi-layers"></i> Satelit';
                } else {
                    map.removeLayer(baseLayers["Peta Standar (OSM)"]);
                    baseLayers["Peta Satelit (Esri)"].addTo(map);
                    this.innerHTML = '<i class="bi bi-map"></i> Peta Standar';
                }
                isSatellite = !isSatellite;
            });
        }

        // Lokasi Pengguna (Geolocation)
        const btnLocate = document.getElementById('btnLocateUser');
        if (btnLocate) {
            btnLocate.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung layanan Geolocation.');
                    return;
                }
                btnLocate.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mencari...';
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        btnLocate.innerHTML = '<i class="bi bi-crosshair"></i> Lokasi Saya';
                        const userLat = pos.coords.latitude;
                        const userLng = pos.coords.longitude;
                        map.flyTo([userLat, userLng], 12);

                        const userMarker = L.circleMarker([userLat, userLng], {
                            radius: 10,
                            fillColor: '#0077b6',
                            color: '#ffffff',
                            weight: 3,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map);
                        userMarker.bindPopup('<b>Lokasi Anda Saat Ini</b>').openPopup();
                    },
                    function (err) {
                        btnLocate.innerHTML = '<i class="bi bi-crosshair"></i> Lokasi Saya';
                        alert('Tidak dapat mendeteksi lokasi: ' + err.message);
                    }
                );
            });
        }

        // Reset Tampilan Peta
        const btnResetMap = document.getElementById('btnResetMap');
        if (btnResetMap) {
            btnResetMap.addEventListener('click', function () {
                resetMapView();
            });
        }
    }

    function resetMapView() {
        if (!map || currentData.length === 0) return;
        const group = L.featureGroup(markersLayer.getLayers());
        if (group.getLayers().length > 0) {
            map.fitBounds(group.getBounds().pad(0.15));
        } else {
            map.flyTo([-7.2335225, 113.5492742], 13);
        }
    }

    // 3. Muat Data Wisata dari Backend (KF-01, KF-02, KF-03)
    window.loadWisataData = function (keyword = '', kategoriId = 0) {
        let url = 'api/wisata.php?status=aktif';
        if (keyword) url += '&search=' + encodeURIComponent(keyword);
        if (kategoriId > 0) url += '&kategori=' + kategoriId;

        const loadingBadge = document.getElementById('mapLoadingIndicator');
        if (loadingBadge) loadingBadge.style.display = 'inline-block';

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (loadingBadge) loadingBadge.style.display = 'none';
                if (!res.status) {
                    console.error('Gagal memuat data:', res.message);
                    return;
                }

                currentData = res.data;
                renderMarkers(currentData);
                renderDestinationCards(currentData);
                updateStats(res.total, res.database_driver);

                // Auto fokus ke Pantai Jumiang Pamekasan jika load pertama kali (tanpa filter pencarian)
                if (!keyword && (!kategoriId || kategoriId === '0' || kategoriId === 0)) {
                    const jumiang = currentData.find(d => d.nama_wisata && d.nama_wisata.toLowerCase().includes('jumiang'));
                    if (jumiang) {
                        map.setView([jumiang.latitude, jumiang.longitude], 13);
                    }
                }
            })
            .catch(err => {
                if (loadingBadge) loadingBadge.style.display = 'none';
                console.error('Error fetch wisata:', err);
            });
    };

    // 4. Render Markers di Peta (KF-01, KF-05)
    function renderMarkers(items) {
        if (!markersLayer) return;
        markersLayer.clearLayers();

        if (items.length === 0) return;

        items.forEach(item => {
            if (!item.latitude || !item.longitude) return;

            // Icon kustom bahari
            const iconHtml = `
                <div class="custom-ocean-pin" title="${item.nama_wisata}">
                    <div class="custom-ocean-pin-inner">
                        <i class="bi bi-water"></i>
                    </div>
                </div>
            `;

            const customIcon = L.divIcon({
                className: 'custom-ocean-pin-container',
                html: iconHtml,
                iconSize: [38, 38],
                iconAnchor: [19, 38],
                popupAnchor: [0, -34]
            });

            // Template Popup Interaktif
            const popupHtml = `
                <div class="popup-card">
                    <div class="popup-card-header">
                        <img src="${item.foto_utama}" class="popup-card-img" alt="${item.nama_wisata}">
                        <span class="popup-card-badge">${item.nama_kategori}</span>
                        <span class="popup-card-price">${item.harga_tiket_format}</span>
                    </div>
                    <div class="popup-card-body">
                        <h6 class="popup-title">${item.nama_wisata}</h6>
                        <p class="popup-address"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${item.alamat}</p>
                        <div class="popup-actions">
                            <button onclick="window.viewDetailWisata(${item.id_wisata})" class="btn-popup-detail">
                                <i class="bi bi-info-circle me-1"></i> Detail
                            </button>
                            <a href="${item.google_maps_url}" target="_blank" rel="noopener" class="btn-popup-direction" title="Buka Petunjuk Arah Google Maps">
                                <i class="bi bi-sign-turn-right-fill text-primary"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;

            const marker = L.marker([item.latitude, item.longitude], { icon: customIcon });
            marker.bindPopup(popupHtml);
            markersLayer.addLayer(marker);
        });

        // Zoom fit ke marker yang tampil
        resetMapView();
    }

    // 5. Render Kartu Destinasi di Bawah Peta (KF-04)
    function renderDestinationCards(items) {
        const container = document.getElementById('destinationCardsContainer');
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 border">
                        <i class="bi bi-compass text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 font-serif">Tidak Ada Wisata Bahari Ditemukan</h5>
                        <p class="text-muted">Coba ubah kata kunci pencarian atau pilih filter kategori lainnya.</p>
                        <button class="btn btn-outline-primary rounded-pill px-4" onclick="window.resetAllFilters()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card-wisata">
                        <div class="card-wisata-img-wrapper">
                            <img src="${item.foto_utama}" class="card-wisata-img" alt="${item.nama_wisata}" loading="lazy">
                            <span class="card-wisata-category">${item.nama_kategori}</span>
                            <span class="card-wisata-price">${item.harga_tiket_format}</span>
                        </div>
                        <div class="card-wisata-body">
                            <h5 class="card-wisata-title">${item.nama_wisata}</h5>
                            <div class="card-wisata-location">
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                <span>${item.alamat.length > 50 ? item.alamat.substring(0, 50) + '...' : item.alamat}</span>
                            </div>
                            <p class="card-wisata-desc">${item.deskripsi}</p>
                            <div class="card-wisata-footer">
                                <button onclick="window.viewDetailWisata(${item.id_wisata})" class="btn-card-detail">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </button>
                                <button onclick="window.focusWisataOnMap(${item.latitude}, ${item.longitude})" class="btn-card-map" title="Lihat Lokasi di Peta">
                                    <i class="bi bi-pin-map-fill"></i>
                                </button>
                                <a href="${item.google_maps_url}" target="_blank" rel="noopener" class="btn-card-map text-success" title="Petunjuk Arah Navigasi">
                                    <i class="bi bi-sign-turn-right-fill"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // 6. Update Stats & Badge
    function updateStats(total, driver) {
        const countBadge = document.getElementById('totalWisataBadge');
        if (countBadge) countBadge.textContent = total + ' Destinasi';

        const dbBadge = document.getElementById('dbDriverBadge');
        if (dbBadge && driver) {
            dbBadge.textContent = 'DB: ' + driver.toUpperCase();
        }
    }

    // 7. Global Actions untuk Filter & Detail
    window.focusWisataOnMap = function (lat, lng) {
        if (!map) return;
        map.flyTo([lat, lng], 13, { duration: 1.2 });
        // Scroll halus ke peta
        const mapEl = document.getElementById('mapWisata');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    window.resetAllFilters = function () {
        activeCategory = 0;
        const searchInput = document.getElementById('searchWisataInput');
        if (searchInput) searchInput.value = '';

        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        const allPill = document.querySelector('.category-pill[data-category="0"]');
        if (allPill) allPill.classList.add('active');

        window.loadWisataData('', 0);
    };

    // 8. Tampilkan Detail Modal (KF-04, KF-06)
    window.viewDetailWisata = function (id) {
        const modalEl = document.getElementById('detailWisataModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const contentEl = document.getElementById('detailWisataContent');
        contentEl.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat informasi wisata bahari...</p>
            </div>
        `;

        fetch('api/wisata.php?id=' + id)
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    contentEl.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                    return;
                }

                const w = res.data;

                // Bangun Galeri Carousel
                let carouselItems = '';
                if (w.foto && w.foto.length > 0) {
                    w.foto.forEach((f, idx) => {
                        carouselItems += `
                            <div class="carousel-item ${idx === 0 ? 'active' : ''} gallery-carousel-item">
                                <img src="${f.nama_file}" alt="${f.keterangan || w.nama_wisata}">
                                ${f.keterangan ? `<div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded px-3 py-1"><small>${f.keterangan}</small></div>` : ''}
                            </div>
                        `;
                    });
                } else {
                    carouselItems = `
                        <div class="carousel-item active gallery-carousel-item">
                            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80" alt="${w.nama_wisata}">
                        </div>
                    `;
                }

                // Bangun Badge Fasilitas
                let fasilitasBadges = '';
                if (w.fasilitas && w.fasilitas.length > 0) {
                    w.fasilitas.forEach(fas => {
                        fasilitasBadges += `
                            <span class="badge-fasilitas" title="${fas.keterangan || fas.deskripsi}">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>${fas.nama_fasilitas}</span>
                            </span>
                        `;
                    });
                } else {
                    fasilitasBadges = '<p class="text-muted small">Informasi fasilitas belum dicatat.</p>';
                }

                contentEl.innerHTML = `
                    <div id="wisataGalleryCarousel" class="carousel slide mb-4 rounded-4 overflow-hidden shadow-sm" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            ${carouselItems}
                        </div>
                        ${w.foto && w.foto.length > 1 ? `
                            <button class="carousel-control-prev" type="button" data-bs-target="#wisataGalleryCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#wisataGalleryCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        ` : ''}
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">${w.nama_kategori}</span>
                        <h4 class="text-success fw-bold mb-0">${w.harga_tiket_format}</h4>
                    </div>

                    <h3 class="font-serif fw-bold text-dark mb-2">${w.nama_wisata}</h3>
                    <p class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i> ${w.alamat}</p>

                    <div class="mb-4">
                        <h6 class="fw-bold text-ocean-primary"><i class="bi bi-card-text me-2"></i>Deskripsi Wisata</h6>
                        <p class="text-secondary" style="line-height: 1.8;">${w.deskripsi}</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-ocean-primary"><i class="bi bi-box-seam me-2"></i>Fasilitas Pendukung</h6>
                        <div class="mt-2">
                            ${fasilitasBadges}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-ocean-primary"><i class="bi bi-info-circle me-2"></i>Informasi & Atribut Geografis</h6>
                        <table class="info-attr-table">
                            <tr>
                                <td>Jam Operasional</td>
                                <td><strong>${w.jam_operasional}</strong></td>
                            </tr>
                            <tr>
                                <td>Koordinat Latitude</td>
                                <td><code>${w.latitude}</code></td>
                            </tr>
                            <tr>
                                <td>Koordinat Longitude</td>
                                <td><code>${w.longitude}</code></td>
                            </tr>
                            <tr>
                                <td>Status Publikasi</td>
                                <td><span class="badge bg-success">${w.status.toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <td>Petugas Pengelola</td>
                                <td>${w.nama_pengelola}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <a href="${w.google_maps_url}" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4 flex-grow-1">
                            <i class="bi bi-sign-turn-right-fill me-1"></i> Buka Petunjuk Arah Navigasi
                        </a>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                `;
            })
            .catch(err => {
                contentEl.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${err.message}</div>`;
            });
    };

    // 9. Event Listener untuk Pencarian Realtime & Filter Kategori
    const searchInput = document.getElementById('searchWisataInput');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                window.loadWisataData(searchInput.value.trim(), activeCategory);
            }, 300);
        });
    }

    // Filter Kategori Pills
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeCategory = parseInt(this.getAttribute('data-category')) || 0;
            const searchKeyword = searchInput ? searchInput.value.trim() : '';
            window.loadWisataData(searchKeyword, activeCategory);
        });
    });

    // Jalankan inisialisasi peta
    initMap();
});
