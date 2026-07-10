<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Edit Absensi - SI Penggajian';

$id_absensi = $_GET['id'] ?? $_POST['id_absensi'] ?? null;

if (!$id_absensi) {
    $_SESSION['flash_error'] = "Data absensi tidak ditemukan.";
    header("Location: absensi_read.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM absensi WHERE id_absensi = :id");
$stmt->bindParam(':id', $id_absensi);
$stmt->execute();
$absensi = $stmt->fetch();

if (!$absensi) {
    $_SESSION['flash_error'] = "Data absensi tidak ditemukan.";
    header("Location: absensi_read.php");
    exit;
}

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

$error = '';
$data  = $absensi;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);

    $id_karyawan = $_POST['id_karyawan'] ?? '';
    $tanggal     = $_POST['tanggal'] ?? '';
    $status      = $_POST['status'] ?? '';
    $jam_masuk   = $_POST['jam_masuk'] ?? '';
    $jam_keluar  = $_POST['jam_keluar'] ?? '';

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
            // Cek duplikasi tanggal untuk karyawan yang sama (kecuali data ini sendiri)
            $cek = $koneksi->prepare(
                "SELECT id_absensi FROM absensi
                 WHERE id_karyawan = :id_karyawan AND tanggal = :tanggal AND id_absensi != :id_absensi"
            );
            $cek->bindParam(':id_karyawan', $id_karyawan);
            $cek->bindParam(':tanggal', $tanggal);
            $cek->bindParam(':id_absensi', $id_absensi);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Karyawan ini sudah memiliki data absensi lain pada tanggal tersebut.");
            }

            $stmt_update = $koneksi->prepare(
                "UPDATE absensi SET
                    id_karyawan = :id_karyawan,
                    tanggal = :tanggal,
                    jam_masuk = :jam_masuk,
                    jam_keluar = :jam_keluar,
                    status = :status
                 WHERE id_absensi = :id_absensi"
            );
            $stmt_update->bindParam(':id_karyawan', $id_karyawan);
            $stmt_update->bindParam(':tanggal', $tanggal);
            $stmt_update->bindParam(':jam_masuk', $jam_masuk);
            $stmt_update->bindParam(':jam_keluar', $jam_keluar);
            $stmt_update->bindParam(':status', $status);
            $stmt_update->bindParam(':id_absensi', $id_absensi);
            $stmt_update->execute();

            $_SESSION['flash_success'] = "Data absensi berhasil diperbarui.";
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
        <h3 class="fw-bold mb-0">Edit Absensi</h3>
        <p class="text-muted mb-0">Perbarui data kehadiran karyawan</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 650px;">
    <div class="card-body">
        <form method="POST" action="absensi_edit.php?id=<?= $id_absensi ?>">
            <input type="hidden" name="id_absensi" value="<?= $id_absensi ?>">

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

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control" required
                           value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="statusSelect" class="form-select" required>
                        <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alpha'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($data['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4" id="jamWrapper">
                <div class="col-md-6">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="form-control"
                           value="<?= $data['jam_masuk'] ? substr($data['jam_masuk'], 0, 5) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control"
                           value="<?= $data['jam_keluar'] ? substr($data['jam_keluar'], 0, 5) : '' ?>">
                </div>
                <div class="form-text">Jam masuk/keluar hanya berlaku untuk status <strong>Hadir</strong>.</div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="absensi_read.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<script>
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
