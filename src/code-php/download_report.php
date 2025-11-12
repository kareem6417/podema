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
// Saya ubah namanya agar lebih jelas
$filename = "podema_laporan_inspeksi.csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tulis header kolom
fputcsv($output, array(
    'Tahun', 
    'Tanggal_Inspeksi', 
    'No_Inspeksi', 
    'Nama_Karyawan', 
    'Divisi', 
    'Lokasi', 
    'Jenis_Perangkat', 
    'Merk_SN', 
    'Keluhan_Utama', 
    'Rekomendasi_ITE',
    'Skor_Akhir'
));

// ================================================================= //
// PERBAIKAN:                                                        //
// 1. Menghapus filter 'WHERE YEAR(date) = ?'                        //
// 2. Mengubah 'ORDER BY' agar mengurutkan berdasarkan tahun terbaru //
// ================================================================= //
$sql = "SELECT 
            YEAR(date) as Tahun,
            date as Tanggal_Inspeksi,
            no as No_Inspeksi,
            nama_user as Nama_Karyawan,
            status as Divisi,
            lokasi as Lokasi,
            jenis as Jenis_Perangkat,
            merk,
            serialnumber,
            informasi_keluhan,
            rekomendasi,
            score as Skor_Akhir
        FROM 
            form_inspeksi 
        ORDER BY 
            Tahun DESC, Tanggal_Inspeksi DESC"; // Urutkan: Tahun terbaru, Tanggal terbaru

// Karena tidak ada parameter, kita bisa pakai $conn->query()
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop melalui data dan tulis setiap baris ke file CSV
    while ($row = $result->fetch_assoc()) {
        
        // Gabungkan Merk dan Serial Number
        $merk_sn = $row['merk'] . ' / ' . $row['serialnumber'];
        
        // Bersihkan newline dari keluhan dan rekomendasi agar rapi di CSV
        $keluhan = str_replace(array("\r", "\n"), ' ', $row['informasi_keluhan']);
        $rekomendasi = str_replace(array("\r", "\n"), ' ', $row['rekomendasi']);

        // Buat baris data sesuai urutan header
        $csv_row = array(
            $row['Tahun'],
            $row['Tanggal_Inspeksi'],
            $row['No_Inspeksi'],
            $row['Nama_Karyawan'],
            $row['Divisi'],
            $row['Lokasi'],
            $row['Jenis_Perangkat'],
            $merk_sn,
            $keluhan,
            $rekomendasi,
            $row['Skor_Akhir']
        );
        
        // Tulis baris ke file CSV
        fputcsv($output, $csv_row);
    }
} else {
    // Jika tidak ada data
    fputcsv($output, array('Tidak ada data inspeksi ditemukan di database.', '', '', '', '', '', '', '', '', '', ''));
}

// Tutup file stream dan koneksi
fclose($output);
$conn->close();
exit();
?>