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
     // Gunakan die() untuk menampilkan pesan error database jika terjadi 500
     die("Koneksi ke database gagal: " . $e->getMessage());
}

// =================================================================
// 1. LOGIKA FILTER STATUS TUGAS (TUGAS AKTIF vs RIWAYAT SELESAI)
// =================================================================

$current_nik = $_SESSION['nik'];
// Ambil status dari URL. Default: 'active'
$task_status = $_GET['status'] ?? 'active'; 

$status_clause = "";
$task_params = [];

if ($task_status == 'active') {
    // Tugas Aktif: Pending atau In Progress (Tidak ada Completed)
    $status_clause = " AND ji.status_jadwal IN ('Pending', 'In Progress')";
    $task_title = "Tugas Inspeksi Aktif";
} elseif ($task_status == 'completed') {
    // Riwayat Selesai: Hanya Completed dan di-filter oleh NIK staf IT yang login
    $status_clause = " AND ji.status_jadwal = 'Completed' AND ji.id_staf_it = :nik";
    $task_title = "Riwayat Tugas Inspeksi Selesai";
    $task_params[':nik'] = $current_nik;
} else {
    // Fallback/Default
    $task_title = "Semua Tugas Inspeksi";
}


// 2. QUERY UNTUK MENGAMBIL DAFTAR TUGAS
$sql_tasks = "
    SELECT 
        ji.jadwal_id,
        ji.tanggal_jadwal,        -- Kolom yang digunakan untuk tanggal jadwal
        ji.tanggal_selesai,
        ji.jenis_perangkat,
        ji.status_jadwal,
        ji.id_hasil_inspeksi,     -- PENTING: ID Hasil Inspeksi untuk link Lihat Hasil
        ma.merk,
        ma.serial_number,
        u.name AS nama_pengguna,
        ma.aset_id,
        staf.name AS nama_staf_it
    FROM 
        jadwal_inspeksi ji
    JOIN 
        master_aset ma ON ji.aset_id = ma.aset_id
    LEFT JOIN 
        users u ON ma.id_user = u.user_id
    LEFT JOIN
        users staf ON ji.id_staf_it = staf.nik
    WHERE
        1=1  -- Kondisi dasar
        " . $status_clause . "
    ORDER BY 
        ji.tanggal_jadwal DESC";

$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->execute($task_params);
$tasks = $stmt_tasks->fetchAll();


// =================================================================
// DATA UNTUK TAB 2: MASTER ASET (DENGAN PAGINATION) - Logika Asli
// =================================================================

$master_limit = 10; 
$master_page = isset($_GET['master_page']) && is_numeric($_GET['master_page']) ? (int)$_GET['master_page'] : 1;
$master_offset = ($master_page - 1) * $master_limit;

// Hitung total aset
$total_aset_stmt = $conn->query("SELECT COUNT(*) FROM master_aset WHERE jenis IS NOT NULL AND jenis != ''");
$total_master_aset = $total_aset_stmt->fetchColumn();
$total_master_pages = ceil($total_master_aset / $master_limit);

// Ambil data aset untuk halaman ini
$sql_aset = "SELECT 
            a.aset_id,
            a.serial_number,
            a.jenis AS jenis_perangkat, -- Menggunakan kolom 'jenis' dari master_aset
            a.lokasi,
            a.tanggal_inspeksi_terakhir,
            u.name as nama_karyawan
         FROM master_aset a
         LEFT JOIN users u ON a.id_user = u.user_id
         WHERE a.jenis IS NOT NULL AND a.jenis != ''
         ORDER BY a.aset_id DESC
         LIMIT :limit OFFSET :offset";

$stmt_aset = $conn->prepare($sql_aset);
$stmt_aset->bindParam(':limit', $master_limit, PDO::PARAM_INT);
$stmt_aset->bindParam(':offset', $master_offset, PDO::PARAM_INT);
$stmt_aset->execute();
$daftar_aset = $stmt_aset->fetchAll();

// Array nama bulan untuk filter filter
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

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
    .nav-tabs .nav-link { font-weight: 600; }
    .nav-tabs .nav-link.active { color: #5D87FF; border-bottom-width: 3px; }
    .tab-content { border: 1px solid #dee2e6; border-top: 0; padding: 1.5rem; border-radius: 0 0 0.25rem 0.25rem; }
    .status-badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; }
    .status-Pending { background-color: #ffc107; color: #343a40; }
    .status-In-Progress { background-color: #007bff; color: white; }
    .status-Completed { background-color: #28a745; color: white; }
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
            
            <li class="sidebar-item active"> <a class="sidebar-link" href="#" aria-expanded="false"><span><i class="ti ti-cards"></i></span><span class="hide-menu">IT Asset Management</span><span class="arrow"><i class="fas fa-chevron-down"></i></span></a>
              <ul class="sidebar-submenu">
                  <li class="sidebar-item active"><a class="sidebar-link" href="./astmgm.php#tugas"><span><i class="ti ti-list-check"></i></span>Tugas Inspeksi</a></li>
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
                  <i class="ti ti-list-check me-1"></i> Tugas Inspeksi
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="master-tab" data-bs-toggle="tab" data-bs-target="#master" type="button" role="tab" aria-controls="master" aria-selected="false">
                  <i class="ti ti-database me-1"></i> Master Aset
                </button>
              </li>
            </ul>

            <div class="tab-content" id="assetTabsContent">
              
              <div class="tab-pane fade show active" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                
                <h5 class="card-title fw-semibold card-title-task mt-3"><?php echo $task_title; ?></h5>
                
                <div class="mb-4">
                    <a href="astmgm.php?status=active#tugas" class="btn btn-<?php echo ($task_status == 'active' ? 'primary' : 'outline-secondary'); ?> me-2">
                        Tugas Aktif (<?php echo $task_status == 'active' ? count($tasks) : $conn->query("SELECT COUNT(*) FROM jadwal_inspeksi WHERE status_jadwal IN ('Pending', 'In Progress')")->fetchColumn(); ?>)
                    </a>
                    <a href="astmgm.php?status=completed#tugas" class="btn btn-<?php echo ($task_status == 'completed' ? 'primary' : 'outline-secondary'); ?>">
                        Riwayat Selesai (<?php echo $task_status == 'completed' ? count($tasks) : $conn->query("SELECT COUNT(*) FROM jadwal_inspeksi WHERE status_jadwal = 'Completed' AND id_staf_it = '$current_nik'")->fetchColumn(); ?>)
                    </a>
                </div>
                
                <div class="table-responsive">
                  <table class="table table-hover table-striped">
                    <thead class="table-light">
                      <tr>
                        <th>Tgl. Jadwal</th>
                        <th>Perangkat</th>
                        <th>Serial Number</th>
                        <th>Pengguna</th>
                        <th>Pelaksana IT</th>
                        <th>Status</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($tasks)): ?>
                        <tr>
                          <td colspan="7" class="text-center text-muted py-4">
                            <i class="ti ti-circle-check fs-5 me-1"></i> Luar biasa! Tidak ada tugas inspeksi yang ditemukan.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($tasks as $task): 
                            $status_class = strtolower(str_replace(' ', '-', $task['status_jadwal']));
                            // Menentukan link Aksi
                            if ($task['status_jadwal'] == 'Completed') {
                                $aksi_link = "viewinspeksi.php?no=" . $task['id_hasil_inspeksi'];
                                $aksi_button_class = "btn-info text-white";
                                $aksi_button_text = "Lihat Hasil";
                            } else {
                                // Logika untuk link 'Kerjakan'
                                $jenis_perangkat_clean = strtolower(str_replace(' ', '_', $task['jenis_perangkat']));
                                $form_url = 'ins_' . $jenis_perangkat_clean . '.php';
                                // Tambahkan pengecekan untuk jenis perangkat khusus (Router/Switch/AP -> ins_infra.php)
                                if (in_array($task['jenis_perangkat'], ['Router', 'Switch', 'Access Point'])) {
                                    $form_url = 'ins_infra.php';
                                } elseif ($task['jenis_perangkat'] == 'PC Desktop') {
                                    $form_url = 'ins_desktop.php'; // Pastikan nama file submit konsisten
                                }
                                $aksi_link = "{$form_url}?jadwal_id={$task['jadwal_id']}&aset_id={$task['aset_id']}";
                                $aksi_button_class = "btn-primary";
                                $aksi_button_text = "Lakukan Inspeksi";
                            }
                        ?>
                          <tr>
                            <td><span class="fw-semibold"><?php echo date('d M Y', strtotime($task['tanggal_jadwal'])); ?></span></td>
                            <td><?php echo htmlspecialchars($task['jenis_perangkat'] . ' / ' . $task['merk']); ?></td>
                            <td><?php echo htmlspecialchars($task['serial_number']); ?></td>
                            <td><?php echo htmlspecialchars($task['nama_pengguna'] ?? '<em>(N/A)</em>'); ?></td>
                            <td><?php echo htmlspecialchars($task['nama_staf_it'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($task['status_jadwal']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo $aksi_link; ?>" class="btn btn-sm <?php echo $aksi_button_class; ?>">
                                    <i class="ti ti-tool me-1"></i> <?php echo $aksi_button_text; ?>
                                </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

              </div>
              
              <div class="tab-pane fade" id="master" role="tabpanel" aria-labelledby="master-tab">
                
                <h5 class="card-title fw-semibold card-title-task mt-3">Master Daftar Aset Perangkat</h5>
                
                <p class="mb-3">Daftar semua aset yang terdaftar dalam sistem.</p>
                
                <div class="table-responsive">
                  <table class="table table-hover table-striped">
                    <thead class="table-light">
                      <tr>
                        <th>ID Aset</th>
                        <th>Serial Number</th>
                        <th>Jenis Perangkat</th>
                        <th>Pemegang Aset</th>
                        <th>Lokasi</th>
                        <th>Inspeksi Terakhir</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($daftar_aset)): ?>
                        <tr>
                          <td colspan="7" class="text-center text-muted py-4">
                            Belum ada data di master aset.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($daftar_aset as $aset): ?>
                          <tr>
                            <td><?php echo $aset['aset_id']; ?></td>
                            <td><span class="fw-semibold"><?php echo htmlspecialchars($aset['serial_number']); ?></span></td>
                            <td><?php echo htmlspecialchars($aset['jenis_perangkat']); ?></td>
                            <td><?php echo htmlspecialchars($aset['nama_karyawan'] ?? '<em>(Belum di-assign)</em>'); ?></td>
                            <td><?php echo htmlspecialchars($aset['lokasi']); ?></td>
                            <td><?php echo $aset['tanggal_inspeksi_terakhir'] ? date('d M Y', strtotime($aset['tanggal_inspeksi_terakhir'])) : 'Belum Pernah'; ?></td>
                            <td>
                              <a href="#" class="btn btn-outline-primary btn-sm">Edit</a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                
                <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-end">
                  <ul class="pagination">
                    <?php if ($master_page > 1): ?>
                      <li class="page-item"><a class="page-link" href="?master_page=<?php echo $master_page - 1; ?>#master">Previous</a></li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_master_pages; $i++): ?>
                      <li class="page-item <?php if ($i == $master_page) echo 'active'; ?>">
                        <a class="page-link" href="?master_page=<?php echo $i; ?>#master"><?php echo $i; ?></a>
                      </li>
                    <?php endfor; ?>
                    
                    <?php if ($master_page < $total_master_pages): ?>
                      <li class="page-item"><a class="page-link" href="?master_page=<?php echo $master_page + 1; ?>#master">Next</a></li>
                    <?php endif; ?>
                  </ul>
                </nav>

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
      
      // ===================================================
      // BARU: Skrip untuk mengaktifkan tab via URL hash
      // ===================================================
      var hash = window.location.hash || '#tugas'; // Default ke #tugas
      var tabButton = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
      
      if (tabButton) {
          // Hapus kelas 'active' dan 'show' dari tab/pane default (jika ada)
          document.querySelectorAll('.nav-tabs .nav-link.active').forEach(e => e.classList.remove('active'));
          document.querySelectorAll('.tab-content .tab-pane.active').forEach(e => e.classList.remove('active', 'show'));
          
          // Aktifkan tab yang dituju
          tabButton.classList.add('active');
          var paneToActivate = document.querySelector(hash);
          if (paneToActivate) {
              paneToActivate.classList.add('active', 'show');
          }
      }

      // Logika agar URL filter status tetap berada di tab Tugas Inspeksi
      var activeTaskLink = document.querySelector('a[href="astmgm.php?status=active#tugas"]');
      if (activeTaskLink) {
          // Ambil status saat ini dan hash yang benar
          const urlParams = new URLSearchParams(window.location.search);
          const currentStatus = urlParams.get('status') || 'active';
          
          // Perbarui semua link filter status agar mengarah ke hash #tugas
          document.querySelectorAll('.mb-4 a[href^="astmgm.php?status="]').forEach(link => {
              const originalHref = link.getAttribute('href');
              link.setAttribute('href', originalHref.split('#')[0] + '#tugas');
          });

          // Tampilkan jumlah task di badge
          document.querySelectorAll('.nav-tabs button').forEach(button => {
              if (button.id === 'tugas-tab') {
                  const activeCount = document.querySelector('a[href="astmgm.php?status=active#tugas"]').textContent.match(/\(([^)]+)\)/)?.[1] || 0;
                  const completedCount = document.querySelector('a[href="astmgm.php?status=completed#tugas"]').textContent.match(/\(([^)]+)\)/)?.[1] || 0;
                  
                  // Ganti badge default di tab Tugas Inspeksi dengan total tugas aktif
                  button.innerHTML = `<i class="ti ti-list-check me-1"></i> Tugas Inspeksi <span class="badge bg-danger ms-1">${activeCount}</span>`;
              }
          });
      }
      
      // Skrip untuk mengaktifkan submenu sidebar (tetap)
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var parentItem = this.closest('.sidebar-item');
                if (parentItem) { parentItem.classList.toggle('active'); }
            });
        });

      var activeSidebarLink = document.querySelector('a[href="./astmgm.php#tugas"]');
      if (activeSidebarLink) {
          var parentSubmenu = activeSidebarLink.closest('.sidebar-submenu');
          if (parentSubmenu) {
              var parentItem = parentSubmenu.closest('.sidebar-item');
              if (parentItem) {
                  parentItem.classList.add('active');
              }
          }
      }

    });
  </script>
</body>
</html>