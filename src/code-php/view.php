<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assessment Result - Portal Device Management</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    /* Mengurangi jarak horizontal area konten utama */
    .body-wrapper {
      padding-left: 24px;
      padding-right: 24px;
    }

    /* Perbaikan gaya submenu sidebar */
    .sidebar-submenu {
        position: static !important; /* Memaksa submenu kembali ke alur dokumen normal */
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease-in-out; /* Animasi buka-tutup yang mulus */
        list-style: none; /* Menghilangkan bullet points */
        padding-left: 25px; /* Memberi indentasi pada submenu */
        background-color: #f8f9fa;
        border-radius: 0 0 5px 5px;
        margin: 0 10px 5px 10px;
    }

    .sidebar-item.active > .sidebar-submenu {
        max-height: 500px; /* Ketinggian maksimal yang cukup untuk semua item */
    }

    /* CSS untuk animasi rotasi panah dropdown */
    .sidebar-item > a .arrow {
        transition: transform 0.3s ease;
        display: inline-block;
        margin-left: auto;
    }
    .sidebar-item.active > a .arrow {
        transform: rotate(180deg);
    }
    
    /* Gaya untuk kartu skor dan detail */
    .score-card { text-align: center; padding: 2rem; border-radius: 12px; color: #fff; margin-bottom: 2rem; }
    .score-card .score-value { font-size: 4rem; font-weight: 700; line-height: 1; }
    .score-card .score-label { font-size: 1.25rem; font-weight: 500; letter-spacing: 1px; }
    .score-high { background: linear-gradient(135deg, #e53935, #b71c1c); }
    .score-medium { background: linear-gradient(135deg, #fdd835, #f9a825); }
    .score-low { background: linear-gradient(135deg, #43a047, #1b5e20); }
    .detail-item { display: flex; align-items: center; margin-bottom: 1.5rem; font-size: 1rem; }
    .detail-item i { font-size: 1.5rem; margin-right: 1rem; color: #5D87FF; }
    .detail-label { display: block; font-weight: 600; color: #2A3547; }
    .detail-value { color: #5A6A85; }
    .btn-download { font-size: 1rem; padding: 0.75rem 1.5rem; }
    .btn-back { font-size: 1rem; padding: 0.75rem 1.5rem; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <aside class="left-sidebar">
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
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./admin.php" aria-expanded="false">
                <span><i class="ti ti-layout-dashboard"></i></span>
                <span class="hide-menu">Administrator</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Dashboard</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_lap.php" aria-expanded="false">
                <span><i class="ti ti-chart-area-line"></i></span>
                <span class="hide-menu">Assessment Laptop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_pc.php" aria-expanded="false">
                <span><i class="ti ti-chart-line"></i></span>
                <span class="hide-menu">Assessment PC Desktop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./dash_ins.php" aria-expanded="false">
                <span><i class="ti ti-chart-donut"></i></span>
                <span class="hide-menu">Inspection</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Evaluation Portal</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./assess_laptop.php" aria-expanded="false">
                <span><i class="ti ti-device-laptop"></i></span>
                <span class="hide-menu">Assessment Laptop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./assess_pc.php" aria-expanded="false">
                <span><i class="ti ti-device-desktop-analytics"></i></span>
                <span class="hide-menu">Assessment PC Desktop</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="#" aria-expanded="false">
                  <span><i class="ti ti-assembly"></i></span>
                  <span class="hide-menu">Device Inspection</span>
                  <span class="arrow"><i class="fas fa-chevron-down"></i></span>
              </a>
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
            <li class="sidebar-item">
              <a class="sidebar-link" href="./about.php" aria-expanded="false">
                <span><i class="ti ti-exclamation-circle"></i></span>
                <span class="hide-menu">About</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Asset Management</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./astmgm.php" aria-expanded="false">
                <span><i class="ti ti-cards"></i></span>
                <span class="hide-menu">IT Asset Management</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    
    <div class="body-wrapper">
    
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
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-user fs-6"></i><p class="mb-0 fs-3">My Profile</p></a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-mail fs-6"></i><p class="mb-0 fs-3">My Account</p></a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item"><i class="ti ti-list-check fs-6"></i><p class="mb-0 fs-3">My Task</p></a>
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
                  <?php
                      $host = "mandiricoal.net"; $user = "podema"; $pass = "Jam10pagi#"; $db = "podema";
                      $conn = new mysqli($host, $user, $pass, $db);
                      if ($conn->connect_error) { die("Koneksi database gagal: " . $conn->connect_error); }
                      $result = $conn->query("SELECT * FROM assess_laptop ORDER BY id DESC LIMIT 1");
                      if ($result && $result->num_rows > 0) {
                          $query = $result->fetch_assoc();
                          $score = $query['score'];

                          if ($score > 99) {
                              $risk_level = "High Risk"; $risk_class = "high"; $alert_class = "danger";
                              $recommendation = '<svg xmlns="http://www.w3.org/2000/svg" class="d-inline-block align-text-top me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg><strong>Rekomendasi: Ganti Perangkat.</strong> Perangkat Anda menunjukkan banyak masalah dan sudah seharusnya dilakukan penggantian untuk menunjang produktivitas.';
                          } elseif ($score >= 50 && $score <= 99) {
                              $risk_level = "Medium Risk"; $risk_class = "medium"; $alert_class = "warning";
                              $recommendation = '<svg xmlns="http://www.w3.org/2000/svg" class="d-inline-block align-text-top me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16h.01" /></svg><strong>Rekomendasi: Perlu Perhatian.</strong> Perangkat Anda masih layak pakai, namun tim IT akan melakukan beberapa peningkatan (upgrade) jika dibutuhkan.';
                          } else {
                              $risk_level = "Low Risk"; $risk_class = "low"; $alert_class = "success";
                              $recommendation = '<svg xmlns="http://www.w3.org/2000/svg" class="d-inline-block align-text-top me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg><strong>Kondisi Baik.</strong> Perangkat Anda dalam kondisi prima dan tidak memerlukan tindakan lebih lanjut saat ini.';
                          }
                  ?>
                  
                  <h3 class="card-title fw-semibold mb-4 text-center">Laptop Replacement Assessment Result</h3>
                  
                  <div class="score-card score-<?php echo $risk_class; ?>">
                      <div class="score-label"><?php echo $risk_level; ?></div>
                      <div class="score-value"><?php echo $score; ?></div>
                      <div class="score-label">Total Score</div>
                  </div>

                  <div class="alert alert-<?php echo $alert_class; ?> d-flex" role="alert">
                      <?php echo $recommendation; ?>
                  </div>

                  <hr class="my-4">

                  <h5 class="fw-semibold mb-4">Assessment Details</h5>
                  <div class="row">
                      <div class="col-md-6">
                          <div class="detail-item"><i class="ti ti-user"></i><div><span class="detail-label">Name</span><span class="detail-value"><?php echo htmlspecialchars($query['name']); ?></span></div></div>
                          <div class="detail-item"><i class="ti ti-building"></i><div><span class="detail-label">Company</span><span class="detail-value"><?php echo htmlspecialchars($query['company']); ?></span></div></div>
                          <div class="detail-item"><i class="ti ti-briefcase"></i><div><span class="detail-label">Division</span><span class="detail-value"><?php echo htmlspecialchars($query['divisi']); ?></span></div></div>
                      </div>
                      <div class="col-md-6">
                          <div class="detail-item"><i class="ti ti-calendar-event"></i><div><span class="detail-label">Assessment Date</span><span class="detail-value"><?php echo htmlspecialchars($query['date']); ?></span></div></div>
                          <div class="detail-item"><i class="ti ti-device-laptop"></i><div><span class="detail-label">Merk / Type</span><span class="detail-value"><?php echo htmlspecialchars($query['type']); ?></span></div></div>
                          <div class="detail-item"><i class="ti ti-id"></i><div><span class="detail-label">Serial Number</span><span class="detail-value"><?php echo htmlspecialchars($query['serialnumber']); ?></span></div></div>
                      </div>
                  </div>

                  <hr class="my-4">

                  <div class="d-flex justify-content-center gap-2">
                      <a href="assess_laptop.php" class="btn btn-outline-secondary btn-back"><i class="ti ti-arrow-left me-1"></i> Back to Form</a>
                      <a href="download.php" class="btn btn-primary btn-download"><i class="ti ti-download me-1"></i> Download Full Report (PDF)</a>
                  </div>
                  
                  <?php
                      } else {
                          echo '<div class="alert alert-danger">Data assessment tidak ditemukan.</div>';
                      }
                      $conn->close();
                  ?>
              </div>
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
        // Cari semua link di sidebar yang punya submenu (link dengan href="#")
        var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');

        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah link pindah halaman
                
                // Cari elemen <li> induk dari link yang di-klik
                var parentItem = this.closest('.sidebar-item');

                // Toggle class 'active' pada elemen <li> induk
                if (parentItem) {
                    parentItem.classList.toggle('active');
                }
            });
        });
    });
  </script>
</body>
</html>