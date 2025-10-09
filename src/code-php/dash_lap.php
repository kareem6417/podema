<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

// Konfigurasi Database
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";

try {
  $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  die("Koneksi ke database gagal: " . $e->getMessage());
}

// Logika untuk filter dan pagination
$all_names = $conn->query("SELECT DISTINCT name FROM assess_laptop WHERE name IS NOT NULL AND name != '' ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
$filter_name = isset($_GET['filter_name']) ? $_GET['filter_name'] : '';
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$params = [];
$where_clauses = "WHERE 1=1";
if (!empty($filter_name)) {
    $where_clauses .= " AND al.name = :name";
    $params[':name'] = $filter_name;
}

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM assess_laptop al " . $where_clauses);
$count_stmt->execute($params);
$totalRows = $count_stmt->fetchColumn();

$totalPages = ceil($totalRows / $limit);
$currentPage = max(1, $currentPage); 
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1); 
$offset = ($currentPage - 1) * $limit;

$main_sql = "SELECT al.*, 
                os.os_name, ram.ram_name, proc.processor_name, bat.battery_name, age.age_name, 
                iss.issue_name, vga.vga_name, store.storage_name, kbd.keyboard_name, 
                scr.screen_name, pad.touchpad_name, aud.audio_name, body.body_name
            FROM assess_laptop al
            LEFT JOIN operating_sistem_laptop os ON al.os = os.os_score
            LEFT JOIN ram_laptop ram ON al.ram = ram.ram_score
            LEFT JOIN processor_laptop proc ON al.processor = proc.processor_score
            LEFT JOIN batterylife_laptop bat ON al.batterylife = bat.battery_score
            LEFT JOIN device_age_laptop age ON al.age = age.age_score
            LEFT JOIN issue_software_laptop iss ON al.issue = iss.issue_score
            LEFT JOIN vga_pc vga ON al.vga = vga.vga_score
            LEFT JOIN storage_laptop store ON al.storage = store.storage_score
            LEFT JOIN keyboard_laptop kbd ON al.keyboard = kbd.keyboard_score
            LEFT JOIN screen_laptop scr ON al.screen = scr.screen_score
            LEFT JOIN touchpad_laptop pad ON al.touchpad = pad.touchpad_score
            LEFT JOIN audio_laptop aud ON al.audio = aud.audio_score
            LEFT JOIN body_laptop body ON al.body = body.body_score " . 
            $where_clauses . " ORDER BY al.date DESC, al.id DESC LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($main_sql);
foreach ($params as $key => &$val) { $stmt->bindParam($key, $val); }
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

function format_detail_string($name, $score) {
    if ($name === null || $name === '') return 'N/A';
    return htmlspecialchars($name) . ' (Skor: ' . htmlspecialchars($score) . ')';
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Laptop Assessment</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    .sidebar-submenu { position: static !important; max-height: 0; overflow: hidden; transition: max-height 0.35s ease-in-out; list-style: none; padding-left: 25px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; margin: 0 10px 5px 10px; }
    .sidebar-item.active > .sidebar-submenu { max-height: 500px; }
    .sidebar-item > a .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: auto; }
    .sidebar-item.active > a .arrow { transform: rotate(180deg); }
    .table th, .table td { vertical-align: middle; }
    .action-icons span, .action-icons a { font-size: 1.2rem; margin: 0 5px; cursor: pointer; }
    .modal-body table td:first-child { font-weight: bold; width: 35%; }
  </style>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <aside class="left-sidebar">
        <div>
            <div class="brand-logo d-flex align-items-center justify-content-center">
                <a href="" class="text-nowrap logo-img"><br><img src="../assets/images/logos/logos.png" width="160" alt="" /></a>
                <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse"><i class="ti ti-x fs-8"></i></div>
            </div>
            <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                <ul id="sidebarnav">
                    <li class="nav-small-cap"><i class="ti ti-dots nav-small-cap-icon fs-4"></i><span class="hide-menu">Home</span></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./admin.php" aria-expanded="false"><span><i class="ti ti-layout-dashboard"></i></span><span class="hide-menu">Administrator</span></a></li>
                    <li class="nav-small-cap"><i class="ti ti-dots nav-small-cap-icon fs-4"></i><span class="hide-menu">Dashboard</span></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./dash_lap.php" aria-expanded="false"><span><i class="ti ti-chart-area-line"></i></span><span class="hide-menu">Assessment Laptop</span></a></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./dash_pc.php" aria-expanded="false"><span><i class="ti ti-chart-line"></i></span><span class="hide-menu">Assessment PC Desktop</span></a></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./dash_ins.php" aria-expanded="false"><span><i class="ti ti-chart-donut"></i></span><span class="hide-menu">Inspection</span></a></li>
                    <li class="nav-small-cap"><i class="ti ti-dots nav-small-cap-icon fs-4"></i><span class="hide-menu">Evaluation Portal</span></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./assess_laptop.php" aria-expanded="false"><span><i class="ti ti-device-laptop"></i></span><span class="hide-menu">Assessment Laptop</span></a></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./assess_pc.php" aria-expanded="false"><span><i class="ti ti-device-desktop-analytics"></i></span><span class="hide-menu">Assessment PC Desktop</span></a></li>
                    <li class="sidebar-item">
                      <a class="sidebar-link" href="#" aria-expanded="false"><span><i class="ti ti-assembly"></i></span><span class="hide-menu">Device Inspection</span><span class="arrow"><i class="fas fa-chevron-down"></i></span></a>
                      <ul class="sidebar-submenu">
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_laptop.php"><span><i class="ti ti-devices"></i></span>Laptop</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_desktop.php"><span><i class="ti ti-device-desktop-search"></i></span>PC Desktop</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_monitor.php"><span><i class="ti ti-screen-share"></i></span>Monitor</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_printer.php"><span><i class="ti ti-printer"></i></span>Printer</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_cctv.php"><span><i class="ti ti-device-cctv"></i></span>CCTV</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_infra.php"><span><i class="ti ti-router"></i></span>Infrastructure</a></li>
                          <li class="sidebar-item"><a class="sidebar-link" href="./ins_tlp.php"><span><i class="ti ti-device-landline-phone"></i></span>Telephone</a></li>
                      </ul>
                    </li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./about.php" aria-expanded="false"><span><i class="ti ti-exclamation-circle"></i></span><span class="hide-menu">About</span></a></li>
                    <li class="nav-small-cap"><i class="ti ti-dots nav-small-cap-icon fs-4"></i><span class="hide-menu">Asset Management</span></li>
                    <li class="sidebar-item"><a class="sidebar-link" href="./astmgm.php" aria-expanded="false"><span><i class="ti ti-cards"></i></span><span class="hide-menu">IT Asset Management</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="body-wrapper">
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item d-block d-xl-none"><a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)"><i class="ti ti-menu-2"></i></a></li>
                <li class="nav-item"><a class="nav-link nav-icon-hover" href="javascript:void(0)"><i class="ti ti-bell-ringing"></i><div class="notification bg-primary rounded-circle"></div></a></li>
            </ul>
            <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false"><img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle"></a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                            <div class="message-body">
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-user fs-6"></i><p class="mb-0 fs-3">My Profile</p></a>
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-mail fs-6"></i><p class="mb-0 fs-3">My Device</p></a>
                                <a href="./authentication-login.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
      </header>
      
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Dashboard Laptop Assessment</h5>
            <div class="card shadow-none">
                <div class="card-body p-3">
                    <form class="row g-3 align-items-center" method="get">
                        <div class="col-md-4">
                            <label for="filter_name" class="form-label">Filter by Name</label>
                            <select id="filter_name" name="filter_name" class="form-select">
                                <option value="">All Names</option>
                                <?php foreach ($all_names as $name): ?>
                                    <option value="<?php echo htmlspecialchars($name); ?>" <?php if ($filter_name == $name) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="limit" class="form-label">Rows per page</label>
                            <select id="limit" name="limit" class="form-select">
                                <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                                <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option>
                                <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Type/Merk</th>
                        <th>Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No assessment data found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $index => $row): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['date']))); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td>
                                <?php 
                                    $score = $row['score'];
                                    $badge_class = 'bg-success';
                                    if ($score >= 100 && $score < 200) {
                                        $badge_class = 'bg-warning';
                                    } elseif ($score >= 200) {
                                        $badge_class = 'bg-danger';
                                    }
                                    echo '<span class="badge ' . $badge_class . ' rounded-3 fw-semibold">' . htmlspecialchars($score) . '</span>';
                                ?>
                            </td>
                            <td class="action-icons">
                                <span data-bs-toggle="modal" data-bs-target="#detailModal" 
                                      data-id="<?php echo $row['id']; ?>"
                                      data-date="<?php echo htmlspecialchars(date('d M Y', strtotime($row['date']))); ?>"
                                      data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                      data-company="<?php echo htmlspecialchars($row['company']); ?>"
                                      data-divisi="<?php echo htmlspecialchars($row['divisi']); ?>"
                                      data-type="<?php echo htmlspecialchars($row['type']); ?>"
                                      data-serialnumber="<?php echo htmlspecialchars($row['serialnumber']); ?>"
                                      data-os="<?php echo format_detail_string($row['os_name'], $row['os']); ?>"
                                      data-processor="<?php echo format_detail_string($row['processor_name'], $row['processor']); ?>"
                                      data-batterylife="<?php echo format_detail_string($row['battery_name'], $row['batterylife']); ?>"
                                      data-age="<?php echo format_detail_string($row['age_name'], $row['age']); ?>"
                                      data-issue="<?php echo format_detail_string($row['issue_name'], $row['issue']); ?>"
                                      data-ram="<?php echo format_detail_string($row['ram_name'], $row['ram']); ?>"
                                      data-vga="<?php echo format_detail_string($row['vga_name'], $row['vga']); ?>"
                                      data-storage="<?php echo format_detail_string($row['storage_name'], $row['storage']); ?>"
                                      data-keyboard="<?php echo format_detail_string($row['keyboard_name'], $row['keyboard']); ?>"
                                      data-screen="<?php echo format_detail_string($row['screen_name'], $row['screen']); ?>"
                                      data-touchpad="<?php echo format_detail_string($row['touchpad_name'], $row['touchpad']); ?>"
                                      data-audio="<?php echo format_detail_string($row['audio_name'], $row['audio']); ?>"
                                      data-body="<?php echo format_detail_string($row['body_name'], $row['body']); ?>"
                                      data-score="<?php echo htmlspecialchars($row['score']); ?>">
                                    <i class="ti ti-eye text-primary" title="View Details"></i>
                                </span>
                                <a href="./download_lap_assessment.php?id=<?php echo $row['id']; ?>" target="_blank">
                                    <i class="ti ti-download text-secondary" title="Download PDF"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-end">
                <ul class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&limit=<?php echo $limit; ?>&filter_name=<?php echo urlencode($filter_name); ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&filter_name=<?php echo urlencode($filter_name); ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&limit=<?php echo $limit; ?>&filter_name=<?php echo urlencode($filter_name); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
          </div>
        </div>
      </div>
       <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Fueling the Bright Future | <a href="https:mandiricoal.co.id" target="_blank" class="pe-1 text-primary text-decoration-underline">mandiricoal.co.id</a></p>
       </div>
    </div>
  </div>

  <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Detail Laptop Assessment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-bordered">
                <tr><td>Assessment ID</td><td id="modal-id"></td></tr>
                <tr><td>Date</td><td id="modal-date"></td></tr>
                <tr><td>Name</td><td id="modal-name"></td></tr>
                <tr><td>Company</td><td id="modal-company"></td></tr>
                <tr><td>Department</td><td id="modal-divisi"></td></tr>
                <tr><td>Type/Merk</td><td id="modal-type"></td></tr>
                <tr><td>Serial Number</td><td id="modal-serialnumber"></td></tr>
                <tr><td colspan="2" class="text-center bg-light"><strong>Assessment Details</strong></td></tr>
                <tr><td>Operating System</td><td id="modal-os"></td></tr>
                <tr><td>Processor</td><td id="modal-processor"></td></tr>
                <tr><td>Battery Life</td><td id="modal-batterylife"></td></tr>
                <tr><td>Device Age</td><td id="modal-age"></td></tr>
                <tr><td>Software Issue</td><td id="modal-issue"></td></tr>
                <tr><td>RAM</td><td id="modal-ram"></td></tr>
                <tr><td>VGA</td><td id="modal-vga"></td></tr>
                <tr><td>Storage</td><td id="modal-storage"></td></tr>
                <tr><td>Keyboard</td><td id="modal-keyboard"></td></tr>
                <tr><td>Screen</td><td id="modal-screen"></td></tr>
                <tr><td>Touchpad</td><td id="modal-touchpad"></td></tr>
                <tr><td>Audio</td><td id="modal-audio"></td></tr>
                <tr><td>Body</td><td id="modal-body"></td></tr>
                <tr><td><strong>Total Score</strong></td><td id="modal-score"></td></tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
      submenuToggles.forEach(function(toggle) {
          toggle.addEventListener('click', function(e) {
              e.preventDefault();
              var parentItem = this.closest('.sidebar-item');
              if (parentItem) { parentItem.classList.toggle('active'); }
          });
      });

      var detailModal = document.getElementById('detailModal');
      detailModal.addEventListener('show.bs.modal', function (event) {
          var triggerElement = event.relatedTarget;
          var modalBody = detailModal.querySelector('.modal-body');
          
          modalBody.querySelector('#modal-id').textContent = triggerElement.getAttribute('data-id');
          modalBody.querySelector('#modal-date').textContent = triggerElement.getAttribute('data-date');
          modalBody.querySelector('#modal-name').textContent = triggerElement.getAttribute('data-name');
          modalBody.querySelector('#modal-company').textContent = triggerElement.getAttribute('data-company');
          modalBody.querySelector('#modal-divisi').textContent = triggerElement.getAttribute('data-divisi');
          modalBody.querySelector('#modal-type').textContent = triggerElement.getAttribute('data-type');
          modalBody.querySelector('#modal-serialnumber').textContent = triggerElement.getAttribute('data-serialnumber');
          modalBody.querySelector('#modal-os').textContent = triggerElement.getAttribute('data-os');
          modalBody.querySelector('#modal-processor').textContent = triggerElement.getAttribute('data-processor');
          modalBody.querySelector('#modal-batterylife').textContent = triggerElement.getAttribute('data-batterylife');
          modalBody.querySelector('#modal-age').textContent = triggerElement.getAttribute('data-age');
          modalBody.querySelector('#modal-issue').textContent = triggerElement.getAttribute('data-issue');
          modalBody.querySelector('#modal-ram').textContent = triggerElement.getAttribute('data-ram');
          modalBody.querySelector('#modal-vga').textContent = triggerElement.getAttribute('data-vga');
          modalBody.querySelector('#modal-storage').textContent = triggerElement.getAttribute('data-storage');
          modalBody.querySelector('#modal-keyboard').textContent = triggerElement.getAttribute('data-keyboard');
          modalBody.querySelector('#modal-screen').textContent = triggerElement.getAttribute('data-screen');
          modalBody.querySelector('#modal-touchpad').textContent = triggerElement.getAttribute('data-touchpad');
          modalBody.querySelector('#modal-audio').textContent = triggerElement.getAttribute('data-audio');
          modalBody.querySelector('#modal-body').textContent = triggerElement.getAttribute('data-body');
          modalBody.querySelector('#modal-score').textContent = triggerElement.getAttribute('data-score');
      });
    });
  </script>
</body>
</html>