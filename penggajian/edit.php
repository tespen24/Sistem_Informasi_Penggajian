<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Hitung Ulang Gaji - SI Penggajian';

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$id_gaji = $_GET['id'] ?? $_POST['id_gaji'] ?? null;

if (!$id_gaji) {
    $_SESSION['flash_error'] = "Data gaji tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$stmt = $koneksi->prepare(
    "SELECT g.*, k.nama, k.id_jabatan
     FROM gaji g
     JOIN karyawan k ON g.id_karyawan = k.id_karyawan
     WHERE g.id_gaji = :id"
);
$stmt->bindParam(':id', $id_gaji);
$stmt->execute();
$gaji = $stmt->fetch();

if (!$gaji) {
    $_SESSION['flash_error'] = "Data gaji tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$error = '';

// ==== HITUNG ULANG BERDASARKAN DATA TERBARU (gaji pokok/tunjangan/perolehan/potongan) ====
$jab = $koneksi->prepare(
    "SELECT COALESCE(gaji_pokok, 0) AS gaji_pokok, COALESCE(tunjangan_jabatan, 0) AS tunjangan_jabatan
     FROM jabatan WHERE id_jabatan = :id_jabatan"
);
$jab->bindParam(':id_jabatan', $gaji['id_jabatan']);
$jab->execute();
$jabatan = $jab->fetch() ?: ['gaji_pokok' => 0, 'tunjangan_jabatan' => 0];

$stmt_perolehan = $koneksi->prepare(
    "SELECT COALESCE(SUM(total_perolehan), 0) AS total
     FROM perolehan_gaji
     WHERE id_karyawan = :id_karyawan AND MONTH(tanggal_perolehan) = :bulan AND YEAR(tanggal_perolehan) = :tahun"
);
$stmt_perolehan->execute([
    ':id_karyawan' => $gaji['id_karyawan'],
    ':bulan'       => $gaji['bulan'],
    ':tahun'       => $gaji['tahun'],
]);
$total_perolehan_tambahan = (int) $stmt_perolehan->fetchColumn();

$stmt_potongan = $koneksi->prepare(
    "SELECT COALESCE(SUM(total_potongan), 0) AS total
     FROM potongan_gaji
     WHERE id_karyawan = :id_karyawan AND MONTH(tanggal_potongan) = :bulan AND YEAR(tanggal_potongan) = :tahun"
);
$stmt_potongan->execute([
    ':id_karyawan' => $gaji['id_karyawan'],
    ':bulan'       => $gaji['bulan'],
    ':tahun'       => $gaji['tahun'],
]);
$total_potongan = (int) $stmt_potongan->fetchColumn();

$perolehan_terbaru = $jabatan['gaji_pokok'] + $jabatan['tunjangan_jabatan'] + $total_perolehan_tambahan;
$total_gaji_terbaru = $perolehan_terbaru - $total_potongan;

$ada_perubahan = ($perolehan_terbaru != $gaji['perolehan_gaji']) || ($total_potongan != $gaji['potongan_gaji']);

// ==== PROSES SIMPAN ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_gaji = $_POST['tanggal_gaji'] ?? $gaji['tanggal_gaji'];

    if ($tanggal_gaji === '') {
        $error = "Tanggal slip wajib diisi!";
    } else {
        try {
            $update = $koneksi->prepare(
                "UPDATE gaji SET
                    perolehan_gaji = :perolehan_gaji,
                    potongan_gaji = :potongan_gaji,
                    total_gaji = :total_gaji,
                    tanggal_gaji = :tanggal_gaji
                 WHERE id_gaji = :id_gaji"
            );
            $update->bindParam(':perolehan_gaji', $perolehan_terbaru, PDO::PARAM_INT);
            $update->bindParam(':potongan_gaji', $total_potongan, PDO::PARAM_INT);
            $update->bindParam(':total_gaji', $total_gaji_terbaru, PDO::PARAM_INT);
            $update->bindParam(':tanggal_gaji', $tanggal_gaji);
            $update->bindParam(':id_gaji', $id_gaji);
            $update->execute();

            $_SESSION['flash_success'] = "Slip gaji {$gaji['nama']} berhasil diperbarui.";
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

require_once '../component/header.php';
require_once '../component/sidebar.php';
?>

<div class="d-flex align-items-center mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Hitung Ulang Slip Gaji</h3>
        <p class="text-muted mb-0"><?= htmlspecialchars($gaji['nama']) ?> — <?= $nama_bulan[(int)$gaji['bulan']] ?> <?= $gaji['tahun'] ?></p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($ada_perubahan): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Ada perubahan data (perolehan/potongan/jabatan) sejak slip ini dibuat. Rincian terbaru ditampilkan di bawah
        — klik <strong>Simpan</strong> untuk memperbarui slip dengan angka terbaru.
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Rincian Perhitungan (Data Terbaru)</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Gaji Pokok</td>
                        <td class="text-end">Rp <?= number_format($jabatan['gaji_pokok'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Tunjangan Jabatan</td>
                        <td class="text-end">Rp <?= number_format($jabatan['tunjangan_jabatan'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Total Perolehan Tambahan (bonus dsb.)</td>
                        <td class="text-end">Rp <?= number_format($total_perolehan_tambahan, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="table-light fw-semibold">
                        <td>Total Perolehan</td>
                        <td class="text-end text-success">Rp <?= number_format($perolehan_terbaru, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Total Potongan</td>
                        <td class="text-end text-danger">Rp <?= number_format($total_potongan, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="table-light fw-bold fs-5">
                        <td>Total Gaji Bersih</td>
                        <td class="text-end">Rp <?= number_format($total_gaji_terbaru, 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Slip Tersimpan Saat Ini</div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <tr>
                        <td>Total Perolehan</td>
                        <td class="text-end">Rp <?= number_format($gaji['perolehan_gaji'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Total Potongan</td>
                        <td class="text-end">Rp <?= number_format($gaji['potongan_gaji'], 0, ',', '.') ?></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Total Gaji Bersih</td>
                        <td class="text-end">Rp <?= number_format($gaji['total_gaji'], 0, ',', '.') ?></td>
                    </tr>
                </table>

                <form method="POST" action="edit.php?id=<?= $id_gaji ?>">
                    <input type="hidden" name="id_gaji" value="<?= $id_gaji ?>">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Slip</label>
                        <input type="date" name="tanggal_gaji" class="form-control"
                               value="<?= htmlspecialchars($gaji['tanggal_gaji']) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Simpan &amp; Perbarui
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../component/footer.php'; ?>
