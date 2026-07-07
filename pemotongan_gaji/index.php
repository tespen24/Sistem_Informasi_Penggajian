<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'potongan Gaji - SI Penggajian';

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==== FILTER ====
$cari    = trim($_GET['cari'] ?? '');
$bulan   = trim($_GET['bulan'] ?? '');   // format: YYYY-MM dari input type="month"

$where  = [];
$params = [];

if ($cari !== '') {
    $where[] = "(k.nama LIKE :cari OR p.nama_potongan LIKE :cari)";
    $params[':cari'] = "%{$cari}%";
}
if ($bulan !== '') {
    $where[] = "DATE_FORMAT(p.tanggal_potongan, '%Y-%m') = :bulan";
    $params[':bulan'] = $bulan;
}

$sql = "SELECT p.*, k.nama
        FROM potongan_gaji p
        JOIN karyawan k ON p.id_karyawan = k.id_karyawan";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY p.tanggal_potongan DESC, k.nama ASC";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$daftar_potongan = $stmt->fetchAll();

// Total keseluruhan (sesuai filter yang aktif)
$total_keseluruhan = array_sum(array_column($daftar_potongan, 'total_potongan'));

require_once '../component/header.php';
require_once '../component/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Potongan Gaji</h3>
        <p class="text-muted mb-0">Kelola pendapatan tambahan / bonus karyawan di luar gaji pokok</p>
    </div>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah potongan
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
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama karyawan / jenis potongan..." value="<?= htmlspecialchars($cari) ?>">
                </div>
            </div>
            <div class="col-12">
                <input type="month" name="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <?php if ($cari !== '' || $bulan !== ''): ?>
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
                        <th>Jenis potongan</th>
                        <th>Total potongan</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_potongan) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data potongan gaji.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_potongan as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['nama_potongan']) ?></td>
                                <td class="text-success fw-semibold">Rp <?= number_format($row['total_potongan'], 0, ',', '.') ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_potongan'])) ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $row['id_potongan'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_potongan'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus data potongan ini?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (count($daftar_potongan) > 0): ?>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total Keseluruhan:</td>
                            <td class="text-success">Rp <?= number_format($total_keseluruhan, 0, ',', '.') ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once '../component/footer.php'; ?>
