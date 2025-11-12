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

// Tentukan tahun yang akan difilter (misal: tahun saat ini)
// Ini akan mengambil '2025' berdasarkan waktu server Anda
$current_year = date('Y');

// Set header untuk memberitahu browser bahwa ini adalah file CSV
$filename = "laporan_inspeksi_detail_" . $current_year . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tulis header kolom baru yang lebih informatif ke CSV
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

// Query BARU untuk mengambil data detail dari form_inspeksi
// Dikelompokkan berdasarkan tahun ini
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
        WHERE 
            YEAR(date) = ? 
        ORDER BY 
            date ASC";

$stmt = $conn->prepare($sql);
// Bind parameter tahun saat ini
$stmt->bind_param("s", $current_year);
$stmt->execute();
$result = $stmt->get_result();

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
    fputcsv($output, array('Tidak ada data inspeksi ditemukan untuk tahun ' . $current_year, '', '', '', '', '', '', '', '', '', ''));
}

// Tutup file stream, statement, dan koneksi
fclose($output);
$stmt->close();
$conn->close();
exit();
?>