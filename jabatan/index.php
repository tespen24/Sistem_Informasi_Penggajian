<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Data Jabatan - SI Penggajian';

// Ambil pesan flash (jika ada)
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Pencarian
$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT j.*,
        (SELECT COUNT(*) FROM karyawan k WHERE k.id_jabatan = j.id_jabatan) AS jumlah_karyawan
        FROM jabatan j";

if ($cari !== '') {
    $sql .= " WHERE j.nama_jabatan LIKE :cari";
}
$sql .= " ORDER BY j.nama_jabatan ASC";

$stmt = $koneksi->prepare($sql);
if ($cari !== '') {
    $stmt->bindValue(':cari', "%{$cari}%");
}
$stmt->execute();
$daftar_jabatan = $stmt->fetchAll();

require_once '../component/header.php';
require_once '../component/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Data Jabatan</h3>
        <p class="text-muted mb-0">Kelola jenis jabatan beserta gaji pokok dan tunjangannya</p>
    </div>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Jabatan
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
                <input type="text" name="cari" class="form-control" placeholder="Cari nama jabatan..." value="<?= htmlspecialchars($cari) ?>">
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
                        <th>Nama Jabatan</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan Jabatan</th>
                        <th>Jumlah Karyawan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_jabatan) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data jabatan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_jabatan as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['tunjangan_jabatan'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary"><?= $row['jumlah_karyawan'] ?> orang</span>
                                </td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $row['id_jabatan'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_jabatan'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus jabatan \'<?= htmlspecialchars(addslashes($row['nama_jabatan'])) ?>\'?<?= $row['jumlah_karyawan'] > 0 ? ' Ada ' . $row['jumlah_karyawan'] . ' karyawan yang memakai jabatan ini, jabatan mereka akan dikosongkan.' : '' ?>');">
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
