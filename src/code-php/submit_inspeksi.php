<?php

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#"; 
$db = "podema";

$conn = new mysqli($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jenis = isset($_POST["jenis"]) ? $_POST["jenis"] : '';
    $date = isset($_POST["date"]) ? $_POST["date"] : '';
    $merk = isset($_POST["merk"]) ? $_POST["merk"] : '';
    $lokasi = isset($_POST["lokasi"]) ? $_POST["lokasi"] : '';
    $status = isset($_POST["status"]) ? $_POST["status"] : '';
    $serialnumber = isset($_POST["serialnumber"]) ? $_POST["serialnumber"] : '';
    $informasi_keluhan = isset($_POST["informasi_keluhan"]) ? $_POST["informasi_keluhan"] : '';
    $hasil_pemeriksaan = isset($_POST["hasil_pemeriksaan"]) ? $_POST["hasil_pemeriksaan"] : '';
    $rekomendasi = isset($_POST["rekomendasi"]) ? $_POST["rekomendasi"] : '';
    $nama_user = isset($_POST["nama_user"]) ? $_POST["nama_user"] : '';
    $score = 0;

    $sql = '';

    if ($jenis == "Laptop") {
        // elemen Laptop
        $age = isset($_POST["age"]) ? $_POST["age"] : '';
        $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
        $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
        $engsel_lap = isset($_POST["engsel_lap"]) ? $_POST["engsel_lap"] : '';
        $keyboard_lap = isset($_POST["keyboard_lap"]) ? $_POST["keyboard_lap"] : '';
        $touchpad_lap = isset($_POST["touchpad_lap"]) ? $_POST["touchpad_lap"] : '';
        $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
        $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
        $tampung_lap = isset($_POST["tampung_lap"]) ? $_POST["tampung_lap"] : '';
        $isi_lap = isset($_POST["isi_lap"]) ? $_POST["isi_lap"] : '';
        $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
        $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
        $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';

        // Hitung skor
        $score = $age + $casing_lap + $layar_lap + $engsel_lap + $keyboard_lap + $touchpad_lap + $booting_lap + $multi_lap + $tampung_lap + $isi_lap + $port_lap + $audio_lap + $software_lap;
        
        $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, engsel_lap, keyboard_lap, touchpad_lap, booting_lap, multi_lap, tampung_lap, isi_lap, port_lap, audio_lap, software_lap, score)
            VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$age', '$casing_lap', '$layar_lap', '$engsel_lap', '$keyboard_lap', '$touchpad_lap', '$booting_lap', '$multi_lap', '$tampung_lap', '$isi_lap', '$port_lap', '$audio_lap', '$software_lap', '$score')";
    }

    
    if ($jenis == "PC Desktop") {
        try {
            // elemen PC Desktop
            $age = isset($_POST["age"]) ? $_POST["age"] : '';
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
            $keyboard_lap = isset($_POST["keyboard_lap"]) ? $_POST["keyboard_lap"] : '';
            $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
            $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
            $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
            $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
            $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';
    
            // Hitung skor
            $score = $age + $casing_lap + $layar_lap + $keyboard_lap + $booting_lap + $multi_lap + $port_lap + $audio_lap + $software_lap;
            
            $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, keyboard_lap, booting_lap, multi_lap, port_lap, audio_lap, software_lap, score)
                VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$age', '$casing_lap', '$layar_lap', '$keyboard_lap', '$booting_lap', '$multi_lap', '$port_lap', '$audio_lap', '$software_lap', '$score')";

        } catch (\Throwable $th) {
        throw $th;
    }
}
   
    if ($jenis == "Monitor") {
        // elemen Monitor
        $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
        $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';

        // Hitung skor
        $score = $casing_lap + $layar_lap;
 
        $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, casing_lap, layar_lap, score)
            VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$casing_lap', '$layar_lap', '$score')";
    }

    if ($jenis == "Printer") {
        // elemen printer
        $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
        $ink_pad = isset($_POST["ink_pad"]) ? $_POST["ink_pad"] : '';
                
        // Hitung skor
        $score = $casing_lap + $ink_pad;
        
        $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, casing_lap, ink_pad, score)
            VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$casing_lap', '$ink_pad', '$score')";
    }   

    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/File Upload Inspeksi";
    
    //     // Upload file lainnya
    //     $file = $_FILES['upload_file']['name'];
    //     $path = pathinfo($file);
    //     $filename = $path['filename'];
    //     $ext = $path['extension'];
    //     $temp_name = $_FILES['upload_file']['tmp_name'];
    //     $path_filename_ext = $target_dir . $filename . '.' . $ext;
    
    //     if (file_exists($path_filename_ext)) {
    //         $error_message = "Maaf, file sudah ada.";
    //         echo $error_message;
    //         error_log($error_message, 0);
    //     } else {
    //         if (move_uploaded_file($temp_name, $path_filename_ext)) {
    //             echo "File Anda berhasil diunggah.";
    //         } else {
    //             $error_message = "Terjadi kesalahan saat mengunggah file.";
    //             echo $error_message;
    //             error_log($error_message, 0);
    //         }
    //     }
    // }

    // $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/screenshot/";

    // // Loop melalui setiap file screenshot yang diunggah
    // foreach ($_FILES['screenshot_file']['tmp_name'] as $key => $tmp_name) {
    //     $file_name = $_FILES['screenshot_file']['name'][$key];
    //     $file_size = $_FILES['screenshot_file']['size'][$key];
    //     $file_tmp = $_FILES['screenshot_file']['tmp_name'][$key];
    //     $file_type = $_FILES['screenshot_file']['type'][$key];

    //     $target_screenshot_file = $target_screenshot_dir . $file_name;

    //     // Pindahkan file dari lokasi sementara ke direktori tujuan
    //     if (move_uploaded_file($file_tmp, $target_screenshot_file)) {
    //         echo "File screenshot berhasil diunggah.";
    //     } else {
    //         $error_message = "Terjadi kesalahan saat mengunggah file screenshot.";
    //         echo $error_message;
    //         error_log($error_message, 0); // Menyimpan pesan error ke file log
    //     }
    // }

    if ($sql != '') {
        if ($conn->query($sql) === TRUE) {
            echo "Data berhasil disimpan.";
            echo "<script>window.location.href='viewinspeksi.php';</script>"; // Pengalihan halaman
            exit(); // Pastikan untuk keluar dari skrip
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
            echo $error_message;
            error_log($error_message, 0); // Menyimpan pesan error ke file log
        }
    }
}

$conn->close();
?>