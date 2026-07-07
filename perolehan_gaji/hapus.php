<?php
require_once '../config/config.php';
cek_role(['admin']);

$id_perolehan = $_GET['id'] ?? null;

if (!$id_perolehan) {
    $_SESSION['flash_error'] = "Data perolehan gaji tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$stmt = $koneksi->prepare(
    "SELECT p.nama_perolehan, k.nama
     FROM perolehan_gaji p
     JOIN karyawan k ON p.id_karyawan = k.id_karyawan
     WHERE p.id_perolehan = :id"
);
$stmt->bindParam(':id', $id_perolehan);
$stmt->execute();
$perolehan = $stmt->fetch();

if (!$perolehan) {
    $_SESSION['flash_error'] = "Data perolehan gaji tidak ditemukan.";
    header("Location: index.php");
    exit;
}

try {
    $hapus = $koneksi->prepare("DELETE FROM perolehan_gaji WHERE id_perolehan = :id");
    $hapus->bindParam(':id', $id_perolehan);
    $hapus->execute();

    $_SESSION['flash_success'] = "Data perolehan '{$perolehan['nama_perolehan']}' milik {$perolehan['nama']} berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: index.php");
exit;
