<?php

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#"; 
$db = "podema";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$jadwal_id = (int)($_POST['jadwal_id'] ?? 0); 
$aset_id = (int)($_POST['aset_id'] ?? 0); 
// NIK Pelaksana Utama (dari hidden field)
$staf_it_nik = $_POST['pelaksana_nik_final'] ?? ($_SESSION['nik'] ?? 'system'); 

// Ambil data form lainnya
$jenis = $_POST["jenis"] ?? '';
$date = $_POST["date"] ?? '';
$merk = $_POST["merk"] ?? '';
$lokasi = $_POST["lokasi"] ?? '';
$status = $_POST["status"] ?? '';
$serialnumber = $_POST["serialnumber"] ?? '';
$informasi_keluhan = $_POST["informasi_keluhan"] ?? '';
$hasil_pemeriksaan = $_POST["hasil_pemeriksaan"] ?? '';
$rekomendasi = $_POST["rekomendasi"] ?? '';
$nama_user = $_POST["nama_user"] ?? '';

// Ambil dan konversi semua Skor ke Integer
$age_score = (int)($_POST["age"] ?? 0);
$casing_lap_score = (int)($_POST["casing_lap"] ?? 0);
$layar_lap_score = (int)($_POST["layar_lap"] ?? 0);
$engsel_lap_score = (int)($_POST["engsel_lap"] ?? 0);
$keyboard_lap_score = (int)($_POST["keyboard_lap"] ?? 0);
$touchpad_lap_score = (int)($_POST["touchpad_lap"] ?? 0);
$booting_lap_score = (int)($_POST["booting_lap"] ?? 0);
$multi_lap_score = (int)($_POST["multi_lap"] ?? 0);
$tampung_lap_score = (int)($_POST["tampung_lap"] ?? 0);
$isi_lap_score = (int)($_POST["isi_lap"] ?? 0);
$port_lap_score = (int)($_POST["port_lap"] ?? 0);
$audio_lap_score = (int)($_POST["audio_lap"] ?? 0);
$software_lap_score = (int)($_POST["software_lap"] ?? 0);

// Kalkulasi Total Skor
$total_score = $age_score + $casing_lap_score + $layar_lap_score + $engsel_lap_score + $keyboard_lap_score + 
               $touchpad_lap_score + $booting_lap_score + $multi_lap_score + $tampung_lap_score + 
               $isi_lap_score + $port_lap_score + $audio_lap_score + $software_lap_score;



$sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, engsel_lap, keyboard_lap, touchpad_lap, booting_lap, multi_lap, tampung_lap, isi_lap, port_lap, audio_lap, software_lap, score)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);


$stmt->bind_param("ssssssssssiiiiiiiiiiiiii", 
    $date, $jenis, $merk, $lokasi, $nama_user, $status, $serialnumber, $informasi_keluhan, $hasil_pemeriksaan, $rekomendasi, 
    $age_score, $casing_lap_score, $layar_lap_score, $engsel_lap_score, $keyboard_lap_score, $touchpad_lap_score, 
    $booting_lap_score, $multi_lap_score, $tampung_lap_score, $isi_lap_score, $port_lap_score, $audio_lap_score, 
    $software_lap_score, $total_score
);

if ($stmt->execute()) {
    $id_hasil_inspeksi = $conn->insert_id; 
    $stmt->close();

    if ($jadwal_id > 0 && $aset_id > 0) {
        
        $update_jadwal_sql = "UPDATE jadwal_inspeksi 
                              SET status_jadwal = 'Completed',
                                  id_hasil_inspeksi = ?,
                                  id_staf_it = ?, 
                                  tanggal_selesai = NOW()
                              WHERE jadwal_id = ?";
        $stmt_update_jadwal = $conn->prepare($update_jadwal_sql);
        $stmt_update_jadwal->bind_param("isi", $id_hasil_inspeksi, $staf_it_nik, $jadwal_id);
        $stmt_update_jadwal->execute();
        $stmt_update_jadwal->close();

        $update_aset_sql = "UPDATE master_aset 
                            SET tanggal_inspeksi_terakhir = ?
                            WHERE aset_id = ?";
        $stmt_update_aset = $conn->prepare($update_aset_sql);
        $stmt_update_aset->bind_param("si", $date, $aset_id);
        $stmt_update_aset->execute();
        $stmt_update_aset->close();
    }

    // 4. Redirect ke halaman hasil inspeksi yang baru
    echo "<script>alert('Inspeksi berhasil disimpan. Task telah ditandai sebagai Completed.');</script>";
    echo "<script>window.location.href='viewinspeksi.php?no=" . $id_hasil_inspeksi . "';</script>";
    exit();

} else {
    // Penanganan Error SQL
    $error_message = "Error saat menyimpan data inspeksi. Detail SQL Error: " . $stmt->error;
    error_log($error_message, 0); 
    
    // Tampilkan pesan error jika error_reporting diaktifkan
    echo "<h1>Terjadi Kesalahan Kritis! (HTTP 500)</h1>";
    echo "<p>Detail Error: " . htmlspecialchars($stmt->error) . "</p>";
    echo "<p>Form tidak tersimpan. Silakan hubungi Administrator dan berikan detail error di atas.</p>";
    $stmt->close();
    $conn->close();
}
?>