<?php
// Ambil semua data dari POST, pastikan ada nilainya.
$date = $_POST['date'] ?? '';
$name = $_POST['name'] ?? '';
$company = $_POST["company"] ?? '';
$divisi = $_POST["divisi"] ?? '';
$type = $_POST["type"] ?? '';
$serialnumber = $_POST["serialnumber"] ?? '';
$os = $_POST["os"] ?? 0;
$processor = $_POST["processor"] ?? 0;
$batterylife = $_POST["batterylife"] ?? 0;
$age = $_POST["age"] ?? 0;
$issue = $_POST["issue"] ?? 0;
$ram = $_POST["ram"] ?? 0;
$vga = $_POST["vga"] ?? 0;
$storage = $_POST["storage"] ?? 0;
$keyboard = $_POST["keyboard"] ?? 0;
$screen = $_POST["screen"] ?? 0;
$touchpad = $_POST["touchpad"] ?? 0;
$audio = $_POST["audio"] ?? 0;
$body = $_POST["body"] ?? 0;

// Hitung total skor dengan mengubah tipe data menjadi integer untuk keamanan
$score = (int)$os + (int)$processor + (int)$batterylife + (int)$age + (int)$issue + (int)$ram + (int)$vga + (int)$storage + (int)$keyboard + (int)$screen + (int)$touchpad + (int)$audio + (int)$body;

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


// ================================================================= //
// PERBAIKAN: Menggunakan PREPARED STATEMENTS untuk mencegah SQL Injection //
// ================================================================= //

// 1. Siapkan query dengan placeholder (?)
$sql = "INSERT INTO assess_laptop (date, name, company, divisi, type, serialnumber, os, processor, batterylife, age, issue, ram, vga, storage, keyboard, screen, touchpad, audio, body, score)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// 2. Prepare statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Gagal mempersiapkan statement: " . $conn->error);
}

// 3. Bind parameter ke placeholder
// "ssssssiiiiiiiiiiisii" -> s = string, i = integer. Tipe data harus sesuai dengan kolom di database.
// Jumlah huruf harus cocok dengan jumlah tanda tanya (?).
$stmt->bind_param(
    "ssssssiiiiiiiiiiiiii", 
    $date, $name, $company, $divisi, $type, $serialnumber, 
    $os, $processor, $batterylife, $age, $issue, $ram, $vga, $storage, 
    $keyboard, $screen, $touchpad, $audio, $body, $score
);

// 4. Eksekusi statement dan cek hasilnya
if ($stmt->execute()) {
    // Jika berhasil, arahkan ke halaman view.php
    header("Location: view.php");
    exit();
} else {
    // Jika gagal, tampilkan error
    echo "Error: " . $stmt->error;
}

// 5. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>