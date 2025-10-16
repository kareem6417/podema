<?php

// 1. Password Anda dimasukkan di sini
$passwordAsli = 'senopati';

// 2. Proses ini mengubah password di atas menjadi kode hash yang aman
$hashAman = password_hash($passwordAsli, PASSWORD_DEFAULT);

// 3. Bagian ini menampilkan hasilnya di layar agar mudah disalin
echo "<h1>Alat Pengaman Password</h1>";
echo "<p>Gunakan kode di bawah ini untuk disimpan di database Anda.</p>";
echo "<hr>";
echo "<p><b>Password Asli:</b> " . htmlspecialchars($passwordAsli) . "</p>";
echo "<p><b>Kode Aman (Hash):</b></p>";
echo "<textarea rows='3' cols='70' readonly>" . $hashAman . "</textarea>";
echo "<br><br><button onclick='navigator.clipboard.writeText(\"" . htmlspecialchars($hashAman) . "\")'>Salin Kode Aman</button>";

?>