<?php
require_once 'koneksi.php';
cek_role(['admin']);

$id_potongan = $_GET['id'] ?? null;

if (!$id_potongan) {
    $_SESSION['flash_error'] = "Data potongan gaji tidak ditemukan.";
    header("Location: pemotongan_gaji_read.php");
    exit;
}

$stmt = $koneksi->prepare(
    "SELECT p.nama_potongan, k.nama
     FROM potongan_gaji p
     JOIN karyawan k ON p.id_karyawan = k.id_karyawan
     WHERE p.id_potongan = :id"
);
$stmt->bindParam(':id', $id_potongan);
$stmt->execute();
$potongan = $stmt->fetch();

if (!$potongan) {
    $_SESSION['flash_error'] = "Data potongan gaji tidak ditemukan.";
    header("Location: pemotongan_gaji_read.php");
    exit;
}

try {
    $hapus = $koneksi->prepare("DELETE FROM potongan_gaji WHERE id_potongan = :id");
    $hapus->bindParam(':id', $id_potongan);
    $hapus->execute();

    $_SESSION['flash_success'] = "Data potongan '{$potongan['nama_potongan']}' milik {$potongan['nama']} berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: pemotongan_gaji_read.php");
exit;
