<?php
require_once '../config/config.php';
cek_role(['admin']);

$id_jabatan = $_GET['id'] ?? null;

if (!$id_jabatan) {
    $_SESSION['flash_error'] = "Data jabatan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT nama_jabatan FROM jabatan WHERE id_jabatan = :id");
$stmt->bindParam(':id', $id_jabatan);
$stmt->execute();
$jabatan = $stmt->fetch();

if (!$jabatan) {
    $_SESSION['flash_error'] = "Data jabatan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

try {
    // Catatan: karyawan yang memakai jabatan ini otomatis id_jabatan-nya
    // menjadi NULL (ON DELETE SET NULL), datanya sendiri tidak ikut terhapus.
    $hapus = $koneksi->prepare("DELETE FROM jabatan WHERE id_jabatan = :id");
    $hapus->bindParam(':id', $id_jabatan);
    $hapus->execute();

    $_SESSION['flash_success'] = "Jabatan '{$jabatan['nama_jabatan']}' berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: index.php");
exit;
