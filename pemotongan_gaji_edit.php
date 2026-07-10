<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Edit Potongan Gaji - SI Penggajian';

$id_potongan = $_GET['id'] ?? $_POST['id_potongan'] ?? null;

if (!$id_potongan) {
    $_SESSION['flash_error'] = "Data potongan gaji tidak ditemukan.";
    header("Location: pemotongan_gaji_read.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM potongan_gaji WHERE id_potongan = :id");
$stmt->bindParam(':id', $id_potongan);
$stmt->execute();
$potongan = $stmt->fetch();

if (!$potongan) {
    $_SESSION['flash_error'] = "Data potongan gaji tidak ditemukan.";
    header("Location: pemotongan_gaji_read.php");
    exit;
}

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

$error = '';
$data  = $potongan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);

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
            $stmt_update = $koneksi->prepare(
                "UPDATE potongan_gaji SET
                    id_karyawan = :id_karyawan,
                    nama_potongan = :nama_potongan,
                    total_potongan = :total_potongan,
                    tanggal_potongan = :tanggal_potongan
                 WHERE id_potongan = :id_potongan"
            );
            $stmt_update->bindParam(':id_karyawan', $id_karyawan);
            $stmt_update->bindParam(':nama_potongan', $nama_potongan);
            $stmt_update->bindParam(':total_potongan', $total_potongan, PDO::PARAM_INT);
            $stmt_update->bindParam(':tanggal_potongan', $tanggal_potongan);
            $stmt_update->bindParam(':id_potongan', $id_potongan);
            $stmt_update->execute();

            $_SESSION['flash_success'] = "Data potongan gaji berhasil diperbarui.";
            header("Location: pemotongan_gaji_read.php");
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
    <a href="pemotongan_gaji_read.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Edit Potongan Gaji</h3>
        <p class="text-muted mb-0">Perbarui data denda, kasbon, atau potongan lain</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 650px;">
    <div class="card-body">
        <form method="POST" action="pemotongan_gaji_edit.php?id=<?= $id_potongan ?>">
            <input type="hidden" name="id_potongan" value="<?= $id_potongan ?>">

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
                <label class="form-label">Jenis Potongan <span class="text-danger">*</span></label>
                <input type="text" name="nama_potongan" class="form-control" required maxlength="20"
                       list="saran_potongan"
                       value="<?= htmlspecialchars($data['nama_potongan'] ?? '') ?>">
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
                               value="<?= htmlspecialchars($data['total_potongan'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Potongan <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_potongan" class="form-control" required
                           value="<?= htmlspecialchars($data['tanggal_potongan'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="pemotongan_gaji_read.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
