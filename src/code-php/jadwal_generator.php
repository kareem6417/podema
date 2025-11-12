<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

// Konfigurasi Database (Gunakan PDO agar lebih aman)
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

echo "<!DOCTYPE html><html lang='id'><head><title>Generator Jadwal</title>";
echo "<style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; } hr { border: 1px solid #ddd; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";
echo "</head><body>";
echo "<h1>Memulai Generator Jadwal Inspeksi...</h1>";
echo "<p>Membaca tabel <b>master_aset</b> dan membuat jadwal (task) di <b>jadwal_inspeksi</b>.</p><hr>";

// 1. Ambil semua aset yang aktif
$stmt_aset = $conn->query("SELECT aset_id, serial_number, frekuensi_inspeksi_bulan, tanggal_inspeksi_terakhir 
                          FROM master_aset 
                          WHERE status_aset = 'Aktif'");
$semua_aset = $stmt_aset->fetchAll();

$total_dibuat = 0;
$total_dilewati = 0;

foreach ($semua_aset as $aset) {
    $aset_id = $aset['aset_id'];
    $sn = $aset['serial_number'];
    
    // Ambil frekuensi (default 6 bulan jika NULL)
    $frekuensi = (int)($aset['frekuensi_inspeksi_bulan'] ?? 6);
    if ($frekuensi <= 0) $frekuensi = 6; // Safety check

    // Tentukan tanggal mulai: dari inspeksi terakhir, atau hari ini jika belum pernah
    $tanggal_mulai = $aset['tanggal_inspeksi_terakhir'] ?? date('Y-m-d');
    
    // Hitung tanggal jadwal berikutnya
    $tanggal_jadwal_baru = date('Y-m-d', strtotime($tanggal_mulai . " +$frekuensi months"));

    // 2. Cek agar tidak duplikat. 
    // Kita cek apakah SUDAH ADA jadwal untuk aset ini dengan tanggal yang SAMA
    $stmt_cek = $conn->prepare("SELECT COUNT(*) FROM jadwal_inspeksi WHERE aset_id = ? AND tanggal_dijadwalkan = ?");
    $stmt_cek->execute([$aset_id, $tanggal_jadwal_baru]);
    $exists = $stmt_cek->fetchColumn();

    if ($exists == 0) {
        // 3. Jika belum ada, masukkan sebagai task baru
        $stmt_insert = $conn->prepare("INSERT INTO jadwal_inspeksi (aset_id, tanggal_dijadwalkan, status_jadwal) VALUES (?, ?, 'Pending')");
        $stmt_insert->execute([$aset_id, $tanggal_jadwal_baru]);
        
        echo "<p class='success'><b>BERHASIL:</b> Jadwal dibuat untuk SN: <b>{$sn}</b> pada tanggal <b>{$tanggal_jadwal_baru}</b>.</p>";
        $total_dibuat++;
    } else {
        // 4. Jika sudah ada, lewati
        echo "<p class='info'><b>DILEWATI:</b> Jadwal untuk SN: <b>{$sn}</b> pada tanggal {$tanggal_jadwal_baru} (sudah ada).</p>";
        $total_dilewati++;
    }
}

echo "<hr><h2>Generator Selesai!</h2>";
echo "<h3 class='success'>Total Jadwal (Task) Baru Dibuat: $total_dibuat</h3>";
echo "<h3 class='info'>Total Jadwal Dilewati (Duplikat): $total_dilewati</h3>";
echo "</body></html>";
?>