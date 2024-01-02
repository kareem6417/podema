<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $host = "mandiricoal.net";
    $db   = "podema";
    $user = "podema";
    $pass = "Jam10pagi#";

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $nik = $_POST['nik'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $company = $_POST['company'];
    $department = $_POST['department'];

    $checkStmt = $conn->prepare("SELECT * FROM users WHERE nik = ? OR name = ?");
    $checkStmt->bind_param("is", $nik, $name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['notification'] = [
            'success' => false,
            'message' => "Failed! User with the same NIK or Name already exists."
        ];
        echo json_encode($_SESSION['notification']);
    } else {
        $insertStmt = $conn->prepare("INSERT INTO users (nik, name, email, company, department) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("issss", $nik, $name, $email, $company, $department);

        if ($insertStmt->execute()) {
            $_SESSION['notification'] = [
                'success' => true,
                'message' => "Success! You have added a new user!"
            ];
            echo json_encode($_SESSION['notification']);
        } else {
            $_SESSION['notification'] = [
                'success' => false,
                'message' => "Failed! Please double-check, there may be something that does not comply with the regulations."
            ];
            echo json_encode($_SESSION['notification']);
        }

        $insertStmt->close();
    }

    $checkStmt->close();
    $conn->close();
} else {
    header("location: admin.php");
    exit;
}
?>