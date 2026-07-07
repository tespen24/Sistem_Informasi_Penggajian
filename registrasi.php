<?php
require_once 'config/config.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi_password']);
    $role = $_POST['role'];

    // Validasi
    if (empty($username) || empty($password) || empty($konfirmasi) || empty($role)) {

        $error = "Semua field wajib diisi.";

    } elseif ($password != $konfirmasi) {

        $error = "Konfirmasi password tidak sesuai.";

    } else {

        // Cek username
        $stmt = mysqli_prepare($conn, "SELECT id_akun FROM akun WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $error = "Username sudah digunakan.";

        } else {

            // Hash Password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO akun(username,password,role)
                 VALUES(?,?,?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $username,
                $passwordHash,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {

                $success = "Registrasi berhasil.";

            } else {

                $error = "Registrasi gagal.";

            }

        }

    }

}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registrasi Akun</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container">

        <div class="row justify-content-center align-items-center vh-100">

            <div class="col-md-5">

                <div class="card shadow">

                    <div class="card-header bg-success text-white text-center">

                        <h3>Registrasi Akun</h3>

                    </div>

                    <div class="card-body">

                        <?php if ($error != ""): ?>

                            <div class="alert alert-danger">

                                <?= $error ?>

                            </div>

                        <?php endif; ?>

                        <?php if ($success != ""): ?>

                            <div class="alert alert-success">

                                <?= $success ?>

                            </div>

                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-3">

                                <label>Username</label>

                                <input type="text" name="username" class="form-control" required>

                            </div>

                            <div class="mb-3">

                                <label>Password</label>

                                <input type="password" name="password" class="form-control" required>

                            </div>

                            <div class="mb-3">

                                <label>Konfirmasi Password</label>

                                <input type="password" name="konfirmasi_password" class="form-control" required>

                            </div>

                            <div class="mb-3">

                                <label>Role</label>

                                <select name="role" class="form-select" required>

                                    <option value="">-- Pilih Role --</option>

                                    <option value="admin">

                                        Admin

                                    </option>

                                    <option value="karyawan">

                                        Karyawan

                                    </option>

                                </select>

                            </div>

                            <div class="d-grid">

                                <button class="btn btn-success">

                                    Daftar

                                </button>

                            </div>

                        </form>

                        <hr>

                        <a href="login.php" class="btn btn-outline-primary w-100">

                            Kembali ke Login

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>