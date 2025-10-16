<?php
// Memulai sesi
session_start();

// Menghapus semua variabel sesi
$_SESSION = array();

// Menghancurkan sesi
session_destroy();

// Mengarahkan pengguna kembali ke halaman login (index.php)
// Path ../../../index.php digunakan asumsi admin.php ada di dalam folder src/code-php/
header("Location: ../../../index.php");
exit;
?>