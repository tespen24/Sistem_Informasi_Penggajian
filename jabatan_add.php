<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Tambah Jabatan - SI Penggajian';

$error = '';
$old   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $nama_jabatan      = trim($_POST['nama_jabatan'] ?? '');
    $gaji_pokok        = $_POST['gaji_pokok'] ?? '';
    $tunjangan_jabatan = $_POST['tunjangan_jabatan'] ?? 0;

    // Bersihkan format ribuan jika ada (misal user mengetik "5.000.000")
    $gaji_pokok        = (int) str_replace(['.', ',', ' '], '', (string) $gaji_pokok);
    $tunjangan_jabatan = (int) str_replace(['.', ',', ' '], '', (string) $tunjangan_jabatan);

    if ($nama_jabatan === '' || $_POST['gaji_pokok'] === '') {
        $error = "Nama jabatan dan gaji pokok wajib diisi!";
    } elseif ($gaji_pokok <= 0) {
        $error = "Gaji pokok harus lebih besar dari 0!";
    } elseif ($tunjangan_jabatan < 0) {
        $error = "Tunjangan jabatan tidak boleh bernilai negatif!";
    } else {
        try {
            // Cek nama jabatan sudah ada atau belum (opsional tapi membantu mencegah duplikat)
            $cek = $koneksi->prepare("SELECT id_jabatan FROM jabatan WHERE nama_jabatan = :nama");
            $cek->bindParam(':nama', $nama_jabatan);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Jabatan '{$nama_jabatan}' sudah ada, silakan gunakan nama lain.");
            }

            $stmt = $koneksi->prepare(
                "INSERT INTO jabatan (nama_jabatan, gaji_pokok, tunjangan_jabatan)
                 VALUES (:nama_jabatan, :gaji_pokok, :tunjangan_jabatan)"
            );
            $stmt->bindParam(':nama_jabatan', $nama_jabatan);
            $stmt->bindParam(':gaji_pokok', $gaji_pokok, PDO::PARAM_INT);
            $stmt->bindParam(':tunjangan_jabatan', $tunjangan_jabatan, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['flash_success'] = "Jabatan '{$nama_jabatan}' berhasil ditambahkan.";
            header("Location: jabatan_read.php");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="d-flex align-items-center mb-3">
    <a href="jabatan_read.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Tambah Jabatan</h3>
        <p class="text-muted mb-0">Tambahkan jenis jabatan baru</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="jabatan_add.php">
            <div class="mb-3">
                <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="nama_jabatan" class="form-control" required
                       placeholder="Contoh: Staff Administrasi"
                       value="<?= htmlspecialchars($old['nama_jabatan'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="gaji_pokok" class="form-control" required min="1" step="1"
                           placeholder="Contoh: 3000000"
                           value="<?= htmlspecialchars($old['gaji_pokok'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Tunjangan Jabatan</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="tunjangan_jabatan" class="form-control" min="0" step="1"
                           placeholder="Contoh: 500000"
                           value="<?= htmlspecialchars($old['tunjangan_jabatan'] ?? '0') ?>">
                </div>
                <div class="form-text">Kosongkan atau isi 0 jika tidak ada tunjangan.</div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="jabatan_read.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
