<?php
require_once 'config/config.php';

// Jika sudah login
if (isset($_SESSION['id_akun'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi.";
    } else {
        // Prepared Statement
        $stmt = mysqli_prepare($conn, "SELECT * FROM akun WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['id_akun'] = $user['id_akun'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Informasi Penggajian</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #ffffff; /* Latar belakang putih bersih luar */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            min-height: vh-100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            padding: 35px;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
            /* Box shadow tipis sesuai gambar */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05); 
        }

        /* Kotak Placeholder Logo Anda */
        .logo-placeholder {
            width: 65px;
            height: 65px;
            border: 2px solid #0056b3; /* Warna border sementara */
            margin: 0 auto 12px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .logo-placeholder span {
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }

        .app-title {
            font-size: 14px;
            color: #333333;
            margin-bottom: 30px;
            font-weight: 500;
        }

        /* Customisasi Form Label sesuai gambar (Hijau) */
        .form-label-custom {
            color: #868686; 
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 6px;
        }

        /* Customisasi Input Field (Border hijau tipis, background biru muda transparan) */
        .form-control-custom {
            border: 1px solid #868686 !important;
            background-color: #ffffff !important;
            border-radius: 0px; /* Sesuai gambar yang cenderung kotak tajam */
            padding: 8px 12px;
            color: #333;
        }

        .form-control-custom:focus {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        /* Tombol Sign In Biru */
        .btn-sign-in {
            background-color: #337ab7;
            border-color: #2e6da4;
            color: #ffffff;
            border-radius: 0px;
            padding: 6px 20px;
            font-size: 14px;
        }

        .btn-sign-in:hover {
            background-color: #286090;
            border-color: #204d74;
            color: #ffffff;
        }

        .remember-text {
            color: #666666;
            font-size: 13px;
        }
        
        .checkbox-custom {
            border: 1px solid #ccc;
            width: 16px;
            height: 16px;
        }
    </style>
</head>

<body>

<div class="container login-container vh-100">
    <div class="login-box text-center">
        
        <div class="logo-placeholder">
            <span>LOGO</span>
        </div>
        
        <div class="app-title">Sistem Informasi Penggajian</div>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger text-start py-2 px-3 style="font-size: 13px;">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="text-start">
            
            <div class="mb-3">
                <label class="form-label form-label-custom">Username</label>
                <input
                    type="text"
                    name="username"
                    class="form-control form-control-custom"
                    value="ce324003"
                    required
                    autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control form-control-custom"
                    required>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="form-check d-flex align-items-center gap-2 ps-0">
                    <input type="checkbox" id="rememberMe" class="checkbox-custom">
                    <label for="rememberMe" class="remember-text">Remember Me</label>
                </div>
                <button type="submit" class="btn btn-sign-in">Sign In</button>
            </div>

        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>