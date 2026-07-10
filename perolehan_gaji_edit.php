<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Edit Perolehan Gaji - SI Penggajian';

$id_perolehan = $_GET['id'] ?? $_POST['id_perolehan'] ?? null;

if (!$id_perolehan) {
    $_SESSION['flash_error'] = "Data perolehan gaji tidak ditemukan.";
    header("Location: perolehan_gaji_read.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM perolehan_gaji WHERE id_perolehan = :id");
$stmt->bindParam(':id', $id_perolehan);
$stmt->execute();
$perolehan = $stmt->fetch();

if (!$perolehan) {
    $_SESSION['flash_error'] = "Data perolehan gaji tidak ditemukan.";
    header("Location: perolehan_gaji_read.php");
    exit;
}

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

$error = '';
$data  = $perolehan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);

    $id_karyawan       = $_POST['id_karyawan'] ?? '';
    $nama_perolehan    = trim($_POST['nama_perolehan'] ?? '');
    $total_perolehan   = (int) str_replace(['.', ',', ' '], '', (string) ($_POST['total_perolehan'] ?? ''));
    $tanggal_perolehan = $_POST['tanggal_perolehan'] ?? '';

    if ($id_karyawan === '' || $nama_perolehan === '' || $_POST['total_perolehan'] === '' || $tanggal_perolehan === '') {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($nama_perolehan) > 20) {
        $error = "Jenis perolehan maksimal 20 karakter!";
    } elseif ($total_perolehan <= 0) {
        $error = "Total perolehan harus lebih besar dari 0!";
    } else {
        try {
            $stmt_update = $koneksi->prepare(
                "UPDATE perolehan_gaji SET
                    id_karyawan = :id_karyawan,
                    nama_perolehan = :nama_perolehan,
                    total_perolehan = :total_perolehan,
                    tanggal_perolehan = :tanggal_perolehan
                 WHERE id_perolehan = :id_perolehan"
            );
            $stmt_update->bindParam(':id_karyawan', $id_karyawan);
            $stmt_update->bindParam(':nama_perolehan', $nama_perolehan);
            $stmt_update->bindParam(':total_perolehan', $total_perolehan, PDO::PARAM_INT);
            $stmt_update->bindParam(':tanggal_perolehan', $tanggal_perolehan);
            $stmt_update->bindParam(':id_perolehan', $id_perolehan);
            $stmt_update->execute();

            $_SESSION['flash_success'] = "Data perolehan gaji berhasil diperbarui.";
            header("Location: perolehan_gaji_read.php");
            exit;
        } catch (Exception $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="d-flex align-items-center mb-3">
    <a href="perolehan_gaji_read.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Edit Perolehan Gaji</h3>
        <p class="text-muted mb-0">Perbarui data bonus / pendapatan tambahan karyawan</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 650px;">
    <div class="card-body">
        <form method="POST" action="perolehan_gaji_edit.php?id=<?= $id_perolehan ?>">
            <input type="hidden" name="id_perolehan" value="<?= $id_perolehan ?>">

            <div class="mb-3">
                <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                <select name="id_karyawan" class="form-select" required>
                    <option value="">-- Pilih Karyawan --</option>
                    <?php foreach ($karyawan_list as $k): ?>
                        <option value="<?= $k['id_karyawan'] ?>"
                            <?= ((string)$data['id_karyawan'] === (string)$k['id_karyawan']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Perolehan <span class="text-danger">*</span></label>
                <input type="text" name="nama_perolehan" class="form-control" required maxlength="20"
                       list="saran_perolehan"
                       value="<?= htmlspecialchars($data['nama_perolehan'] ?? '') ?>">
                <datalist id="saran_perolehan">
                    <option value="Bonus Lembur">
                    <option value="Bonus Kinerja">
                    <option value="THR">
                    <option value="Uang Makan">
                    <option value="Uang Transport">
                    <option value="Insentif">
                </datalist>
                <div class="form-text">Maksimal 20 karakter.</div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Total Perolehan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="total_perolehan" class="form-control" required min="1" step="1"
                               value="<?= htmlspecialchars($data['total_perolehan'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Perolehan <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_perolehan" class="form-control" required
                           value="<?= htmlspecialchars($data['tanggal_perolehan'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="perolehan_gaji_read.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
