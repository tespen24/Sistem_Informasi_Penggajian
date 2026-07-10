<?php
require_once 'koneksi.php';
cek_role(['admin']); // hanya admin yang boleh mengakses halaman ini

$judul_halaman = 'Dashboard Admin - SI Penggajian';

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
$bulan_ini = (int) date('n');
$tahun_ini = (int) date('Y');
$hari_ini  = date('Y-m-d');

// ==== KARTU RINGKASAN ====

// Total karyawan
$total_karyawan = (int) $koneksi->query("SELECT COUNT(*) FROM karyawan")->fetchColumn();

// Jumlah jabatan
$jumlah_jabatan = (int) $koneksi->query("SELECT COUNT(*) FROM jabatan")->fetchColumn();

// Absensi hari ini: jumlah karyawan yang sudah tercatat Hadir hari ini
$stmt_absensi_hari_ini = $koneksi->prepare(
    "SELECT COUNT(*) FROM absensi WHERE tanggal = :hari_ini AND status = 'Hadir'"
);
$stmt_absensi_hari_ini->bindParam(':hari_ini', $hari_ini);
$stmt_absensi_hari_ini->execute();
$hadir_hari_ini = (int) $stmt_absensi_hari_ini->fetchColumn();

// Gaji bulan ini: total keseluruhan slip gaji yang sudah di-generate bulan berjalan
$stmt_gaji_bulan_ini = $koneksi->prepare(
    "SELECT COALESCE(SUM(total_gaji), 0) FROM gaji WHERE bulan = :bulan AND tahun = :tahun"
);
$stmt_gaji_bulan_ini->bindParam(':bulan', $bulan_ini);
$stmt_gaji_bulan_ini->bindParam(':tahun', $tahun_ini);
$stmt_gaji_bulan_ini->execute();
$total_gaji_bulan_ini = (int) $stmt_gaji_bulan_ini->fetchColumn();

// Sudah berapa karyawan yang gajinya di-generate bulan ini (untuk keterangan tambahan)
$stmt_jumlah_slip = $koneksi->prepare(
    "SELECT COUNT(*) FROM gaji WHERE bulan = :bulan AND tahun = :tahun"
);
$stmt_jumlah_slip->bindParam(':bulan', $bulan_ini);
$stmt_jumlah_slip->bindParam(':tahun', $tahun_ini);
$stmt_jumlah_slip->execute();
$jumlah_slip_bulan_ini = (int) $stmt_jumlah_slip->fetchColumn();

// ==== RINGKASAN ABSENSI HARI INI (per status) ====
$stmt_rekap_hari_ini = $koneksi->prepare(
    "SELECT status, COUNT(*) AS jumlah FROM absensi WHERE tanggal = :hari_ini GROUP BY status"
);
$stmt_rekap_hari_ini->bindParam(':hari_ini', $hari_ini);
$stmt_rekap_hari_ini->execute();
$rekap_status_hari_ini = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpha' => 0];
foreach ($stmt_rekap_hari_ini->fetchAll() as $r) {
    $rekap_status_hari_ini[$r['status']] = (int) $r['jumlah'];
}
$belum_absen_hari_ini = max(0, $total_karyawan - array_sum($rekap_status_hari_ini));

// ==== 5 KARYAWAN TERBARU ====
$karyawan_terbaru = $koneksi->query(
    "SELECT k.nama, k.tanggal_masuk, j.nama_jabatan
     FROM karyawan k
     LEFT JOIN jabatan j ON k.id_jabatan = j.id_jabatan
     ORDER BY k.id_karyawan DESC
     LIMIT 5"
)->fetchAll();

require_once 'header.php';
require_once 'sidebar.php';
?>

<h3 class="fw-bold mb-1">Dashboard Admin</h3>
<p class="text-muted mb-4">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋</p>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Karyawan</div>
                    <div class="fs-4 fw-bold"><?= $total_karyawan ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                    <i class="bi bi-calendar-check fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Hadir Hari Ini</div>
                    <div class="fs-4 fw-bold"><?= $hadir_hari_ini ?> <span class="fs-6 text-muted fw-normal">/ <?= $total_karyawan ?></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3">
                    <i class="bi bi-briefcase-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Jumlah Jabatan</div>
                    <div class="fs-4 fw-bold"><?= $jumlah_jabatan ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Gaji Bulan Ini</div>
                    <div class="fs-5 fw-bold">Rp <?= number_format($total_gaji_bulan_ini, 0, ',', '.') ?></div>
                    <div class="text-muted" style="font-size: .75rem;"><?= $jumlah_slip_bulan_ini ?> slip di-generate</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar-check me-1"></i> Absensi Hari Ini
                <span class="text-muted fw-normal">(<?= date('d-m-Y') ?>)</span>
            </div>
            <div class="card-body">
                <?php if ($total_karyawan === 0): ?>
                    <p class="text-muted mb-0 text-center py-3">Belum ada data karyawan.</p>
                <?php else: ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-success me-1" style="font-size:.6rem;"></i>Hadir</span>
                        <span class="fw-semibold"><?= $rekap_status_hari_ini['Hadir'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-info me-1" style="font-size:.6rem;"></i>Izin</span>
                        <span class="fw-semibold"><?= $rekap_status_hari_ini['Izin'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-warning me-1" style="font-size:.6rem;"></i>Sakit</span>
                        <span class="fw-semibold"><?= $rekap_status_hari_ini['Sakit'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle-fill text-danger me-1" style="font-size:.6rem;"></i>Alpha</span>
                        <span class="fw-semibold"><?= $rekap_status_hari_ini['Alpha'] ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between text-muted">
                        <span>Belum tercatat</span>
                        <span class="fw-semibold"><?= $belum_absen_hari_ini ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-people-fill me-1"></i> Karyawan Terbaru
            </div>
            <div class="card-body">
                <?php if (count($karyawan_terbaru) === 0): ?>
                    <p class="text-muted mb-0 text-center py-3">Belum ada data karyawan.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Tanggal Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($karyawan_terbaru as $k): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($k['nama']) ?></td>
                                        <td><?= htmlspecialchars($k['nama_jabatan'] ?? '-') ?></td>
                                        <td><?= $k['tanggal_masuk'] ? date('d-m-Y', strtotime($k['tanggal_masuk'])) : '-' ?></td>
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

<?php require_once 'footer.php'; ?>
