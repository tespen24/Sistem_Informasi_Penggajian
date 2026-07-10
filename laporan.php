<?php
require_once 'koneksi.php';
cek_login(); // halaman ini bisa diakses admin maupun karyawan, kontennya beda per role

$judul_halaman = 'Laporan - SI Penggajian';
$role = $_SESSION['role'];

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

// ==== FILTER (dipakai oleh admin & karyawan) ====
$bulan = trim($_GET['bulan'] ?? '');
$tahun = trim($_GET['tahun'] ?? date('Y'));

// Daftar tahun untuk dropdown filter, diambil dari data absensi & gaji yang ada
$tahun_tersedia = $koneksi->query(
    "SELECT tahun FROM (
        SELECT YEAR(tanggal) AS tahun FROM absensi
        UNION SELECT tahun FROM gaji
     ) t ORDER BY tahun DESC"
)->fetchAll(PDO::FETCH_COLUMN);
if (!in_array(date('Y'), $tahun_tersedia)) {
    array_unshift($tahun_tersedia, date('Y'));
}

// =====================================================================
// LAPORAN UNTUK ADMIN — rekap seluruh karyawan
// =====================================================================
if ($role === 'admin') {
    $id_karyawan_filter = trim($_GET['id_karyawan'] ?? '');

    $karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

    // ---- Rekap Absensi ----
    $where_absensi  = [];
    $params_absensi = [];
    if ($tahun !== '') {
        $where_absensi[] = "YEAR(a.tanggal) = :tahun";
        $params_absensi[':tahun'] = $tahun;
    }
    if ($bulan !== '') {
        $where_absensi[] = "MONTH(a.tanggal) = :bulan";
        $params_absensi[':bulan'] = $bulan;
    }
    if ($id_karyawan_filter !== '') {
        $where_absensi[] = "a.id_karyawan = :id_karyawan";
        $params_absensi[':id_karyawan'] = $id_karyawan_filter;
    }

    $sql_absensi = "SELECT k.id_karyawan, k.nama,
                        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
                        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) AS izin,
                        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
                        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) AS alpha
                     FROM karyawan k
                     JOIN absensi a ON a.id_karyawan = k.id_karyawan";
    if (!empty($where_absensi)) {
        $sql_absensi .= " WHERE " . implode(" AND ", $where_absensi);
    }
    $sql_absensi .= " GROUP BY k.id_karyawan, k.nama ORDER BY k.nama ASC";

    $stmt_absensi = $koneksi->prepare($sql_absensi);
    $stmt_absensi->execute($params_absensi);
    $rekap_absensi = $stmt_absensi->fetchAll();

    // ---- Rekap Gaji ----
    $where_gaji  = [];
    $params_gaji = [];
    if ($tahun !== '') {
        $where_gaji[] = "g.tahun = :tahun";
        $params_gaji[':tahun'] = $tahun;
    }
    if ($bulan !== '') {
        $where_gaji[] = "g.bulan = :bulan";
        $params_gaji[':bulan'] = $bulan;
    }
    if ($id_karyawan_filter !== '') {
        $where_gaji[] = "g.id_karyawan = :id_karyawan";
        $params_gaji[':id_karyawan'] = $id_karyawan_filter;
    }

    $sql_gaji = "SELECT g.*, k.nama
                 FROM gaji g
                 JOIN karyawan k ON g.id_karyawan = k.id_karyawan";
    if (!empty($where_gaji)) {
        $sql_gaji .= " WHERE " . implode(" AND ", $where_gaji);
    }
    $sql_gaji .= " ORDER BY g.tahun DESC, g.bulan DESC, k.nama ASC";

    $stmt_gaji = $koneksi->prepare($sql_gaji);
    $stmt_gaji->execute($params_gaji);
    $rekap_gaji = $stmt_gaji->fetchAll();

    $total_perolehan_semua = array_sum(array_column($rekap_gaji, 'perolehan_gaji'));
    $total_potongan_semua  = array_sum(array_column($rekap_gaji, 'potongan_gaji'));
    $total_gaji_semua      = array_sum(array_column($rekap_gaji, 'total_gaji'));
}

// =====================================================================
// LAPORAN UNTUK KARYAWAN — hanya data miliknya sendiri
// =====================================================================
if ($role === 'karyawan') {
    $stmt_karyawan = $koneksi->prepare("SELECT id_karyawan, nama FROM karyawan WHERE id_akun = :id_akun");
    $stmt_karyawan->bindParam(':id_akun', $_SESSION['id_akun']);
    $stmt_karyawan->execute();
    $karyawan_saya = $stmt_karyawan->fetch();

    if ($karyawan_saya) {
        $id_karyawan_saya = $karyawan_saya['id_karyawan'];

        // ---- Rekap Absensi milik sendiri ----
        $where_absensi  = ["a.id_karyawan = :id_karyawan"];
        $params_absensi = [':id_karyawan' => $id_karyawan_saya];
        if ($tahun !== '') {
            $where_absensi[] = "YEAR(a.tanggal) = :tahun";
            $params_absensi[':tahun'] = $tahun;
        }
        if ($bulan !== '') {
            $where_absensi[] = "MONTH(a.tanggal) = :bulan";
            $params_absensi[':bulan'] = $bulan;
        }

        $sql_absensi_saya = "SELECT
                                SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
                                SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) AS izin,
                                SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
                                SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) AS alpha
                              FROM absensi a WHERE " . implode(" AND ", $where_absensi);
        $stmt_absensi_saya = $koneksi->prepare($sql_absensi_saya);
        $stmt_absensi_saya->execute($params_absensi);
        $rekap_absensi_saya = $stmt_absensi_saya->fetch();

        // ---- Riwayat Gaji milik sendiri ----
        $where_gaji  = ["g.id_karyawan = :id_karyawan"];
        $params_gaji = [':id_karyawan' => $id_karyawan_saya];
        if ($tahun !== '') {
            $where_gaji[] = "g.tahun = :tahun";
            $params_gaji[':tahun'] = $tahun;
        }
        if ($bulan !== '') {
            $where_gaji[] = "g.bulan = :bulan";
            $params_gaji[':bulan'] = $bulan;
        }

        $sql_gaji_saya = "SELECT * FROM gaji g WHERE " . implode(" AND ", $where_gaji)
                        . " ORDER BY g.tahun DESC, g.bulan DESC";
        $stmt_gaji_saya = $koneksi->prepare($sql_gaji_saya);
        $stmt_gaji_saya->execute($params_gaji);
        $riwayat_gaji_saya = $stmt_gaji_saya->fetchAll();
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
    @media print {
        .sidebar, .navbar, .offcanvas, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding-top: 0 !important; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-0">Laporan</h3>
        <p class="text-muted mb-0">
            <?= $role === 'admin' ? 'Rekap absensi dan gaji seluruh karyawan' : 'Rekap absensi dan riwayat gaji Anda' ?>
        </p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print">
        <i class="bi bi-printer me-1"></i> Cetak
    </button>
</div>

<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row row-cols-lg-auto g-2 align-items-center">
            <?php if ($role === 'admin'): ?>
                <div class="col-12">
                    <select name="id_karyawan" class="form-select">
                        <option value="">Semua Karyawan</option>
                        <?php foreach ($karyawan_list as $k): ?>
                            <option value="<?= $k['id_karyawan'] ?>" <?= (string)($id_karyawan_filter ?? '') === (string)$k['id_karyawan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <?php foreach ($tahun_tersedia as $th): ?>
                        <option value="<?= $th ?>" <?= (string)$tahun === (string)$th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mb-3 d-none d-print-block">
    <h5 class="fw-bold">Laporan Periode: <?= $bulan !== '' ? $nama_bulan[(int)$bulan] . ' ' : 'Semua Bulan ' ?><?= $tahun ?></h5>
</div>

<?php // =============================== TAMPILAN ADMIN =============================== ?>
<?php if ($role === 'admin'): ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-calendar-check me-1"></i> Rekap Absensi
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Karyawan</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Alpha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rekap_absensi) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data absensi pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($rekap_absensi as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="text-center"><span class="badge bg-success"><?= $row['hadir'] ?></span></td>
                                    <td class="text-center"><span class="badge bg-info"><?= $row['izin'] ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?= $row['sakit'] ?></span></td>
                                    <td class="text-center"><span class="badge bg-danger"><?= $row['alpha'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-wallet2 me-1"></i> Rekap Gaji
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Karyawan</th>
                            <th>Periode</th>
                            <th>Total Perolehan</th>
                            <th>Total Potongan</th>
                            <th>Total Gaji Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rekap_gaji) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data gaji pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($rekap_gaji as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= $nama_bulan[(int)$row['bulan']] ?> <?= $row['tahun'] ?></td>
                                    <td class="text-success">Rp <?= number_format($row['perolehan_gaji'], 0, ',', '.') ?></td>
                                    <td class="text-danger">Rp <?= number_format($row['potongan_gaji'], 0, ',', '.') ?></td>
                                    <td class="fw-semibold">Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (count($rekap_gaji) > 0): ?>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-success">Rp <?= number_format($total_perolehan_semua, 0, ',', '.') ?></td>
                                <td class="text-danger">Rp <?= number_format($total_potongan_semua, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($total_gaji_semua, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

<?php // =============================== TAMPILAN KARYAWAN =============================== ?>
<?php else: ?>

    <?php if (!isset($karyawan_saya) || !$karyawan_saya): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Akun Anda belum terhubung ke data karyawan manapun. Silakan hubungi admin.
        </div>
    <?php else: ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small">Hadir</div>
                        <div class="fs-3 fw-bold text-success"><?= $rekap_absensi_saya['hadir'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small">Izin</div>
                        <div class="fs-3 fw-bold text-info"><?= $rekap_absensi_saya['izin'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small">Sakit</div>
                        <div class="fs-3 fw-bold text-warning"><?= $rekap_absensi_saya['sakit'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small">Alpha</div>
                        <div class="fs-3 fw-bold text-danger"><?= $rekap_absensi_saya['alpha'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-wallet2 me-1"></i> Riwayat Gaji Saya
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Periode</th>
                                <th>Total Perolehan</th>
                                <th>Total Potongan</th>
                                <th>Total Gaji Bersih</th>
                                <th>Tanggal Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riwayat_gaji_saya) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data gaji pada periode ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($riwayat_gaji_saya as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $nama_bulan[(int)$row['bulan']] ?> <?= $row['tahun'] ?></td>
                                        <td class="text-success">Rp <?= number_format($row['perolehan_gaji'], 0, ',', '.') ?></td>
                                        <td class="text-danger">Rp <?= number_format($row['potongan_gaji'], 0, ',', '.') ?></td>
                                        <td class="fw-semibold">Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_gaji'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

<?php endif; ?>

<?php require_once 'footer.php'; ?>
