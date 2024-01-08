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

$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 9999999;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filterCompany = isset($_GET['filterCompany']) ? $_GET['filterCompany'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM users WHERE 1";

    if (!empty($search)) {
        $query .= " AND nik LIKE '%$search%'";
    }

    if (!empty($filterCompany)) {
        $companyOptions = [
            'pam' => 'PT Prima Andalan Mandiri',
            'mipho' => 'PT. Mandiri Intiperkasa - HO',
            'mipsite' => 'PT. Mandiri Intiperkasa - Site',
            'mkpho' => 'PT. Mandala Karya Prima - HO',
            'mkpsite' => 'PT. Mandala Karya Prima - Site',
            'mpmho' => 'PT. Maritim Prima Mandiri - HO',
            'mpmsite' => 'PT. Maritim Prima Mandiri - Site',
            'mandiriland' => 'PT. Mandiriland',
            'gms' => 'PT. Global Mining Service',
            'eam' => 'PT. Edika Agung Mandiri',
        ];

        if (array_key_exists($filterCompany, $companyOptions)) {
          $query .= " AND company = '" . $companyOptions[$filterCompany] . "'";
      } else {
          echo "Invalid filterCompany: $filterCompany";
      }
  }

        $query .= " ORDER BY user_id DESC LIMIT $limit";

        $result = $conn->query($query);
  if (!$result) {
      echo "Error: " . $conn->error;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = $_POST['nik'];
    // $password = md5($_POST['password']);
    $name = $_POST['name'];
    $company = $_POST['company'];
    $department = $_POST['department'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO users (nik, name, company, department, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nik, $name, $company, $department, $email);
    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Success! You have added a new user.'));
        exit();
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed! Please double-check, there may be something that does not comply with the regulations.'));
        exit();
    }
}

if (isset($_GET['delete_notification'])) {
    $deleteNotification = isset($_GET['delete_notification']) ? $_GET['delete_notification'] : '';
}

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Device Management Application</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="stylesheet" href="../assets/css/style_admin.css">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
        .modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          max-width: 35%;
          width: auto;
          background-color: #fff;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
          opacity: 0;
          animation: fadeIn 0.3s forwards;
        }

        /* Animasi muncul */
        @keyframes fadeIn {
          to {
            opacity: 3;
          }
        }

        .btn-container {
          display: flex;
          justify-content: flex-end;
          margin-top: 15px;
        }

        .btn-container button {
          margin-left: 10px;
        }

        .popup {
            display: none;
            position: fixed;
            width: 35%;
            top: 50%;
            left: 57%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border: 1px solid #ddd;
            z-index: 1000;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .popup label {
            display: block;
            margin-bottom: 8px;
        }

        .popup select,
        .popup input {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .popup button {
            padding: 8px 16px;
            margin-right: 8px;
        }

        #addUserBtn {
          display: block;
          margin: 20px auto;
        }

        #addUserBtn {
          padding: 10px 20px; 
          font-size: 14px;
        }

        .success-notification {
            color: green;
        }

        .error-notification {
            color: red;
        }

        .input-group-text[title]:hover::after {
          content: attr(title);
          padding: 4px 8px;
          color: #fff;
          background-color: #000;
          position: absolute;
          z-index: 1000;
          border-radius: 4px;
          white-space: nowrap;
      }

      .notification-popup {
        position: fixed;
        top: 50%;
        left: 55%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        width: 300px;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
    var submenuItems = document.querySelectorAll('.sidebar-submenu');

    submenuItems.forEach(function(item) {
        item.style.maxHeight = '0';

        item.closest('.sidebar-item').addEventListener('click', function(e) {
            e.preventDefault();

            this.classList.toggle('active');

            submenuItems.forEach(function(subitem) {
                if (subitem !== item) {
                    subitem.style.maxHeight = '0';
                }
            });

            if (this.classList.contains('active')) {
                item.style.maxHeight = '1000px';
            } else {
                item.style.maxHeight = '0';
            }
        });

        var submenuLinks = item.querySelectorAll('.sidebar-link');
        submenuLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
});
  </script>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="" class="text-nowrap logo-img">
            <br>
            <img src="../assets/images/logos/logo.png" width="180" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./admin.php" aria-expanded="false">
                <span>
                  <i class="ti ti-layout-dashboard"></i>
                </span>
                <span class="hide-menu">Administrator</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Dashboard</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_lap.php" aria-expanded="false">
                <span>
                  <i class="ti ti-chart-area-line"></i>
                </span>
                <span class="hide-menu">Assessment Laptop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_pc.php" aria-expanded="false">
                <span>
                  <i class="ti ti-chart-line"></i>
                </span>
                <span class="hide-menu">Assessment PC Desktop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_ins.php" aria-expanded="false">
                <span>
                  <i class="ti ti-chart-donut"></i>
                </span>
                <span class="hide-menu">Inspection</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Evaluation Portal</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./assess_laptop.php" aria-expanded="false">
                <span>
                  <i class="ti ti-device-laptop"></i>
                </span>
                <span class="hide-menu">Assessment Laptop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./assess_pc.php" aria-expanded="false">
                <span>
                  <i class="ti ti-device-desktop-analytics"></i>
                </span>
                <span class="hide-menu">Assessment PC Desktop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="#" aria-expanded="false">
                  <span>
                      <i class="ti ti-assembly"></i>
                  </span>
                  <span class="hide-menu">Device Inspection</span>
                  <span class="arrow">
                    <i class="fas fa-chevron-down"></i>
                  </span>
              </a>
              <ul class="sidebar-submenu">
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_laptop.php">
                          <span>
                              <i class="ti ti-devices"></i>
                          </span>
                          Laptop
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_desktop.php">
                          <span>
                              <i class="ti ti-device-desktop-search"></i>
                          </span>
                          PC Desktop
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_monitor.php">
                          <span>
                              <i class="ti ti-screen-share"></i>
                          </span>
                          Monitor
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_printer.php">
                          <span>
                              <i class="ti ti-printer"></i>
                          </span>
                          Printer
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_cctv.php">
                          <span>
                              <i class="ti ti-device-cctv"></i>
                          </span>
                          CCTV
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_infra.php">
                          <span>
                              <i class="ti ti-router"></i>
                          </span>
                          Infrastructure
                      </a>
                  </li>
                  <li class="sidebar-item">
                      <a class="sidebar-link" href="./ins_tlp.php">
                          <span>
                              <i class="ti ti-device-landline-phone"></i>
                          </span>
                          Telephone
                      </a>
                  </li>
              </ul>
          </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./about.php" aria-expanded="false">
                <span>
                  <i class="ti ti-exclamation-circle"></i>
                </span>
                <span class="hide-menu">About</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Asset Management</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./astmgm.php" aria-expanded="false">
                <span>
                <i class="ti ti-cards"></i>
              </span>
                <span class="hide-menu">IT Asset Management</span>
              </a>
            </li>
          </ul>

        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-user fs-6"></i>
                      <p class="mb-0 fs-3">My Profile</p>
                    </a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-mail fs-6"></i>
                      <p class="mb-0 fs-3">My Device</p>
                    </a>
                    <a href="./authentication-login.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header></form>
      <!--  Header End -->
      <div class="container-fluid">
        <!--  Row 1 -->
            <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                    <div class="mb-3 mb-sm-0">
                        <h5 class="card-title fw-semibold">User Management</h5>
                    </div>
                </div>
                <div id="chart"></div>
                <div class="search-form">
                  <label for="search" class="search-label">Search Users</label>
                  <input type="text" name="search" id="search" onkeyup="searchUsers()">
                </div>
                <form class="filter-form" method="get">
                    <label for="limit">Rows per page:</label>
                    <select id="limit" name="limit">
                        <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
                        <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
                        <option value="500" <?= ($limit == 500) ? 'selected' : '' ?>>500</option>
                        <option value="1000" <?= ($limit == 1000) ? 'selected' : '' ?>>1000</option>
                        <option value="999999" <?= ($limit == 999999) ? 'selected' : '' ?>>All</option>
                    </select>
                    <select id="filterCompany" name="filterCompany">
                      <option value="" <?= empty($filterCompany) ? 'selected' : '' ?>>All Companies</option>
                      <option value="mipho" <?= ($filterCompany == 'mipho') ? 'selected' : '' ?>>MIP - HO</option>
                      <option value="mipsite" <?= ($filterCompany == 'mipsite') ? 'selected' : '' ?>>MIP - Site</option>
                      <option value="mkpho" <?= ($filterCompany == 'mkpho') ? 'selected' : '' ?>>MKP - HO</option>
                      <option value="mkpsite" <?= ($filterCompany == 'mkpsite') ? 'selected' : '' ?>>MKP - Site</option>
                      <option value="mpmho" <?= ($filterCompany == 'mpmho') ? 'selected' : '' ?>>MPM - HO</option>
                      <option value="mpmsite" <?= ($filterCompany == 'mpmsite') ? 'selected' : '' ?>>MPM - Site</option>
                      <option value="mhaho" <?= ($filterCompany == 'mhaho') ? 'selected' : '' ?>>MHA - HO</option>
                      <option value="mhasite" <?= ($filterCompany == 'mhasite') ? 'selected' : '' ?>>MHA - Site</option>
                      <option value="mandiriland" <?= ($filterCompany == 'mandiriland') ? 'selected' : '' ?>>MandiriLand</option>
                      <option value="gms" <?= ($filterCompany == 'gms') ? 'selected' : '' ?>>GMS</option>
                      <option value="eam" <?= ($filterCompany == 'eam') ? 'selected' : '' ?>>Edika</option>
                    </select>
                </form>
                  <button class="btn btn-primary ml-auto" id="addUserBtn" onclick="showPopup()">Add User</button>
                    <div id="userPopup" class="popup">
                        <h5>Add New User</h5>
                        <form class="popup-form" id="addUserForm" action="../code-php/add_user.php" method="post" onsubmit="saveUser(); return false;">
                            <input type="hidden" name="roles_id" id="roles_id" value="2">

                            <label for="company">Company:</label>
                            <select name="company" id="company" class="form-control">
                                <option value="pam">PT. Prima Andalan Mandiri</option>
                                <optgroup label="PT Mandiri Intiperkasa">
                                    <option value="mipho">PT. Mandiri Intiperkasa - HO</option>
                                    <option value="mipsite">PT. Mandiri Intiperkasa - Site</option>
                                </optgroup>
                                <optgroup label="PT Mandala Karya Prima">
                                    <option value="mkpho">PT. Mandala Karya Prima - HO</option>
                                    <option value="mkpsite">PT. Mandala Karya Prima - Site</option>
                                </optgroup>
                                <optgroup label="PT Maritim Prima Mandiri">
                                    <option value="mpmho">PT. Maritim Prima Mandiri - HO</option>
                                    <option value="mpmsite">PT. Maritim Prima Mandiri - Site</option>
                                </optgroup>
                                <optgroup label="PT Mandiri Herindo Adiperkasa">
                                    <option value="mhaho">PT. Mandiri Herindo Adiperkasa - HO</option>
                                    <option value="mhasite">PT. Mandiri Herindo Adiperkasa - Site</option>
                                </optgroup>
                                <option value="mandiriland">PT. Mandiriland</option>
                                <option value="gms">PT. Global Mining Service</option>
                                <option value="eam">PT. Edika Agung mandiri</option>
                            </select>

                            <label for="nik">NIK:</label>
                            <div class="input-group">
                                <input type="text" name="nik" id="nik" class="form-control" required>
                                <div class="input-group-append">
                                <span class="input-group-text" onclick="searchNIK()">
                                    <i class="fas fa-search"></i>
                                </span>
                                </div>
                            </div>
                            <div id="nik-result"></div>
                            <br>

                            <label for="name">Name:</label>
                            <input type="text" name="name" id="name" class="form-control" required>

                            <label for="department">Department:</label>
                            <input type="text" name="department" id="department" class="form-control" required>

                            <!-- <label for="password">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" required> -->

                            <label for="email">Email:</label>
                            <input type="text" name="email" id="email" class="form-control" required oninput="validateEmailInput(this)">
                            <span id="email-error" style="color: red;"></span>

                            <script>
                            function validateEmailInput(input) {
                                var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                                if (!emailRegex.test(input.value)) {
                                    document.getElementById('email-error').innerText = 'Format email tidak valid';
                                    input.setCustomValidity('');
                                } else {
                                    document.getElementById('email-error').innerText = '';
                                    input.setCustomValidity('');
                                }
                            }
                            </script>

                            <button type="submit" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-secondary" onclick="hidePopup()">Close</button>
                        </form>
                    </div>
                    <div id="notification-popup" class="notification-popup" style="display: none;">
                        <div id="notification" class="alert" role="alert"></div>
                    </div>
                </div>
                <!-- Display User Table -->
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">NIK</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Name</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Company</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Department</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Action</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                          $companyOptions = [
                              'PAM' => 'PT. Prima Andalan Mandiri',
                              'MIP HO' => 'PT. Mandiri Intiperkasa - HO',
                              'MIP Site' => 'PT. Mandiri Intiperkasa - Site',
                              'MKP HO' => 'PT. Mandala Karya Prima - HO',
                              'MKP Site' => 'PT. Mandala Karya Prima - Site',
                              'MPM HO' => 'PT. Maritim Prima Mandiri - HO',
                              'MPM Site' => 'PT. Maritim Prima Mandiri - Site',
                              'MHA HO' => 'PT. Mandiri Herindo Adiperkasa - HO',
                              'MHA Site' => 'PT. Mandiri Herindo Adiperkasa - Site',
                              'mandiriland' => 'PT. Mandiriland',
                              'GMS' => 'PT. Global Mining Service',
                              'eam' => 'PT. Edika Agung Mandiri',
                          ];

                          if ($result->num_rows > 0) {
                              while ($row = $result->fetch_assoc()) {
                                  echo "<tr>";
                                  echo "<td>{$row['nik']}</td>";
                                  echo "<td>{$row['name']}</td>";
                                  echo "<td>" . (isset($companyOptions[$row['company']]) ? $companyOptions[$row['company']] : 'Unknown') . "</td>";
                                  echo "<td>{$row['department']}</td>";
                                  echo "<td>";
                                  echo "<a class='edit-icon' style='margin-right: 10px;' href='user_detail.php?user_id=" . $row['user_id'] . "&name=" . urlencode($row['name']) . "'><i class='fas fa-edit'></i></a>";
                                  echo "<a class='delete-icon' href='javascript:confirmDelete(" . $row['user_id'] . ")'><i class='fas fa-trash'></i></a>";
                                  echo "</td>";
                                  echo "</tr>";
                              }
                          } else {
                              echo "<tr><td colspan='4'>No users found</td></tr>";
                          }
                          ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="deleteModal" class="modal">
              <div class="modal-content">
                <h5 style="font-size: 24px; color: red; margin-bottom: 15px;">Caution!</h5>
                <p style="font-size: 16px; color: #555;">Please be informed, all Inspection & Assessment results that are already linked to this user will also be permanently deleted.</p>

                <!-- Tambahkan container untuk tombol -->
                <div class="btn-container">
                  <button id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                  <button id="cancelDeleteBtn" class="btn btn-secondary">Cancel</button>
                </div>
              </div>
            </div>
            <div id="deleteSuccessPopup" class="popup" style="background-color: #4CAF50; color: white;">
              <p id="deleteSuccessMessage"><?php echo isset($deleteNotification) ? $deleteNotification : ''; ?></p>
          </div>
        <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Fueling the Bright Future | <a href="https:mandiricoal.co.id" target="_blank" class="pe-1 text-primary text-decoration-underline">mandiricoal.co.id</a>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/js-admin.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        var deleteSuccessMessage = document.getElementById('deleteSuccessMessage');

        if (deleteSuccessMessage !== null && deleteSuccessMessage.innerText !== '') {
            var deleteSuccessPopup = document.getElementById('deleteSuccessPopup');
            deleteSuccessPopup.style.display = 'block';

            setTimeout(function () {
                deleteSuccessPopup.style.display = 'none';
                window.history.replaceState({}, document.title, "admin.php");
            }, 3000);
        }
    });

    function confirmDelete(userId) {
        var modal = document.getElementById('deleteModal');
        modal.style.display = 'block';

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            window.location.href = "delete_user.php?user_id=" + userId;
        });

        document.getElementById('cancelDeleteBtn').addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    function searchNIK() {
        var nikInput = document.getElementById("nik");
        var nik = nikInput.value.trim();

        if (nik !== '') {
            var selectedCompany = document.getElementById('company').value;

            var apiEndpoint = getApiEndpoint(selectedCompany);

            fetch(`${apiEndpoint}?bukrs=${getBukrsFromCompany(selectedCompany)}&search=${nik}`)
                .then(response => response.json())
                .then(data => handleResponse(data))
                .catch(error => {
                    console.error('Error fetching user data:', error);
                });
        } else {
            // Handle empty NIK
        }
    }

    function handleResponse(data) {
        if (data.length > 0) {
            var userData = data[0];
            document.getElementById('name').value = userData[1] || '';
            document.getElementById('department').value = userData[7] || '';
        } else {
            document.getElementById('name').value = '';
            document.getElementById('department').value = '';
        }
    }

    function saveUser() {
        var nikInput = document.getElementById('nik');
        var nik = nikInput.value.trim();

        if (!nik) {
            console.error('Error: NIK is required');
            return;
        }

        var selectedCompany = document.getElementById('company').value;

        var form = document.getElementById("addUserForm");
        var formData = new FormData(form);

        formData.set("nik", nik);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "add_user.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                var notificationElement = document.getElementById("notification");
                var notificationPopup = document.getElementById("notification-popup");

                notificationElement.innerText = response.message;

                if (response.success) {
                    notificationElement.classList.add('alert-success');
                } else {
                    notificationElement.classList.add('alert-danger');
                }

                notificationPopup.style.display = 'block';

                // Hide notification popup after 3 seconds
                setTimeout(function () {
                    notificationPopup.style.display = 'none';
                }, 3000);

                searchUsers();
            }
        };
        xhr.send(formData);
    }

    function getApiEndpoint(selectedCompany) {
        switch (selectedCompany) {
            case 'mipho':
            case 'mipsite':
                return 'https://sisakty.mandiricoal.co.id/controller/userController.php';
            case 'mkpho':
            case 'mkpsite':
                return 'https://sisakty.mandiricoal.co.id/controller/userController.php';
            case 'mpmho':
            case 'mpmsite':
                return 'https://sisakty.mandiricoal.co.id/controller/userController.php';
            case 'mhaho':
            case 'mhasite':
                return 'https://sisakty.mandiricoal.co.id/controller/userController.php';
            case 'gms':
                return 'https://sisakty.mandiricoal.co.id/controller/userController.php';
            default:
                return '';
        }
    }

    function getBukrsFromCompany(selectedCompany) {
        switch (selectedCompany) {
            case 'mipho':
            case 'mipsite':
                return '0100';
            case 'mkpho':
            case 'mkpsite':
                return '0200';
            case 'mpmho':
            case 'mpmsite':
                return '0500';
            case 'mhaho':
            case 'mhasite':
                return '0300';
            case 'gms':
                return '0400';
            // Handle case for 'mandiriland' if necessary
            default:
                return '';
        }
    }

    function searchUsers() {
        var searchInput = document.getElementById('search').value.toLowerCase();
        var tableRows = document.querySelectorAll('.table tbody tr');

        tableRows.forEach(function (row) {
            var cells = row.querySelectorAll('td');
            var rowMatches = false; 

            Array.from(cells).forEach(function (cell, index) {
                if (index !== 4) {
                    var cellText = cell.textContent.toLowerCase();

                    if (cellText.includes(searchInput)) {
                        rowMatches = true;
                    }
                }
            });

            if (rowMatches) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function setTableRowsPerPage() {
        var limitSelect = document.getElementById('limit');
        var selectedLimit = parseInt(limitSelect.value);
        var tableRows = document.querySelectorAll('.table tbody tr');

        tableRows.forEach(function (row, index) {
            row.style.display = '';

            if (index >= selectedLimit && selectedLimit !== 999999) {
                row.style.display = 'none';
            }
        });
    }

    var searchInput = document.getElementById('search');
    searchInput.addEventListener('input', function () {
        searchUsers();
    });

    var limitSelect = document.getElementById('limit');
    limitSelect.addEventListener('change', function () {
        setTableRowsPerPage();
    });

    function showPopup() {
        document.getElementById('userPopup').style.display = 'block';
    }

    function hidePopup() {
        document.getElementById('userPopup').style.display = 'none';
    }
    </script>
</body>
</html>