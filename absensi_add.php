<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Tambah Absensi - SI Penggajian';

$error = '';
$old   = ['status' => 'Hadir'];

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $id_karyawan = $_POST['id_karyawan'] ?? '';
    $tanggal     = $_POST['tanggal'] ?? '';
    $status      = $_POST['status'] ?? '';
    $jam_masuk   = $_POST['jam_masuk'] ?? '';
    $jam_keluar  = $_POST['jam_keluar'] ?? '';

    // Jam masuk/keluar hanya relevan kalau status Hadir
    if ($status !== 'Hadir') {
        $jam_masuk  = null;
        $jam_keluar = null;
    } else {
        $jam_masuk  = $jam_masuk !== '' ? $jam_masuk : null;
        $jam_keluar = $jam_keluar !== '' ? $jam_keluar : null;
    }

    if ($id_karyawan === '' || $tanggal === '' || $status === '') {
        $error = "Karyawan, tanggal, dan status wajib diisi!";
    } else {
        try {
            // Cek apakah karyawan ini sudah punya data absensi di tanggal yang sama
            $cek = $koneksi->prepare(
                "SELECT id_absensi FROM absensi WHERE id_karyawan = :id_karyawan AND tanggal = :tanggal"
            );
            $cek->bindParam(':id_karyawan', $id_karyawan);
            $cek->bindParam(':tanggal', $tanggal);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Karyawan ini sudah memiliki data absensi pada tanggal tersebut.");
            }

            $stmt = $koneksi->prepare(
                "INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, jam_keluar, status)
                 VALUES (:id_karyawan, :tanggal, :jam_masuk, :jam_keluar, :status)"
            );
            $stmt->bindParam(':id_karyawan', $id_karyawan);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':jam_masuk', $jam_masuk);
            $stmt->bindParam(':jam_keluar', $jam_keluar);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $_SESSION['flash_success'] = "Data absensi berhasil ditambahkan.";
            header("Location: absensi_read.php");
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
    <a href="absensi_read.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Tambah Absensi</h3>
        <p class="text-muted mb-0">Catat kehadiran karyawan</p>
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
                Belum ada data karyawan. Tambahkan karyawan terlebih dahulu sebelum mencatat absensi.
            </div>
        <?php else: ?>
            <form method="POST" action="absensi_add.php" id="formAbsensi">
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

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" required
                               value="<?= htmlspecialchars($old['tanggal'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-select" required>
                            <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alpha'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($old['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4" id="jamWrapper">
                    <div class="col-md-6">
                        <label class="form-label">Jam Masuk</label>
                        <input type="time" name="jam_masuk" class="form-control"
                               value="<?= htmlspecialchars($old['jam_masuk'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam Keluar</label>
                        <input type="time" name="jam_keluar" class="form-control"
                               value="<?= htmlspecialchars($old['jam_keluar'] ?? '') ?>">
                    </div>
                    <div class="form-text">Jam masuk/keluar hanya berlaku untuk status <strong>Hadir</strong>.</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <a href="absensi_read.php" class="btn btn-outline-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Tampilkan/sembunyikan input jam sesuai status yang dipilih
    const statusSelect = document.getElementById('statusSelect');
    const jamWrapper   = document.getElementById('jamWrapper');

    function toggleJam() {
        if (!statusSelect) return;
        jamWrapper.style.display = (statusSelect.value === 'Hadir') ? 'flex' : 'none';
    }

    if (statusSelect) {
        toggleJam();
        statusSelect.addEventListener('change', toggleJam);
    }
</script>

<?php require_once 'footer.php'; ?>
