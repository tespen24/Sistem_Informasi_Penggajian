<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Edit Karyawan - SI Penggajian';

$id_karyawan = $_GET['id'] ?? $_POST['id_karyawan'] ?? null;

if (!$id_karyawan) {
    $_SESSION['flash_error'] = "Data karyawan tidak ditemukan.";
    header("Location: karyawan_read.php");
    exit;
}

// Ambil data karyawan + akun yang akan diedit
$stmt = $koneksi->prepare(
    "SELECT k.*, a.username, a.id_akun
     FROM karyawan k
     LEFT JOIN akun a ON k.id_akun = a.id_akun
     WHERE k.id_karyawan = :id"
);
$stmt->bindParam(':id', $id_karyawan);
$stmt->execute();
$karyawan = $stmt->fetch();

if (!$karyawan) {
    $_SESSION['flash_error'] = "Data karyawan tidak ditemukan.";
    header("Location: karyawan_read.php");
    exit;
}

$jabatan_list = $koneksi->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC")->fetchAll();

$error = '';
$data  = $karyawan; // dipakai untuk mengisi ulang form

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);

    $username        = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? ''; // opsional, kosongkan jika tidak ingin ganti
    $nama            = trim($_POST['nama'] ?? '');
    $jenis_kelamin   = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir   = $_POST['tanggal_lahir'] ?: null;
    $alamat          = trim($_POST['alamat'] ?? '');
    $no_hp           = trim($_POST['no_hp'] ?? '');
    $tanggal_masuk   = $_POST['tanggal_masuk'] ?: null;
    $id_jabatan      = $_POST['id_jabatan'] ?: null;

    if ($username === '' || $nama === '' || $jenis_kelamin === '') {
        $error = "Username, nama, dan jenis kelamin wajib diisi!";
    } elseif ($password !== '' && strlen($password) < 6) {
        $error = "Password baru minimal 6 karakter (kosongkan jika tidak ingin mengganti password).";
    } else {
        try {
            $koneksi->beginTransaction();

            // Cek username sudah dipakai akun lain atau belum
            $cek = $koneksi->prepare("SELECT id_akun FROM akun WHERE username = :username AND id_akun != :id_akun");
            $cek->bindParam(':username', $username);
            $cek->bindParam(':id_akun', $karyawan['id_akun']);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Username '{$username}' sudah digunakan oleh akun lain.");
            }

            // Update tabel akun (username, dan password jika diisi)
            if ($karyawan['id_akun']) {
                if ($password !== '') {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_akun = $koneksi->prepare(
                        "UPDATE akun SET username = :username, password = :password WHERE id_akun = :id_akun"
                    );
                    $stmt_akun->bindParam(':password', $password_hash);
                } else {
                    $stmt_akun = $koneksi->prepare(
                        "UPDATE akun SET username = :username WHERE id_akun = :id_akun"
                    );
                }
                $stmt_akun->bindParam(':username', $username);
                $stmt_akun->bindParam(':id_akun', $karyawan['id_akun']);
                $stmt_akun->execute();
            }

            // Update tabel karyawan
            $stmt_karyawan = $koneksi->prepare(
                "UPDATE karyawan SET
                    id_jabatan = :id_jabatan,
                    nama = :nama,
                    jenis_kelamin = :jenis_kelamin,
                    tanggal_lahir = :tanggal_lahir,
                    alamat = :alamat,
                    no_hp = :no_hp,
                    tanggal_masuk = :tanggal_masuk
                 WHERE id_karyawan = :id_karyawan"
            );
            $stmt_karyawan->bindParam(':id_jabatan', $id_jabatan);
            $stmt_karyawan->bindParam(':nama', $nama);
            $stmt_karyawan->bindParam(':jenis_kelamin', $jenis_kelamin);
            $stmt_karyawan->bindParam(':tanggal_lahir', $tanggal_lahir);
            $stmt_karyawan->bindParam(':alamat', $alamat);
            $stmt_karyawan->bindParam(':no_hp', $no_hp);
            $stmt_karyawan->bindParam(':tanggal_masuk', $tanggal_masuk);
            $stmt_karyawan->bindParam(':id_karyawan', $id_karyawan);
            $stmt_karyawan->execute();

            $koneksi->commit();

            $_SESSION['flash_success'] = "Data karyawan '{$nama}' berhasil diperbarui.";
            header("Location: karyawan_read.php");
            exit;
        } catch (Exception $e) {
            $koneksi->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="d-flex align-items-center mb-3">
    <a href="karyawan_read.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Edit Karyawan</h3>
        <p class="text-muted mb-0">Perbarui data karyawan dan akun login-nya</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="karyawan_edit.php?id=<?= $id_karyawan ?>">
    <input type="hidden" name="id_karyawan" value="<?= $id_karyawan ?>">

    <div class="row g-3">
        <!-- Kolom Akun Login -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-shield-lock me-1"></i> Akun Login
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required
                               value="<?= htmlspecialchars($data['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <div class="form-text">Kosongkan jika tidak ingin mengganti password.</div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Role akun tetap <strong>karyawan</strong>.
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Data Diri -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person-vcard me-1"></i> Data Diri Karyawan
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required
                               value="<?= htmlspecialchars($data['nama'] ?? '') ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" id="lk"
                                       <?= (($data['jenis_kelamin'] ?? '') === 'L') ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="lk">Laki-laki</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" id="pr"
                                       <?= (($data['jenis_kelamin'] ?? '') === 'P') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pr">Perempuan</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select name="id_jabatan" class="form-select">
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach ($jabatan_list as $j): ?>
                                <option value="<?= $j['id_jabatan'] ?>"
                                    <?= ((string)($data['id_jabatan'] ?? '') === (string)$j['id_jabatan']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['nama_jabatan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" class="form-control"
                                   value="<?= htmlspecialchars($data['tanggal_masuk'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
        <a href="karyawan_read.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

<?php require_once 'footer.php'; ?>
