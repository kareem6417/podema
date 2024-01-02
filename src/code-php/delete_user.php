<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: authentication-login.php");
  exit();
}

$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $deleteNotification = "User successfully deleted.";
        header("Location: admin.php?delete_notification=" . urlencode($deleteNotification));
        exit();
    } else {
        echo "Failed to delete user. Error: " . $stmt->error;
    }     

    $stmt->close();
}

$conn->close();

header("Location: admin.php");
exit();
?>
