<?php
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT nik, password FROM users WHERE password IS NOT NULL");
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nik = $row['nik'];
        $plainPassword = $row['password'];

        // Update password di database
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE nik = ?");
        $updateStmt->bind_param("ss", $plainPassword, $nik);
        $updateStmt->execute();
    }

    echo "Password berhasil diperbarui pada database tanpa dihash.";
} else {
    echo "Tidak ada password yang perlu diperbarui pada database.";
}

$stmt->close();
$conn->close();
?>
