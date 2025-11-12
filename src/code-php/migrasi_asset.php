<?php
session_start();

// Pastikan hanya admin yang bisa menjalankan ini
if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

// Koneksi Database
$host = "mandiricoal.net"; 
$user = "podema"; 
$pass = "Jam10pagi#"; 
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengatur UTF-8 agar nama_user tidak bermasalah
$conn->set_charset("utf8mb4");

echo "<!DOCTYPE html><html lang='id'><head><title>Migrasi Aset</title>";
echo "<style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; } hr { border: 1px solid #ddd; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";
echo "</head><body>";
echo "<h1>Memulai Migrasi Aset dari 'form_inspeksi' ke 'master_aset'...</h1>";
echo "<hr>";
echo "<p>Skrip ini akan membaca semua inspeksi, mencari aset unik (via Serial Number), dan memindahkannya ke tabel master...</p>";

// 1. Ambil semua serial number unik dari form_inspeksi (yang tidak kosong)
$sql_sn = "SELECT DISTINCT serialnumber FROM form_inspeksi WHERE serialnumber IS NOT NULL AND serialnumber != ''";
$result_sn = $conn->query($sql_sn);

if ($result_sn->num_rows == 0) {
    die("<h3>Tidak ada serial number unik ditemukan di 'form_inspeksi'. Migrasi dihentikan.</h3></body></html>");
}

$aset_berhasil = 0;
$aset_gagal = 0;
$aset_dilewati = 0;

while ($row_sn = $result_sn->fetch_assoc()) {
    $sn = $row_sn['serialnumber'];

    // 2. Untuk setiap SN, ambil data inspeksi TERBARU (berdasarkan tanggal & 'no' terbaru)
    $sql_latest = "SELECT * FROM form_inspeksi WHERE serialnumber = ? ORDER BY date DESC, no DESC LIMIT 1";
    $stmt_latest = $conn->prepare($sql_latest);
    $stmt_latest->bind_param("s", $sn);
    $stmt_latest->execute();
    $latest_data = $stmt_latest->get_result()->fetch_assoc();

    if ($latest_data) {
        $jenis = $latest_data['jenis'];
        $merk = $latest_data['merk'];
        $lokasi = $latest_data['lokasi'];
        $nama_user_text = $latest_data['nama_user']; // Nama pengguna (Teks) dari inspeksi terakhir
        $tanggal_terakhir = $latest_data['date']; // Tanggal inspeksi terakhir
        
        // 3. Cari user_id (Angka) di tabel 'users' berdasarkan nama_user (Teks)
        $user_id_int = null; // Ini adalah ID yang akan kita masukkan
        if (!empty($nama_user_text)) {
            $sql_user = "SELECT user_id FROM users WHERE name = ? LIMIT 1";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("s", $nama_user_text);
            $stmt_user->execute();
            $user_result = $stmt_user->get_result();
            if ($user_row = $user_result->fetch_assoc()) {
                $user_id_int = $user_row['user_id'];
            } else {
                echo "<p class='info'><b>INFO:</b> User '{$nama_user_text}' (dari SN: {$sn}) tidak ditemukan di tabel 'users'. Kolom id_user akan di-set NULL.</p>";
            }
            $stmt_user->close();
        }

        // 4. Masukkan ke tabel master_aset
        // 'INSERT ... ON DUPLICATE KEY UPDATE' akan memasukkan baru, 
        // atau meng-update jika SN sudah ada (ini aman untuk dijalankan ulang)
        
        // =============================================================
        // PERUBAHAN ANDA: 'id_karyawan_pemegang' diganti menjadi 'id_user'
        // =============================================================
        $sql_insert = "INSERT INTO master_aset 
                       (serial_number, jenis_perangkat, merk, lokasi, id_user, tanggal_inspeksi_terakhir, status_aset) 
                       VALUES (?, ?, ?, ?, ?, ?, 'Aktif')
                       ON DUPLICATE KEY UPDATE
                       jenis_perangkat = VALUES(jenis_perangkat),
                       merk = VALUES(merk),
                       lokasi = VALUES(lokasi),
                       id_user = VALUES(id_user),
                       tanggal_inspeksi_terakhir = VALUES(tanggal_inspeksi_terakhir)";
                       
        $stmt_insert = $conn->prepare($sql_insert);
        // Tipe data bind_param: s(sn), s(jenis), s(merk), s(lokasi), i(id_user_int), s(tanggal_terakhir)
        $stmt_insert->bind_param("ssssis", $sn, $jenis, $merk, $lokasi, $user_id_int, $tanggal_terakhir);
        
        if ($stmt_insert->execute()) {
            if ($stmt_insert->affected_rows > 0) {
                echo "<p class='success'><b>BERHASIL:</b> Aset dengan SN '<b>{$sn}</b>' (User ID: {$user_id_int}) berhasil dimigrasi/diupdate.</p>";
                $aset_berhasil++;
            } else {
                echo "<p class='info'><b>INFO:</b> Aset dengan SN '<b>{$sn}</b>' datanya sudah sama, dilewati.</p>";
                $aset_dilewati++;
            }
        } else {
            echo "<p class='error'><b>GAGAL (Insert):</b> Error untuk SN '{$sn}' - " . $stmt_insert->error . "</p>";
            $aset_gagal++;
        }
        $stmt_insert->close();
        
    } else {
        echo "<p class='error'><b>GAGAL (Data):</b> Tidak ditemukan data terbaru untuk SN '{$sn}'.</p>";
        $aset_gagal++;
    }
    $stmt_latest->close();
}

echo "<hr>";
echo "<h2>Migrasi Selesai!</h2>";
echo "<h3 class='success'>Aset Berhasil Dimigrasi/Update: $aset_berhasil</h3>";
echo "<h3 class='info'>Aset Dilewati (Data Sama): $aset_dilewati</h3>";
echo "<h3 class='error'>Aset Gagal Diproses: $aset_gagal</h3>";
echo "</body></html>";

$conn->close();
?>