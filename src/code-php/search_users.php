<?php

$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$keyword = $_GET['keyword'];

$query = "SELECT * FROM users WHERE name LIKE '%$keyword%' OR name LIKE '%$keyword%' OR nik LIKE '%$keyword%' OR company LIKE '%$keyword%' OR department LIKE '%$keyword%'";
$result = $conn->query($query);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['user_id'] = $row['user_id'];
        $users[] = $row;
    }
}

echo json_encode($users);
?>
