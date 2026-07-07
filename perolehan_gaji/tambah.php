<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Tambah Perolehan Gaji - SI Penggajian';

$error = '';
$old   = [];

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

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
            $stmt = $koneksi->prepare(
                "INSERT INTO perolehan_gaji (id_karyawan, nama_perolehan, total_perolehan, tanggal_perolehan)
                 VALUES (:id_karyawan, :nama_perolehan, :total_perolehan, :tanggal_perolehan)"
            );
            $stmt->bindParam(':id_karyawan', $id_karyawan);
            $stmt->bindParam(':nama_perolehan', $nama_perolehan);
            $stmt->bindParam(':total_perolehan', $total_perolehan, PDO::PARAM_INT);
            $stmt->bindParam(':tanggal_perolehan', $tanggal_perolehan);
            $stmt->execute();

            $_SESSION['flash_success'] = "Data perolehan gaji berhasil ditambahkan.";
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
        <h3 class="fw-bold mb-0">Tambah Perolehan Gaji</h3>
        <p class="text-muted mb-0">Catat bonus / pendapatan tambahan karyawan</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 650px;">
    <div class="card-body">
        <?php if (count($karyawan_list) === 0): ?>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Belum ada data karyawan. Tambahkan karyawan terlebih dahulu.
            </div>
        <?php else: ?>
            <form method="POST" action="tambah.php">
                <div class="mb-3">
                    <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                    <select name="id_karyawan" class="form-select" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($karyawan_list as $k): ?>
                            <option value="<?= $k['id_karyawan'] ?>"
                                <?= ((string)($old['id_karyawan'] ?? '') === (string)$k['id_karyawan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Perolehan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_perolehan" class="form-control" required maxlength="20"
                           list="saran_perolehan" placeholder="Contoh: Bonus Lembur"
                           value="<?= htmlspecialchars($old['nama_perolehan'] ?? '') ?>">
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
                                   placeholder="Contoh: 250000"
                                   value="<?= htmlspecialchars($old['total_perolehan'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Perolehan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_perolehan" class="form-control" required
                               value="<?= htmlspecialchars($old['tanggal_perolehan'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../component/footer.php'; ?>
