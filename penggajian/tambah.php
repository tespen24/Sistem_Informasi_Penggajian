<?php
require_once '../config/config.php';
cek_role(['admin']);

$judul_halaman = 'Generate Gaji - SI Penggajian';

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$karyawan_list = $koneksi->query("SELECT id_karyawan, nama FROM karyawan ORDER BY nama ASC")->fetchAll();

$error = '';
$old   = ['mode' => 'satu', 'bulan' => date('n'), 'tahun' => date('Y')];

/**
 * Menghitung & menyimpan satu slip gaji untuk 1 karyawan pada periode tertentu.
 * Mengembalikan array status: ['status' => 'berhasil'|'duplikat'|'gagal', 'nama' => ..., 'pesan' => ...]
 */
function generate_gaji_karyawan(PDO $koneksi, $id_karyawan, $bulan, $tahun)
{
    // Ambil nama, gaji pokok & tunjangan dari jabatan karyawan
    $stmt = $koneksi->prepare(
        "SELECT k.nama, COALESCE(j.gaji_pokok, 0) AS gaji_pokok, COALESCE(j.tunjangan_jabatan, 0) AS tunjangan_jabatan
         FROM karyawan k
         LEFT JOIN jabatan j ON k.id_jabatan = j.id_jabatan
         WHERE k.id_karyawan = :id_karyawan"
    );
    $stmt->bindParam(':id_karyawan', $id_karyawan);
    $stmt->execute();
    $karyawan = $stmt->fetch();

    if (!$karyawan) {
        return ['status' => 'gagal', 'nama' => '-', 'pesan' => 'Karyawan tidak ditemukan.'];
    }

    // Cek apakah slip gaji periode ini sudah ada
    $cek = $koneksi->prepare(
        "SELECT id_gaji FROM gaji WHERE id_karyawan = :id_karyawan AND bulan = :bulan AND tahun = :tahun"
    );
    $cek->bindParam(':id_karyawan', $id_karyawan);
    $cek->bindParam(':bulan', $bulan);
    $cek->bindParam(':tahun', $tahun);
    $cek->execute();

    if ($cek->fetch()) {
        return ['status' => 'duplikat', 'nama' => $karyawan['nama'], 'pesan' => 'Slip gaji periode ini sudah ada.'];
    }

    // Jumlahkan total perolehan tambahan pada bulan & tahun yang sama
    $stmt_perolehan = $koneksi->prepare(
        "SELECT COALESCE(SUM(total_perolehan), 0) AS total
         FROM perolehan_gaji
         WHERE id_karyawan = :id_karyawan
           AND MONTH(tanggal_perolehan) = :bulan
           AND YEAR(tanggal_perolehan) = :tahun"
    );
    $stmt_perolehan->bindParam(':id_karyawan', $id_karyawan);
    $stmt_perolehan->bindParam(':bulan', $bulan);
    $stmt_perolehan->bindParam(':tahun', $tahun);
    $stmt_perolehan->execute();
    $total_perolehan_tambahan = (int) $stmt_perolehan->fetchColumn();

    // Jumlahkan total potongan pada bulan & tahun yang sama
    $stmt_potongan = $koneksi->prepare(
        "SELECT COALESCE(SUM(total_potongan), 0) AS total
         FROM potongan_gaji
         WHERE id_karyawan = :id_karyawan
           AND MONTH(tanggal_potongan) = :bulan
           AND YEAR(tanggal_potongan) = :tahun"
    );
    $stmt_potongan->bindParam(':id_karyawan', $id_karyawan);
    $stmt_potongan->bindParam(':bulan', $bulan);
    $stmt_potongan->bindParam(':tahun', $tahun);
    $stmt_potongan->execute();
    $total_potongan = (int) $stmt_potongan->fetchColumn();

    $total_perolehan = $karyawan['gaji_pokok'] + $karyawan['tunjangan_jabatan'] + $total_perolehan_tambahan;
    $total_gaji      = $total_perolehan - $total_potongan;

    $insert = $koneksi->prepare(
        "INSERT INTO gaji (id_karyawan, bulan, tahun, perolehan_gaji, potongan_gaji, total_gaji, tanggal_gaji)
         VALUES (:id_karyawan, :bulan, :tahun, :perolehan_gaji, :potongan_gaji, :total_gaji, :tanggal_gaji)"
    );
    $tanggal_gaji = date('Y-m-d');
    $insert->bindParam(':id_karyawan', $id_karyawan);
    $insert->bindParam(':bulan', $bulan);
    $insert->bindParam(':tahun', $tahun);
    $insert->bindParam(':perolehan_gaji', $total_perolehan, PDO::PARAM_INT);
    $insert->bindParam(':potongan_gaji', $total_potongan, PDO::PARAM_INT);
    $insert->bindParam(':total_gaji', $total_gaji, PDO::PARAM_INT);
    $insert->bindParam(':tanggal_gaji', $tanggal_gaji);
    $insert->execute();

    return ['status' => 'berhasil', 'nama' => $karyawan['nama'], 'pesan' => 'Slip gaji berhasil dibuat.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $mode  = $_POST['mode'] ?? 'satu';
    $bulan = (int) ($_POST['bulan'] ?? 0);
    $tahun = (int) ($_POST['tahun'] ?? 0);

    if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
        $error = "Bulan dan tahun wajib dipilih dengan benar!";
    } elseif ($mode === 'satu' && empty($_POST['id_karyawan'])) {
        $error = "Silakan pilih karyawan terlebih dahulu!";
    } else {
        try {
            if ($mode === 'satu') {
                $id_karyawan = $_POST['id_karyawan'];
                $hasil = generate_gaji_karyawan($koneksi, $id_karyawan, $bulan, $tahun);

                if ($hasil['status'] === 'berhasil') {
                    $_SESSION['flash_success'] = "Slip gaji {$hasil['nama']} periode {$nama_bulan[$bulan]} {$tahun} berhasil dibuat.";
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "{$hasil['nama']}: {$hasil['pesan']} Gunakan menu edit (hitung ulang) jika ingin memperbarui slip yang sudah ada.";
                }
            } else {
                // Mode generate untuk semua karyawan
                $daftar_id = array_column($karyawan_list, 'id_karyawan');

                if (empty($daftar_id)) {
                    $error = "Belum ada data karyawan.";
                } else {
                    $berhasil = 0;
                    $dilewati = 0;
                    $gagal    = 0;

                    $koneksi->beginTransaction();
                    foreach ($daftar_id as $id_karyawan) {
                        $hasil = generate_gaji_karyawan($koneksi, $id_karyawan, $bulan, $tahun);
                        if ($hasil['status'] === 'berhasil') {
                            $berhasil++;
                        } elseif ($hasil['status'] === 'duplikat') {
                            $dilewati++;
                        } else {
                            $gagal++;
                        }
                    }
                    $koneksi->commit();

                    $_SESSION['flash_success'] =
                        "Generate gaji periode {$nama_bulan[$bulan]} {$tahun} selesai: "
                        . "<strong>{$berhasil}</strong> berhasil dibuat, "
                        . "<strong>{$dilewati}</strong> dilewati (sudah ada), "
                        . "<strong>{$gagal}</strong> gagal.";
                    header("Location: index.php");
                    exit;
                }
            }
        } catch (Exception $e) {
            if ($koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            $error = "Gagal memproses data: " . $e->getMessage();
        }
    }
}

require_once '../component/header.php';
require_once '../component/sidebar.php';
?>

<div class="d-flex align-items-center mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Generate Gaji</h3>
        <p class="text-muted mb-0">Buat slip gaji bulanan berdasarkan jabatan, perolehan, dan potongan</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width: 650px;">
    <div class="card-body">
        <?php if (count($karyawan_list) === 0): ?>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Belum ada data karyawan. Tambahkan karyawan terlebih dahulu.
            </div>
        <?php else: ?>
            <form method="POST" action="tambah.php" id="formGenerate">
                <label class="form-label d-block mb-2">Mode Generate <span class="text-danger">*</span></label>
                <div class="btn-group w-100 mb-4" role="group">
                    <input type="radio" class="btn-check" name="mode" id="modeSatu" value="satu" autocomplete="off"
                        <?= ($old['mode'] ?? 'satu') === 'satu' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="modeSatu">
                        <i class="bi bi-person me-1"></i> Satu Karyawan
                    </label>

                    <input type="radio" class="btn-check" name="mode" id="modeSemua" value="semua" autocomplete="off"
                        <?= ($old['mode'] ?? '') === 'semua' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="modeSemua">
                        <i class="bi bi-people me-1"></i> Semua Karyawan
                    </label>
                </div>

                <div class="mb-3" id="wrapperKaryawan">
                    <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                    <select name="id_karyawan" class="form-select">
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($karyawan_list as $k): ?>
                            <option value="<?= $k['id_karyawan'] ?>"
                                <?= ((string)($old['id_karyawan'] ?? '') === (string)$k['id_karyawan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Bulan <span class="text-danger">*</span></label>
                        <select name="bulan" class="form-select" required>
                            <?php foreach ($nama_bulan as $angka => $nama): ?>
                                <option value="<?= $angka ?>" <?= (int)$old['bulan'] === $angka ? 'selected' : '' ?>><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" class="form-control" required min="2000" max="2100"
                               value="<?= htmlspecialchars($old['tahun']) ?>">
                    </div>
                </div>

                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    Sistem akan menghitung otomatis: <strong>gaji pokok + tunjangan jabatan</strong>, ditambah
                    <strong>total perolehan</strong> dan dikurangi <strong>total potongan</strong> pada bulan &amp; tahun yang dipilih.
                    Karyawan yang sudah punya slip gaji di periode yang sama akan dilewati.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Generate
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    const modeSatu       = document.getElementById('modeSatu');
    const modeSemua      = document.getElementById('modeSemua');
    const wrapperKaryawan = document.getElementById('wrapperKaryawan');
    const selectKaryawan  = wrapperKaryawan.querySelector('select');

    function toggleModeKaryawan() {
        if (modeSemua.checked) {
            wrapperKaryawan.style.display = 'none';
            selectKaryawan.removeAttribute('required');
        } else {
            wrapperKaryawan.style.display = 'block';
            selectKaryawan.setAttribute('required', 'required');
        }
    }

    toggleModeKaryawan();
    modeSatu.addEventListener('change', toggleModeKaryawan);
    modeSemua.addEventListener('change', toggleModeKaryawan);
</script>

<?php require_once '../component/footer.php'; ?>
