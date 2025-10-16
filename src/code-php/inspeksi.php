<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

$conn_podema = mysqli_connect("mandiricoal.net", "podema", "Jam10pagi#", "podema");

if (!$conn_podema) {
    die("Koneksi database podema gagal: " . mysqli_connect_error());
}

function fetchData($table) {
    global $conn_podema;
    $data = array();
    $result = mysqli_query($conn_podema, "SELECT * FROM $table");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    }
    return $data;
}

$userInfos = array();
$users = fetchData("users");
foreach ($users as $user) {
    $userInfos[$user['name']] = array(
        'company' => $user['company'],
        'divisi' => $user['department']
    );
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Device Management Application</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="stylesheet" href="../assets/css/style_assess.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    /* Gaya submenu */
    .sidebar-submenu {
        max-height: 0;
        overflow: hidden;
    }

    .sidebar-item.active .sidebar-submenu {
        max-height: 1000px;
    }

    .sidebar-submenu .sidebar-item {
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.4s ease, transform 0.7s ease;
        display: flex;
        align-items: center;
        padding-left: 20px;
    }

    .sidebar-item.active .sidebar-submenu .sidebar-item {
        opacity: 1;
        transform: translateY(0);
    }

    .sidebar-submenu .sidebar-link i {
        margin-right: 10px;
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
        <div class="brand-logo d-flex align-items-center justify-content-center">
          <a href="" class="text-nowrap logo-img">
            <br>
            <img src="../assets/images/logos/logos.png" width="160" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
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
    <!--  Sidebar End -->
    <!--  Main wrapper -->
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
                      <p class="mb-0 fs-3">My Account</p>
                    </a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-list-check fs-6"></i>
                      <p class="mb-0 fs-3">My Task</p>
                    </a>
                    <a href="./logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      <div class="container-fluid">
        <div class="card">
          <h1 class="card-title fw-semibold mb-4">Device Inspection</h1>
          <form>
          
          </form>
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
</body>

</html>