<?php
require_once 'config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_akun'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Informasi Penggajian</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#f5f6fa;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-dashboard{
            max-width:500px;
            margin:100px auto;
            border:none;
            border-radius:12px;
            box-shadow:0 5px 15px rgba(0,0,0,.1);
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card card-dashboard">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Sistem Informasi Penggajian</h4>
        </div>

        <div class="card-body">

            <h5>Selamat Datang</h5>

            <hr>

            <p><strong>Username :</strong> <?= htmlspecialchars($username); ?></p>

            <p><strong>Role :</strong> <?= htmlspecialchars(ucfirst($role)); ?></p>

            <div class="mt-4">
                <a href="logout.php" class="btn btn-danger">
                    Logout
                </a>
            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>