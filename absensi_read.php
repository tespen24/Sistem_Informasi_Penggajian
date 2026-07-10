<?php
require_once 'koneksi.php';
cek_role(['admin']);

$judul_halaman = 'Data Absensi - SI Penggajian';

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==== FILTER ====
$cari      = trim($_GET['cari'] ?? '');
$tanggal   = trim($_GET['tanggal'] ?? '');
$status    = trim($_GET['status'] ?? '');

$where  = [];
$params = [];

if ($cari !== '') {
    $where[] = "k.nama LIKE :cari";
    $params[':cari'] = "%{$cari}%";
}
if ($tanggal !== '') {
    $where[] = "a.tanggal = :tanggal";
    $params[':tanggal'] = $tanggal;
}
if ($status !== '') {
    $where[] = "a.status = :status";
    $params[':status'] = $status;
}

$sql = "SELECT a.*, k.nama
        FROM absensi a
        JOIN karyawan k ON a.id_karyawan = k.id_karyawan";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.tanggal DESC, k.nama ASC";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$daftar_absensi = $stmt->fetchAll();

// Badge warna per status
$warna_status = [
    'Hadir' => 'success',
    'Izin'  => 'info',
    'Sakit' => 'warning',
    'Alpha' => 'danger',
];

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Data Absensi</h3>
        <p class="text-muted mb-0">Kelola riwayat kehadiran karyawan</p>
    </div>
    <a href="absensi_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Absensi
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
        <form method="GET" class="row row-cols-lg-auto g-2 align-items-center mb-3">
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama karyawan..." value="<?= htmlspecialchars($cari) ?>">
                </div>
            </div>
            <div class="col-12">
                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
            </div>
            <div class="col-12">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alpha'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <?php if ($cari !== '' || $tanggal !== '' || $status !== ''): ?>
                    <a href="index.php" class="btn btn-outline-danger">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_absensi) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data absensi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_absensi as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['jam_masuk'] ? substr($row['jam_masuk'], 0, 5) : '-' ?></td>
                                <td><?= $row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-' ?></td>
                                <td>
                                    <span class="badge bg-<?= $warna_status[$row['status']] ?? 'secondary' ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="absensi_edit.php?id=<?= $row['id_absensi'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="absensi_del.php?id=<?= $row['id_absensi'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus data absensi ini?');">
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

<?php require_once 'footer.php'; ?>
