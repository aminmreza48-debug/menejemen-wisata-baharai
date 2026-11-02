<?php
/**
 * BahariGIS — Database Connection & Utilities
 * Mendukung MySQL (default XAMPP) dengan auto-setup & fallback cerdas ke SQLite jika MySQL belum aktif.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi MySQL XAMPP
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'baharigis_db');

// Path database SQLite fallback
define('SQLITE_FILE', __DIR__ . '/../database/baharigis_db.sqlite');
define('SQL_SCHEMA_FILE', __DIR__ . '/../database/baharigis_db.sql');

class Database {
    private static ?PDO $pdo = null;
    private static string $driver = 'none';
    private static string $statusMessage = '';

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // 1. Coba sambungkan ke MySQL
        try {
            // Pertama cek koneksi ke server MySQL tanpa database
            $dsnCheck = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $tempPdo = new PDO($dsnCheck, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);

            // Cek apakah database baharigis_db sudah ada
            $stmt = $tempPdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
            $dbExists = $stmt->fetch();

            if (!$dbExists) {
                // Auto create database & import schema
                $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                if (file_exists(SQL_SCHEMA_FILE)) {
                    $sqlContent = file_get_contents(SQL_SCHEMA_FILE);
                    // Filter USE dan CREATE DATABASE dari SQL file
                    $sqlContent = preg_replace('/CREATE DATABASE.*?;/is', '', $sqlContent);
                    $sqlContent = preg_replace('/USE.*?;/is', '', $sqlContent);
                    $pdo->exec($sqlContent);
                }
            } else {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            }

            self::$pdo = $pdo;
            self::$driver = 'mysql';
            self::$statusMessage = 'Terhubung ke MySQL (baharigis_db)';
            return self::$pdo;

        } catch (Throwable $e) {
            // 2. Jika MySQL tidak aktif, fallback otomatis ke SQLite
            try {
                $sqliteDir = dirname(SQLITE_FILE);
                if (!is_dir($sqliteDir)) {
                    mkdir($sqliteDir, 0777, true);
                }

                $needInit = !file_exists(SQLITE_FILE) || filesize(SQLITE_FILE) === 0;
                $pdo = new PDO("sqlite:" . SQLITE_FILE, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                // Aktifkan Foreign Keys di SQLite
                $pdo->exec("PRAGMA foreign_keys = ON;");

                if ($needInit) {
                    self::initSQLiteDatabase($pdo);
                }

                self::$pdo = $pdo;
                self::$driver = 'sqlite';
                self::$statusMessage = 'Terhubung ke SQLite (Standalone Fallback). Aktifkan MySQL di XAMPP untuk beralih.';
                return self::$pdo;

            } catch (Throwable $sqle) {
                die("Gagal inisialisasi basis data: " . htmlspecialchars($sqle->getMessage()));
            }
        }
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    public static function getStatusMessage(): string {
        return self::$statusMessage;
    }

    private static function initSQLiteDatabase(PDO $pdo): void {
        // Tabel Users
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id_user INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        // Tabel Kategori Wisata
        $pdo->exec("CREATE TABLE IF NOT EXISTS kategori_wisata (
            id_kategori INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_kategori TEXT NOT NULL,
            deskripsi TEXT
        );");

        // Tabel Fasilitas
        $pdo->exec("CREATE TABLE IF NOT EXISTS fasilitas (
            id_fasilitas INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_fasilitas TEXT NOT NULL,
            deskripsi TEXT
        );");

        // Tabel Wisata
        $pdo->exec("CREATE TABLE IF NOT EXISTS wisata (
            id_wisata INTEGER PRIMARY KEY AUTOINCREMENT,
            id_kategori INTEGER NOT NULL,
            nama_wisata TEXT NOT NULL,
            deskripsi TEXT NOT NULL,
            alamat TEXT NOT NULL,
            latitude REAL NOT NULL,
            longitude REAL NOT NULL,
            harga_tiket REAL DEFAULT 0,
            jam_operasional TEXT DEFAULT '08:00 - 17:00 WIB',
            status TEXT DEFAULT 'aktif',
            id_user INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kategori) REFERENCES kategori_wisata(id_kategori) ON DELETE RESTRICT,
            FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT
        );");

        // Tabel Wisata Fasilitas
        $pdo->exec("CREATE TABLE IF NOT EXISTS wisata_fasilitas (
            id_wisata INTEGER NOT NULL,
            id_fasilitas INTEGER NOT NULL,
            keterangan TEXT DEFAULT 'Tersedia',
            PRIMARY KEY (id_wisata, id_fasilitas),
            FOREIGN KEY (id_wisata) REFERENCES wisata(id_wisata) ON DELETE CASCADE,
            FOREIGN KEY (id_fasilitas) REFERENCES fasilitas(id_fasilitas) ON DELETE CASCADE
        );");

        // Tabel Foto Wisata
        $pdo->exec("CREATE TABLE IF NOT EXISTS foto_wisata (
            id_foto INTEGER PRIMARY KEY AUTOINCREMENT,
            id_wisata INTEGER NOT NULL,
            nama_file TEXT NOT NULL,
            keterangan TEXT DEFAULT '',
            FOREIGN KEY (id_wisata) REFERENCES wisata(id_wisata) ON DELETE CASCADE
        );");

        // Seed Default Users (Password: 'Admin@2024')
        $hashed = password_hash('Admin@2024', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (id_user, nama, email, password, role) VALUES 
            (1, 'Administrator JumiangGIS', 'admin@baharigis.id', '$hashed', 'admin'),
            (2, 'Pokdarwis Desa Tanjung', 'operator@baharigis.id', '$hashed', 'operator');");

        // Seed Kategori Khusus Kawasan Pantai Jumiang
        $pdo->exec("INSERT INTO kategori_wisata (id_kategori, nama_kategori, deskripsi) VALUES
            (1, 'Jumiang Atas (Tebing & Panorama)', 'Kawasan atas bukit dan tebing batu karang terjal dengan balkon alami menghadap Selat Madura.'),
            (2, 'Jumiang Bawah (Pantai & Pesisir)', 'Area pesisir pantai landai berpasir putih, air jernih ombak tenang, dan pangkalan perahu.'),
            (3, 'Hutan Cemara & Jalur Teduh', 'Kawasan rindang ribuan pohon cemara udang pesisir yang sejuk untuk bersantai dan piknik.'),
            (4, 'Wahana Air & Spot Swafoto', 'Wahana rekreasi perahu, banana boat, ayunan tebing, dan spot selfie sunrise/sunset.'),
            (5, 'Kuliner Tradisional & Fasilitas', 'Sentra warung rujak petis Madura, es kelapa muda, pos retribusi, mushola, dan bilas.');");

        // Seed Fasilitas
        $pdo->exec("INSERT INTO fasilitas (id_fasilitas, nama_fasilitas, deskripsi) VALUES
            (1, 'Gazebo & Saung Bambu Tebing', 'Deretan saung kayu/bambu di tepi tebing karang untuk berteduh menikmati hembusan angin laut.'),
            (2, 'Gardu Pandang Sunset & Sunrise', 'Balkon alami di atas tebing karang tinggi menghadap panorama 360 derajat laut Selat Madura.'),
            (3, 'Kamar Mandi Bilas Air Tawar', 'Fasilitas kamar bilas air tawar dan toilet umum setelah bermain air di pantai.'),
            (4, 'Mushola Wisata', 'Mushola sederhana yang bersih dan nyaman untuk ibadah pengunjung.'),
            (5, 'Warung Rujak Petis & Es Kelapa Muda', 'Deretan warung kuliner tradisional khas pesisir Madura dengan harga ramah di kantong.'),
            (6, 'Sewa Perahu Tradisional Nelayan', 'Perahu jukung bermesin khas Madura untuk berkeliling tanjung tebing karang dari arah laut.'),
            (7, 'Area Parkir Motor & Mobil', 'Lahan parkir aman di bawah pohon rindang yang dikelola warga lokal & Pokdarwis.'),
            (8, 'Spot Ayunan Kayu & Dekorasi Foto', 'Instalasi ayunan kayu di bibir tebing menghadap laut bebas untuk berswafoto.'),
            (9, 'Wahana Banana Boat & Sewa Ban', 'Wahana permainan air banana boat keliling teluk dan penyewaan ban pelampung renang.'),
            (10, 'Pos Tiket Masuk & Informasi Pokdarwis', 'Pintu retribusi tiket masuk resmi yang dikelola Pokdarwis Desa Tanjung, Pademawu.');");

        // Seed Wisata Kawasan Pantai Jumiang
        $pdo->exec("INSERT INTO wisata (id_wisata, id_kategori, nama_wisata, deskripsi, alamat, latitude, longitude, harga_tiket, jam_operasional, status, id_user) VALUES
            (1, 1, 'Bukit Tebing Karang Jumiang Atas', 'Ikon utama Pantai Jumiang berupa tebing batu karang kokoh setinggi belasan meter yang menjorok langsung ke deburan ombak Selat Madura. Berfungsi sebagai balkon alam terbuka untuk menikmati panorama laut lepas membiru dan pemandangan eksotis pesisir selatan Pamekasan.', 'Kawasan Tebing Karang Jumiang Atas, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2341200, 113.5501500, 5000.00, '06:00 - 17:30 WIB', 'aktif', 1),
            (2, 2, 'Pantai Pasir Putih Jumiang Bawah', 'Area pesisir landai berpasir putih alami di bagian bawah tebing Jumiang. Memiliki deburan ombak yang relatif tenang dan perairan jernih, sangat disukai anak-anak dan keluarga untuk bermain pasir pantai, berenang, serta berjalan-jalan di tepi air laut.', 'Pesisir Teluk Jumiang Bawah, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2325800, 113.5512000, 5000.00, '06:00 - 17:30 WIB', 'aktif', 1),
            (3, 1, 'Gardu Pandang Puncak Tebing Sunset & Sunrise', 'Titik elevasi tertinggi di atas bukit karang Jumiang Atas. Menjadi lokasi terbaik dan paling ramai dikunjungi saat pagi hari untuk melihat indahnya matahari terbit (sunrise) serta sore hari untuk menikmati matahari terbenam (sunset) dengan latar siluet Selat Madura.', 'Puncak Bukit Karang Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2338500, 113.5489000, 0.00, '05:30 - 17:30 WIB', 'aktif', 1),
            (4, 3, 'Hutan Cemara Udang Pesisir Jumiang', 'Kawasan rindang pepohonan cemara udang khas pesisir Madura yang tumbuh lebat di sepanjang bibir pantai. Menghadirkan udara sejuk dan suasana teduh alami, menjadi lokasi favorit pengunjung untuk menggelar tikar piknik keluarga sambil menikmati camilan pesisir.', 'Jalur Cemara Pesisir Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2319000, 113.5482000, 0.00, '06:00 - 17:30 WIB', 'aktif', 1),
            (5, 2, 'Pangkalan Perahu Tradisional Nelayan Madura', 'Pangkalan perahu jukung tradisional nelayan Madura bermotif khas warna-warni yang bersandar di pesisir Jumiang Bawah. Pengunjung dapat menyewa perahu motor nelayan lokal untuk berlayar mengitari tanjung tebing karang dan melihat keindahan batuan karang dari tengah laut.', 'Pesisir Tambat Perahu Jumiang Bawah, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2312000, 113.5468000, 20000.00, '06:30 - 16:30 WIB', 'aktif', 1),
            (6, 4, 'Wahana Banana Boat & Sewa Ban Pelampung', 'Wahana permainan air seru di pesisir Jumiang Bawah berupa perahu pisang (banana boat) berkecepatan sedang yang ditarik perahu motor mengelilingi perairan teluk. Tersedia pula penyewaan ban karet pelampung untuk berenang santai di tepi pantai.', 'Zona Rekreasi Air Pantai Jumiang Bawah, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2323000, 113.5505000, 30000.00, '07:30 - 16:30 WIB', 'aktif', 1),
            (7, 4, 'Spot Ayunan Tebing & Dekorasi Swafoto', 'Spot foto instagramable di tepi tebing karang Jumiang Atas berupa ayunan kayu yang menghadap langsung ke laut lepas biru serta hiasan gardu foto berbentuk kapal. Lokasi favorit muda-mudi untuk berfoto dengan latar panorama laut bebas.', 'Bibir Tebing Jumiang Atas, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2348000, 113.5496000, 0.00, '06:00 - 17:30 WIB', 'aktif', 1),
            (8, 5, 'Sentra Warung Rujak Petis Madura & Es Kelapa Muda', 'Deretan warung kuliner tradisional khas pesisir di area Jumiang Atas. Menyajikan kuliner otentik rujak petis Madura ulek dengan bumbu petis ikan gurih pedas khas Pamekasan, es kelapa muda murni segar, keripik tette, mi instan, kopi jahe, dan aneka jajanan lokal.', 'Sentra Kuliner Pesisir Jumiang Atas, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2329500, 113.5478000, 0.00, '07:00 - 17:30 WIB', 'aktif', 1),
            (9, 5, 'Pos Retribusi Tiket Masuk & Parkir Kendaraan', 'Pintu masuk utama kawasan Wisata Pantai Jumiang yang dijaga petugas pengelola Pokdarwis Desa Tanjung. Tiket masuk sangat terjangkau Rp5.000 per orang, didukung area parkir teduh dan aman untuk sepeda motor (Rp3.000) maupun mobil (Rp5.000).', 'Pintu Masuk Pantai Jumiang, Dusun Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2308000, 113.5475000, 5000.00, '06:00 - 17:30 WIB', 'aktif', 1),
            (10, 5, 'Area Gazebo Santai, Mushola & Kamar Bilas', 'Pusat fasilitas umum di kawasan Pantai Jumiang yang menyediakan deretan saung kayu/bambu sederhana di tepi tebing untuk duduk santai menikmati angin laut, mushola untuk ibadah sholat, serta bilik kamar mandi bilas air tawar bersih setelah bermain di laut.', 'Zona Fasilitas Tengah Jumiang, Desa Tanjung, Kec. Pademawu, Pamekasan, Madura', -7.2322000, 113.5491000, 2000.00, '06:00 - 17:30 WIB', 'aktif', 1);");

        // Seed Wisata Fasilitas
        $pdo->exec("INSERT INTO wisata_fasilitas (id_wisata, id_fasilitas, keterangan) VALUES
            (1, 1, 'Gazebo bambu sederhana di tepi tebing karang'),
            (1, 2, 'Gardu pandang alami menghadap deburan ombak Selat Madura'),
            (1, 8, 'Spot foto panorama tebing batu karang'),
            (2, 1, 'Tikar santai dan saung teduh di pinggir pasir putih'),
            (2, 3, 'Dekat dengan bilas air tawar'),
            (2, 9, 'Area aman bermain air dan sewa ban pelampung'),
            (3, 1, 'Bangku kayu santai di puncak bukit'),
            (3, 2, 'Pemandangan 360 derajat cakrawala Selat Madura'),
            (3, 8, 'Spot foto sunrise dan sunset terbaik di Pamekasan'),
            (4, 1, 'Gazebo sejuk di bawah naungan pohon cemara udang'),
            (4, 7, 'Jalur jalan setapak teduh'),
            (4, 8, 'Spot foto lorong pohon cemara pesisir'),
            (5, 6, 'Sewa perahu tradisional nelayan Madura keliling tebing'),
            (5, 10, 'Pemandu nelayan lokal berpengalaman'),
            (6, 9, 'Wahana banana boat seru keliling teluk Jumiang'),
            (6, 3, 'Kamar bilas air tawar setelah bermain basah-basahan'),
            (7, 8, 'Ayunan kayu di bibir tebing menghadap laut lepas'),
            (7, 2, 'Pemandangan laut Selat Madura yang membiru'),
            (8, 5, 'Rujak petis Madura, es degan murni, dan aneka camilan'),
            (8, 1, 'Saung makan santai dengan angin sepoi-sepoi tebing'),
            (9, 7, 'Area parkir motor Rp3.000 dan mobil Rp5.000'),
            (9, 10, 'Loket tiket resmi Rp5.000/orang Pokdarwis Desa Tanjung'),
            (10, 1, 'Deretan gazebo kayu/bambu untuk istirahat keluarga'),
            (10, 3, 'Kamar mandi bilas air tawar bersih (Rp2.000)'),
            (10, 4, 'Mushola sederhana untuk sholat pengunjung');");

        // Seed Foto Wisata
        $pdo->exec("INSERT INTO foto_wisata (id_foto, id_wisata, nama_file, keterangan) VALUES
            (1, 1, 'assets/img/pantai_jumiang_hero.jpg', 'Panorama Tebing Karang Jumiang Atas Menghadap Selat Madura'),
            (2, 1, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'Pemandangan Ombak Menghempas Tebing Karang Alami'),
            (3, 2, 'https://images.unsplash.com/photo-1509233725247-49e657c54213?auto=format&fit=crop&w=1200&q=80', 'Hamparan Pasir Putih dan Perairan Tenang Jumiang Bawah'),
            (4, 2, 'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?auto=format&fit=crop&w=1200&q=80', 'Pesisir Pantai Landai Cocok untuk Rekreasi Keluarga'),
            (5, 3, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'Pemandangan Sunset Keemasan dari Gardu Pandang Tebing Jumiang'),
            (6, 3, 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80', 'Siluet Senja di Atas Bukit Tebing Selat Madura'),
            (7, 4, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1200&q=80', 'Suasana Sejuk dan Teduh di Bawah Pohon Cemara Udang'),
            (8, 5, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80', 'Perahu Tradisional Nelayan Madura Bersandar di Jumiang Bawah'),
            (9, 6, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80', 'Aktivitas Wahana Air dan Rekreasi di Pesisir Pantai'),
            (10, 7, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=1200&q=80', 'Spot Ayunan Kayu di Tepi Tebing dengan Latar Laut Biru'),
            (11, 8, 'assets/img/rujak_petis_madura.jpg', 'Sajian Otentik Rujak Petis Madura Khas Pamekasan'),
            (12, 9, 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1200&q=80', 'Area Parkir Kendaraan yang Rindang dan Pos Masuk Wisata'),
            (13, 10, 'https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1200&q=80', 'Deretan Gazebo Sederhana Tempat Beristirahat Wisatawan');");
    }
}

// Global Helper Functions
function getDB(): PDO {
    return Database::getConnection();
}

function getDBDriver(): string {
    return Database::getDriver();
}

function getDBStatus(): string {
    return Database::getStatusMessage();
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatRupiah(float|int $amount): string {
    if ($amount <= 0) return 'Gratis / Bebas';
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function checkAdminAuth(): void {
    if (empty($_SESSION['user_id'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(['status' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.'], 401);
        }
        header('Location: /admin/login.php');
        exit;
    }
}
