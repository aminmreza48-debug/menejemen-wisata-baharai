-- ==============================================================
-- Database: baharigis_db
-- Aplikasi: BahariGIS — Sistem Informasi Geografis Wisata Bahari
-- Perancangan Tugas Manajemen Web GIS (Semester 5)
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `baharigis_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `baharigis_db`;

-- --------------------------------------------------------
-- 1. Tabel users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `foto_wisata`;
DROP TABLE IF EXISTS `wisata_fasilitas`;
DROP TABLE IF EXISTS `wisata`;
DROP TABLE IF EXISTS `fasilitas`;
DROP TABLE IF EXISTS `kategori_wisata`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'operator') DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Tabel kategori_wisata
-- --------------------------------------------------------
CREATE TABLE `kategori_wisata` (
  `id_kategori` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Tabel fasilitas
-- --------------------------------------------------------
CREATE TABLE `fasilitas` (
  `id_fasilitas` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_fasilitas` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Tabel wisata
-- --------------------------------------------------------
CREATE TABLE `wisata` (
  `id_wisata` INT AUTO_INCREMENT PRIMARY KEY,
  `id_kategori` INT NOT NULL,
  `nama_wisata` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `alamat` TEXT NOT NULL,
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `harga_tiket` DECIMAL(12,2) DEFAULT 0.00,
  `jam_operasional` VARCHAR(100) DEFAULT '08:00 - 17:00 WIB',
  `status` ENUM('aktif', 'draft', 'nonaktif') DEFAULT 'aktif',
  `id_user` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wisata_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_wisata` (`id_kategori`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_wisata_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Tabel wisata_fasilitas (Relasi Many-to-Many)
-- --------------------------------------------------------
CREATE TABLE `wisata_fasilitas` (
  `id_wisata` INT NOT NULL,
  `id_fasilitas` INT NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT 'Tersedia',
  PRIMARY KEY (`id_wisata`, `id_fasilitas`),
  CONSTRAINT `fk_wf_wisata` FOREIGN KEY (`id_wisata`) REFERENCES `wisata` (`id_wisata`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wf_fasilitas` FOREIGN KEY (`id_fasilitas`) REFERENCES `fasilitas` (`id_fasilitas`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Tabel foto_wisata
-- --------------------------------------------------------
CREATE TABLE `foto_wisata` (
  `id_foto` INT AUTO_INCREMENT PRIMARY KEY,
  `id_wisata` INT NOT NULL,
  `nama_file` VARCHAR(255) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT '',
  CONSTRAINT `fk_foto_wisata` FOREIGN KEY (`id_wisata`) REFERENCES `wisata` (`id_wisata`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================
-- DATA AWAL (SEED DATA)
-- ==============================================================

-- 1. Users Default (Password default: 'admin123' di-hash bcrypt)
INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator BahariGIS', 'admin@baharigis.id', '$2y$10$eE0m9Xj5KkUf5lqCjCfZ9.T8HlYf7lA6FvhM9C6uK/0oH05p1W.ey', 'admin', NOW()),
(2, 'Operator Dinas Kelautan', 'operator@baharigis.id', '$2y$10$eE0m9Xj5KkUf5lqCjCfZ9.T8HlYf7lA6FvhM9C6uK/0oH05p1W.ey', 'operator', NOW());

-- 2. Kategori Wisata
INSERT INTO `kategori_wisata` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Pantai Pasir Putih', 'Kawasan pesisir pantai dengan hamparan pasir putih alami dan pemandangan laut lepas.'),
(2, 'Pulau Eksotis', 'Destinasi kepulauan terpencil dengan keindahan panorama alam bahari dan ketenangan.'),
(3, 'Spot Snorkeling', 'Kawasan perairan dangkal dengan terumbu karang warna-warni dan ikan hias yang indah.'),
(4, 'Spot Diving & Coral Reef', 'Lokasi penyelaman laut dalam untuk observasi terumbu karang dan keanekaragaman hayati laut.'),
(5, 'Konservasi Mangrove & Penyu', 'Kawasan edukasi pelestarian ekosistem pesisir bakau dan tempat penangkaran penyu.');

-- 3. Fasilitas
INSERT INTO `fasilitas` (`id_fasilitas`, `nama_fasilitas`, `deskripsi`) VALUES
(1, 'Dermaga Perahu Wisata', 'Fasilitas penyeberangan kapal motor dan perahu sewa untuk keliling pulau.'),
(2, 'Penyewaan Alat Snorkeling / Diving', 'Penyewaan snorkel, fin/kaki katak, masker, pelampung dan tabung selam.'),
(3, 'Pusat Informasi & Tour Guide', 'Layanan informasi lokal dan pemandu wisata bahari bersertifikat.'),
(4, 'Musholla & Toilet Bersih', 'Fasilitas ibadah dan kamar bilas air tawar setelah berenang di laut.'),
(5, 'Warung Kuliner Seafood', 'Sentra kuliner makanan laut segar olahan nelayan setempat.'),
(6, 'Spot Foto & Gazebo Pantai', 'Spot foto estetik dan saung santai tepi pantai dengan pemandangan sunset.'),
(7, 'Area Parkir Luas', 'Tempat parkir aman untuk roda dua, roda empat, maupun bus pariwisata.'),
(8, 'Pos Keamanan & Lifeguard', 'Penjagaan pantai dan penyelamat keselamatan perairan.');

-- 4. Wisata Bahari Unggulan (Fokus: Pantai Jumiang Pamekasan Madura Jawa Timur)
INSERT INTO `wisata` (`id_wisata`, `id_kategori`, `nama_wisata`, `deskripsi`, `alamat`, `latitude`, `longitude`, `harga_tiket`, `jam_operasional`, `status`, `id_user`, `created_at`) VALUES
(1, 1, 'Pantai Jumiang Pamekasan', 'Pantai Jumiang merupakan destinasi wisata bahari ikonik di Kabupaten Pamekasan, Pulau Madura, Jawa Timur. Memiliki daya tarik tebing karang terjal di atas bukit yang menjorok langsung ke laut Selat Madura, dihiasi hamparan pasir putih di dataran rendah, rimbunnya hutan cemara udang pesisir, serta gardu pandang favorit untuk menikmati matahari terbit (sunrise) dan matahari terbenam (sunset).', 'Dusun Jumiang, Desa Tanjung, Kecamatan Pademawu, Kabupaten Pamekasan, Madura, Jawa Timur 69381', -7.2335225, 113.5492742, 10000.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW()),
(2, 5, 'Pantai & Wisata Mangrove Talang Siring', 'Kawasan ekowisata pesisir pantai utara Pamekasan yang memadukan keasrian hutan mangrove dengan pemandangan perahu nelayan tradisional Madura beraneka warna serta dermaga kayu estetik untuk menikmati angin sepoi-sepoi pesisir.', 'Desa Montok, Kecamatan Larangan, Kabupaten Pamekasan, Madura, Jawa Timur', -7.1472000, 113.6267000, 5000.00, '06:00 - 17:30 WIB', 'aktif', 1, NOW()),
(3, 2, 'Pulau Oksigen Gili Iyang Madura', 'Pulau wisata bahari fenomenal di timur Madura yang dinobatkan sebagai lokasi dengan kadar oksigen tertinggi kedua di dunia (21,5%). Menyajikan pantai pasir putih bersih, tebing karang Batu Canggah, dan gua alam pesisir.', 'Kepulauan Gili Iyang, Kecamatan Dungkek, Kabupaten Sumenep, Madura, Jawa Timur', -6.9928000, 114.1883000, 15000.00, '24 Jam (Akses Penyeberangan Pagi - Sore)', 'aktif', 1, NOW()),
(4, 3, 'Pulau Gili Labak Madura', 'Surga snorkeling tersembunyi di perairan Madura seluas 5 hektar dengan pasir putih sehalus tepung dan perairan sebening kristal yang menyimpan gugusan terumbu karang warna-warni dan ikan badut (clownfish).', 'Pulau Gili Labak, Desa Kombang, Kecamatan Talango, Kabupaten Sumenep, Madura, Jawa Timur', -7.2289000, 114.0728000, 20000.00, '06:00 - 17:00 WIB', 'aktif', 1, NOW()),
(5, 1, 'Pantai Lombang Cemara Udang Madura', 'Pantai legendaris di ujung timur Pulau Madura dengan garis pantai pasir putih sepanjang 12 km yang dipagari ribuan pohon cemara udang rindang khas pesisir Madura.', 'Desa Lombang, Kecamatan Batang-Batang, Kabupaten Sumenep, Madura, Jawa Timur', -6.9064000, 114.0531000, 10000.00, '07:00 - 18:00 WIB', 'aktif', 1, NOW()),
(6, 1, 'Pantai Camplong Sampang Madura', 'Destinasi wisata pantai di pesisir selatan Madura dengan pasir landai, ombak tenang Selat Madura, dan panorama matahari terbenam yang mempesona.', 'Jalan Raya Camplong, Kecamatan Camplong, Kabupaten Sampang, Madura, Jawa Timur', -7.1994000, 113.3197000, 10000.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW()),
(7, 1, 'Pantai & Air Terjun Toroan Sampang', 'Keajaiban alam pesisir utara Madura di mana air terjun alami setinggi 20 meter mengalir di antara tebing batu karang dan bermuara langsung ke deburan ombak laut lepas.', 'Desa Ketapang Daya, Kecamatan Ketapang, Kabupaten Sampang, Madura, Jawa Timur', -6.8925000, 113.2567000, 5000.00, '06:00 - 17:30 WIB', 'aktif', 1, NOW()),
(8, 1, 'Pantai Slopeng Sumenep Madura', 'Pantai eksotis dengan bukit-bukit pasir putih setinggi 15 meter yang menyerupai gurun pasir di tepi laut Madura, dikelilingi pohon siwalan dan cemara.', 'Desa Semaan, Kecamatan Dasuk, Kabupaten Sumenep, Madura, Jawa Timur', -6.8839000, 113.8206000, 10000.00, '06:30 - 17:30 WIB', 'aktif', 1, NOW());

-- 5. Relasi Wisata dan Fasilitas
INSERT INTO `wisata_fasilitas` (`id_wisata`, `id_fasilitas`, `keterangan`) VALUES
(1, 4, 'Toilet umum bilas air tawar dan musholla nyaman'),
(1, 5, 'Sentra kuliner seafood dan warung rujak Madura khas pesisir'),
(1, 6, 'Gardu pandang bukit karang dan gazebo santai tepi laut'),
(1, 7, 'Area parkir kendaraan roda dua dan roda empat luas'),
(1, 8, 'Pos pantau keselamatan pantai dan informasi wisata'),
(2, 3, 'Pusat informasi konservasi hutan bakau pesisir'),
(2, 4, 'Musholla dan toilet bilas'),
(2, 6, 'Spot foto jembatan mangrove dan gazebo'),
(3, 1, 'Dermaga perahu penyeberangan Dungkek - Gili Iyang'),
(3, 3, 'Pemandu lokal dan sewa kendaraan odong-odong keliling pulau'),
(3, 6, 'Gardu pandang titik kadar oksigen terbaik'),
(4, 1, 'Dermaga kapal sewa dari Pelabuhan Kalianget'),
(4, 2, 'Penyewaan kacamata snorkel, fin kaki katak, dan pelampung'),
(4, 5, 'Warung kelapa muda segar dan ikan bakar lokal'),
(5, 4, 'Kamar bilas air tawar dan toilet bersih'),
(5, 5, 'Kuliner lontong campur dan es kelapa muda'),
(5, 7, 'Area parkir mobil, bus, dan motor yang sangat luas'),
(6, 4, 'Toilet dan musholla umum'),
(6, 6, 'Taman bermain tepi pantai dan saung santai'),
(6, 7, 'Parkir pinggir jalan raya utama Madura'),
(7, 4, 'Kamar bilas dan musholla'),
(7, 6, 'Spot foto batuan tebing karang pesisir'),
(7, 7, 'Tempat parkir pengunjung'),
(8, 1, 'Tunggang kuda wisata pantai'),
(8, 4, 'Toilet dan tempat bilas'),
(8, 5, 'Sentra kuliner sate Madura dan es kelapa');

-- 6. Foto Wisata (URL resolusi tinggi Unsplash)
INSERT INTO `foto_wisata` (`id_foto`, `id_wisata`, `nama_file`, `keterangan`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'Panorama Tebing Karang dan Pantai Jumiang Pamekasan'),
(2, 1, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'Pemandangan Pesisir Laut Madura di Pantai Jumiang'),
(3, 2, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80', 'Jembatan Kayu Menelusuri Mangrove Talang Siring Pamekasan'),
(4, 3, 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80', 'Keindahan Alami Tebing Karang Gili Iyang Madura'),
(5, 4, 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1200&q=80', 'Perairan Dangkal Bening dan Spot Terumbu Karang Gili Labak'),
(6, 5, 'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?auto=format&fit=crop&w=1200&q=80', 'Hamparan Pasir Putih Berpadu Hutan Cemara Udang'),
(7, 6, 'https://images.unsplash.com/photo-1509233725247-49e657c54213?auto=format&fit=crop&w=1200&q=80', 'Pantai Camplong dengan Suasana Pesisir Selat Madura'),
(8, 7, 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?auto=format&fit=crop&w=1200&q=80', 'Air Terjun Toroan yang Jatuh Langsung ke Laut Madura'),
(9, 8, 'https://images.unsplash.com/photo-1682687220063-4742bd7fd538?auto=format&fit=crop&w=1200&q=80', 'Gundukan Bukit Pasir Putih Alami Pantai Slopeng');
