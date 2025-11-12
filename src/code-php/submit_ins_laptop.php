<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
    header("location: ./index.php");
    exit();
}

// Koneksi Database
$host = "mandiricoal.net"; $user = "podema"; $pass = "Jam10pagi#"; $db = "podema";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// =================================================================
// 1. AMBIL DATA DARI FORM (Termasuk ID Tugas)
// =================================================================
$jadwal_id = (int)($_POST['jadwal_id'] ?? 0); // ID Jadwal (jika dari To-Do List)
$aset_id = (int)($_POST['aset_id'] ?? 0); // ID Aset (jika dari To-Do List)
// NIK diambil dari hidden field di form
$staf_it_nik = $_POST['pelaksana_nik_final'] ?? ($_SESSION['nik'] ?? 'system'); 

// Amankan dan ambil data form lainnya
$jenis = $_POST["jenis"] ?? '';
$date = $_POST["date"] ?? '';
$merk = $_POST["merk"] ?? '';
$lokasi = $_POST["lokasi"] ?? '';
$status = $_POST["status"] ?? ''; // Ini adalah Divisi
$serialnumber = $_POST["serialnumber"] ?? '';
$informasi_keluhan = $_POST["informasi_keluhan"] ?? '';
$hasil_pemeriksaan = $_POST["hasil_pemeriksaan"] ?? '';
$rekomendasi = $_POST["rekomendasi"] ?? '';
$nama_user = $_POST["nama_user"] ?? '';

// Ambil Skor dari setiap item inspeksi (Semua ini harus di-bind sebagai INTEGER 'i')
$age_score = !empty($_POST["age"]) ? (int)$_POST["age"] : 0;
$casing_lap_score = !empty($_POST["casing_lap"]) ? (int)$_POST["casing_lap"] : 0;
$layar_lap_score = !empty($_POST["layar_lap"]) ? (int)$_POST["layar_lap"] : 0;
$engsel_lap_score = !empty($_POST["engsel_lap"]) ? (int)$_POST["engsel_lap"] : 0;
$keyboard_lap_score = !empty($_POST["keyboard_lap"]) ? (int)$_POST["keyboard_lap"] : 0;
$touchpad_lap_score = !empty($_POST["touchpad_lap"]) ? (int)$_POST["touchpad_lap"] : 0;
$booting_lap_score = !empty($_POST["booting_lap"]) ? (int)$_POST["booting_lap"] : 0;
$multi_lap_score = !empty($_POST["multi_lap"]) ? (int)$_POST["multi_lap"] : 0;
$tampung_lap_score = !empty($_POST["tampung_lap"]) ? (int)$_POST["tampung_lap"] : 0;
$isi_lap_score = !empty($_POST["isi_lap"]) ? (int)$_POST["isi_lap"] : 0;
$port_lap_score = !empty($_POST["port_lap"]) ? (int)$_POST["port_lap"] : 0;
$audio_lap_score = !empty($_POST["audio_lap"]) ? (int)$_POST["audio_lap"] : 0;
$software_lap_score = !empty($_POST["software_lap"]) ? (int)$_POST["software_lap"] : 0;

// Kalkulasi Total Skor
$total_score = $age_score + $casing_lap_score + $layar_lap_score + $engsel_lap_score + $keyboard_lap_score + 
               $touchpad_lap_score + $booting_lap_score + $multi_lap_score + $tampung_lap_score + 
               $isi_lap_score + $port_lap_score + $audio_lap_score + $software_lap_score;


// =================================================================
// 2. INSERT KE form_inspeksi
// =================================================================
$sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, engsel_lap, keyboard_lap, touchpad_lap, booting_lap, multi_lap, tampung_lap, isi_lap, port_lap, audio_lap, software_lap, score)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>> KOREKSI FATAL ERROR ADA DI BARIS INI <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
// Koreksi: 10 's' diikuti 14 'i'
$stmt->bind_param("ssssssssssiiiiiiiiiiiiii", 
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>> KOREKSI FATAL ERROR ADA DI BARIS INI <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    $date, $jenis, $merk, $lokasi, $nama_user, $status, $serialnumber, $informasi_keluhan, $hasil_pemeriksaan, $rekomendasi, 
    $age_score, $casing_lap_score, $layar_lap_score, $engsel_lap_score, $keyboard_lap_score, $touchpad_lap_score, 
    $booting_lap_score, $multi_lap_score, $tampung_lap_score, $isi_lap_score, $port_lap_score, $audio_lap_score, 
    $software_lap_score, $total_score
);

if ($stmt->execute()) {
    // Dapatkan ID dari form_inspeksi yang baru saja dibuat
    $id_hasil_inspeksi = $stmt->insert_id; 
    $stmt->close();

    // =================================================================
    // 3. UPDATE STATUS TUGAS (Hanya jika berasal dari To-Do List)
    // =================================================================
    if ($jadwal_id > 0 && $aset_id > 0) {
        
        // 3a. Update status task di 'jadwal_inspeksi' menjadi "Completed"
        $update_jadwal_sql = "UPDATE jadwal_inspeksi 
                              SET status_jadwal = 'Completed',
                                  id_hasil_inspeksi = ?,
                                  id_staf_it = ?, -- Menyimpan NIK Pelaksana Utama
                                  tanggal_selesai = NOW()
                              WHERE jadwal_id = ?";
        $stmt_update_jadwal = $conn->prepare($update_jadwal_sql);
        $stmt_update_jadwal->bind_param("isi", $id_hasil_inspeksi, $staf_it_nik, $jadwal_id);
        $stmt_update_jadwal->execute();
        $stmt_update_jadwal->close();

        // 3b. Update tanggal inspeksi terakhir di 'master_aset'
        $update_aset_sql = "UPDATE master_aset 
                            SET tanggal_inspeksi_terakhir = ?
                            WHERE aset_id = ?";
        $stmt_update_aset = $conn->prepare($update_aset_sql);
        $stmt_update_aset->bind_param("si", $date, $aset_id);
        $stmt_update_aset->execute();
        $stmt_update_aset->close();
    }

    // $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";

    // if (isset($_FILES['screenshot_file'])) {
    //     foreach ($_FILES['screenshot_file']['tmp_name'] as $key => $tmp_name) {
    //         if ($_FILES['screenshot_file']['error'][$key] == UPLOAD_ERR_OK) {
    //             $original_name = basename($_FILES['screenshot_file']['name'][$key]);
    //             $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
    //             // Membuat nama file unik berdasarkan ID inspeksi, timestamp, dan urutan file
    //             $file_name = $id_hasil_inspeksi . '_' . time() . '_' . $key . '.' . $file_extension;
    //             $target_screenshot_file = $target_screenshot_dir . $file_name;

    //             // Pindahkan file ke folder tujuan
    //             if (move_uploaded_file($tmp_name, $target_screenshot_file)) {
    //                 // Simpan nama file ke tabel `screenshots`
    //                 $stmt_scr = $conn->prepare("INSERT INTO screenshots (form_no, screenshot_name) VALUES (?, ?)");
    //                 $stmt_scr->bind_param("is", $id_hasil_inspeksi, $file_name);
    //                 $stmt_scr->execute();
    //                 $stmt_scr->close();
    //             }
    //         }
    //     }
    // }

    // 5. Redirect ke halaman hasil inspeksi yang baru
    echo "<script>alert('Inspeksi berhasil disimpan. Task telah ditandai sebagai Completed.');</script>";
    echo "<script>window.location.href='viewinspeksi.php?no=" . $id_hasil_inspeksi . "';</script>";
    exit();

} else {
    // Error handling yang lebih detail
    $error_message = "Error saat menyimpan data inspeksi: " . $stmt->error;
    // Log error ini agar Anda bisa melihatnya di log PHP server
    error_log($error_message, 0); 
    // Tampilkan pesan error sederhana (atau hapus ini di lingkungan produksi)
    echo "<h1>Terjadi Kesalahan! (HTTP 500)</h1>";
    echo "<p>Detail Error: " . htmlspecialchars($stmt->error) . "</p>";
    echo "<p>Silakan periksa log server Anda untuk informasi lebih lanjut.</p>";
    $stmt->close();
    $conn->close();
}
?>