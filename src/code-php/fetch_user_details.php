<?php

$conn = new mysqli("mandiricoal.net", "podema", "Jam10pagi#", "podema");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_GET['user_id'];

$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    echo json_encode($user);
} else {
    echo "User not found";
}

$conn->close();
?>
