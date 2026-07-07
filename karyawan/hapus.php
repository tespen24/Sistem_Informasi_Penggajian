<?php
require_once '../config/config.php';
cek_role(['admin']);

$id_karyawan = $_GET['id'] ?? null;

if (!$id_karyawan) {
    $_SESSION['flash_error'] = "Data karyawan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Ambil id_akun terkait sebelum karyawan dihapus
$stmt = $koneksi->prepare("SELECT nama, id_akun FROM karyawan WHERE id_karyawan = :id");
$stmt->bindParam(':id', $id_karyawan);
$stmt->execute();
$karyawan = $stmt->fetch();

if (!$karyawan) {
    $_SESSION['flash_error'] = "Data karyawan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

try {
    $koneksi->beginTransaction();

    // Hapus data karyawan.
    // Data terkait (absensi, perolehan_gaji, potongan_gaji, gaji) otomatis ikut
    // terhapus karena relasinya ON DELETE CASCADE ke tabel karyawan.
    $hapus_karyawan = $koneksi->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
    $hapus_karyawan->bindParam(':id', $id_karyawan);
    $hapus_karyawan->execute();

    // Hapus juga akun login-nya, karena akun tidak berguna lagi tanpa data karyawan
    if ($karyawan['id_akun']) {
        $hapus_akun = $koneksi->prepare("DELETE FROM akun WHERE id_akun = :id_akun");
        $hapus_akun->bindParam(':id_akun', $karyawan['id_akun']);
        $hapus_akun->execute();
    }

    $koneksi->commit();

    $_SESSION['flash_success'] = "Karyawan '{$karyawan['nama']}' beserta akunnya berhasil dihapus.";
} catch (Exception $e) {
    $koneksi->rollBack();
    $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
}

header("Location: index.php");
exit;
