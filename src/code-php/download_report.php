<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  // Jika tidak ada sesi, jangan tampilkan apa-apa selain error
  die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Konfigurasi Database
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Set header untuk memberitahu browser bahwa ini adalah file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_inspeksi_tahunan.csv');

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tulis header kolom ke CSV
fputcsv($output, array('Tahun', 'Tipe Perangkat', 'Jumlah Inspeksi'));

// Query untuk mengambil data yang diminta
// (Jumlah perangkat yang diinspeksi, dikelompokkan per tahun dan per jenis)
$sql = "SELECT YEAR(date) as Tahun, jenis as Tipe_Perangkat, COUNT(*) as Jumlah_Inspeksi 
        FROM form_inspeksi 
        WHERE jenis IS NOT NULL AND jenis != '' AND date IS NOT NULL
        GROUP BY Tahun, Tipe_Perangkat 
        ORDER BY Tahun DESC, Tipe_Perangkat ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop melalui data dan tulis setiap baris ke file CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, array('Tidak ada data ditemukan.', '', ''));
}

// Tutup koneksi
$conn->close();
fclose($output);
exit();
?>