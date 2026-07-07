<?php
require_once 'config/config.php';

// Hapus semua data session dan hancurkan session
$_SESSION = [];
session_unset();
session_destroy();

header("Location: login.php");
exit;
