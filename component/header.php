<?php
// Pastikan session sudah dimulai (config.php harus di-include sebelum file ini)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nama_tampil = $_SESSION['username'] ?? 'User';
$role_tampil = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul_halaman ?? 'Sistem Informasi Penggajian' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>dashboard_<?= $role_tampil === 'admin' ? 'admin' : 'karyawan' ?>.php">
            <i class="bi bi-cash-coin me-1"></i> SISFO Penggajian
        </a>

        <div class="d-flex align-items-center ms-auto">
            <span class="text-white me-3 d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($nama_tampil) ?>
                <span class="badge bg-light text-primary text-capitalize ms-1"><?= htmlspecialchars($role_tampil) ?></span>
            </span>

            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear-fill"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text small text-muted d-md-none">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($nama_tampil) ?>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider d-md-none"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
