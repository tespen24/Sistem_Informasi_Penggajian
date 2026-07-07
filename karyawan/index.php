<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Data Karyawan - SI Penggajian';
require_once '../component/header.php';
require_once '../component/sidebar.php';

// Ambil pesan flash (jika ada)
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Pencarian
$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT k.*, a.username, j.nama_jabatan
        FROM karyawan k
        LEFT JOIN akun a ON k.id_akun = a.id_akun
        LEFT JOIN jabatan j ON k.id_jabatan = j.id_jabatan";

if ($cari !== '') {
    $sql .= " WHERE k.nama LIKE :cari OR a.username LIKE :cari OR j.nama_jabatan LIKE :cari";
}
$sql .= " ORDER BY k.nama ASC";

$stmt = $koneksi->prepare($sql);
if ($cari !== '') {
    $stmt->bindValue(':cari', "%{$cari}%");
}
$stmt->execute();
$daftar_karyawan = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Data Karyawan</h3>
        <p class="text-muted mb-0">Kelola data karyawan beserta akun login-nya</p>
    </div>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Karyawan
    </a>
</div>

<?php if ($flash_success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($flash_success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($flash_error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($flash_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width: 350px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="cari" class="form-control" placeholder="Cari nama, username, atau jabatan..." value="<?= htmlspecialchars($cari) ?>">
                <button type="submit" class="btn btn-outline-secondary">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a href="index.php" class="btn btn-outline-danger">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Jenis Kelamin</th>
                        <th>Jabatan</th>
                        <th>No. HP</th>
                        <th>Tanggal Masuk</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_karyawan) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_karyawan as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>
                                <td><?= $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td><?= htmlspecialchars($row['nama_jabatan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                <td><?= $row['tanggal_masuk'] ? date('d-m-Y', strtotime($row['tanggal_masuk'])) : '-' ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $row['id_karyawan'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_karyawan'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus karyawan \'<?= htmlspecialchars(addslashes($row['nama'])) ?>\'? Akun login-nya juga akan ikut terhapus.');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../component/footer.php'; ?>
