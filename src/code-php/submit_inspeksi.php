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


    if ($jenis == "PC Desktop") {
        try {
            // elemen PC Desktop
            $age = isset($_POST["age"]) ? $_POST["age"] : '';
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
            $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
            $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
            $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
            $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
            $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';
    
            // Hitung skor
            $score = $age + $casing_lap + $layar_lap +  $booting_lap + $multi_lap + $port_lap + $audio_lap + $software_lap;
            
            $sql = "INSERT INTO form_inspeksi (date, jenis, merk, lokasi, nama_user, status, serialnumber, informasi_keluhan, hasil_pemeriksaan, rekomendasi, age, casing_lap, layar_lap, booting_lap, multi_lap, port_lap, audio_lap, software_lap, score)
                VALUES ('$date', '$jenis', '$merk', '$lokasi', '$nama_user', '$status', '$serialnumber', '$informasi_keluhan', '$hasil_pemeriksaan', '$rekomendasi', '$age', '$casing_lap', '$layar_lap', '$booting_lap', '$multi_lap', '$port_lap', '$audio_lap', '$software_lap', '$score')";

        } catch (\Throwable $th) {
        echo $th -> getMessage();
    }

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
}

$conn->close();
?>