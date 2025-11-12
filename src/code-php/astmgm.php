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

// Ambil NIK dan filter status
$current_nik = $_SESSION['nik'];
$task_status = $_GET['task_status'] ?? 'active'; // Default: 'active'

// =================================================================
// 1. LOGIKA QUERY UNTUK TUGAS
// =================================================================

$task_params = [':nik' => $current_nik];
$status_clause = "";

// 1. Filter Status Tugas
if ($task_status == 'active') {
    // Tampilkan task yang statusnya BUKAN Completed dan BUKAN Canceled
    $status_clause = " AND ji.status_jadwal IN ('Pending', 'In Progress')";
    $task_title = "Tugas Inspeksi Aktif";
} elseif ($task_status == 'completed') {
    // Tampilkan task yang statusnya Completed
    $status_clause = " AND ji.status_jadwal = 'Completed'";
    $task_title = "Riwayat Tugas Inspeksi Selesai";
} else {
    // Fallback/All tasks
    $task_title = "Semua Tugas Inspeksi";
}

// 2. Query untuk mengambil daftar tugas
$sql_tasks = "
    SELECT 
        ji.jadwal_id,
        ji.tanggal_jadwal,
        ji.jenis_perangkat,
        ji.status_jadwal,
        ma.merk,
        ma.serial_number,
        u.name AS nama_pengguna,
        ma.aset_id,
        ma.tanggal_inspeksi_terakhir,
        staf.name AS nama_staf_it
    FROM 
        jadwal_inspeksi ji
    JOIN 
        master_aset ma ON ji.aset_id = ma.aset_id
    JOIN 
        users u ON ma.id_user = u.user_id
    LEFT JOIN
        users staf ON ji.id_staf_it = staf.nik
    WHERE
        ji.jenis_perangkat = ma.jenis AND ma.jenis IS NOT NULL
        " . $status_clause . "
    ORDER BY 
        ji.tanggal_jadwal ASC";

$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->execute($task_params);
$tasks = $stmt_tasks->fetchAll();
// =================================================================


// =================================================================
// DATA UNTUK TAB 2: MASTER ASET (TETAP SAMA)
// =================================================================
$sql_master = "SELECT a.*, u.name AS nama_pengguna, u.department AS divisi, u.company AS perusahaan
               FROM master_aset a
               LEFT JOIN users u ON a.id_user = u.user_id
               WHERE a.jenis IS NOT NULL AND a.jenis != '' 
               ORDER BY a.aset_id DESC";

$stmt_master = $conn->query($sql_master);
$master_assets = $stmt_master->fetchAll();
// =================================================================
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
    /* ... (Gaya CSS sidebar Anda) ... */
    .sidebar-submenu { position: static !important; max-height: 0; overflow: hidden; transition: max-height 0.35s ease-in-out; list-style: none; padding-left: 25px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; margin: 0 10px 5px 10px; }
    .sidebar-item.active > .sidebar-submenu { max-height: 500px; }
    .sidebar-item > a .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: auto; }
    .sidebar-item.active > a .arrow { transform: rotate(180deg); }
    .status-badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; }
    .status-pending { background-color: #ffc107; color: #343a40; }
    .status-in-progress { background-color: #007bff; color: white; }
    .status-completed { background-color: #28a745; color: white; }
    .card-title-task { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <div class="body-wrapper">
      <header class="app-header">
        </header>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">IT Asset Management</h5>
            
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab" aria-controls="tugas" aria-selected="true">Tugas Inspeksi</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="master-tab" data-bs-toggle="tab" data-bs-target="#master" type="button" role="tab" aria-controls="master" aria-selected="false">Master Aset</button>
              </li>
            </ul>
            
            <div class="tab-content pt-3" id="myTabContent">
                
              <div class="tab-pane fade show active" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                <h5 class="card-title fw-semibold card-title-task"><?php echo $task_title; ?></h5>
                
                <div class="mb-4">
                    <a href="astmgm.php?status=active#tugas" class="btn btn-<?php echo ($task_status == 'active' ? 'primary' : 'outline-secondary'); ?> me-2">
                        Tugas Aktif
                    </a>
                    <a href="astmgm.php?status=completed#tugas" class="btn btn-<?php echo ($task_status == 'completed' ? 'primary' : 'outline-secondary'); ?>">
                        Riwayat Selesai
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
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
                            <?php if (count($tasks) > 0): ?>
                                <?php $no = 1; foreach ($tasks as $task): 
                                    $status_class = strtolower(str_replace(' ', '-', $task['status_jadwal']));
                                    $url_inspeksi = "ins_" . strtolower($task['jenis_perangkat']) . ".php?jadwal_id=" . $task['jadwal_id'] . "&aset_id=" . $task['aset_id'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($task['tanggal_jadwal']); ?></td>
                                    <td><?php echo htmlspecialchars($task['jenis_perangkat'] . ' / ' . $task['merk']); ?></td>
                                    <td><?php echo htmlspecialchars($task['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($task['nama_pengguna']); ?></td>
                                    <td><?php echo htmlspecialchars($task['nama_staf_it'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($task['status_jadwal']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($task_status == 'active'): ?>
                                            <a href="<?php echo $url_inspeksi; ?>" class="btn btn-sm btn-success">
                                                Lakukan Inspeksi
                                            </a>
                                        <?php elseif ($task_status == 'completed'): ?>
                                            <a href="viewinspeksi.php?no=<?php echo $task['id_hasil_inspeksi']; ?>" class="btn btn-sm btn-info text-white">
                                                Lihat Hasil
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada tugas inspeksi yang ditemukan.</td>
                                </tr>
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
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Skrip untuk sidebar
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
      submenuToggles.forEach(function(toggle) {
          toggle.addEventListener('click', function(e) {
              e.preventDefault();
              var parentItem = this.closest('.sidebar-item');
              if (parentItem) { parentItem.classList.toggle('active'); }
          });
      });

      // Secara otomatis membuka submenu "IT Asset Management" dan mengaktifkan sidebar link
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

      // Mengaktifkan tab saat memuat halaman dengan hash di URL
      var hash = window.location.hash || '#tugas'; // Default to #tugas
      if (hash) {
          var tabToActivate = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
          if (tabToActivate) {
              var tab = new bootstrap.Tab(tabToActivate);
              tab.show();
          }
      }
    });
  </script>
</body>
</html>