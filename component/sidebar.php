<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? '';

// Path saat ini, dipakai untuk menandai menu yang sedang aktif
$current_path = $_SERVER['SCRIPT_NAME'];

/**
 * Helper untuk menandai menu aktif berdasarkan folder/file
 */
function menu_aktif($keyword, $current_path)
{
    return (strpos($current_path, $keyword) !== false) ? 'active' : '';
}

// Definisi menu untuk masing-masing role
$menu_admin = [
    ['icon' => 'bi-speedometer2',   'label' => 'Dashboard',        'link' => 'dashboard_admin.php', 'key' => 'dashboard_admin'],
    ['icon' => 'bi-people-fill',    'label' => 'Data Karyawan',    'link' => 'karyawan/index.php',  'key' => '/karyawan/'],
    ['icon' => 'bi-briefcase-fill', 'label' => 'Jabatan',          'link' => 'jabatan/index.php',   'key' => '/jabatan/'],
    ['icon' => 'bi-calendar-check', 'label' => 'Absensi',          'link' => 'absensi/index.php',   'key' => '/absensi/'],
    ['icon' => 'bi-cash-stack',     'label' => 'Perolehan Gaji',   'link' => 'perolehan_gaji/index.php', 'key' => '/perolehan_gaji/'],
    ['icon' => 'bi-dash-circle',    'label' => 'Potongan Gaji',    'link' => 'pemotongan_gaji/index.php', 'key' => '/pemotongan_gaji/'],
    ['icon' => 'bi-wallet2',        'label' => 'Penggajian',       'link' => 'penggajian/index.php', 'key' => '/penggajian/'],
    ['icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan', 'link' => 'laporan/index.php',   'key' => '/laporan/'],
];

$menu_karyawan = [
    ['icon' => 'bi-speedometer2',   'label' => 'Dashboard',       'link' => 'dashboard_karyawan.php', 'key' => 'dashboard_karyawan'],
    ['icon' => 'bi-wallet2',        'label' => 'Data Gaji',       'link' => 'penggajian/index.php',   'key' => '/penggajian/'],
    ['icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan', 'link' => 'laporan/index.php',      'key' => '/laporan/'],
];

$menu = ($role === 'admin') ? $menu_admin : $menu_karyawan;
?>

<style>
    .sidebar {
        width: 240px;
        height: 100vh;
        position: fixed;
        top: 56px;
        left: 0;
        background: #084298;
        padding-top: 1rem;
        overflow-y: auto;
    }
    .sidebar .nav-link {
        color: #dbe6ff;
        padding: .65rem 1.2rem;
        border-radius: .4rem;
        margin: .15rem .6rem;
        font-size: .93rem;
    }
    .sidebar .nav-link:hover {
        background: rgba(255,255,255,.1);
        color: #fff;
    }
    .sidebar .nav-link.active {
        background: #0d6efd;
        color: #fff;
        font-weight: 600;
    }
    .main-content {
        margin-left: 240px;
        padding-top: 76px;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        padding-bottom: 2rem;
        min-height: 100vh;
        background: #f4f6f9;
    }
    @media (max-width: 991.98px) {
        .main-content { margin-left: 0; }
    }
</style>

<!-- Sidebar versi desktop -->
<div class="sidebar d-none d-lg-block">
    <ul class="nav flex-column">
        <?php foreach ($menu as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= menu_aktif($item['key'], $current_path) ?>" href="<?= BASE_URL . $item['link'] ?>">
                    <i class="bi <?= $item['icon'] ?> me-2"></i><?= $item['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li><hr class="text-white-50 mx-3"></li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>

<!-- Sidebar versi mobile (offcanvas) -->
<div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="bi bi-cash-coin me-1"></i> SI Penggajian</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column">
            <?php foreach ($menu as $item): ?>
                <li class="nav-item">
                    <a class="nav-link text-white <?= menu_aktif($item['key'], $current_path) ? 'fw-bold' : '' ?>" href="<?= BASE_URL . $item['link'] ?>">
                        <i class="bi <?= $item['icon'] ?> me-2"></i><?= $item['label'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><hr class="text-white-50 mx-3"></li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= BASE_URL ?>logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="main-content">
