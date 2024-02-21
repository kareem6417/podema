<?php
session_start();

$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

if (isset($_POST['reset'])) {
    unset($_POST['nik']);
    unset($_POST['password']);
}

if (isset($_POST['nik']) && isset($_POST['password'])) {
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT nik, password FROM users WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password == $row['password']) {
            $_SESSION['nik'] = $nik;
            header("Location: admin.php");
            exit();
        } else {
            echo "Login failed. Password does not match.<br>";
        }
    } else {
        echo "Login failed. No user found with the given NIK.<br>";
    }

    $conn->close();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Device Management Application</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="src/assets/css/styles.min.css "/>
  <style>
    .popup {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
    }

    .popup-content {
      background-color: #fefefe;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      /* Tambahkan ini untuk memusatkan konten */
      position: fixed;
      /* Tambahkan ini untuk memastikan posisinya tetap */
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .popup-content span {
      display: block;
      text-align: center;
      font-weight: bold;
    }
  </style>
  <script>
    function togglePassword() {
      var passwordInput = document.getElementById('password');
      var checkBox = document.getElementById('flexCheckChecked');

      if (checkBox.checked) {
        passwordInput.type = 'text';
      } else {
        passwordInput.type = 'password';
      }
    }
  </script>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="./index.html" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="src/assets/images/logos/logo.png" width="180" alt="">
                </a>
                <p class="text-center">Portal Device Management Application</p>
                <form method="POST" action="authentication-login.php">
                  <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" name="nik"
                      value="<?php echo isset($_POST['nik']) ? $_POST['nik'] : ''; ?>" required>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked"
                        onclick="togglePassword()">
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Show Password
                      </label>
                    </div>
                    <a class="text-primary fw-bold" href="./index.php"></a>
                  </div>
                  <button type="submit"
                    class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                </form>
                <?php
                if (isset($_POST['username']) && isset($_POST['password'])) {
                  if (empty($_POST['username']) || empty($_POST['password'])) {
                    echo '<p class="login-fail">Username dan password harus diisi.</p>';
                  } elseif (!isset($result) || $result->num_rows == 0) {
                    echo '<p class="login-fail">Login gagal. Silakan cek kembali username dan password Anda.</p>';
                  }
                }
                ?>
              </div>
              <div id="popup" class="popup">
                <span class="popup-content" id="popup-message"></span>
                <button onclick="hidePopup()">Close</button>
              </div>
              <script>
                function showPopup(message) {
                  console.log("showPopup dipanggil dengan pesan:", message);
                  var popupMessage = document.getElementById("popup-message");
                  popupMessage.innerHTML = "<span>" + message + "</span>";
                  var popup = document.getElementById("popup");
                  popup.style.display = "flex";
                }

                function hidePopup() {
                  var popup = document.getElementById("popup");
                  popup.style.display = "none";
                }
              </script>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
