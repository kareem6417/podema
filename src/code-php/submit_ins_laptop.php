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

// Ambil semua data ID dari POST dengan aman
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

// Ambil ID dari setiap item inspeksi
$age_id = !empty($_POST["age"]) ? (int)$_POST["age"] : null;
$casing_lap_id = !empty($_POST["casing_lap"]) ? (int)$_POST["casing_lap"] : null;
$layar_lap_id = !empty($_POST["layar_lap"]) ? (int)$_POST["layar_lap"] : null;
$engsel_lap_id = !empty($_POST["engsel_lap"]) ? (int)$_POST["engsel_lap"] : null;
$keyboard_lap_id = !empty($_POST["keyboard_lap"]) ? (int)$_POST["keyboard_lap"] : null;
$touchpad_lap_id = !empty($_POST["touchpad_lap"]) ? (int)$_POST["touchpad_lap"] : null;
$booting_lap_id = !empty($_POST["booting_lap"]) ? (int)$_POST["booting_lap"] : null;
$multi_lap_id = !empty($_POST["multi_lap"]) ? (int)$_POST["multi_lap"] : null;
$tampung_lap_id = !empty($_POST["tampung_lap"]) ? (int)$_POST["tampung_lap"] : null;
$isi_lap_id = !empty($_POST["isi_lap"]) ? (int)$_POST["isi_lap"] : null;
$port_lap_id = !empty($_POST["port_lap"]) ? (int)$_POST["port_lap"] : null;
$audio_lap_id = !empty($_POST["audio_lap"]) ? (int)$_POST["audio_lap"] : null;
$software_lap_id = !empty($_POST["software_lap"]) ? (int)$_POST["software_lap"] : null;

// LOGIKA BARU: HITUNG TOTAL SKOR BERDASARKAN ID YANG DITERIMA
$total_score = 0;
$lookup_map = [
    'age' => ['table' => 'device_age_laptop', 'id_col' => 'age_id', 'score_col' => 'age_score', 'value' => $age_id],
    'casing_lap' => ['table' => 'ins_casing_lap', 'id_col' => 'casing_lap_id', 'score_col' => 'casing_lap_score', 'value' => $casing_lap_id],
    'layar_lap' => ['table' => 'ins_layar_lap', 'id_col' => 'layar_lap_id', 'score_col' => 'layar_lap_score', 'value' => $layar_lap_id],
    'engsel_lap' => ['table' => 'ins_engsel_lap', 'id_col' => 'engsel_lap_id', 'score_col' => 'engsel_lap_score', 'value' => $engsel_lap_id],
    'keyboard_lap' => ['table' => 'ins_keyboard_lap', 'id_col' => 'keyboard_lap_id', 'score_col' => 'keyboard_lap_score', 'value' => $keyboard_lap_id],
    'touchpad_lap' => ['table' => 'ins_touchpad_lap', 'id_col' => 'touchpad_lap_id', 'score_col' => 'touchpad_lap_score', 'value' => $touchpad_lap_id],
    'booting_lap' => ['table' => 'ins_booting_lap', 'id_col' => 'booting_lap_id', 'score_col' => 'booting_lap_score', 'value' => $booting_lap_id],
    'multi_lap' => ['table' => 'ins_multi_lap', 'id_col' => 'multi_lap_id', 'score_col' => 'multi_lap_score', 'value' => $multi_lap_id],
    'tampung_lap' => ['table' => 'ins_tampung_lap', 'id_col' => 'tampung_lap_id', 'score_col' => 'tampung_lap_score', 'value' => $tampung_lap_id],
    'isi_lap' => ['table' => 'ins_isi_lap', 'id_col' => 'isi_lap_id', 'score_col' => 'isi_lap_score', 'value' => $isi_lap_id],
    'port_lap' => ['table' => 'ins_port_lap', 'id_col' => 'port_lap_id', 'score_col' => 'port_lap_score', 'value' => $port_lap_id],
    'audio_lap' => ['table' => 'ins_audio_lap', 'id_col' => 'audio_lap_id', 'score_col' => 'audio_lap_score', 'value' => $audio_lap_id],
    'software_lap' => ['table' => 'ins_software_lap', 'id_col' => 'software_lap_id', 'score_col' => 'software_lap_score', 'value' => $software_lap_id]
];

foreach ($lookup_map as $details) {
    if ($details['value'] !== null) {
        $stmt_score = $conn->prepare("SELECT {$details['score_col']} FROM {$details['table']} WHERE {$details['id_col']} = ?");
        $stmt_score->bind_param("i", $details['value']);
        $stmt_score->execute();
        $score_result = $stmt_score->get_result()->fetch_assoc();
        if ($score_result) {
            $total_score += $score_result[$details['score_col']];
        }
        $stmt_score->close();
    }
}

// Gunakan Prepared Statement untuk menyimpan data dengan aman
$sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, engsel_lap, keyboard_lap, touchpad_lap, booting_lap, multi_lap, tampung_lap, isi_lap, port_lap, audio_lap, software_lap, score)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssiiiiiiiiiiisii", 
    $date, $jenis, $merk, $lokasi, $nama_user, $status, $serialnumber, $informasi_keluhan, $hasil_pemeriksaan, $rekomendasi, 
    $age_id, $casing_lap_id, $layar_lap_id, $engsel_lap_id, $keyboard_lap_id, $touchpad_lap_id, $booting_lap_id, $multi_lap_id, 
    $tampung_lap_id, $isi_lap_id, $port_lap_id, $audio_lap_id, $software_lap_id, $total_score
);

if ($stmt->execute()) {
    header("Location: viewinspeksi.php");
    exit();
} else {
    die("Error saat menyimpan data: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>