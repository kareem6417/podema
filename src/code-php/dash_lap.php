<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi ke database gagal: " . $e->getMessage();
    exit();
}

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM assess_laptop"); 
    $totalRows = $stmt->fetchColumn();

    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        if ($limit == 999999) {
            $totalRows = $conn->query("SELECT COUNT(*) FROM assess_laptop")->fetchColumn();
        } else {
            $allowedLimits = [10, 25, 50, 100];
            $limit = in_array($limit, $allowedLimits) ? $limit : 10;
            $totalRows = $limit;
        }

    $totalPages = 1;

    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    $currentPage = max(1, $currentPage); 
    $currentPage = min($currentPage, $totalPages); 

    $offset = ($currentPage - 1) * $limit;

    $stmt = $conn->prepare("SELECT assess_laptop.*, operating_sistem_laptop.os_name AS os_name, operating_sistem_laptop.os_score AS os_score,
                            processor_laptop.processor_name AS processor_name, processor_laptop.processor_score AS processor_score,
                            batterylife_laptop.battery_name AS battery_name, batterylife_laptop.battery_score AS battery_score,
                            device_age_laptop.age_name AS age_name, device_age_laptop.age_score AS age_score,
                            issue_software_laptop.issue_name AS issue_name, issue_software_laptop.issue_score AS issue_score,
                            ram_laptop.ram_name AS ram_name, ram_laptop.ram_score AS ram_score,
                            vga_pc.vga_name AS vga_name, vga_pc.vga_score AS vga_score,
                            storage_laptop.storage_name AS storage_name, storage_laptop.storage_score AS storage_score,
                            keyboard_laptop.keyboard_name AS keyboard_name, keyboard_laptop.keyboard_score AS keyboard_score,
                            screen_laptop.screen_name AS screen_name, screen_laptop.screen_score AS screen_score,
                            touchpad_laptop.touchpad_name AS touchpad_name, touchpad_laptop.touchpad_score AS touchpad_score,
                            audio_laptop.audio_name AS audio_name, audio_laptop.audio_score AS audio_score,
                            body_laptop.body_name AS body_name, body_laptop.body_score AS body_score
                            FROM assess_laptop
                            LEFT JOIN operating_sistem_laptop ON assess_laptop.os = operating_sistem_laptop.os_score
                            LEFT JOIN processor_laptop ON assess_laptop.processor = processor_laptop.processor_score
                            LEFT JOIN batterylife_laptop ON assess_laptop.batterylife = batterylife_laptop.battery_score
                            LEFT JOIN device_age_laptop ON assess_laptop.age = device_age_laptop.age_score
                            LEFT JOIN issue_software_laptop ON assess_laptop.issue = issue_software_laptop.issue_score
                            LEFT JOIN ram_laptop ON assess_laptop.ram = ram_laptop.ram_score
                            LEFT JOIN vga_pc ON assess_laptop.vga = vga_pc.vga_score
                            LEFT JOIN storage_laptop ON assess_laptop.storage = storage_laptop.storage_score
                            LEFT JOIN keyboard_laptop ON assess_laptop.keyboard = keyboard_laptop.keyboard_score
                            LEFT JOIN screen_laptop ON assess_laptop.screen = screen_laptop.screen_score
                            LEFT JOIN touchpad_laptop ON assess_laptop.touchpad = touchpad_laptop.touchpad_score
                            LEFT JOIN audio_laptop ON assess_laptop.audio = audio_laptop.audio_score
                            LEFT JOIN body_laptop ON assess_laptop.body = body_laptop.body_score
                            ORDER BY assess_laptop.id DESC
                            LIMIT :limit OFFSET :offset");
                        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Device Management Application</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/js-dsh.js"></script>
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

    table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    /* Popup Styles */
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
      z-index: 1;
    }

    .popup-content {
      display: none;
      position: fixed;
      background: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
      z-index: 2;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 60%;
      max-width: 600px;
      max-height: 80%;
      overflow-y: auto;
    }

    .popup-content h3 {
      color: #333;
      margin-bottom: 20px;
    }

    .popup-content table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .popup-content td {
      border: 1px solid #ddd;
      padding: 8px;
    }

    .popup-content td:first-child {
      font-weight: bold;
      width: 150px;
    }

    .popup-content span {
      margin-right: 5px;
    }

    .popup-content .icon-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .blue-eye {
      color: blue;
      cursor: pointer;
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
                      <p class="mb-0 fs-3">My Device</p>
                    </a>
                    <a href="./authentication-login.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                  <div class="mb-3 mb-sm-0">
                    <h5 class="card-title fw-semibold">Dashboard Assessment Laptop</h5>
                  </div>
                  <div>
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
                    </form>
                  </div>
                </div>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Company/Department</th>
                        <th>Type/Merk</th>
                        <th>Score</th>
                        <th>Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($results as $count => $row) { ?>
                          <tr>
                            <td><?php echo (($currentPage - 1) * $limit) + $count + 1; ?></td>
                              <td><?php echo $row['date']; ?></td>
                              <td><?php echo $row['name']; ?></td>
                              <td><?php echo $row['company'] . ' / ' . $row['divisi']; ?></td>
                              <td><?php echo $row['type']; ?></td>
                              <td><?php echo $row['score']; ?></td>
                            <td>
                            <span class="popup-trigger"
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo $row['name']; ?>"
                                data-company="<?php echo $row['company'] . ' / ' . $row['divisi']; ?>"
                                data-type="<?php echo $row['type']; ?>"
                                data-serialnumber="<?php echo $row['serialnumber']; ?>"
                                data-os_name="<?php echo $row['os_name']; ?>"
                                data-os_score="<?php echo $row['os_score']; ?>"
                                data-processor_name="<?php echo $row['processor_name']; ?>"
                                data-processor_score="<?php echo $row['processor_score']; ?>"
                                data-battery_name="<?php echo $row['battery_name']; ?>"
                                data-battery_score="<?php echo $row['battery_score']; ?>"
                                data-age_name="<?php echo $row['age_name']; ?>"
                                data-age_score="<?php echo $row['age_score']; ?>"
                                data-issue_name="<?php echo $row['issue_name']; ?>"
                                data-issue_score="<?php echo $row['issue_score']; ?>"
                                data-ram_name="<?php echo $row['ram_name']; ?>"
                                data-ram_score="<?php echo $row['ram_score']; ?>"
                                data-vga_name="<?php echo $row['vga_name']; ?>"
                                data-vga_score="<?php echo $row['vga_score']; ?>"
                                data-storage_name="<?php echo $row['storage_name']; ?>"
                                data-storage_score="<?php echo $row['storage_score']; ?>"
                                data-keyboard_name="<?php echo $row['keyboard_name']; ?>"
                                data-keyboard_score="<?php echo $row['keyboard_score']; ?>"
                                data-screen_name="<?php echo $row['screen_name']; ?>"
                                data-screen_score="<?php echo $row['screen_score']; ?>"
                                data-touchpad_name="<?php echo $row['touchpad_name']; ?>"
                                data-touchpad_score="<?php echo $row['touchpad_score']; ?>"
                                data-audio_name="<?php echo $row['audio_name']; ?>"
                                data-audio_score="<?php echo $row['audio_score']; ?>"
                                data-body_name="<?php echo $row['body_name']; ?>"
                                data-body_score="<?php echo $row['body_score']; ?>"
                                data-score="<?php echo $row['score']; ?>">
                            <span class="icon-container">
                                <i class="fas fa-eye blue-eye" style="display: flex; justify-content: center; align-items: center;"></i>
                            </span>
                            </span>
                            </td>
                        </tr>           
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <h5>Detail Assessment Laptop</h5>
            <table>
                <tr>
                    <td>ID:</td>
                    <td id="popup-id"></td>
                </tr>
                <tr>
                    <td>Name:</td>
                    <td id="popup-name"></td>
                </tr>
                <tr>
                    <td>Company:</td>
                    <td id="popup-company"></td>
                </tr>
                <tr>
                    <td>Type/Merk:</td>
                    <td id="popup-type"></td>
                </tr>
                <tr>
                    <td>Serialnumber:</td>
                    <td id="popup-serialnumber"></td>
                </tr>
                <tr>
                    <td>Operating System:</td>
                    <td><span id="popup-os_name"></span><span id="popup-os_score"></span></td>
                </tr>
                <tr>
                    <td>Prosesor:</td>
                    <td><span id="popup-processor_name"></span> <span id="popup-processor_score"></span></td>
                </tr>
                <tr>
                    <td>Battery Life:</td>
                    <td><span id="popup-batterylife_name"></span><span id="popup-batterylife_score"></span></td>
                </tr>
                <tr>
                    <td>Device Age:</td>
                    <td><span id="popup-age_name"></span><span id="popup-age_score"></span></td>
                </tr>
                <tr>
                    <td>Issue Related Software:</td>
                    <td><span id="popup-issue_name"></span><span id="popup-issue_score"></span></td>
                </tr>
                <tr>
                    <td>RAM:</td>
                    <td><span id="popup-ram_name"></span><span id="popup-ram_score"></span></td>
                </tr>
                <tr>
                    <td>VGA:</td>
                    <td><span id="popup-vga_name"></span><span id="popup-vga_score"></span></td>
                </tr>
                <tr>
                    <td>Storage:</td>
                    <td><span id="popup-storage_name"></span><span id="popup-storage_score"></span></td>
                </tr>
                <tr>
                    <td>Keyboard:</td>
                    <td><span id="popup-keyboard_name"></span><span id="popup-keyboard_score"></span></td>
                </tr>
                <tr>
                    <td>Screen:</td>
                    <td><span id="popup-screen_name"></span><span id="popup-screen_score"></span></td>
                </tr>
                <tr>
                    <td>Touchpad:</td>
                    <td><span id="popup-touchpad_name"></span><span id="popup-touchpad_score"></span></td>
                </tr>
                <tr>
                    <td>Audio:</td>
                    <td><span id="popup-audio_name"></span><span id="popup-audio_score"></span></td>
                </tr>
                <tr>
                    <td>Body:</td>
                    <td><span id="popup-body_name"></span><span id="popup-body_score"></span></td>
                </tr>
                <tr>
                    <td>Score:</td>
                    <td id="popup-score"></td>
                </tr>
            </table>
          </div>
                <div class="row">
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
        <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Fueling the Bright Future | <a href="https:mandiricoal.co.id" target="_blank" class="pe-1 text-primary text-decoration-underline">mandiricoal.co.id</a>
        </div>
</body>
</html>