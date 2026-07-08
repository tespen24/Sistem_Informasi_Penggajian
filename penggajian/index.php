<?php
require_once '../config/config.php';
cek_login(); // admin bisa kelola semua data, karyawan hanya bisa melihat datanya sendiri

$judul_halaman = 'Penggajian - SI Penggajian';
$role = $_SESSION['role'];

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

// Jika karyawan, cari id_karyawan miliknya sendiri lebih dulu
$id_karyawan_saya = null;
if ($role === 'karyawan') {
    $stmt_saya = $koneksi->prepare("SELECT id_karyawan FROM karyawan WHERE id_akun = :id_akun");
    $stmt_saya->bindParam(':id_akun', $_SESSION['id_akun']);
    $stmt_saya->execute();
    $id_karyawan_saya = $stmt_saya->fetchColumn() ?: null;
}

// ==== FILTER ====
$cari  = trim($_GET['cari'] ?? '');
$bulan = trim($_GET['bulan'] ?? '');
$tahun = trim($_GET['tahun'] ?? '');

$where  = [];
$params = [];

if ($role === 'karyawan') {
    // Karyawan hanya boleh melihat datanya sendiri, apapun yang terjadi
    $where[] = "g.id_karyawan = :id_karyawan_saya";
    $params[':id_karyawan_saya'] = $id_karyawan_saya;
} elseif ($cari !== '') {
    $where[] = "k.nama LIKE :cari";
    $params[':cari'] = "%{$cari}%";
}
if ($bulan !== '') {
    $where[] = "g.bulan = :bulan";
    $params[':bulan'] = $bulan;
}
if ($tahun !== '') {
    $where[] = "g.tahun = :tahun";
    $params[':tahun'] = $tahun;
}

$daftar_gaji = [];
if ($role === 'admin' || $id_karyawan_saya) {
    $sql = "SELECT g.*, k.nama
            FROM gaji g
            JOIN karyawan k ON g.id_karyawan = k.id_karyawan";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY g.tahun DESC, g.bulan DESC, k.nama ASC";

    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    $daftar_gaji = $stmt->fetchAll();
}

// Daftar tahun untuk filter (dari data yang ada, minimal tahun berjalan)
$tahun_tersedia = $koneksi->query("SELECT DISTINCT tahun FROM gaji ORDER BY tahun DESC")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array(date('Y'), $tahun_tersedia)) {
    array_unshift($tahun_tersedia, date('Y'));
}

require_once '../component/header.php';
require_once '../component/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-0"><?= $role === 'admin' ? 'Penggajian' : 'Data Gaji Saya' ?></h3>
        <p class="text-muted mb-0">
            <?= $role === 'admin' ? 'Rekap slip gaji bulanan karyawan' : 'Riwayat slip gaji Anda' ?>
        </p>
    </div>
    <?php if ($role === 'admin'): ?>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Generate Gaji
        </a>
    <?php endif; ?>
</div>

<?php if ($flash_success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-1"></i> <?= $flash_success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($flash_error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $flash_error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($role === 'karyawan' && !$id_karyawan_saya): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Akun Anda belum terhubung ke data karyawan manapun. Silakan hubungi admin.
    </div>
<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row row-cols-lg-auto g-2 align-items-center mb-3">
            <?php if ($role === 'admin'): ?>
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" placeholder="Cari nama karyawan..." value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-12">
                <select name="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($nama_bulan as $angka => $nama): ?>
                        <option value="<?= $angka ?>" <?= (string)$bulan === (string)$angka ? 'selected' : '' ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <select name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($tahun_tersedia as $th): ?>
                        <option value="<?= $th ?>" <?= (string)$tahun === (string)$th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <?php if ($cari !== '' || $bulan !== '' || $tahun !== ''): ?>
                    <a href="index.php" class="btn btn-outline-danger">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <?php if ($role === 'admin'): ?><th>Nama Karyawan</th><?php endif; ?>
                        <th>Periode</th>
                        <th>Total Perolehan</th>
                        <th>Total Potongan</th>
                        <th>Total Gaji Bersih</th>
                        <th>Tanggal Slip</th>
                        <?php if ($role === 'admin'): ?><th class="text-center">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_gaji) === 0): ?>
                        <tr>
                            <td colspan="<?= $role === 'admin' ? 8 : 5 ?>" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Belum ada data gaji <?= $role === 'admin' ? 'yang di-generate.' : 'untuk Anda pada periode ini.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_gaji as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <?php if ($role === 'admin'): ?><td><?= htmlspecialchars($row['nama']) ?></td><?php endif; ?>
                                <td><?= $nama_bulan[(int)$row['bulan']] ?> <?= $row['tahun'] ?></td>
                                <td class="text-success fw-semibold">Rp <?= number_format($row['perolehan_gaji'], 0, ',', '.') ?></td>
                                <td class="text-danger fw-semibold">Rp <?= number_format($row['potongan_gaji'], 0, ',', '.') ?></td>
                                <td class="fw-bold">Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_gaji'])) ?></td>
                                <?php if ($role === 'admin'): ?>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $row['id_gaji'] ?>" class="btn btn-sm btn-warning text-white" title="Hitung Ulang / Detail">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_gaji'] ?>"
                                       class="btn btn-sm btn-danger"
                                       title="Hapus"
                                       onclick="return confirm('Yakin ingin menghapus slip gaji ini?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once '../component/footer.php'; ?>
