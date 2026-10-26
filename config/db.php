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

        // Seed Default Users (Password default: 'admin123')
        $hashed = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (id_user, nama, email, password, role) VALUES 
            (1, 'Administrator BahariGIS', 'admin@baharigis.id', '$hashed', 'admin'),
            (2, 'Operator Dinas Kelautan', 'operator@baharigis.id', '$hashed', 'operator');");

        // Seed Kategori
        $pdo->exec("INSERT INTO kategori_wisata (id_kategori, nama_kategori, deskripsi) VALUES
            (1, 'Pantai Pasir Putih', 'Kawasan pesisir pantai dengan hamparan pasir putih alami dan pemandangan laut lepas.'),
            (2, 'Pulau Eksotis', 'Destinasi kepulauan terpencil dengan keindahan panorama alam bahari dan ketenangan.'),
            (3, 'Spot Snorkeling', 'Kawasan perairan dangkal dengan terumbu karang warna-warni dan ikan hias yang indah.'),
            (4, 'Spot Diving & Coral Reef', 'Lokasi penyelaman laut dalam untuk observasi terumbu karang dan keanekaragaman hayati laut.'),
            (5, 'Konservasi Mangrove & Penyu', 'Kawasan edukasi pelestarian ekosistem pesisir bakau dan tempat penangkaran penyu.');");

        // Seed Fasilitas
        $pdo->exec("INSERT INTO fasilitas (id_fasilitas, nama_fasilitas, deskripsi) VALUES
            (1, 'Dermaga Perahu Wisata', 'Fasilitas penyeberangan kapal motor dan perahu sewa untuk keliling pulau.'),
            (2, 'Penyewaan Alat Snorkeling / Diving', 'Penyewaan snorkel, fin, masker, pelampung dan tabung selam.'),
            (3, 'Pusat Informasi & Tour Guide', 'Layanan informasi lokal dan pemandu wisata bahari bersertifikat.'),
            (4, 'Musholla & Toilet Bersih', 'Fasilitas ibadah dan kamar bilas air tawar setelah berenang di laut.'),
            (5, 'Warung Kuliner Seafood', 'Sentra kuliner makanan laut segar olahan nelayan setempat.'),
            (6, 'Spot Foto & Gazebo Pantai', 'Spot foto estetik dan saung santai tepi pantai dengan pemandangan sunset.'),
            (7, 'Area Parkir Luas', 'Tempat parkir aman untuk roda dua, roda empat, maupun bus pariwisata.'),
            (8, 'Pos Keamanan & Lifeguard', 'Penjagaan pantai dan penyelamat keselamatan perairan.');");

        // Seed Wisata
        $pdo->exec("INSERT INTO wisata (id_wisata, id_kategori, nama_wisata, deskripsi, alamat, latitude, longitude, harga_tiket, jam_operasional, status, id_user) VALUES
            (1, 1, 'Pantai Tanjung Tinggi Belitung', 'Pantai ikonik di Pulau Belitung yang terkenal dengan formasi batu granit raksasa purba yang tersebar eksotis di tepi pantai pasir putih dan air laut hijau toska yang sangat jernih serta tenang.', 'Kecamatan Sijuk, Kabupaten Belitung, Kepulauan Bangka Belitung', -2.5701389, 107.7121667, 10000.00, '06:00 - 18:00 WIB', 'aktif', 1),
            (2, 2, 'Taman Nasional Kepulauan Karimunjawa', 'Gugusan 27 pulau di Laut Jawa yang menjadi surga wisata bahari dengan kekayaan terumbu karang melimpah, pantai pasir putih, dan habitat ikan badut serta hiu jinak.', 'Kepulauan Karimunjawa, Kabupaten Jepara, Jawa Tengah', -5.8455556, 110.4283333, 25000.00, '24 Jam (Akses Penyeberangan Terjadwal)', 'aktif', 1),
            (3, 4, 'Taman Laut Nasional Bunaken', 'Destinasi selam kelas dunia dengan 91 jenis ikan laut dan 390 spesies terumbu karang. Memiliki dinding terumbu karang vertikal bawah laut (underwater wall) yang spektakuler.', 'Teluk Manado, Kota Manado, Sulawesi Utara', 1.6225000, 124.7613889, 50000.00, '07:00 - 17:00 WITA', 'aktif', 1),
            (4, 2, 'Kepulauan Raja Ampat (Pianemo)', 'Ikon mahakarya bahari dunia dengan gugusan pulau karang karst hijau yang menjulang di atas laut biru kehijauan yang luar biasa mempesona.', 'Distrik Waigeo Barat Kepulauan, Kabupaten Raja Ampat, Papua Barat Daya', -0.5636111, 130.2708333, 100000.00, '07:00 - 17:30 WIT', 'aktif', 1),
            (5, 3, 'Pantai Pink (Pink Beach) Lombok', 'Salah satu pantai unik di dunia yang memiliki pasir berwarna merah muda alami karena serpihan koral merah yang bercampur dengan pasir putih, berpadu dengan air laut bening.', 'Desa Sekaroh, Kecamatan Jerowaru, Kabupaten Lombok Timur, NTB', -8.8475000, 116.5161111, 20000.00, '06:30 - 18:00 WITA', 'aktif', 1),
            (6, 1, 'Pantai Tanjung Kelayang', 'Pintu gerbang wisata bahari Belitung dengan formasi batu granit berbentuk kepala burung Garuda di tengah laut serta lokasi pemberangkatan hopping island.', 'Desa Keciput, Kecamatan Sijuk, Kabupaten Belitung, Bangka Belitung', -2.5594444, 107.6711111, 10000.00, '06:00 - 18:00 WIB', 'aktif', 1),
            (7, 4, 'Kepulauan Derawan & Maratua', 'Surga bahari di Kalimantan Timur yang terkenal dengan danau ubur-ubur tanpa sengat di Pulau Kakaban, penangkaran penyu hijau, dan terumbu karang menakjubkan.', 'Kecamatan Derawan, Kabupaten Berau, Kalimantan Timur', 2.2472222, 118.2430556, 35000.00, '24 Jam (Akses Kapal Speedboat)', 'aktif', 1),
            (8, 3, 'Taman Nasional Wakatobi', 'Cagar biosfer dunia UNESCO dengan keanekaragaman hayati laut terkaya, gugusan terumbu karang atol terbesar di dunia, dan surga bagi para pecinta snorkeling dan diving.', 'Kabupaten Wakatobi, Sulawesi Tenggara', -5.3186111, 123.5936111, 30000.00, '07:00 - 17:00 WITA', 'aktif', 1),
            (9, 5, 'Ekowisata Mangrove Piaynemo & Teluk Mayalibit', 'Kawasan konservasi mangrove pesisir dan laguna air payau yang menjadi benteng perlindungan habitat kepiting bakau, burung laut, dan biota perairan.', 'Kabupaten Raja Ampat, Papua Barat Daya', -0.3208333, 130.8200000, 25000.00, '08:00 - 16:30 WIT', 'aktif', 1),
            (10, 1, 'Pantai Kuta Mandalika', 'Pantai berpasir putih butiran merica khas Lombok Selatan yang dilengkapi dengan pemandangan bukit hijau dan fasilitas wisata modern di kawasan KEK Mandalika.', 'Kuta, Kecamatan Pujut, Kabupaten Lombok Tengah, NTB', -8.8950000, 116.2797222, 10000.00, '24 Jam', 'aktif', 1);");

        // Seed Wisata Fasilitas
        $pdo->exec("INSERT INTO wisata_fasilitas (id_wisata, id_fasilitas, keterangan) VALUES
            (1, 4, 'Toilet bilas dan musholla tersedia di dekat pintu masuk'),
            (1, 5, 'Warung seafood dan es kelapa muda khas pesisir'),
            (1, 6, 'Gazebo kayu santai dan spot foto batuan granit'),
            (1, 7, 'Area parkir mobil dan motor memadai'),
            (2, 1, 'Dermaga kapal feri dan perahu sewa hopping island'),
            (2, 2, 'Sewa alat snorkeling lengkap dan pelampung'),
            (2, 3, 'Guide lokal ramah dan bersertifikat HPI'),
            (2, 4, 'Kamar bilas air tawar dan fasilitas umum lengkap'),
            (2, 5, 'Pasar ikan malam dan warung kuliner Karimunjawa'),
            (3, 1, 'Dermaga kapal wisata Bunaken'),
            (3, 2, 'Pusat persewaan tabung selam dan baju selam lengkap'),
            (3, 3, 'Instruktur dive master bersertifikasi PADI'),
            (3, 8, 'Pos pengawas pelestarian terumbu karang'),
            (4, 1, 'Dermaga speedboat dan tangga kayu menuju viewpoint'),
            (4, 3, 'Pemandu masyarakat adat lokal Waigeo'),
            (4, 6, 'Viewpoint balkon kayu dengan pemandangan gugusan karst'),
            (5, 1, 'Perahu nelayan untuk keliling teluk'),
            (5, 2, 'Sewa kacamata renang dan fin'),
            (5, 5, 'Warung ikan bakar segar khas suku Sasak'),
            (5, 6, 'Pondok kayu santai berteduh di bawah pohon'),
            (6, 1, 'Dermaga utama perahu sewa hopping island'),
            (6, 4, 'Toilet dan fasilitas kebersihan lengkap'),
            (6, 5, 'Restoran hidangan laut khas Belitung'),
            (6, 7, 'Parkir bus pariwisata sangat luas'),
            (7, 1, 'Dermaga pulau Derawan dan Maratua'),
            (7, 2, 'Alat snorkeling dan kamera bawah air'),
            (7, 8, 'Pusat perlindungan habitat penyu sisik dan penyu hijau'),
            (8, 2, 'Pusat persewaan dive gear internasional'),
            (8, 3, 'Pemandu selam perairan Wakatobi'),
            (8, 8, 'Balai konservasi taman nasional Wakatobi'),
            (9, 3, 'Edukasi konservasi bakau dan satwa pesisir'),
            (9, 6, 'Jembatan kayu melintasi hutan mangrove'),
            (10, 4, 'Fasilitas umum masjid megah dan toilet bersih'),
            (10, 6, 'Pedestrian santai tepi pantai dengan pemandangan bukit'),
            (10, 7, 'Area parkir terintegrasi kawasan Mandalika');");

        // Seed Foto Wisata
        $pdo->exec("INSERT INTO foto_wisata (id_foto, id_wisata, nama_file, keterangan) VALUES
            (1, 1, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'Batu granit raksasa di tepi pantai Tanjung Tinggi'),
            (2, 1, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'Air laut toska jernih Pantai Tanjung Tinggi'),
            (3, 2, 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80', 'Gugusan pulau dan terumbu karang Kepulauan Karimunjawa'),
            (4, 2, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 'Sunset di dermaga pantai Karimunjawa'),
            (5, 3, 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1200&q=80', 'Keindahan terumbu karang dan biota laut Bunaken'),
            (6, 4, 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80', 'Gugusan pulau karang Pianemo Raja Ampat nan megah'),
            (7, 4, 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=80', 'Perairan jernih Raja Ampat dari atas bukit'),
            (8, 5, 'https://images.unsplash.com/photo-1509233725247-49e657c54213?auto=format&fit=crop&w=1200&q=80', 'Pasir merah muda dan laut jernih Pink Beach Lombok'),
            (9, 6, 'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?auto=format&fit=crop&w=1200&q=80', 'Panorama batu Garuda di Pantai Tanjung Kelayang'),
            (10, 7, 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?auto=format&fit=crop&w=1200&q=80', 'Penyu laut berenang bebas di Kepulauan Derawan'),
            (11, 8, 'https://images.unsplash.com/photo-1682687220063-4742bd7fd538?auto=format&fit=crop&w=1200&q=80', 'Keajaiban surga bawah laut Wakatobi'),
            (12, 9, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1200&q=80', 'Hutan mangrove pesisir rimbun dan asri'),
            (13, 10, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=1200&q=80', 'Pantai berpasir putih dan garis pantai Mandalika');");
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
