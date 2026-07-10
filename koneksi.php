<?php
/**
 * config.php
 * Konfigurasi koneksi database & fungsi bantu untuk autentikasi.
 */

// Mulai session di sini agar semua halaman yang meng-include config.php
// otomatis punya akses ke $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==== KONFIGURASI DATABASE ====
$host     = 'localhost';
$dbname   = 'sisfopenggajian_ester';
$db_user  = 'root';
$db_pass  = '';

try {
    $koneksi = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $db_user,
        $db_pass
    );
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// ==== BASE URL ====
// Sesuaikan dengan nama folder project di web server (mis. htdocs/sisfopenggajian_ester)
define('BASE_URL', '/sisfopenggajian_ester/');

/**
 * Memastikan user sudah login.
 * Jika belum, redirect ke halaman login.
 */
function cek_login()
{
    if (!isset($_SESSION['id_akun'])) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

/**
 * Membatasi akses halaman hanya untuk role tertentu.
 * Contoh: cek_role(['admin']);
 */
function cek_role(array $role_diizinkan)
{
    cek_login();
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}
