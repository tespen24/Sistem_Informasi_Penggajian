<?php

require_once 'config/config.php';

echo "<h2>Pengujian Config</h2>";

if ($conn) {
    echo "✅ Koneksi database berhasil";
} else {
    echo "❌ Koneksi database gagal";
}

?>