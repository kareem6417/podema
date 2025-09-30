<?php
session_start();

// 1. PENGAMBILAN DATA TERPUSAT
// =================================================================

// Periksa apakah user_id ada di URL, jika tidak, hentikan skrip.
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die("Error: Tidak ada ID pengguna yang diberikan di URL.");
}

$user_id = $_GET['user_id'];

// Buka koneksi database sekali saja di awal.
$servername = "mandiricoal.net";
$username = "podema";
$password = "Jam10pagi#";
$dbname = "podema";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 2. QUERY UTAMA: Dapatkan detail pengguna berdasarkan user_id.
// =================================================================

$user_sql = "SELECT nik, name, email, company, department FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    die("Pengguna dengan ID " . htmlspecialchars($user_id) . " tidak ditemukan.");
}

// Simpan data pengguna ke variabel yang akan digunakan di seluruh halaman.
$user_data = $user_result->fetch_assoc();
$name = $user_data["name"]; // Nama ini akan menjadi kunci untuk query riwayat.
$_SESSION['name_for_header'] = $name; // Simpan ke session jika diperlukan di header.
$user_stmt->close();


// 3. QUERY RIWAYAT ASSESSMENT LAPTOP (Menggunakan $name dari query utama)
// PERBAIKAN: Menambahkan LEFT JOIN untuk VGA dan mengubah semua menjadi LEFT JOIN.
// =================================================================

$assessment_sql = "SELECT a.date, a.type, a.serialnumber,
                        os.os_name, a.os as os_score,
                        processor.processor_name, a.processor as processor_score,
                        batterylife.battery_name, a.batterylife as batterylife_score,
                        age.age_name, a.age as age_score,
                        issue.issue_name, a.issue as issue_score,
                        ram.ram_name, a.ram as ram_score,
                        vga.vga_name, a.vga as vga_score,
                        storage.storage_name, a.storage as storage_score,
                        keyboard.keyboard_name, a.keyboard as keyboard_score,
                        screen.screen_name, a.screen as screen_score,
                        touchpad.touchpad_name, a.touchpad as touchpad_score,
                        audio.audio_name, a.audio as audio_score,
                        body.body_name, a.body as body_score,
                        a.score
                FROM assess_laptop a
                LEFT JOIN operating_sistem_laptop os ON a.os = os.os_score
                LEFT JOIN processor_laptop processor ON a.processor = processor.processor_score
                LEFT JOIN batterylife_laptop batterylife ON a.batterylife = batterylife.battery_score
                LEFT JOIN device_age_laptop age ON a.age = age.age_score
                LEFT JOIN issue_software_laptop issue ON a.issue = issue.issue_score
                LEFT JOIN ram_laptop ram ON a.ram = ram.ram_score
                LEFT JOIN vga_pc vga ON a.vga = vga.vga_score
                LEFT JOIN storage_laptop storage ON a.storage = storage.storage_score
                LEFT JOIN keyboard_laptop keyboard ON a.keyboard = keyboard.keyboard_score
                LEFT JOIN screen_laptop screen ON a.screen = screen.screen_score
                LEFT JOIN touchpad_laptop touchpad ON a.touchpad = touchpad.touchpad_score
                LEFT JOIN audio_laptop audio ON a.audio = audio.audio_score
                LEFT JOIN body_laptop body ON a.body = body.body_score
                WHERE a.name = ? ORDER BY a.date DESC";
$assessment_stmt = $conn->prepare($assessment_sql);
$assessment_stmt->bind_param("s", $name);
$assessment_stmt->execute();
$assessment_result = $assessment_stmt->get_result();
$assessment_stmt->close();


// 4. QUERY RIWAYAT ASSESSMENT PC (Menggunakan $name)
// =================================================================

$assessmentpc_sql = "SELECT a.date, a.merk, a.serialnumber,
                        pctype.pctype_name, a.typepc as typepc_score,
                        os.os_name, a.os as os_score,
                        processor.processor_name, a.processor as processor_score,
                        vga.vga_name, a.vga as vga_score,
                        age.age_name, a.age as age_score,
                        issue.issue_name, a.issue as issue_score,
                        ram.ram_name, a.ram as ram_score,
                        storage.storage_name, a.storage as storage_score,
                        typemonitor.monitor_name, a.typemonitor as typemonitor_score,
                        sizemonitor.size_name, a.sizemonitor as sizemonitor_score,
                        a.score
                FROM assess_pc a
                LEFT JOIN pctype_pc pctype ON a.typepc = pctype.pctype_score
                LEFT JOIN operating_sistem_pc os ON a.os = os.os_score
                LEFT JOIN processor_pc processor ON a.processor = processor.processor_score
                LEFT JOIN vga_pc vga ON a.vga = vga.vga_score
                LEFT JOIN device_age_pc age ON a.age = age.age_score
                LEFT JOIN issue_software_pc issue ON a.issue = issue.issue_score
                LEFT JOIN ram_pc ram ON a.ram = ram.ram_score
                LEFT JOIN storage_pc storage ON a.storage = storage.storage_score
                LEFT JOIN typemonitor_pc typemonitor ON a.typemonitor = typemonitor.monitor_score
                LEFT JOIN sizemonitor_pc sizemonitor ON a.sizemonitor = sizemonitor.size_score
                WHERE a.name = ? ORDER BY a.date DESC";
$assessmentpc_stmt = $conn->prepare($assessmentpc_sql);
$assessmentpc_stmt->bind_param("s", $name);
$assessmentpc_stmt->execute();
$assessmentpc_result = $assessmentpc_stmt->get_result();
$assessmentpc_stmt->close();


// 5. QUERY RIWAYAT INSPEKSI (Menggunakan $name)
// =================================================================

$form_inspeksi_sql = "SELECT fi.*, age.age_name, age.age_score, casing_lap.casing_lap_name, casing_lap.casing_lap_score,
                          layar_lap.layar_lap_name, layar_lap.layar_lap_score, engsel_lap.engsel_lap_name, engsel_lap.engsel_lap_score,
                          keyboard_lap.keyboard_lap_name, keyboard_lap.keyboard_lap_score, touchpad_lap.touchpad_lap_name, touchpad_lap.touchpad_lap_score,
                          booting_lap.booting_lap_name, booting_lap.booting_lap_score, multi_lap.multi_lap_name, multi_lap.multi_lap_score,
                          tampung_lap.tampung_lap_name, tampung_lap.tampung_lap_score, isi_lap.isi_lap_name, isi_lap.isi_lap_score,
                          port_lap.port_lap_name, port_lap.port_lap_score, audio_lap.audio_lap_name, audio_lap.audio_lap_score,
                          software_lap.software_lap_name, software_lap.software_lap_score
                      FROM form_inspeksi fi
                      LEFT JOIN device_age_laptop age ON fi.age = age.age_id
                      LEFT JOIN ins_casing_lap casing_lap ON fi.casing_lap = casing_lap.casing_lap_id
                      LEFT JOIN ins_layar_lap layar_lap ON fi.layar_lap = layar_lap.layar_lap_id
                      LEFT JOIN ins_engsel_lap engsel_lap ON fi.engsel_lap = engsel_lap.engsel_lap_id
                      LEFT JOIN ins_keyboard_lap keyboard_lap ON fi.keyboard_lap = keyboard_lap.keyboard_lap_id
                      LEFT JOIN ins_touchpad_lap touchpad_lap ON fi.touchpad_lap = touchpad_lap.touchpad_lap_id
                      LEFT JOIN ins_booting_lap booting_lap ON fi.booting_lap = booting_lap.booting_lap_id
                      LEFT JOIN ins_multi_lap multi_lap ON fi.multi_lap = multi_lap.multi_lap_id
                      LEFT JOIN ins_tampung_lap tampung_lap ON fi.tampung_lap = tampung_lap.tampung_lap_id
                      LEFT JOIN ins_isi_lap isi_lap ON fi.isi_lap = isi_lap.isi_lap_id
                      LEFT JOIN ins_port_lap port_lap ON fi.port_lap = port_lap.port_lap_id
                      LEFT JOIN ins_audio_lap audio_lap ON fi.audio_lap = audio_lap.audio_lap_id
                      LEFT JOIN ins_software_lap software_lap ON fi.software_lap = software_lap.software_lap_id
                      WHERE fi.nama_user = ? ORDER BY fi.date DESC";
$form_inspeksi_stmt = $conn->prepare($form_inspeksi_sql);
$form_inspeksi_stmt->bind_param("s", $name);
$form_inspeksi_stmt->execute();
$form_inspeksi_result = $form_inspeksi_stmt->get_result();
$form_inspeksi_stmt->close();

$conn->close();

// Fungsi bantuan untuk menampilkan data dengan skor, atau pesan jika kosong.
function display_data_with_score($name, $score) {
    if (!empty($name)) {
        return htmlspecialchars($name) . " (Skor: " . htmlspecialchars($score) . ")";
    }
    return '<em class="text-muted">Data tidak tersedia</em>';
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Detail - <?php echo htmlspecialchars($name); ?></title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    .back-button { cursor: pointer; color: #5D87FF; font-weight: 500; }
    .back-button:hover { text-decoration: underline; }
    .expand-btn {
        cursor: pointer;
        font-weight: 600;
        padding: 10px;
        background-color: #f2f2f2;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .assessment-content { display: none; margin-top: 10px; }
    .table-bordered th, .table-bordered td { vertical-align: middle; }
  </style>
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
                <div class="back-button" onclick="goBack()">
                    <!-- <i class="ti ti-arrow-big-left-line-filled"></i> Back -->
                    <i class="ti ti-circle-arrow-left-filled"></i> Back
                </div>
                <br>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-4">User Detail</h5>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 20%;">NIK</th>
                                    <td><?php echo htmlspecialchars($user_data['nik']); ?></td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td><?php echo htmlspecialchars($name); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Company</th>
                                    <td>
                                        <?php
                                            $companyOptions = [ 'PAM' => 'PT. Prima Andalan Mandiri', 'MIP HO' => 'PT. Mandiri Intiperkasa - HO', 'MIP Site' => 'PT. Mandiri Intiperkasa - Site', 'MIP Site Staff' => 'PT. Mandiri Intiperkasa - Site', 'MIP Site NonStaff' => 'PT. Mandiri Intiperkasa - Site', 'MKP HO' => 'PT. Mandala Karya Prima - HO', 'MKP Site' => 'PT. Mandala Karya Prima - Site', 'MPM HO' => 'PT. Maritim Prima Mandiri - HO', 'MPM Site' => 'PT. Maritim Prima Mandiri - Site', 'mandiriland' => 'PT. Mandiriland', 'GMS' => 'PT. Global Mining Service', 'eam' => 'PT. Edika Agung Mandiri' ];
                                            echo isset($companyOptions[$user_data['company']]) ? $companyOptions[$user_data['company']] : htmlspecialchars($user_data['company']);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td><?php echo htmlspecialchars($user_data['department']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-4">Device History</h5>

                        <h6 class="fw-semibold mb-3">Laptop Assessments</h6>
                        <?php if ($assessment_result->num_rows > 0): ?>
                            <?php $count = 1; while ($row = $assessment_result->fetch_assoc()): ?>
                                <div class="expand-btn" onclick="toggleContent('lap-<?php echo $count; ?>')">
                                    Assessment #<?php echo $count; ?> (<?php echo htmlspecialchars($row['date']); ?>) - Score: <?php echo htmlspecialchars($row['score']); ?>
                                </div>
                                <div class="assessment-content" id="content-lap-<?php echo $count; ?>">
                                    <table class="table table-striped table-bordered mt-2">
                                        <tr class="table-light"><th colspan="2"><?php echo htmlspecialchars($row["type"]); ?> / <?php echo htmlspecialchars($row["serialnumber"]); ?></th></tr>
                                        <tr><td style="width: 40%;">Sistem Operasi</td><td><?php echo display_data_with_score($row["os_name"], $row["os_score"]); ?></td></tr>
                                        <tr><td>Processor</td><td><?php echo display_data_with_score($row["processor_name"], $row["processor_score"]); ?></td></tr>
                                        <tr><td>Ketahanan Baterai</td><td><?php echo display_data_with_score($row["battery_name"], $row["batterylife_score"]); ?></td></tr>
                                        <tr><td>Usia Perangkat</td><td><?php echo display_data_with_score($row["age_name"], $row["age_score"]); ?></td></tr>
                                        <tr><td>Isu Terkait Software</td><td><?php echo display_data_with_score($row["issue_name"], $row["issue_score"]); ?></td></tr>
                                        <tr><td>RAM</td><td><?php echo display_data_with_score($row["ram_name"], $row["ram_score"]); ?></td></tr>
                                        <tr><td>VGA</td><td><?php echo display_data_with_score($row["vga_name"], $row["vga_score"]); ?></td></tr>
                                        <tr><td>Penyimpanan</td><td><?php echo display_data_with_score($row["storage_name"], $row["storage_score"]); ?></td></tr>
                                        <tr><td>Keyboard</td><td><?php echo display_data_with_score($row["keyboard_name"], $row["keyboard_score"]); ?></td></tr>
                                        <tr><td>Layar</td><td><?php echo display_data_with_score($row["screen_name"], $row["screen_score"]); ?></td></tr>
                                        <tr><td>Touchpad</td><td><?php echo display_data_with_score($row["touchpad_name"], $row["touchpad_score"]); ?></td></tr>
                                        <tr><td>Audio</td><td><?php echo display_data_with_score($row["audio_name"], $row["audio_score"]); ?></td></tr>
                                        <tr><td>Rangka (Body)</td><td><?php echo display_data_with_score($row["body_name"], $row["body_score"]); ?></td></tr>
                                        <tr class="table-info"><td><b>Total Score</b></td><td><b><?php echo htmlspecialchars($row["score"]); ?></b></td></tr>
                                    </table>
                                </div>
                            <?php $count++; endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada riwayat assessment laptop ditemukan.</p>
                        <?php endif; ?>

                        <hr>

                        <h6 class="fw-semibold my-3">PC Desktop Assessments</h6>
                        <?php if ($assessmentpc_result->num_rows > 0): ?>
                            <?php $count = 1; while ($row = $assessmentpc_result->fetch_assoc()): ?>
                                <div class="expand-btn" onclick="toggleContent('pc-<?php echo $count; ?>')">
                                    Assessment #<?php echo $count; ?> (<?php echo htmlspecialchars($row['date']); ?>) - Score: <?php echo htmlspecialchars($row['score']); ?>
                                </div>
                                <div class="assessment-content" id="content-pc-<?php echo $count; ?>">
                                    <table class="table table-striped table-bordered mt-2">
                                        <tr class="table-light"><th colspan="2"><?php echo htmlspecialchars($row["merk"]); ?> / <?php echo htmlspecialchars($row["serialnumber"]); ?></th></tr>
                                        <tr><td style="width: 40%;">Tipe PC</td><td><?php echo display_data_with_score($row["pctype_name"], $row["typepc_score"]); ?></td></tr>
                                        <tr><td>Sistem Operasi</td><td><?php echo display_data_with_score($row["os_name"], $row["os_score"]); ?></td></tr>
                                        <tr><td>Processor</td><td><?php echo display_data_with_score($row["processor_name"], $row["processor_score"]); ?></td></tr>
                                        <tr><td>VGA</td><td><?php echo display_data_with_score($row["vga_name"], $row["vga_score"]); ?></td></tr>
                                        <tr><td>Usia Perangkat</td><td><?php echo display_data_with_score($row["age_name"], $row["age_score"]); ?></td></tr>
                                        <tr><td>Isu Terkait Software</td><td><?php echo display_data_with_score($row["issue_name"], $row["issue_score"]); ?></td></tr>
                                        <tr><td>RAM</td><td><?php echo display_data_with_score($row["ram_name"], $row["ram_score"]); ?></td></tr>
                                        <tr><td>Penyimpanan</td><td><?php echo display_data_with_score($row["storage_name"], $row["storage_score"]); ?></td></tr>
                                        <tr><td>Tipe Monitor</td><td><?php echo display_data_with_score($row["monitor_name"], $row["typemonitor_score"]); ?></td></tr>
                                        <tr><td>Ukuran Monitor</td><td><?php echo display_data_with_score($row["size_name"], $row["sizemonitor_score"]); ?></td></tr>
                                        <tr class="table-info"><td><b>Total Score</b></td><td><b><?php echo htmlspecialchars($row["score"]); ?></b></td></tr>
                                    </table>
                                </div>
                            <?php $count++; endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada riwayat assessment PC ditemukan.</p>
                        <?php endif; ?>

                        <hr>

                        <h6 class="fw-semibold my-3">Device Inspections</h6>
                        <?php if ($form_inspeksi_result->num_rows > 0): ?>
                            <?php $count = 1; while ($row = $form_inspeksi_result->fetch_assoc()): ?>
                                <div class="expand-btn" onclick="toggleContent('ins-<?php echo $count; ?>')">
                                    Inspeksi #<?php echo $count; ?> (<?php echo htmlspecialchars($row['date']); ?>): <?php echo htmlspecialchars($row['jenis']); ?>
                                </div>
                                <div class="assessment-content" id="content-ins-<?php echo $count; ?>">
                                    <table class="table table-striped table-bordered mt-2">
                                        <tr><td style="width: 40%;">No. Inspeksi</td><td><?php echo htmlspecialchars($row["no"]); ?></td></tr>
                                        <tr><td>Merk / SN</td><td><?php echo htmlspecialchars($row["merk"]); ?> / <?php echo htmlspecialchars($row["serialnumber"]); ?></td></tr>
                                        <tr><td>Lokasi</td><td><?php echo htmlspecialchars($row["lokasi"]); ?></td></tr>
                                        <tr><td>Informasi Keluhan</td><td><?php echo nl2br(htmlspecialchars($row["informasi_keluhan"])); ?></td></tr>
                                        <tr><td>Hasil Pemeriksaan</td><td><?php echo nl2br(htmlspecialchars($row["hasil_pemeriksaan"])); ?></td></tr>
                                        <tr><td>Rekomendasi</td><td><?php echo nl2br(htmlspecialchars($row["rekomendasi"])); ?></td></tr>
                                        <tr><td>Usia Perangkat</td><td><?php echo display_data_with_score($row["age_name"], $row["age_score"]); ?></td></tr>
                                        <tr><td>Casing</td><td><?php echo display_data_with_score($row["casing_lap_name"], $row["casing_lap_score"]); ?></td></tr>
                                        <tr><td>Layar</td><td><?php echo display_data_with_score($row["layar_lap_name"], $row["layar_lap_score"]); ?></td></tr>
                                        <tr class="table-info"><td><b>Total Score</b></td><td><b><?php echo !empty($row['score']) ? htmlspecialchars($row['score']) : '<em class="text-muted">N/A</em>'; ?></b></td></tr>
                                    </table>
                                </div>
                            <?php $count++; endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada riwayat inspeksi ditemukan.</p>
                        <?php endif; ?>

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
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script>
    function goBack() {
        window.history.back();
    }
  </script>
</body>

</html>