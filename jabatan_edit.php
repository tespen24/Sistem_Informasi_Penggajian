<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Edit Jabatan - SI Penggajian';

$id_jabatan = $_GET['id'] ?? $_POST['id_jabatan'] ?? null;

if (!$id_jabatan) {
    $_SESSION['flash_error'] = "Data jabatan tidak ditemukan.";
    header("Location: jabatan_read.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM jabatan WHERE id_jabatan = :id");
$stmt->bindParam(':id', $id_jabatan);
$stmt->execute();
$jabatan = $stmt->fetch();

if (!$jabatan) {
    $_SESSION['flash_error'] = "Data jabatan tidak ditemukan.";
    header("Location: jabatan_read.php");
    exit;
}

$error = '';
$data  = $jabatan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);

    $nama_jabatan      = trim($_POST['nama_jabatan'] ?? '');
    $gaji_pokok        = (int) str_replace(['.', ',', ' '], '', (string) ($_POST['gaji_pokok'] ?? ''));
    $tunjangan_jabatan = (int) str_replace(['.', ',', ' '], '', (string) ($_POST['tunjangan_jabatan'] ?? '0'));

    if ($nama_jabatan === '' || $_POST['gaji_pokok'] === '') {
        $error = "Nama jabatan dan gaji pokok wajib diisi!";
    } elseif ($gaji_pokok <= 0) {
        $error = "Gaji pokok harus lebih besar dari 0!";
    } elseif ($tunjangan_jabatan < 0) {
        $error = "Tunjangan jabatan tidak boleh bernilai negatif!";
    } else {
        try {
            // Cek nama jabatan dipakai jabatan lain atau tidak
            $cek = $koneksi->prepare("SELECT id_jabatan FROM jabatan WHERE nama_jabatan = :nama AND id_jabatan != :id");
            $cek->bindParam(':nama', $nama_jabatan);
            $cek->bindParam(':id', $id_jabatan);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Jabatan '{$nama_jabatan}' sudah digunakan oleh data lain.");
            }

            $stmt_update = $koneksi->prepare(
                "UPDATE jabatan SET
                    nama_jabatan = :nama_jabatan,
                    gaji_pokok = :gaji_pokok,
                    tunjangan_jabatan = :tunjangan_jabatan
                 WHERE id_jabatan = :id_jabatan"
            );
            $stmt_update->bindParam(':nama_jabatan', $nama_jabatan);
            $stmt_update->bindParam(':gaji_pokok', $gaji_pokok, PDO::PARAM_INT);
            $stmt_update->bindParam(':tunjangan_jabatan', $tunjangan_jabatan, PDO::PARAM_INT);
            $stmt_update->bindParam(':id_jabatan', $id_jabatan);
            $stmt_update->execute();

            $_SESSION['flash_success'] = "Jabatan '{$nama_jabatan}' berhasil diperbarui.";
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
        <h3 class="fw-bold mb-0">Edit Jabatan</h3>
        <p class="text-muted mb-0">Perbarui data jabatan</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="jabatan_edit.php?id=<?= $id_jabatan ?>">
            <input type="hidden" name="id_jabatan" value="<?= $id_jabatan ?>">

            <div class="mb-3">
                <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="nama_jabatan" class="form-control" required
                       value="<?= htmlspecialchars($data['nama_jabatan'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="gaji_pokok" class="form-control" required min="1" step="1"
                           value="<?= htmlspecialchars($data['gaji_pokok'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Tunjangan Jabatan</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="tunjangan_jabatan" class="form-control" min="0" step="1"
                           value="<?= htmlspecialchars($data['tunjangan_jabatan'] ?? '0') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="jabatan_read.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
