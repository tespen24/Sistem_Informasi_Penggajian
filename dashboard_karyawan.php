<?php
require_once 'config/config.php';
cek_role(['karyawan']); // hanya karyawan yang boleh mengakses halaman ini

$judul_halaman = 'Dashboard Karyawan - SI Penggajian';

$bulan_ini = (int) date('n');
$tahun_ini = (int) date('Y');

// Cari data karyawan milik akun yang sedang login
$stmt_karyawan = $koneksi->prepare(
    "SELECT k.*, j.nama_jabatan
     FROM karyawan k
     LEFT JOIN jabatan j ON k.id_jabatan = j.id_jabatan
     WHERE k.id_akun = :id_akun"
);
$stmt_karyawan->bindParam(':id_akun', $_SESSION['id_akun']);
$stmt_karyawan->execute();
$karyawan_saya = $stmt_karyawan->fetch();

$gaji_bulan_ini       = null;
$hadir_bulan_ini      = 0;
$potongan_bulan_ini   = 0;
$riwayat_absensi_saya = [];

if ($karyawan_saya) {
    $id_karyawan_saya = $karyawan_saya['id_karyawan'];

    // Gaji bulan ini (jika sudah di-generate admin)
    $stmt_gaji = $koneksi->prepare(
        "SELECT total_gaji FROM gaji WHERE id_karyawan = :id AND bulan = :bulan AND tahun = :tahun"
    );
    $stmt_gaji->execute([':id' => $id_karyawan_saya, ':bulan' => $bulan_ini, ':tahun' => $tahun_ini]);
    $gaji_row = $stmt_gaji->fetch();
    $gaji_bulan_ini = $gaji_row ? (int) $gaji_row['total_gaji'] : null;

    // Kehadiran bulan ini
    $stmt_hadir = $koneksi->prepare(
        "SELECT COUNT(*) FROM absensi
         WHERE id_karyawan = :id AND status = 'Hadir' AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun"
    );
    $stmt_hadir->execute([':id' => $id_karyawan_saya, ':bulan' => $bulan_ini, ':tahun' => $tahun_ini]);
    $hadir_bulan_ini = (int) $stmt_hadir->fetchColumn();

    // Potongan bulan ini
    $stmt_potongan = $koneksi->prepare(
        "SELECT COALESCE(SUM(total_potongan), 0) FROM potongan_gaji
         WHERE id_karyawan = :id AND MONTH(tanggal_potongan) = :bulan AND YEAR(tanggal_potongan) = :tahun"
    );
    $stmt_potongan->execute([':id' => $id_karyawan_saya, ':bulan' => $bulan_ini, ':tahun' => $tahun_ini]);
    $potongan_bulan_ini = (int) $stmt_potongan->fetchColumn();

    // 5 riwayat absensi terbaru
    $stmt_riwayat = $koneksi->prepare(
        "SELECT tanggal, jam_masuk, jam_keluar, status FROM absensi
         WHERE id_karyawan = :id ORDER BY tanggal DESC LIMIT 5"
    );
    $stmt_riwayat->execute([':id' => $id_karyawan_saya]);
    $riwayat_absensi_saya = $stmt_riwayat->fetchAll();
}

$warna_status = [
    'Hadir' => 'success',
    'Izin'  => 'info',
    'Sakit' => 'warning',
    'Alpha' => 'danger',
];

require_once 'component/header.php';
require_once 'component/sidebar.php';
?>

<h3 class="fw-bold mb-1">Dashboard Karyawan</h3>
<p class="text-muted mb-4">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋</p>

<?php if (!$karyawan_saya): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Akun Anda belum terhubung ke data karyawan manapun. Silakan hubungi admin.
    </div>
<?php else: ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Gaji Bulan Ini</div>
                        <?php if ($gaji_bulan_ini === null): ?>
                            <div class="fs-6 fw-semibold text-muted">Belum di-generate</div>
                        <?php else: ?>
                            <div class="fs-5 fw-bold">Rp <?= number_format($gaji_bulan_ini, 0, ',', '.') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Kehadiran Bulan Ini</div>
                        <div class="fs-4 fw-bold"><?= $hadir_bulan_ini ?> <span class="fs-6 text-muted fw-normal">hari</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 me-3">
                        <i class="bi bi-dash-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Potongan Bulan Ini</div>
                        <div class="fs-5 fw-bold">Rp <?= number_format($potongan_bulan_ini, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person-badge me-1"></i> Info Kepegawaian
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Nama</td>
                            <td class="fw-semibold"><?= htmlspecialchars($karyawan_saya['nama']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jabatan</td>
                            <td class="fw-semibold"><?= htmlspecialchars($karyawan_saya['nama_jabatan'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Masuk</td>
                            <td class="fw-semibold"><?= $karyawan_saya['tanggal_masuk'] ? date('d-m-Y', strtotime($karyawan_saya['tanggal_masuk'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. HP</td>
                            <td class="fw-semibold"><?= htmlspecialchars($karyawan_saya['no_hp'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i> Riwayat Absensi Terbaru
                </div>
                <div class="card-body">
                    <?php if (count($riwayat_absensi_saya) === 0): ?>
                        <p class="text-muted mb-0 text-center py-3">Belum ada riwayat absensi.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat_absensi_saya as $row): ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= $row['jam_masuk'] ? substr($row['jam_masuk'], 0, 5) : '-' ?></td>
                                            <td><?= $row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-' ?></td>
                                            <td><span class="badge bg-<?= $warna_status[$row['status']] ?? 'secondary' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'component/footer.php'; ?>
