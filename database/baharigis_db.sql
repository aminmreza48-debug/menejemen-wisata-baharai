-- ==============================================================
-- Database: baharigis_db
-- Aplikasi: SIG Wisata Madura Pantai Jumiang
-- Perancangan Tugas Manajemen Web GIS (Semester 5)
-- Fokus: Kawasan Wisata Pantai Jumiang, Pademawu, Pamekasan, Madura
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
-- DATA AWAL (SEED DATA KHUSUS KAWASAN PANTAI JUMIANG MADURA)
-- ==============================================================

-- 1. Users Default (Password default: 'admin123')
INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator JumiangGIS', 'admin@baharigis.id', '$2y$10$eE0m9Xj5KkUf5lqCjCfZ9.T8HlYf7lA6FvhM9C6uK/0oH05p1W.ey', 'admin', NOW()),
(2, 'Pokdarwis Desa Tanjung', 'operator@baharigis.id', '$2y$10$eE0m9Xj5KkUf5lqCjCfZ9.T8HlYf7lA6FvhM9C6uK/0oH05p1W.ey', 'operator', NOW());

-- 2. Kategori Objek & Spot Kawasan Pantai Jumiang
INSERT INTO `kategori_wisata` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Spot Panorama & Tebing Karang', 'Daya tarik utama tebing batu karang laut, bukit pemandangan, dan pesisir alami.'),
(2, 'Pantai Pasir Putih & Pesisir', 'Hamparan pesisir pasir putih untuk rekreasi air, berenang santai, dan pesona teluk.'),
(3, 'Hutan Cemara & Area Teduh', 'Kawasan pepohonan cemara udang pesisir yang rimbun, sejuk, dan asri khas pesisir Madura.'),
(4, 'Wahana, Spot Foto & Sunset', 'Gardu pandang matahari terbenam, ayunan laut, dek foto instagramable, dan camping ground.'),
(5, 'Kuliner Khas & Fasilitas Wisata', 'Sentra kuliner rujak petis & ikan bakar, area parkir, loket retribusi, musholla & kamar bilas.');

-- 3. Master Fasilitas
INSERT INTO `fasilitas` (`id_fasilitas`, `nama_fasilitas`, `deskripsi`) VALUES
(1, 'Gazebo & Saung Santai Tepi Tebing', 'Saung istirahat kayu kelapa dengan hembusan angin laut dan pemandangan ombak tebing.'),
(2, 'Gardu Pandang Panorama Sunset & Selat Madura', 'Deck elevasi tinggi untuk menikmati pemandangan 360 derajat laut lepas dan matahari terbenam.'),
(3, 'Toilet Bersih & Kamar Bilas Air Tawar', 'Kamar bilas air tawar bersih setelah bermain air laut dan fasilitas sanitasi terawat.'),
(4, 'Musholla Al-Bahri', 'Tempat ibadah nyaman dan bersih yang berlokasi strategis di dalam area wisata.'),
(5, 'Sentra Kuliner Rujak Petis & Ikan Bakar Madura', 'Deretan warung makanan khas Pamekasan dengan menu rujak petis Madura dan ikan laut segar.'),
(6, 'Sewa Perahu Wisata Tradisional', 'Layanan jelajah tanjung tebing karang dengan perahu bercorak khas nelayan Madura.'),
(7, 'Area Parkir Luas (Motor, Mobil, Bus)', 'Lahan parkir tertata aman yang dijaga petugas parkir resmi Pokdarwis.'),
(8, 'Spot Foto Instagramable & Ayunan Laut', 'Instalasi foto kekinian berupa ayunan di bibir tebing dan frame kapal menghadap laut.'),
(9, 'Area Camping Ground & Api Unggun', 'Padang rumput datar di atas bukit pesisir untuk perkemahan ceria dan memandang bintang.'),
(10, 'Pos Informasi Pokdarwis & Keamanan Pantai', 'Pusat layanan informasi pengunjung dan pos pengawas keselamatan perairan.');

-- 4. 10 Spot & Objek Kawasan Wisata Pantai Jumiang Pamekasan Madura
INSERT INTO `wisata` (`id_wisata`, `id_kategori`, `nama_wisata`, `deskripsi`, `alamat`, `latitude`, `longitude`, `harga_tiket`, `jam_operasional`, `status`, `id_user`, `created_at`) VALUES
(1, 1, 'Tebing Karang Utama Pantai Jumiang', 'Tebing batu karang terjal setinggi 15 meter yang menjorok langsung ke deburan ombak Selat Madura. Menjadi ikon legendaris Pantai Jumiang Pamekasan dengan pemandangan batuan karang eksotis, panorama laut lepas membiru, dan angin sepoi-sepoi khas pesisir Madura.', 'Semenanjung Tebing Karang Jumiang, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura, Jawa Timur 69381', -7.2341200, 113.5501500, 10000.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW()),
(2, 2, 'Pantai Pasir Putih Teluk Jumiang', 'Area teluk pesisir landai berpasir putih alami di sisi timur tebing karang Jumiang. Memiliki ombak yang tenang dan air laut yang jernih, sangat aman untuk rekreasi keluarga, anak-anak bermain pasir pantai, dan berendam air laut.', 'Kawasan Dataran Rendah Teluk Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura, Jawa Timur', -7.2325800, 113.5512000, 10000.00, '06:00 - 17:30 WIB', 'aktif', 1, NOW()),
(3, 4, 'Gardu Pandang & Puncak Bukit Sunset Jumiang', 'Titik elevasi tertinggi di atas bukit karang Jumiang yang dilengkapi anjungan balkon kayu dan deretan bangku pandang. Menjadi lokasi terfavorit di Pamekasan Madura untuk menyaksikan indahnya matahari terbit (sunrise) dan matahari terbenam (sunset) dengan latar siluet Selat Madura.', 'Puncak Bukit Karang Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura, Jawa Timur', -7.2338500, 113.5489000, 0.00, '06:00 - 18:30 WIB', 'aktif', 1, NOW()),
(4, 3, 'Hutan Cemara Udang Pesisir Jumiang', 'Kawasan rindang ribuan pohon cemara udang khas pesisir Madura yang membentang asri di sepanjang daratan Pantai Jumiang. Menghadirkan keteduhan alami, udara sejuk, serta spot bersantai menggelar tikar piknik keluarga di bawah naungan pohon.', 'Jalur Hijau Cemara Udang, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2319000, 113.5482000, 0.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW()),
(5, 4, 'Spot Ayunan Laut & Deck Selfie Bahari', 'Wahana foto instagramable yang didesain di tepi bibir bukit karang dengan ayunan kayu ikonik dan anjungan dermaga miniatur kapal menghadap langsung ke lautan luas. Spot wajib bagi para pemburu foto estetik saat berkunjung ke Pantai Jumiang.', 'Bibir Tebing Karang Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2348000, 113.5496000, 5000.00, '07:00 - 17:30 WIB', 'aktif', 1, NOW()),
(6, 5, 'Sentra Kuliner Rujak Petis & Ikan Bakar Madura', 'Sentra jajanan dan makanan khas pesisir Madura di Pantai Jumiang. Menyajikan hidangan otentik rujak petis Madura dengan taburan keripik tette, ikan tongkol dan kakap bakar bumbu rica pesisir, es degan (kelapa muda) segar, dan camilan lokal khas Pamekasan.', 'Zona Kuliner Pesisir Pantai Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2329500, 113.5478000, 0.00, '07:30 - 18:00 WIB', 'aktif', 1, NOW()),
(7, 2, 'Dermaga & Pangkalan Perahu Wisata Jumiang', 'Pangkalan perahu motor tradisional bercorak khas nelayan Madura. Menyediakan paket sewa perahu keliling tanjung karang Jumiang untuk melihat keelokan tebing dari tengah laut serta menikmati sensasi hembusan ombak perairan pesisir.', 'Pesisir Barat Tanjung Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2312000, 113.5468000, 25000.00, '06:30 - 16:30 WIB', 'aktif', 1, NOW()),
(8, 4, 'Camping Ground & Area Outbound Bukit Jumiang', 'Area perkemahan rumput datar di bagian perbukitan pesisir Pantai Jumiang. Cocok untuk kegiatan perkemahan akhir pekan (camping ground), gathering komunitas, outbond, serta menikmati ketenangan malam berbintang dengan deburan suara ombak.', 'Bukit Selatan Pantai Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2351000, 113.5481000, 20000.00, '24 Jam (Registrasi Pengelola)', 'aktif', 1, NOW()),
(9, 5, 'Gerbang Utama, Loket Retribusi & Parkir Terpadu', 'Pintu gerbang kedatangan utama kawasan Wisata Pantai Jumiang yang dilengkapi pos retribusi resmi tiket masuk, papan peta navigasi kawasan, sentra informasi Pokdarwis, serta lapangan parkir tertata luas untuk sepeda motor, mobil, dan bus pariwisata.', 'Pintu Gerbang Pantai Jumiang, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2308000, 113.5475000, 5000.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW()),
(10, 5, 'Kompleks Gazebo & Fasilitas Sanitasi (Musholla & Bilas)', 'Pusat fasilitas umum dan kenyamanan pengunjung yang menyediakan deretan gazebo saung kayu kelapa untuk istirahat keluarga, Musholla Al-Bahri untuk ibadah, serta deretan kamar mandi bilas air tawar yang bersih dan higienis.', 'Zona Fasilitas Tengah Pantai Jumiang, Desa Tanjung, Kec. Pademawu, Kab. Pamekasan, Madura', -7.2322000, 113.5491000, 3000.00, '06:00 - 18:00 WIB', 'aktif', 1, NOW());

-- 5. Relasi Wisata dan Fasilitas
INSERT INTO `wisata_fasilitas` (`id_wisata`, `id_fasilitas`, `keterangan`) VALUES
(1, 1, 'Gazebo kayu santai di atas tebing karang'),
(1, 2, 'Gardu pandang menghadap deburan ombak Selat Madura'),
(1, 8, 'Spot foto panorama tebing batu karang alami'),
(1, 10, 'Pos pantau keselamatan tebing Pokdarwis'),
(2, 1, 'Pondok santai di pinggir hamparan pasir putih'),
(2, 3, 'Akses dekat ke bilas air tawar'),
(2, 8, 'Spot foto pesisir teluk berpasir putih'),
(2, 10, 'Pengawasan tepi pantai untuk keluarga'),
(3, 1, 'Saung berteduh di puncak bukit'),
(3, 2, 'Anjungan kayu pandang panorama 360 derajat'),
(3, 8, 'Spot foto sunset terbaik di Pamekasan Madura'),
(4, 1, 'Gazebo sejuk di bawah naungan pohon cemara'),
(4, 7, 'Akses jalan setapak teduh'),
(4, 8, 'Spot foto lorong pohon cemara estetik'),
(5, 2, 'Pemandangan laut lepas dari bibir tebing'),
(5, 8, 'Ayunan kayu gantung tepi laut & anjungan perahu'),
(6, 3, 'Wastafel cuci tangan dan toilet'),
(6, 5, 'Warung rujak petis Madura & ikan bakar segar'),
(6, 7, 'Dekat dengan area parkir kendaraan'),
(7, 6, 'Penyewaan perahu motor tradisional nelayan Madura'),
(7, 10, 'Petugas pemandu penyeberangan perairan'),
(8, 3, 'Toilet bilas darurat dekat area bukit'),
(8, 9, 'Area perkemahan rumput luas & zona api unggun'),
(8, 10, 'Izin keamanan dan pengawasan malam hari'),
(9, 7, 'Area parkir motor, mobil pribadi, dan bus luas'),
(9, 10, 'Pusat informasi Pokdarwis & loket retribusi'),
(10, 1, 'Deretan gazebo kayu keluarga'),
(10, 3, '12 bilik kamar mandi bilas air tawar bersih'),
(10, 4, 'Musholla Al-Bahri dengan tempat wudhu bersih');

-- 6. Foto Wisata (Resolusi tinggi Unsplash)
INSERT INTO `foto_wisata` (`id_foto`, `id_wisata`, `nama_file`, `keterangan`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'Eksotisme Tebing Batu Karang Pantai Jumiang Pamekasan'),
(2, 1, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'Deburan Ombak Selat Madura Menghempas Tebing Jumiang'),
(3, 2, 'https://images.unsplash.com/photo-1509233725247-49e657c54213?auto=format&fit=crop&w=1200&q=80', 'Hamparan Pasir Putih Bersih di Teluk Pantai Jumiang'),
(4, 2, 'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?auto=format&fit=crop&w=1200&q=80', 'Perairan Tenang Teluk Pesisir Jumiang Pamekasan'),
(5, 3, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'Pemandangan Sunset Keemasan dari Gardu Pandang Bukit Jumiang'),
(6, 3, 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80', 'Panorama Cakrawala Selat Madura dari Atas Tebing'),
(7, 4, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1200&q=80', 'Suasana Sejuk dan Asri Hutan Cemara Udang Jumiang'),
(8, 5, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=1200&q=80', 'Spot Ayunan Tepi Laut Berlatar Samudera Biru'),
(9, 6, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80', 'Suasana Santai Saung Kuliner Pesisir Pantai Jumiang'),
(10, 7, 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?auto=format&fit=crop&w=1200&q=80', 'Perahu Nelayan Tradisional Madura Siap Mengantar Wisatawan'),
(11, 8, 'https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1200&q=80', 'Area Perkemahan Bukit Karang Menghadap Lautan Lepas'),
(12, 9, 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=80', 'Pintu Gerbang dan Papan Selamat Datang Kawasan Jumiang'),
(13, 10, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80', 'Deretan Gazebo Nyaman Tempat Beristirahat Wisatawan');
