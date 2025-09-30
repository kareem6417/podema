<?php
$date = $_POST['date'];
$name = $_POST['name'];
$company = $_POST["company"];
$divisi = $_POST["divisi"];
$type = $_POST["type"];
$serialnumber = $_POST["serialnumber"];
$os = $_POST["os"];
$processor = $_POST["processor"];
$batterylife = $_POST["batterylife"];
$age = $_POST["age"];
$issue = $_POST["issue"];
$ram = $_POST["ram"];
$vga = $_POST[ "vga" ];
$storage = $_POST["storage"];
$keyboard = $_POST["keyboard"];
$screen = $_POST["screen"];
$touchpad = $_POST["touchpad"];
$audio = $_POST["audio"];
$body = $_POST["body"];
$score = $os + $processor + $batterylife + $age + $issue + $ram + $vga + $storage + $keyboard + $screen + $touchpad + $audio + $body;

$host = "mandiricoal.net";
$user = "podema";
$pass = "Jam10pagi#";
$db = "podema";

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Perbaikan pada urutan kolom di query SQL
$sql = "INSERT INTO assess_laptop (date, name, company, divisi, type, serialnumber, os, processor, batterylife, age, issue, ram, vga, storage, keyboard, screen, touchpad, audio, body, score)
        VALUES ('$date', '$name', '$company', '$divisi', '$type', '$serialnumber', '$os', '$processor', '$batterylife', '$age', '$issue', '$ram', '$vga', '$storage', '$keyboard', '$screen', '$touchpad', '$audio', '$body', '$score')";

// Eksekusi query dan redirect ke view.php jika berhasil
if ($conn->query($sql) === TRUE) {
    // Mengarahkan ke halaman view.php setelah data berhasil disimpan
    header("Location: view.php");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>