<?php
$name = $_POST['name'];
$date = $_POST['date'];
$company = $_POST["company"];
$divisi = $_POST["divisi"];
$serialnumber = $_POST["serialnumber"];
$merk = $_POST["merk"];
$typepc = $_POST["typepc"];
$os = $_POST["os"];
$processor = $_POST["processor"];
$vga = $_POST["vga"];
$age = $_POST["age"];
$issue = $_POST["issue"];
$ram = $_POST["ram"];
$storage = $_POST["storage"];
$typemonitor = $_POST["typemonitor"];
$sizemonitor = $_POST["sizemonitor"];
$score = $typepc + $os + $processor + $vga + $age + $issue + $ram + $storage + $typemonitor + $sizemonitor;

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#"; 
$db = "podema";

$conn = new mysqli($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal");
} else {
    echo "Koneksi Berhasil";
}

$sql = "INSERT INTO assess_pc (date, name, company, divisi, serialnumber, merk, typepc, os, processor, vga, age, issue, ram, storage, typemonitor, sizemonitor, score)
        VALUES ('$date', '$name', '$company', '$divisi', '$serialnumber', '$merk', '$typepc', '$os', '$processor', '$vga', '$age', '$issue', '$ram', '$storage', '$typemonitor', '$sizemonitor', '$score')";

if ($conn->query($sql) === TRUE) {
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/src/File Upload PC/";
    $file = $_FILES['upload_file']['name'];
    $path = pathinfo($file);
    $filename = $path['filename'];
    $ext = $path['extension'];
    $temp_name = $_FILES['upload_file']['tmp_name'];
    $path_filename_ext = $target_dir . $filename . '.' . $ext;

    if (file_exists($path_filename_ext)) {
        echo "Maaf, file sudah ada.";
    } else {
        if (move_uploaded_file($temp_name, $path_filename_ext)) {
            echo "File Anda berhasil diunggah.";
            echo '<meta http-equiv="refresh" content="0;url=viewpc.php">';
            exit();
        } else {
            echo "Terjadi kesalahan saat mengunggah file.";
        }
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>