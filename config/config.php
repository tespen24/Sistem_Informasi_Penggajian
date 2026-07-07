<?php

// ======================================================
// KONFIGURASI SISTEM 
// ======================================================

// URL Project
define('BASE_URL', 'http://localhost/SistemInformasiPenggajian/');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Mulai Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================================================
// KONFIGURASI DATABASE
// ======================================================

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db_penggajian";

// Membuat koneksi
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal : " . mysqli_connect_error());
}

// Mengatur charset
mysqli_set_charset($conn, "utf8mb4");

?>