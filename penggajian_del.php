<?php
require_once 'koneksi.php';
cek_role(['admin']);

$id_gaji = $_GET['id'] ?? null;

if (!$id_gaji) {
    $_SESSION['flash_error'] = "Data gaji tidak ditemukan.";
    header("Location: penggajian_read.php");
    exit;
}

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$stmt = $koneksi->prepare(
    "SELECT g.bulan, g.tahun, k.nama
     FROM gaji g
     JOIN karyawan k ON g.id_karyawan = k.id_karyawan
     WHERE g.id_gaji = :id"
);
$stmt->bindParam(':id', $id_gaji);
$stmt->execute();
$gaji = $stmt->fetch();

if (!$gaji) {
    $_SESSION['flash_error'] = "Data gaji tidak ditemukan.";
    header("Location: penggajian_read.php");
    exit;
}

try {
    $hapus = $koneksi->prepare("DELETE FROM gaji WHERE id_gaji = :id");
    $hapus->bindParam(':id', $id_gaji);
    $hapus->execute();

    $periode = $nama_bulan[(int)$gaji['bulan']] . ' ' . $gaji['tahun'];
    $_SESSION['flash_success'] = "Slip gaji {$gaji['nama']} periode {$periode} berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: penggajian_read.php");
exit;
