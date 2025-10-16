<?php
session_start();

// --- PENGATURAN KONEKSI DATABASE ---
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

// --- Variabel untuk pesan error ---
$error_message = '';

// --- LOGIKA LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nik'])) {
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    // Validasi input dasar
    if (empty($nik) || empty($password)) {
        $error_message = "NIK dan Password harus diisi.";
    } else {
        // Koneksi ke database
        $conn = new mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            // Sebaiknya jangan tampilkan error detail ke user, cukup pesan umum.
            // die("Koneksi gagal: " . $conn->connect_error);
            $error_message = "Terjadi masalah koneksi ke server.";
        } else {
            // Menggunakan prepared statement untuk keamanan
            $stmt = $conn->prepare("SELECT nik, password FROM users WHERE nik = ?");
            $stmt->bind_param("s", $nik);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // --- PERUBAHAN KEAMANAN KRUSIAL ---
                // Verifikasi password yang di-hash, bukan plain text
                if (password_verify($password, $row['password'])) {
                    // Jika password cocok
                    $_SESSION['nik'] = $nik;
                    header("Location: ./src/code-php/admin.php");
                    exit();
                } else {
                    // Jika password salah
                    $error_message = "Login gagal. Periksa kembali NIK dan Password Anda.";
                }
            } else {
                // Jika NIK tidak ditemukan
                $error_message = "Login gagal. Periksa kembali NIK dan Password Anda.";
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Device Management Application</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="src/assets/css/styles.min.css" />

  <style>
    body, .page-wrapper {
      /* Gradien latar belakang yang lebih halus */
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    }

    .card {
      /* Efek "Frosted Glass" */
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      /* Bayangan yang lebih lembut */
      box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
    }

    /* Mengubah warna teks label agar kontras dengan background baru */
    .form-label, .form-check-label, p.text-center {
      color: #e0e0e0;
    }

    .form-control {
      background-color: rgba(255, 255, 255, 0.2);
      border: none;
      color: #ffffff;
    }

    .form-control:focus {
      background-color: rgba(255, 255, 255, 0.3);
      color: #ffffff;
      box-shadow: none;
    }

    .form-control::placeholder {
      color: #c0c0c0;
    }

    .btn-primary {
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    /* Style untuk Pop-up */
    .popup {
      display: none;
      position: fixed;
      z-index: 1050;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
    }

    .popup-content {
      background-color: #ffffff;
      color: #333;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    
    .popup-content span {
      display: block;
      font-size: 1.1rem;
      margin-bottom: 20px;
    }

    .popup-close-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .popup-close-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="./index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="src/assets/images/logos/new_logo.png" width="180" alt="">
                </a>
                <p class="text-center mb-4">Portal Device Management Application</p>
                <form method="POST" action="./index.php">
                  <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" name="nik" value="<?= htmlspecialchars($_POST['nik'] ?? '', ENT_QUOTES) ?>" required>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" onclick="togglePassword()">
                      <label class="form-check-label" for="flexCheckChecked">
                        Show Password
                      </label>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="popup" class="popup">
      <div class="popup-content">
          <span id="popup-message"></span>
          <button onclick="hidePopup()" class="popup-close-btn">Close</button>
      </div>
  </div>

  <script>
    function togglePassword() {
      var passwordInput = document.getElementById('password');
      passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    }

    function showPopup(message) {
      document.getElementById("popup-message").innerText = message;
      document.getElementById("popup").style.display = "flex";
    }

    function hidePopup() {
      document.getElementById("popup").style.display = "none";
    }
  </script>

  <?php if (!empty($error_message)) : ?>
  <script>
    showPopup("<?= $error_message ?>");
  </script>
  <?php endif; ?>

</body>
</html>