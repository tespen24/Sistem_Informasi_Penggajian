<?php
require_once 'koneksi.php';
cek_role(['admin']);

$id_absensi = $_GET['id'] ?? null;

if (!$id_absensi) {
    $_SESSION['flash_error'] = "Data absensi tidak ditemukan.";
    header("Location: absensi_read.php");
    exit;
}

$stmt = $koneksi->prepare(
    "SELECT a.tanggal, k.nama
     FROM absensi a
     JOIN karyawan k ON a.id_karyawan = k.id_karyawan
     WHERE a.id_absensi = :id"
);
$stmt->bindParam(':id', $id_absensi);
$stmt->execute();
$absensi = $stmt->fetch();

if (!$absensi) {
    $_SESSION['flash_error'] = "Data absensi tidak ditemukan.";
    header("Location: absensi_read.php");
    exit;
}

try {
    $hapus = $koneksi->prepare("DELETE FROM absensi WHERE id_absensi = :id");
    $hapus->bindParam(':id', $id_absensi);
    $hapus->execute();

    $tanggal_tampil = date('d-m-Y', strtotime($absensi['tanggal']));
    $_SESSION['flash_success'] = "Data absensi {$absensi['nama']} pada tanggal {$tanggal_tampil} berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: absensi_read.php");
exit;
