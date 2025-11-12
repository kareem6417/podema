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

// =================================================================
// BARU: QUERY DATA UNTUK DASHBOARD VISUAL
// =================================================================

// 1. Data untuk Inspeksi per Jenis Perangkat (Pie Chart)
$stmt_jenis = $conn->query("SELECT jenis, COUNT(*) as jumlah 
                           FROM form_inspeksi 
                           WHERE jenis IS NOT NULL AND jenis != '' 
                           GROUP BY jenis");
$data_jenis = $stmt_jenis->fetchAll(PDO::FETCH_ASSOC);

// 2. Data untuk Inspeksi per Tahun (Bar Chart)
$stmt_tahunan = $conn->query("SELECT YEAR(date) as tahun, COUNT(*) as jumlah 
                             FROM form_inspeksi 
                             GROUP BY tahun 
                             ORDER BY tahun ASC");
$data_tahunan = $stmt_tahunan->fetchAll(PDO::FETCH_ASSOC);

// 3. Data untuk Tren Bulanan (Line Chart) - Hanya tahun ini
$stmt_bulanan = $conn->query("SELECT MONTHNAME(date) as bulan, COUNT(*) as jumlah 
                             FROM form_inspeksi 
                             WHERE YEAR(date) = YEAR(CURDATE()) 
                             GROUP BY MONTH(date), bulan 
                             ORDER BY MONTH(date) ASC");
$data_bulanan = $stmt_bulanan->fetchAll(PDO::FETCH_ASSOC);


// =================================================================
// LAMA: Logika untuk filter dan pagination tabel
// =================================================================
$all_jenis = $conn->query("SELECT DISTINCT jenis FROM form_inspeksi WHERE jenis IS NOT NULL AND jenis != '' ORDER BY jenis ASC")->fetchAll(PDO::FETCH_COLUMN);
$filter_jenis = isset($_GET['filter_jenis']) ? $_GET['filter_jenis'] : '';
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$params = [];
$where_clauses = "WHERE 1=1";
if (!empty($filter_jenis)) {
    $where_clauses .= " AND jenis = :jenis";
    $params[':jenis'] = $filter_jenis;
}

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM form_inspeksi " . $where_clauses);
$count_stmt->execute($params);
$totalRows = $count_stmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);
$currentPage = max(1, $currentPage); 
$currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1); 
$offset = ($currentPage - 1) * $limit;

$main_sql = "SELECT * FROM form_inspeksi " . $where_clauses . " ORDER BY date DESC, no DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($main_sql);
foreach ($params as $key => &$val) { $stmt->bindParam($key, $val); }
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Device Inspection</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* CSS untuk sidebar (Sudah ada dari perbaikan sebelumnya) */
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
    .table th, .table td { vertical-align: middle; }
    .action-icons a, .action-icons span { font-size: 1.2rem; margin: 0 5px; cursor: pointer; }
    .modal-body table td:first-child { font-weight: bold; width: 35%; }
    
    /* BARU: CSS untuk area chart */
    .chart-container {
        width: 100%;
        height: 350px;
        margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <aside class="left-sidebar">
        </aside>

    <div class="body-wrapper">
      <header class="app-header">
        </header>
      
      <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-3">
                    <h5 class="card-title fw-semibold">Visualisasi Data Inspeksi</h5>
                    <a href="download_report.php" class="btn btn-primary btn-sm">
                        <i class="ti ti-download me-1"></i> Download Laporan (CSV)
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <h6 class="text-center">Inspeksi per Jenis Perangkat</h6>
                        <div class="chart-container">
                            <canvas id="chartJenisPerangkat"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="text-center">Total Inspeksi per Tahun</h6>
                        <div class="chart-container">
                            <canvas id="chartInspeksiTahunan"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="text-center">Tren Inspeksi Bulanan (Tahun Ini)</h6>
                        <div class="chart-container">
                            <canvas id="chartTrenBulanan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Data Detail Inspeksi</h5>
            <div class="card shadow-none">
                <div class="card-body p-3">
                    <form class="row g-3 align-items-center" method="get">
                        <div class="col-md-4"><label for="filter_jenis" class="form-label">Filter by Device Type</label><select id="filter_jenis" name="filter_jenis" class="form-select"><option value="">All Types</option><?php foreach ($all_jenis as $jenis): ?><option value="<?php echo htmlspecialchars($jenis); ?>" <?php if ($filter_jenis == $jenis) echo 'selected'; ?>><?php echo htmlspecialchars($jenis); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label for="limit" class="form-label">Rows per page</label><select id="limit" name="limit" class="form-select"><option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option><option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option><option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option></select></div>
                        <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr><th>No</th><th>Date</th><th>Name</th><th>Device Type</th><th>Issue</th><th>Findings</th><th>Recommendation</th><th>Score</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No inspection data found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $index => $row): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['date']))); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_user']); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($row['informasi_keluhan'], 0, 100))) . (strlen($row['informasi_keluhan']) > 100 ? '...' : ''); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($row['hasil_pemeriksaan'], 0, 100))) . (strlen($row['hasil_pemeriksaan']) > 100 ? '...' : ''); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($row['rekomendasi'], 0, 100))) . (strlen($row['rekomendasi']) > 100 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($row['score']); ?></td>
                            <td class="action-icons">
                                <span data-bs-toggle="modal" data-bs-target="#detailModal" data-no="<?php echo htmlspecialchars($row['no']); ?>" data-date="<?php echo htmlspecialchars($row['date']); ?>" data-nama_user="<?php echo htmlspecialchars($row['nama_user']); ?>" data-jenis="<?php echo htmlspecialchars($row['jenis']); ?>" data-merk="<?php echo htmlspecialchars($row['merk']); ?>" data-serialnumber="<?php echo htmlspecialchars($row['serialnumber']); ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>" data-lokasi="<?php echo htmlspecialchars($row['lokasi']); ?>" data-keluhan="<?php echo htmlspecialchars($row['informasi_keluhan']); ?>" data-pemeriksaan="<?php echo htmlspecialchars($row['hasil_pemeriksaan']); ?>" data-rekomendasi="<?php echo htmlspecialchars($row['rekomendasi']); ?>"><i class="ti ti-eye text-primary" title="View Details"></i></span>
                                <?php
                                  $jenis_perangkat = $row['jenis']; $download_link = '#';
                                  if ($jenis_perangkat == 'Laptop') { $download_link = './download_ins_laptop.php?no=' . $row['no']; } 
                                  elseif ($jenis_perangkat == 'PC Desktop') { $download_link = './download_ins_pc.php?no=' . $row['no']; }
                                  elseif ($jenis_perangkat == 'Monitor') { $download_link = './download_ins_monitor.php?no=' . $row['no']; }
                                  elseif ($jenis_perangkat == 'Printer') { $download_link = './download_ins_printer.php?no=' . $row['no']; }
                                  elseif ($jenis_perangkat == 'CCTV') { $download_link = './download_ins_cctv.php?no=' . $row['no']; }
                                  elseif (in_array($jenis_perangkat, ['Router', 'Switch', 'Access Point'])) { $download_link = './download_ins_infra.php?no=' . $row['no']; }
                                  elseif ($jenis_perangkat == 'Telephone') { $download_link = './download_ins_telp.php?no=' . $row['no']; }
                                ?>
                                <a href="<?php echo $download_link; ?>" target="_blank"><i class="ti ti-download text-secondary" title="Download PDF"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
              </table>
            </div>
            <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-end">
                <ul class="pagination">
                    <?php if ($currentPage > 1): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&limit=<?php echo $limit; ?>&filter_jenis=<?php echo urlencode($filter_jenis); ?>">Previous</a></li><?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?><li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&filter_jenis=<?php echo urlencode($filter_jenis); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&limit=<?php echo $limit; ?>&filter_jenis=<?php echo urlencode($filter_jenis); ?>">Next</a></li><?php endif; ?>
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
    </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Script untuk sidebar (sudah ada)
      var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var parentItem = this.closest('.sidebar-item');
                if (parentItem) { parentItem.classList.toggle('active'); }
            });
        });

      // Script untuk modal (sudah ada)
      var detailModal = document.getElementById('detailModal');
      detailModal.addEventListener('show.bs.modal', function (event) {
          var triggerElement = event.relatedTarget;
          var modalBody = detailModal.querySelector('.modal-body');
          modalBody.querySelector('#modal-no').textContent = triggerElement.getAttribute('data-no');
          modalBody.querySelector('#modal-date').textContent = triggerElement.getAttribute('data-date');
          modalBody.querySelector('#modal-nama_user').textContent = triggerElement.getAttribute('data-nama_user');
          modalBody.querySelector('#modal-jenis').textContent = triggerElement.getAttribute('data-jenis');
          modalBody.querySelector('#modal-merk').textContent = triggerElement.getAttribute('data-merk');
          modalBody.querySelector('#modal-serialnumber').textContent = triggerElement.getAttribute('data-serialnumber');
          modalBody.querySelector('#modal-status').textContent = triggerElement.getAttribute('data-status');
          modalBody.querySelector('#modal-lokasi').textContent = triggerElement.getAttribute('data-lokasi');
          modalBody.querySelector('#modal-keluhan').textContent = triggerElement.getAttribute('data-keluhan');
          modalBody.querySelector('#modal-pemeriksaan').textContent = triggerElement.getAttribute('data-pemeriksaan');
          modalBody.querySelector('#modal-rekomendasi').textContent = triggerElement.getAttribute('data-rekomendasi');
      });

      // =================================================================
      // BARU: JAVASCRIPT UNTUK MENGGAMBAR CHART
      // =================================================================

      // Mengambil data dari PHP dan mengubahnya jadi JSON
      const dataJenis = <?php echo json_encode($data_jenis); ?>;
      const dataTahunan = <?php echo json_encode($data_tahunan); ?>;
      const dataBulanan = <?php echo json_encode($data_bulanan); ?>;

      // Chart 1: Jenis Perangkat (Pie Chart)
      const ctxJenis = document.getElementById('chartJenisPerangkat').getContext('2d');
      new Chart(ctxJenis, {
          type: 'pie',
          data: {
              labels: dataJenis.map(d => d.jenis),
              datasets: [{
                  label: 'Jumlah',
                  data: dataJenis.map(d => d.jumlah),
                  backgroundColor: ['#5D87FF', '#49BEFF', '#FFAE1F', '#FA896B', '#13DEB9', '#A092F1', '#FFC107'],
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { position: 'bottom' } }
          }
      });

      // Chart 2: Inspeksi Tahunan (Bar Chart)
      const ctxTahun = document.getElementById('chartInspeksiTahunan').getContext('2d');
      new Chart(ctxTahun, {
          type: 'bar',
          data: {
              labels: dataTahunan.map(d => d.tahun),
              datasets: [{
                  label: 'Total Inspeksi',
                  data: dataTahunan.map(d => d.jumlah),
                  backgroundColor: '#49BEFF',
                  borderColor: '#49BEFF',
                  borderWidth: 1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true } }
          }
      });

      // Chart 3: Tren Bulanan (Line Chart)
      const ctxBulan = document.getElementById('chartTrenBulanan').getContext('2d');
      new Chart(ctxBulan, {
          type: 'line',
          data: {
              labels: dataBulanan.map(d => d.bulan),
              datasets: [{
                  label: 'Inspeksi Bulan Ini',
                  data: dataBulanan.map(d => d.jumlah),
                  fill: false,
                  borderColor: '#5D87FF',
                  tension: 0.1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true } }
          }
      });

    });
  </script>
</body>
</html>