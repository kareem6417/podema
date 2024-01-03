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
    $date = isset($_POST["date"]) ? $_POST["date"] : '';
    $jenis = isset($_POST["jenis"]) ? $_POST["jenis"] : '';
    $merk = isset($_POST["merk"]) ? $_POST["merk"] : '';
    $lokasi = isset($_POST["lokasi"]) ? $_POST["lokasi"] : '';
    $nama_user = isset($_POST["nama_user"]) ? $_POST["nama_user"] : '';
    $status = isset($_POST["status"]) ? $_POST["status"] : '';
    $serialnumber = isset($_POST["serialnumber"]) ? $_POST["serialnumber"] : '';
    $informasi_keluhan = isset($_POST["informasi_keluhan"]) ? $_POST["informasi_keluhan"] : '';
    $hasil_pemeriksaan = isset($_POST["hasil_pemeriksaan"]) ? $_POST["hasil_pemeriksaan"] : '';
    $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
    $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
    $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
    $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
    $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
    $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
    $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';
    $rekomendasi = isset($_POST["rekomendasi"]) ? $_POST["rekomendasi"] : '';

    $score = $casing_lap + $layar_lap  + $booting_lap + $multi_lap + $port_lap + $audio_lap + $software_lap;

    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, casing_lap, layar_lap, booting_lap, multi_lap, port_lap, audio_lap, software_lap, score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssssss", $date, $jenis, $merk, $lokasi, $nama_user, $status, $serialnumber, $informasi_keluhan, $hasil_pemeriksaan, $rekomendasi, $casing_lap, $layar_lap, $booting_lap, $multi_lap, $port_lap, $audio_lap, $software_lap, $score);

    if ($stmt->execute()) {
        $form_no = $conn->insert_id;

        $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";

        if(isset($_FILES['screenshot_file']['name']) && count($_FILES['screenshot_file']['name']) > 0) {
            foreach ($_FILES['screenshot_file']['tmp_name'] as $key => $tmp_name) {            

                $file_name = $_FILES['screenshot_file']['name'][$key];
                $target_screenshot_file = $target_screenshot_dir . $file_name;

                if ($_FILES['screenshot_file']['error'][$key] !== UPLOAD_ERR_OK) {
                    $error_message = "Terjadi kesalahan saat mengunggah file screenshot. Kode kesalahan: " . $_FILES['screenshot_file']['error'][$key];
                    echo $error_message;
                    error_log($error_message, 0);
                }                

                if (move_uploaded_file($_FILES['screenshot_file']['tmp_name'][$key], $target_screenshot_file)) {
                    echo "File screenshot berhasil diunggah.";

                    // Memasukkan data ke dalam tabel screenshot
                    $screenshot_name = $_FILES['screenshot_file']['name'][$key];
                    $stmt_screenshot = $conn->prepare("INSERT INTO screenshots (form_no, screenshot_name) VALUES (?, ?)");
                    $stmt_screenshot->bind_param("is", $form_no, $screenshot_name);
                    $stmt_screenshot->execute();
                    $stmt_screenshot->close();
                } else {
                    $error_message = "Terjadi kesalahan saat mengunggah file screenshot.";
                    echo $error_message;
                    error_log($error_message, 0);
                }
            }
        } else {
            echo "Tidak ada screenshot yang diunggah.";
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/File Upload Inspeksi/";
        
            // Upload file lainnya
            $file = $_FILES['upload_file']['name'];
            $path = pathinfo($file);
            $filename = $path['filename'];
            $ext = $path['extension'];
            $temp_name = $_FILES['upload_file']['tmp_name'];
            $path_filename_ext = $target_dir . $filename . '.' . $ext;
        
            if (file_exists($path_filename_ext)) {
                $error_message = "Maaf, file sudah ada.";
                echo $error_message;
                error_log($error_message, 0); // Menyimpan pesan error ke file log
            } else {
                if (move_uploaded_file($temp_name, $path_filename_ext)) {
                    echo "File Anda berhasil diunggah.";
                } else {
                    $error_message = "Terjadi kesalahan saat mengunggah file.";
                    echo $error_message;
                    error_log($error_message, 0); // Menyimpan pesan error ke file log
                }
            }
        }

        $stmt->close();
        $conn->commit();
        
        echo "Data berhasil disimpan.";
        echo "<script>window.location.href='viewinspeksi.php';</script>";
        exit();
    } else {
        $error_message = "Error: " . $stmt->error;
        echo $error_message;
        error_log($error_message, 0);

        $stmt->close();
        $conn->rollback(); // Rollback jika ada kesalahan
    }
}

$conn->close();
?>
