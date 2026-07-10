<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Tambah Karyawan - SI Penggajian';

$error   = '';
$old     = []; // menyimpan input lama jika terjadi error, supaya form tidak kosong lagi

// Ambil daftar jabatan untuk dropdown
$jabatan_list = $koneksi->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $username        = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';
    $nama             = trim($_POST['nama'] ?? '');
    $jenis_kelamin    = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir    = $_POST['tanggal_lahir'] ?? null;
    $alamat           = trim($_POST['alamat'] ?? '');
    $no_hp            = trim($_POST['no_hp'] ?? '');
    $tanggal_masuk    = $_POST['tanggal_masuk'] ?? null;
    $id_jabatan       = $_POST['id_jabatan'] ?: null;

    // Validasi sederhana
    if ($username === '' || $password === '' || $nama === '' || $jenis_kelamin === '') {
        $error = "Username, password, nama, dan jenis kelamin wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        try {
            $koneksi->beginTransaction();

            // 1. Cek username sudah dipakai atau belum
            $cek = $koneksi->prepare("SELECT id_akun FROM akun WHERE username = :username");
            $cek->bindParam(':username', $username);
            $cek->execute();

            if ($cek->fetch()) {
                throw new Exception("Username '{$username}' sudah digunakan, silakan pilih username lain.");
            }

            // 2. Insert ke tabel akun
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_akun = $koneksi->prepare(
                "INSERT INTO akun (username, password, role) VALUES (:username, :password, 'karyawan')"
            );
            $stmt_akun->bindParam(':username', $username);
            $stmt_akun->bindParam(':password', $password_hash);
            $stmt_akun->execute();

            $id_akun_baru = $koneksi->lastInsertId();

            // 3. Insert ke tabel karyawan
            $stmt_karyawan = $koneksi->prepare(
                "INSERT INTO karyawan (id_akun, id_jabatan, nama, jenis_kelamin, tanggal_lahir, alamat, no_hp, tanggal_masuk)
                 VALUES (:id_akun, :id_jabatan, :nama, :jenis_kelamin, :tanggal_lahir, :alamat, :no_hp, :tanggal_masuk)"
            );
            $stmt_karyawan->bindParam(':id_akun', $id_akun_baru);
            $stmt_karyawan->bindParam(':id_jabatan', $id_jabatan);
            $stmt_karyawan->bindParam(':nama', $nama);
            $stmt_karyawan->bindParam(':jenis_kelamin', $jenis_kelamin);
            $stmt_karyawan->bindParam(':tanggal_lahir', $tanggal_lahir);
            $stmt_karyawan->bindParam(':alamat', $alamat);
            $stmt_karyawan->bindParam(':no_hp', $no_hp);
            $stmt_karyawan->bindParam(':tanggal_masuk', $tanggal_masuk);
            $stmt_karyawan->execute();

            $koneksi->commit();

            $_SESSION['flash_success'] = "Data karyawan '{$nama}' berhasil ditambahkan.";
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
        <h3 class="fw-bold mb-0">Tambah Karyawan</h3>
        <p class="text-muted mb-0">Buat data karyawan baru beserta akun login-nya</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="karyawan_add.php">
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
                               value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <div class="form-text">Minimal 6 karakter.</div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Role akun otomatis diset sebagai <strong>karyawan</strong>.
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
                               value="<?= htmlspecialchars($old['nama'] ?? '') ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" id="lk"
                                       <?= (($old['jenis_kelamin'] ?? '') === 'L') ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="lk">Laki-laki</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" id="pr"
                                       <?= (($old['jenis_kelamin'] ?? '') === 'P') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pr">Perempuan</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="<?= htmlspecialchars($old['tanggal_lahir'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select name="id_jabatan" class="form-select">
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach ($jabatan_list as $j): ?>
                                <option value="<?= $j['id_jabatan'] ?>"
                                    <?= ((string)($old['id_jabatan'] ?? '') === (string)$j['id_jabatan']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['nama_jabatan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (count($jabatan_list) === 0): ?>
                            <div class="form-text text-danger">Belum ada data jabatan. Tambahkan jabatan terlebih dahulu.</div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="<?= htmlspecialchars($old['no_hp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" class="form-control"
                                   value="<?= htmlspecialchars($old['tanggal_masuk'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($old['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan
        </button>
        <a href="karyawan_read.php" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

<?php require_once 'footer.php'; ?>
