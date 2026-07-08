<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Tambah Potongan Gaji - SI Penggajian';

$error = '';
$old   = [];

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $id_karyawan      = $_POST['id_karyawan'] ?? '';
    $nama_potongan    = trim($_POST['nama_potongan'] ?? '');
    $total_potongan   = (int) str_replace(['.', ',', ' '], '', (string) ($_POST['total_potongan'] ?? ''));
    $tanggal_potongan = $_POST['tanggal_potongan'] ?? '';

    if ($id_karyawan === '' || $nama_potongan === '' || $_POST['total_potongan'] === '' || $tanggal_potongan === '') {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($nama_potongan) > 20) {
        $error = "Jenis potongan maksimal 20 karakter!";
    } elseif ($total_potongan <= 0) {
        $error = "Total potongan harus lebih besar dari 0!";
    } else {
        try {
            $stmt = $koneksi->prepare(
                "INSERT INTO potongan_gaji (id_karyawan, nama_potongan, total_potongan, tanggal_potongan)
                 VALUES (:id_karyawan, :nama_potongan, :total_potongan, :tanggal_potongan)"
            );
            $stmt->bindParam(':id_karyawan', $id_karyawan);
            $stmt->bindParam(':nama_potongan', $nama_potongan);
            $stmt->bindParam(':total_potongan', $total_potongan, PDO::PARAM_INT);
            $stmt->bindParam(':tanggal_potongan', $tanggal_potongan);
            $stmt->execute();

            $_SESSION['flash_success'] = "Data potongan gaji berhasil ditambahkan.";
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
        <h3 class="fw-bold mb-0">Tambah Potongan Gaji</h3>
        <p class="text-muted mb-0">Catat denda, kasbon, atau potongan lain milik karyawan</p>
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
                    <label class="form-label">Jenis Potongan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_potongan" class="form-control" required maxlength="20"
                           list="saran_potongan" placeholder="Contoh: Kasbon"
                           value="<?= htmlspecialchars($old['nama_potongan'] ?? '') ?>">
                    <datalist id="saran_potongan">
                        <option value="Kasbon">
                        <option value="Denda Terlambat">
                        <option value="Alpha">
                        <option value="BPJS">
                        <option value="Pelanggaran">
                    </datalist>
                    <div class="form-text">Maksimal 20 karakter.</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Total Potongan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="total_potongan" class="form-control" required min="1" step="1"
                                   placeholder="Contoh: 50000"
                                   value="<?= htmlspecialchars($old['total_potongan'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Potongan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_potongan" class="form-control" required
                               value="<?= htmlspecialchars($old['tanggal_potongan'] ?? date('Y-m-d')) ?>">
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
