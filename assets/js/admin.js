/**
 * BahariGIS — Admin Script (CRUD Wisata, Kategori, Fasilitas, Leaflet Coordinate Picker)
 * Menangani kebutuhan KF-07, KF-08, KF-09, KF-10, BR-01 s/d BR-15
 */

document.addEventListener('DOMContentLoaded', function () {
    let pickerMap = null;
    let pickerMarker = null;

    // 1. Inisialisasi Peta Picker Koordinat untuk Tambah/Edit Wisata (KF-08)
    function initPickerMap() {
        const pickerContainer = document.getElementById('mapPicker');
        if (!pickerContainer || pickerMap) return;

        // Default center Pantai Jumiang Pamekasan Madura Jawa Timur
        pickerMap = L.map('mapPicker').setView([-7.2332, 113.5490], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(pickerMap);

        // Marker draggable untuk memilih koordinat
        pickerMarker = L.marker([-7.2332, 113.5490], {
            draggable: true
        }).addTo(pickerMap);

        // Event saat marker digeser
        pickerMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateCoordinateInputs(pos.lat, pos.lng);
        });

        // Event saat peta diklik
        pickerMap.on('click', function (e) {
            pickerMarker.setLatLng(e.latlng);
            updateCoordinateInputs(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateCoordinateInputs(lat, lng) {
        const latInput = document.getElementById('inputLatitude');
        const lngInput = document.getElementById('inputLongitude');
        if (latInput) latInput.value = lat.toFixed(7);
        if (lngInput) lngInput.value = lng.toFixed(7);
    }

    // Refresh ukuran peta saat modal dibuka
    const modalWisata = document.getElementById('modalFormWisata');
    if (modalWisata) {
        modalWisata.addEventListener('shown.bs.modal', function () {
            if (!pickerMap) {
                initPickerMap();
            } else {
                pickerMap.invalidateSize();
            }

            // Jika koordinat sudah terisi, posisikan marker ke koordinat tersebut
            const latVal = parseFloat(document.getElementById('inputLatitude').value);
            const lngVal = parseFloat(document.getElementById('inputLongitude').value);
            if (!isNaN(latVal) && !isNaN(lngVal)) {
                pickerMarker.setLatLng([latVal, lngVal]);
                pickerMap.setView([latVal, lngVal], 11);
            }
        });
    }

    // Sinkronisasi manual saat user ketik lat/lng
    const latIn = document.getElementById('inputLatitude');
    const lngIn = document.getElementById('inputLongitude');
    function syncInputsToMap() {
        if (!pickerMap || !pickerMarker) return;
        const latVal = parseFloat(latIn.value);
        const lngVal = parseFloat(lngIn.value);
        if (!isNaN(latVal) && !isNaN(lngVal) && latVal >= -90 && latVal <= 90 && lngVal >= -180 && lngVal <= 180) {
            pickerMarker.setLatLng([latVal, lngVal]);
            pickerMap.setView([latVal, lngVal], 12);
        }
    }
    if (latIn) latIn.addEventListener('change', syncInputsToMap);
    if (lngIn) lngIn.addEventListener('change', syncInputsToMap);

    // 2. FORM TAMBAH / EDIT WISATA (KF-08)
    const formWisata = document.getElementById('formWisata');
    if (formWisata) {
        formWisata.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = formWisata.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            const formData = new FormData(formWisata);

            fetch('../api/wisata.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (res.status) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Validasi Gagal: ' + res.message);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                alert('Terjadi kesalahan jaringan: ' + err.message);
            });
        });
    }

    // 3. EDIT WISATA (Populate Form Modal)
    window.editWisata = function (id) {
        fetch('../api/wisata.php?id=' + id)
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert('Gagal mengambil data wisata: ' + res.message);
                    return;
                }

                const w = res.data;
                document.getElementById('modalWisataTitle').textContent = 'Edit Data Wisata Bahari';
                document.getElementById('inputWisataAction').value = 'update';
                document.getElementById('inputIdWisata').value = w.id_wisata;
                document.getElementById('inputNamaWisata').value = w.nama_wisata;
                document.getElementById('selectKategori').value = w.id_kategori;
                document.getElementById('inputAlamat').value = w.alamat;
                document.getElementById('inputDeskripsi').value = w.deskripsi;
                document.getElementById('inputLatitude').value = w.latitude;
                document.getElementById('inputLongitude').value = w.longitude;
                document.getElementById('inputHargaTiket').value = w.harga_tiket;
                document.getElementById('inputJamOperasional').value = w.jam_operasional;
                document.getElementById('selectStatus').value = w.status;

                // Centang fasilitas terkait
                document.querySelectorAll('.check-fasilitas').forEach(cb => {
                    cb.checked = false;
                });
                if (w.fasilitas && w.fasilitas.length > 0) {
                    w.fasilitas.forEach(f => {
                        const cb = document.querySelector(`.check-fasilitas[value="${f.id_fasilitas}"]`);
                        if (cb) cb.checked = true;
                    });
                }

                // Tampilkan Galeri Foto yang ada
                const photoContainer = document.getElementById('existingPhotosContainer');
                if (photoContainer) {
                    if (w.foto && w.foto.length > 0) {
                        let photoHtml = '<div class="row g-2">';
                        w.foto.forEach(photo => {
                            photoHtml += `
                                <div class="col-4 col-md-3 position-relative">
                                    <div class="card p-1 border">
                                        <img src="${photo.nama_file}" class="card-img-top rounded" style="height: 70px; object-fit: cover;" alt="Foto">
                                        <button type="button" onclick="window.deletePhoto(${photo.id_foto})" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 py-0 px-1" title="Hapus foto ini">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        photoHtml += '</div>';
                        photoContainer.innerHTML = photoHtml;
                    } else {
                        photoContainer.innerHTML = '<small class="text-muted">Belum ada foto tambahan.</small>';
                    }
                }

                const modal = new bootstrap.Modal(document.getElementById('modalFormWisata'));
                modal.show();
            })
            .catch(err => {
                alert('Kesalahan saat memuat data: ' + err.message);
            });
    };

    // 4. RESET FORM TAMBAH WISATA
    window.openAddWisataModal = function () {
        document.getElementById('modalWisataTitle').textContent = 'Tambah Wisata Bahari Baru';
        document.getElementById('inputWisataAction').value = 'create';
        document.getElementById('inputIdWisata').value = '';
        formWisata.reset();

        document.querySelectorAll('.check-fasilitas').forEach(cb => cb.checked = false);
        const photoContainer = document.getElementById('existingPhotosContainer');
        if (photoContainer) photoContainer.innerHTML = '<small class="text-muted">Foto baru akan disimpan saat submit.</small>';

        const modal = new bootstrap.Modal(document.getElementById('modalFormWisata'));
        modal.show();
    };

    // 5. HAPUS WISATA (KF-08, BR-12)
    window.deleteWisata = function (id, nama) {
        if (!confirm(`Apakah Anda yakin ingin menghapus data wisata "${nama}"?\n\nSemua relasi fasilitas dan foto terkait akan dihapus secara otomatis (BR-12).`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_wisata', id);

        fetch('../api/wisata.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                alert('Sukses: ' + res.message);
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        })
        .catch(err => {
            alert('Kesalahan jaringan: ' + err.message);
        });
    };

    // 6. HAPUS FOTO TUNGGAL (KF-10)
    window.deletePhoto = function (id_foto) {
        if (!confirm('Hapus foto ini dari destinasi?')) return;

        const formData = new FormData();
        formData.append('action', 'delete_photo');
        formData.append('id_foto', id_foto);

        fetch('../api/wisata.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                alert(res.message);
                // Refresh modal data
                const currentId = document.getElementById('inputIdWisata').value;
                if (currentId) window.editWisata(currentId);
            } else {
                alert(res.message);
            }
        });
    };

    // 7. CRUD KATEGORI WISATA
    const formKategori = document.getElementById('formKategori');
    if (formKategori) {
        formKategori.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(formKategori);

            fetch('../api/kategori.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            });
        });
    }

    window.editKategori = function (id, nama, deskripsi) {
        document.getElementById('inputKategoriAction').value = 'update';
        document.getElementById('inputIdKategori').value = id;
        document.getElementById('inputNamaKategori').value = nama;
        document.getElementById('inputDeskripsiKategori').value = deskripsi;
        document.getElementById('modalKategoriTitle').textContent = 'Edit Kategori Wisata';

        new bootstrap.Modal(document.getElementById('modalFormKategori')).show();
    };

    window.openAddKategoriModal = function () {
        document.getElementById('inputKategoriAction').value = 'create';
        document.getElementById('inputIdKategori').value = '';
        formKategori.reset();
        document.getElementById('modalKategoriTitle').textContent = 'Tambah Kategori Baru';

        new bootstrap.Modal(document.getElementById('modalFormKategori')).show();
    };

    window.deleteKategori = function (id, nama) {
        if (!confirm(`Hapus kategori "${nama}"?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_kategori', id);

        fetch('../api/kategori.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                alert('Sukses: ' + res.message);
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        });
    };

    // 8. CRUD FASILITAS WISATA
    const formFasilitas = document.getElementById('formFasilitas');
    if (formFasilitas) {
        formFasilitas.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(formFasilitas);

            fetch('../api/fasilitas.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            });
        });
    }

    window.editFasilitas = function (id, nama, deskripsi) {
        document.getElementById('inputFasilitasAction').value = 'update';
        document.getElementById('inputIdFasilitas').value = id;
        document.getElementById('inputNamaFasilitas').value = nama;
        document.getElementById('inputDeskripsiFasilitas').value = deskripsi;
        document.getElementById('modalFasilitasTitle').textContent = 'Edit Fasilitas';

        new bootstrap.Modal(document.getElementById('modalFormFasilitas')).show();
    };

    window.openAddFasilitasModal = function () {
        document.getElementById('inputFasilitasAction').value = 'create';
        document.getElementById('inputIdFasilitas').value = '';
        formFasilitas.reset();
        document.getElementById('modalFasilitasTitle').textContent = 'Tambah Fasilitas Baru';

        new bootstrap.Modal(document.getElementById('modalFormFasilitas')).show();
    };

    window.deleteFasilitas = function (id, nama) {
        if (!confirm(`Hapus fasilitas "${nama}"?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_fasilitas', id);

        fetch('../api/fasilitas.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                alert('Sukses: ' + res.message);
                location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        });
    };

    // 9. LOGOUT HANDLER
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Apakah Anda ingin keluar dari akun admin?')) return;

            fetch('../api/auth.php?action=logout', { method: 'POST' })
                .then(res => res.json())
                .then(res => {
                    alert(res.message);
                    window.location.href = '../index.php';
                });
        });
    }
});
