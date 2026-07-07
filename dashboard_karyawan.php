<?php
require_once 'config/config.php';
cek_role(['karyawan']); // hanya karyawan yang boleh mengakses halaman ini

$judul_halaman = 'Dashboard Karyawan - SI Penggajian';
require_once 'component/header.php';
require_once 'component/sidebar.php';
?>

<h3 class="fw-bold mb-1">Dashboard Karyawan</h3>
<p class="text-muted mb-4">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋</p>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Gaji Bulan Ini</div>
                    <div class="fs-4 fw-bold">-</div>
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
                    <div class="fs-4 fw-bold">-</div>
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
                    <div class="fs-4 fw-bold">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Konten dashboard (rincian gaji, riwayat absensi, dsb) akan ditambahkan pada tahap berikutnya.
        </p>
    </div>
</div>

<?php require_once 'component/footer.php'; ?>
