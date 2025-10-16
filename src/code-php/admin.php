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

// Logika untuk filter, pencarian, dan pagination
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filterCompany = isset($_GET['filterCompany']) ? $_GET['filterCompany'] : '';

// Handle Penambahan User (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $nik = $_POST['nik'];
        $name = $_POST['name'];
        $company = $_POST['company'];
        $department = $_POST['department'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("INSERT INTO users (nik, name, company, department, email) VALUES (:nik, :name, :company, :department, :email)");
        $stmt->bindParam(':nik', $nik);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':company', $company);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Success! A new user has been added.']);
        } else {
            throw new Exception('Failed to execute statement.');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed! Please double-check the data. ' . $e->getMessage()]);
    }
    exit();
}


// Opsi Perusahaan untuk filter dan tampilan
$companyOptions = [
    'MIP HO' => 'PT. Mandiri Intiperkasa - HO',
    'MIP Site' => 'PT. Mandiri Intiperkasa - Site',
    'MKP HO' => 'PT. Mandala Karya Prima - HO',
    'MKP Site' => 'PT. Mandala Karya Prima - Site',
    'MPM HO' => 'PT. Maritim Prima Mandiri - HO',
    'MPM Site' => 'PT. Maritim Prima Mandiri - Site',
    'MHA HO' => 'PT. Mandiri Herindo Adiperkasa - HO',
    'MHA Site' => 'PT. Mandiri Herindo Adiperkasa - Site',
    'PAM' => 'PT. Prima Andalan Mandiri',
    'mandiriland' => 'PT. Mandiriland',
    'GMS' => 'PT. Global Mining Service',
    'eam' => 'PT. Edika Agung Mandiri',
];


// Persiapan query utama (GET Request)
$params = [];
$where_clauses = "WHERE 1=1";
if (!empty($search)) {
    $where_clauses .= " AND (u.nik LIKE :search OR u.name LIKE :search OR u.department LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if (!empty($filterCompany)) {
    $where_clauses .= " AND u.company = :company";
    $params[':company'] = $filterCompany;
}

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users u " . $where_clauses);
$count_stmt->execute($params);
$totalRows = $count_stmt->fetchColumn();

// Pagination
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPages = $limit > 0 ? ceil($totalRows / $limit) : 1;
$currentPage = max(1, min($currentPage, $totalPages > 0 ? $totalPages : 1));
$offset = ($currentPage - 1) * $limit;

$main_sql = "SELECT * FROM users u " . $where_clauses . " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($main_sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <base href="/src/">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Management - Administrator</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    .sidebar-submenu { position: static !important; max-height: 0; overflow: hidden; transition: max-height 0.35s ease-in-out; list-style: none; padding-left: 25px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; margin: 0 10px 5px 10px; }
    .sidebar-item.active > .sidebar-submenu { max-height: 500px; }
    .sidebar-item > a .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: auto; }
    .sidebar-item.active > a .arrow { transform: rotate(180deg); }
    .action-icons a { font-size: 1.2rem; margin: 0 5px; }
    .notification-popup { position: fixed; top: 20px; right: 20px; z-index: 1055; min-width: 300px; }
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
                                <a href="/logout" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
      </header>
      
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">User Management</h5>

            <div class="card shadow-none">
                <div class="card-body p-3">
                    <form class="row g-3 align-items-center" method="get">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search by NIK, Name, or Department</label>
                            <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="filterCompany" class="form-label">Company</label>
                            <select id="filterCompany" name="filterCompany" class="form-select">
                                <option value="">All Companies</option>
                                <?php foreach($companyOptions as $key => $value): ?>
                                    <option value="<?php echo $key; ?>" <?php if ($filterCompany == $key) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2 d-flex align-items-end">
                           <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="ti ti-plus me-1"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover table-striped text-nowrap mb-0 align-middle">
                <thead class="text-dark fs-4">
                    <tr>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">NIK</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Name</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Company</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Department</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Action</h6></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <td><p class="mb-0 fw-normal"><?php echo htmlspecialchars($row['nik']); ?></p></td>
                            <td><p class="mb-0 fw-normal"><?php echo htmlspecialchars($row['name']); ?></p></td>
                            <td><p class="mb-0 fw-normal"><?php echo isset($companyOptions[$row['company']]) ? htmlspecialchars($companyOptions[$row['company']]) : htmlspecialchars($row['company']); ?></p></td>
                            <td><p class="mb-0 fw-normal"><?php echo htmlspecialchars($row['department']); ?></p></td>
                            <td class="action-icons">
                                <a href="user_detail.php?user_id=<?php echo $row['user_id']; ?>" class="text-info" title="Edit User"><i class="ti ti-pencil"></i></a>
                                <a href="#" class="text-danger" title="Delete User" onclick="confirmDelete('<?php echo $row['user_id']; ?>', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')"><i class="ti ti-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-end">
                <ul class="pagination">
                    <?php if ($currentPage > 1):
                        $prevParams = http_build_query(array_merge($_GET, ['page' => $currentPage - 1]));?>
                        <li class="page-item"><a class="page-link" href="?<?php echo $prevParams; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++):
                        $pageParams = http_build_query(array_merge($_GET, ['page' => $i]));?>
                        <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>"><a class="page-link" href="?<?php echo $pageParams; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages):
                        $nextParams = http_build_query(array_merge($_GET, ['page' => $currentPage + 1]));?>
                        <li class="page-item"><a class="page-link" href="?<?php echo $nextParams; ?>">Next</a></li>
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

  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="nik" name="nik" required>
                                <button class="btn btn-outline-secondary" type="button" id="searchNikBtn"><i class="ti ti-search"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveUserBtn">Save User</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteUserModalLabel">Caution!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-muted small">All Inspection & Assessment results linked to this user will also be permanently deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteLink" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <div id="notification-popup" class="notification-popup"></div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Sidebar Submenu Toggle
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
      submenuToggles.forEach(function(toggle) {
          toggle.addEventListener('click', function(e) {
              e.preventDefault();
              var parentItem = this.closest('.sidebar-item');
              if (parentItem) { parentItem.classList.toggle('active'); }
          });
      });

      // NIK Search Functionality
      document.getElementById('searchNikBtn').addEventListener('click', function() {
          const nik = document.getElementById('nik').value.trim();
          if (!nik) return;

          const myHeaders = new Headers();
          myHeaders.append("api_key", "ca6cda3462809fc894801c6f84e0cd8ecff93afb");
          const requestOptions = { method: 'GET', headers: myHeaders, redirect: 'follow' };

          fetch(`http://mandiricoal.co.id:1880/master/employee/pernr/${nik}`, requestOptions)
              .then(response => response.json())
              .then(data => {
                  if (data.employee && data.employee.length > 0) {
                      const userData = data.employee[0];
                      document.getElementById('name').value = userData.CNAME || '';
                      document.getElementById('department').value = userData.ORGTX || '';
                      document.getElementById('email').value = userData.UMAIL || '';
                      document.getElementById('company').value = userData.ABKTX || '';
                  } else {
                      showNotification('User NIK not found.', 'danger');
                  }
              })
              .catch(error => {
                console.error('Error:', error);
                showNotification('Error fetching user data.', 'danger');
              });
      });

      // Save User Functionality
      document.getElementById('saveUserBtn').addEventListener('click', function() {
          const form = document.getElementById('addUserForm');
          const formData = new FormData(form);
          
          fetch('admin.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              showNotification(data.message, data.success ? 'success' : 'danger');
              if (data.success) {
                  // Close modal and reload page after a short delay
                  var addUserModal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                  addUserModal.hide();
                  setTimeout(() => window.location.reload(), 1500);
              }
          })
          .catch(error => {
              console.error('Error:', error);
              showNotification('An unexpected error occurred.', 'danger');
          });
      });
    });

    // Function to show notification
    function showNotification(message, type) {
        const notificationPopup = document.getElementById('notification-popup');
        const alertClass = `alert alert-${type}`;
        notificationPopup.innerHTML = `<div class="${alertClass}" role="alert">${message}</div>`;
        
        setTimeout(() => {
            notificationPopup.innerHTML = '';
        }, 3000);
    }
    
    // Function to open delete confirmation modal
    function confirmDelete(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('confirmDeleteLink').href = `delete_user.php?user_id=${userId}`;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        deleteModal.show();
    }
  </script>
</body>
</html>