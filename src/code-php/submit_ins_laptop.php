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
    // Amankan input untuk mencegah SQL Injection
    $jenis = $conn->real_escape_string($_POST["jenis"] ?? '');
    $date = $conn->real_escape_string($_POST["date"] ?? '');
    $merk = $conn->real_escape_string($_POST["merk"] ?? '');
    $lokasi = $conn->real_escape_string($_POST["lokasi"] ?? '');
    $status = $conn->real_escape_string($_POST["status"] ?? '');
    $serialnumber = $conn->real_escape_string($_POST["serialnumber"] ?? '');
    $informasi_keluhan = $conn->real_escape_string($_POST["informasi_keluhan"] ?? '');
    $hasil_pemeriksaan = $conn->real_escape_string($_POST["hasil_pemeriksaan"] ?? '');
    $rekomendasi = $conn->real_escape_string($_POST["rekomendasi"] ?? '');
    $nama_user = $conn->real_escape_string($_POST["nama_user"] ?? '');
    $score = 0;
    
    $sql = '';

    if ($jenis == "Laptop") {
        $age = (int)($_POST["age"] ?? 0);
        $casing_lap = (int)($_POST["casing_lap"] ?? 0);
        $layar_lap = (int)($_POST["layar_lap"] ?? 0);
        $engsel_lap = (int)($_POST["engsel_lap"] ?? 0);
        $keyboard_lap = (int)($_POST["keyboard_lap"] ?? 0);
        $touchpad_lap = (int)($_POST["touchpad_lap"] ?? 0);
        $booting_lap = (int)($_POST["booting_lap"] ?? 0);
        $multi_lap = (int)($_POST["multi_lap"] ?? 0);
        $tampung_lap = (int)($_POST["tampung_lap"] ?? 0);
        $isi_lap = (int)($_POST["isi_lap"] ?? 0);
        $port_lap = (int)($_POST["port_lap"] ?? 0);
        $audio_lap = (int)($_POST["audio_lap"] ?? 0);
        $software_lap = (int)($_POST["software_lap"] ?? 0);

        $score = $age + $casing_lap + $layar_lap + $engsel_lap + $keyboard_lap + $touchpad_lap + $booting_lap + $multi_lap + $tampung_lap + $isi_lap + $port_lap + $audio_lap + $software_lap;
        
        $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, engsel_lap, keyboard_lap, touchpad_lap, booting_lap, multi_lap, tampung_lap, isi_lap, port_lap, audio_lap, software_lap, score)
            VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$age', '$casing_lap', '$layar_lap', '$engsel_lap', '$keyboard_lap', '$touchpad_lap', '$booting_lap', '$multi_lap', '$tampung_lap', '$isi_lap', '$port_lap', '$audio_lap', '$software_lap', '$score')";
    }
    
    // Anda bisa menambahkan logika untuk device lain di sini (PC Desktop, Monitor, dll)

    if ($sql != '') {
        if ($conn->query($sql) === TRUE) {
            // 1. Dapatkan ID dari data inspeksi yang baru saja disimpan
            $last_id = $conn->insert_id;

            // 2. Tentukan folder tujuan screenshot
            // PASTIKAN FOLDER INI ADA DAN BISA DITULIS OLEH SERVER
            $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";

            // 3. Proses setiap file screenshot yang diunggah
            if (isset($_FILES['screenshot_file'])) {
                foreach ($_FILES['screenshot_file']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['screenshot_file']['error'][$key] == UPLOAD_ERR_OK) {
                        $file_name = time() . '_' . basename($_FILES['screenshot_file']['name'][$key]);
                        $target_screenshot_file = $target_screenshot_dir . $file_name;

                        // Pindahkan file ke folder tujuan
                        if (move_uploaded_file($tmp_name, $target_screenshot_file)) {
                            // 4. Simpan nama file ke tabel `screenshots` dan hubungkan dengan ID inspeksi
                            $stmt = $conn->prepare("INSERT INTO screenshots (form_no, screenshot_name) VALUES (?, ?)");
                            $stmt->bind_param("is", $last_id, $file_name);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }

            // Arahkan ke halaman hasil
            echo "<script>window.location.href='viewinspeksi.php';</script>";
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
            echo $error_message;
            error_log($error_message, 0);
        }
    }
}

$conn->close();
?>