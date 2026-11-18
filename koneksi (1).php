<?php
/**
 * Koneksi database SIM Relawan Desa menggunakan PDO.
 * Sesuaikan konfigurasi apabila username/password MySQL berbeda.
 */

$host = 'localhost';
$dbname = 'sim_relawan';
$dbuser = 'root';
$dbpass = '';

try {
    $conn = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Jangan tampilkan detail koneksi database kepada pengunjung website.
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi pada file koneksi.php.');
}
