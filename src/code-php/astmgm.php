<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

// Konfigurasi Database (Gunakan PDO)
$host = "mandiricoal.net";
$db   = "podema";
$user = "podema";
$pass = "Jam10pagi#";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Koneksi ke database gagal: " . $e->getMessage());
}

// =================================================================
// DATA UNTUK TAB 1: TUGAS INSPEKSI (TO-DO LIST)
// =================================================================

$filter_bulan = isset($_GET['filter_bulan']) ? (int)$_GET['filter_bulan'] : date('m'); // Default bulan ini
$filter_tahun = isset($_GET['filter_tahun']) ? (int)$_GET['filter_tahun'] : date('Y'); // Default tahun ini

$sql_tugas = "SELECT 
            j.jadwal_id, 
            j.tanggal_dijadwalkan, 
            a.aset_id,
            a.jenis_perangkat, 
            a.serial_number,
            u.name as nama_karyawan,
            u.department as divisi_karyawan
        FROM jadwal_inspeksi j
        JOIN master_aset a ON j.aset_id = a.aset_id
        LEFT JOIN users u ON a.id_user = u.user_id
        WHERE 
            j.status_jadwal = 'Pending' AND
            MONTH(j.tanggal_dijadwalkan) = ? AND
            YEAR(j.tanggal_dijadwalkan) = ?
        ORDER BY 
            j.tanggal_dijadwalkan ASC";

$stmt_tugas = $conn->prepare($sql_tugas);
$stmt_tugas->execute([$filter_bulan, $filter_tahun]);
$daftar_tugas = $stmt_tugas->fetchAll();

// =================================================================
// DATA UNTUK TAB 2: MASTER ASET (DENGAN PAGINATION)
// =================================================================

$master_limit = 10; // Jumlah aset per halaman
$master_page = isset($_GET['master_page']) && is_numeric($_GET['master_page']) ? (int)$_GET['master_page'] : 1;
$master_offset = ($master_page - 1) * $master_limit;

// Hitung total aset
$total_aset_stmt = $conn->query("SELECT COUNT(*) FROM master_aset");
$total_master_aset = $total_aset_stmt->fetchColumn();
$total_master_pages = ceil($total_master_aset / $master_limit);

// Ambil data aset untuk halaman ini
$sql_aset = "SELECT 
            a.aset_id,
            a.serial_number,
            a.jenis_perangkat,
            a.lokasi,
            a.tanggal_inspeksi_terakhir,
            u.name as nama_karyawan
         FROM master_aset a
         LEFT JOIN users u ON a.id_user = u.user_id
         ORDER BY a.aset_id DESC
         LIMIT :limit OFFSET :offset";

$stmt_aset = $conn->prepare($sql_aset);
$stmt_aset->bindParam(':limit', $master_limit, PDO::PARAM_INT);
$stmt_aset->bindParam(':offset', $master_offset, PDO::PARAM_INT);
$stmt_aset->execute();
$daftar_aset = $stmt_aset->fetchAll();

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>IT Asset Management</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    /* Perbaikan gaya submenu sidebar */
    .sidebar-submenu {
        position: static !important; 
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease-in-out;
        list-style: none;
        padding-left: 25px;
        background-color: #f8f9fa;
        border-radius: 0 0 5px 5px;
        margin: 0 10px 5px 10px;
    }
    .sidebar-item.active > .sidebar-submenu { max-height: 500px; }
    .sidebar-item > a .arrow {
        transition: transform 0.3s ease;
        display: inline-block;
        margin-left: auto;
    }
    .sidebar-item.active > a .arrow { transform: rotate(180deg); }
    .table-hover tbody tr:hover { background-color: #f1f1f1; }
    
    /* Gaya untuk Tab */
    .nav-tabs .nav-link {
        font-weight: 600;
    }
    .nav-tabs .nav-link.active {
        color: #5D87FF;
        border-bottom-width: 3px;
    }
    .tab-content {
        border: 1px solid #dee2e6;
        border-top: 0;
        padding: 1.5rem;
        border-radius: 0 0 0.25rem 0.25rem;
    }
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
            
            <li class="sidebar-item"> <a class="sidebar-link" href="#" aria-expanded="false"><span><i class="ti ti-cards"></i></span><span class="hide-menu">IT Asset Management</span><span class="arrow"><i class="fas fa-chevron-down"></i></span></a>
              <ul class="sidebar-submenu">
                  <li class="sidebar-item"><a class="sidebar-link" href="./astmgm.php#tugas"><span><i class="ti ti-list-check"></i></span>Tugas Inspeksi</a></li>
                  <li class="sidebar-item"><a class="sidebar-link" href="./astmgm.php#master"><span><i class="ti ti-database"></i></span>Master Aset</a></li>
              </ul>
            </li>
            </ul>
        </nav>
      </div>
    </aside>

    <div class="body-wrapper">
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none"><a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)"><i class="ti ti-menu-2"></i></a></li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false"><img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle"></a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-user fs-6"></i><p class="mb-0 fs-3">My Profile</p></a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-mail fs-6"></i><p class="mb-0 fs-3">My Device</p></a>
                    <a href="./logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
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
            
            <h5 class="card-title fw-semibold mb-4">IT Asset Management</h5>

            <ul class="nav nav-tabs" id="assetTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab" aria-controls="tugas" aria-selected="true">
                  <i class="ti ti-list-check me-1"></i> Tugas Inspeksi (To-Do List)
                  <span class="badge bg-danger ms-1"><?php echo count($daftar_tugas); ?></span>
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="master-tab" data-bs-toggle="tab" data-bs-target="#master" type="button" role="tab" aria-controls="master" aria-selected="false">
                  <i class="ti ti-database me-1"></i> Master Aset
                  <span class="badge bg-secondary ms-1"><?php echo $total_master_aset; ?></span>
                </button>
              </li>
            </ul>

            <div class="tab-content" id="assetTabsContent">
              
              <div class="tab-pane fade show active" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                
                <div class="card shadow-none">
                  <div class="card-body p-3">
                    <form class="row g-3 align-items-center" method="get">
                      <div class="col-md-4">
                        <label for="filter_bulan" class="form-label">Bulan</label>
                        <select id="filter_bulan" name="filter_bulan" class="form-select">
                          <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php if ($m == $filter_bulan) echo 'selected'; ?>>
                              <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                            </option>
                          <?php endfor; ?>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label for="filter_tahun" class="form-label">Tahun</label>
                        <select id="filter_tahun" name="filter_tahun" class="form-select">
                          <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php if ($y == $filter_tahun) echo 'selected'; ?>>
                              <?php echo $y; ?>
                            </option>
                          <?php endfor; ?>
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
                        <th>Tanggal Dijadwalkan</th>
                        <th>Nama Karyawan</th>
                        <th>Divisi</th>
                        <th>Jenis Perangkat</th>
                        <th>Serial Number</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($daftar_tugas)): ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted py-4">
                            <i class="ti ti-circle-check fs-5 me-1"></i> Luar biasa! Tidak ada tugas inspeksi yang tertunda untuk periode ini.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($daftar_tugas as $tugas): ?>
                          <tr>
                            <td><span class="fw-semibold"><?php echo date('d M Y', strtotime($tugas['tanggal_dijadwalkan'])); ?></span></td>
                            <td><?php echo htmlspecialchars($tugas['nama_karyawan'] ?? '<em>(Karyawan tdk terdaftar)</em>'); ?></td>
                            <td><?php echo htmlspecialchars($tugas['divisi_karyawan'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($tugas['jenis_perangkat']); ?></td>
                            <td><?php echo htmlspecialchars($tugas['serial_number']); ?></td>
                            <td>
                              <?php
                                // Logika untuk menentukan URL form yang benar
                                $form_url = ''; $jenis = $tugas['jenis_perangkat'];
                                if ($jenis == 'Laptop') $form_url = 'ins_laptop.php';
                                elseif ($jenis == 'PC Desktop') $form_url = 'ins_desktop.php';
                                elseif ($jenis == 'Monitor') $form_url = 'ins_monitor.php';
                                elseif ($jenis == 'Printer') $form_url = 'ins_printer.php';
                                elseif ($jenis == 'CCTV') $form_url = 'ins_cctv.php';
                                elseif (in_array($jenis, ['Router', 'Switch', 'Access Point'])) $form_url = 'ins_infra.php';
                                elseif ($jenis == 'Telephone') $form_url = 'ins_tlp.php';
                                
                                if ($form_url):
                                  $link_inspeksi = "{$form_url}?jadwal_id={$tugas['jadwal_id']}&aset_id={$tugas['aset_id']}";
                              ?>
                                <a href="<?php echo $link_inspeksi; ?>" class="btn btn-primary btn-sm"><i class="ti ti-tool me-1"></i> Kerjakan</a>
                              <?php else: ?>
                                <span class="btn btn-secondary btn-sm disabled">Form Tdk Ada</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

              </div>
              
              <div class="tab-pane fade" id="master" role="tabpanel" aria-labelledby="master-tab">
                <h5 class="card-title fw-semibold card-title-task">Master Daftar Aset Perangkat</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Jenis</th>
                                <th>Merk/Model</th>
                                <th>Serial Number</th>
                                <th>Pengguna</th>
                                <th>Divisi</th>
                                <th>Tgl. Inspeksi Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($master_assets) > 0): ?>
                                <?php $no = 1; foreach ($master_assets as $asset): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($asset['jenis']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['merk']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['nama_pengguna']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['divisi']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['tanggal_inspeksi_terakhir'] ?? 'Belum pernah'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="alert('Fitur edit master aset belum diimplementasikan.')">Edit</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data aset yang ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
              </div>

            </div>
            
          </div>
        </div>    
      
       <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Fueling the Bright Future | <a href="https:mandiricoal.co.id" target="_blank" class="pe-1 text-primary text-decoration-underline">mandiricoal.co.id</a></p>
       </div>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Skrip untuk mengaktifkan submenu sidebar
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var parentItem = this.closest('.sidebar-item');
                if (parentItem) { parentItem.classList.toggle('active'); }
            });
        });

      var activeLink = document.querySelector('a[href="./astmgm.php#tugas"]');
      if (activeLink) {
          var parentSubmenu = activeLink.closest('.sidebar-submenu');
          if (parentSubmenu) {
              var parentItem = parentSubmenu.closest('.sidebar-item');
              if (parentItem) {
                  parentItem.classList.add('active');
              }
          }
      }
      
      var hash = window.location.hash || '#tugas'; // Default to #tugas
      if (hash) {
          var tabToActivate = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
          if (tabToActivate) {
              // Pastikan tab yang benar aktif berdasarkan URL hash
              var tab = new bootstrap.Tab(tabToActivate);
              tab.show();
          }
      }
    });
  </script>
</body>
</html>