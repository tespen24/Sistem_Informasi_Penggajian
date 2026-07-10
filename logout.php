<?php
require_once 'koneksi.php';

// Hapus semua data session dan hancurkan session
$_SESSION = [];
session_unset();
session_destroy();

header("Location: login.php");
exit;
