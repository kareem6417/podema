<?php
$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#"; 
$db = "podema";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Mengambil data umum dari form
    $date = $_POST["date"] ?? '';
    $jenis = $_POST["jenis"] ?? '';
    $nama_user = $_POST["nama_user"] ?? '';
    $status = $_POST["status"] ?? '';
    $lokasi = $_POST["lokasi"] ?? '';
    $merk = $_POST["merk"] ?? '';
    $serialnumber = $_POST["serialnumber"] ?? '';
    $informasi_keluhan = $_POST["informasi_keluhan"] ?? '';
    $hasil_pemeriksaan = $_POST["hasil_pemeriksaan"] ?? '';
    $rekomendasi = $_POST["rekomendasi"] ?? '';
    
    $sql = '';
    $types = '';
    $params = [];
    $score = 0;

    if ($jenis == "PC Desktop") {
        // Mengambil data khusus PC Desktop
        $age = (int)($_POST["age"] ?? 0);
        $casing_lap = (int)($_POST["casing_lap"] ?? 0);
        $layar_lap = (int)($_POST["layar_lap"] ?? 0);
        $keyboard_lap = (int)($_POST["keyboard_lap"] ?? 0);
        $booting_lap = (int)($_POST["booting_lap"] ?? 0);
        $multi_lap = (int)($_POST["multi_lap"] ?? 0);
        $port_lap = (int)($_POST["port_lap"] ?? 0);
        $audio_lap = (int)($_POST["audio_lap"] ?? 0);
        $software_lap = (int)($_POST["software_lap"] ?? 0);
        
        // Hitung skor
        $score = $age + $casing_lap + $layar_lap + $keyboard_lap + $booting_lap + $multi_lap + $port_lap + $audio_lap + $software_lap;
        
        // Query menggunakan placeholder (?) untuk keamanan
        $sql = "INSERT INTO form_inspeksi (date, jenis, nama_user, status, lokasi, merk, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, keyboard_lap, booting_lap, multi_lap, port_lap, audio_lap, software_lap, score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Tipe data untuk bind_param
        $types = "ssssssssssiiiiiiiii";
        
        // Parameter yang akan di-bind
        $params = [
            $date, $jenis, $nama_user, $status, $lokasi, $merk, $serialnumber, 
            $informasi_keluhan, $hasil_pemeriksaan, $rekomendasi,
            $age, $casing_lap, $layar_lap, $keyboard_lap, $booting_lap, $multi_lap, 
            $port_lap, $audio_lap, $software_lap, $score
        ];
    }
    // Anda bisa menambahkan blok "else if ($jenis == 'Laptop')" di sini jika file ini ingin menangani banyak jenis perangkat

    if (!empty($sql)) {
        $stmt = $conn->prepare($sql);
        // "splat operator" (...) untuk memasukkan array $params ke bind_param
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // 1. Dapatkan ID dari data inspeksi yang baru saja disimpan
            $last_id = $stmt->insert_id;
            $stmt->close();

            // 2. Tentukan folder tujuan screenshot
            // PASTIKAN FOLDER INI ADA DAN MEMILIKI IZIN TULIS (WRITABLE)
            $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";
            if (!file_exists($target_screenshot_dir)) {
                mkdir($target_screenshot_dir, 0777, true);
            }

            // 3. Proses setiap file screenshot yang diunggah
            if (isset($_FILES['screenshot_file']) && !empty($_FILES['screenshot_file']['name'][0])) {
                foreach ($_FILES['screenshot_file']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['screenshot_file']['error'][$key] == UPLOAD_ERR_OK) {
                        // Buat nama file yang unik untuk menghindari penimpaan
                        $file_extension = pathinfo($_FILES['screenshot_file']['name'][$key], PATHINFO_EXTENSION);
                        $file_name = "ss_" . $last_id . "_" . time() . "_" . $key . "." . $file_extension;
                        $target_screenshot_file = $target_screenshot_dir . $file_name;

                        // Pindahkan file ke folder tujuan
                        if (move_uploaded_file($tmp_name, $target_screenshot_file)) {
                            // 4. Simpan nama file ke tabel `screenshots` dan hubungkan dengan ID inspeksi
                            $stmt_ss = $conn->prepare("INSERT INTO screenshots (form_no, screenshot_name) VALUES (?, ?)");
                            $stmt_ss->bind_param("is", $last_id, $file_name);
                            $stmt_ss->execute();
                            $stmt_ss->close();
                        }
                    }
                }
            }

            // Arahkan ke halaman hasil
            echo "<script>window.location.href='viewinspeksi.php';</script>";
            exit();

        } else {
            $error_message = "Error: " . $stmt->error;
            echo $error_message;
            error_log($error_message, 0);
        }
    } else {
        echo "Jenis perangkat tidak valid atau tidak didukung.";
    }
}

$conn->close();
?>