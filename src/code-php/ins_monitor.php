<?php
session_start();

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
  header("location: ./index.php");
  exit();
}

// Cukup satu kali koneksi di atas
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

// Opsi Perusahaan untuk tampilan
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
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inspection - Monitor</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/icon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    .sidebar-submenu { position: static !important; max-height: 0; overflow: hidden; transition: max-height 0.35s ease-in-out; list-style: none; padding-left: 20px; background-color: #fff; }
    .sidebar-item.active > .sidebar-submenu { max-height: 500px; }
    .sidebar-item > a .arrow { transition: transform 0.3s ease; display: inline-block; margin-left: auto; }
    .sidebar-item.active > a .arrow { transform: rotate(180deg); }
    .sidebar-nav ul .sidebar-item.active > .sidebar-link { background: var(--bs-primary); color: var(--bs-white); border-radius: 7px; }
    .sidebar-nav ul .sidebar-item.active > .sidebar-link i,
    .sidebar-nav ul .sidebar-item.active > .sidebar-link .arrow { color: var(--bs-white); }
      
    .card-title { margin-bottom: 1.5rem; }
    .form-label { font-weight: 600; }
    .input-group-text { background-color: #f8f9fa; }
    .form-section-card { margin-bottom: 2rem; }
    .required-star { color: crimson; }
    .form-textarea { height: 100px; width: 100%; border-radius: 6px; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; }

    .file-drop-zone { border: 2px dashed #0d6efd; border-radius: 6px; padding: 2rem; text-align: center; cursor: pointer; background-color: #f8f9fa; transition: background-color 0.2s ease-in-out; }
    .file-drop-zone:hover { background-color: #e9ecef; }
    .file-drop-zone-icon { font-size: 3rem; color: #0d6efd; }
    .file-drop-zone p { margin-top: 1rem; color: #6c757d; }

    #screenshot_preview_container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 1.5rem; }
    .preview-image-wrapper { position: relative; width: 150px; height: 150px; }
    .preview-image { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
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
              <a class="sidebar-link" href="#" aria-expanded="false">
                  <span><i class="ti ti-assembly"></i></span><span class="hide-menu">Device Inspection</span>
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
            <h1 class="card-title fw-semibold mb-4">Device Inspection (Monitor)</h1>
            <form id="assessmentForm" method="post" action="submit_monitor.php" enctype="multipart/form-data">

              <div class="card form-section-card">
                <div class="card-body">
                  <h5 class="mb-3">Data Inspeksi</h5>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="date" class="form-label">Date<span class="required-star">*</span></label>
                      <div class="input-group"><span class="input-group-text"><i class="ti ti-calendar"></i></span><input type="date" id="date" name="date" class="form-control" required></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="name" class="form-label">Name<span class="required-star">*</span></label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                        <select id="name" name="nama_user" class="form-select" required>
                            <option value="">--- Select User ---</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="card form-section-card">
                <div class="card-body">
                  <h5 class="mb-3">Detail Pengguna & Perangkat</h5>
                  <div class="row">
                    <div class="col-md-6 mb-3"><label for="status" class="form-label">Position/Division<span class="required-star">*</span></label><input type="text" id="status" name="status" class="form-control" readonly placeholder="Auto-filled"></div>
                    <div class="col-md-6 mb-3"><label for="lokasi" class="form-label">Location<span class="required-star">*</span></label><input type="text" id="lokasi" name="lokasi" class="form-control" readonly placeholder="Auto-filled"></div>
                    <div class="col-md-6 mb-3"><label for="jenis" class="form-label">Device Type<span class="required-star">*</span></label><input type="text" id="jenis" name="jenis" class="form-control" value="Monitor" readonly></div>
                    <div class="col-md-6 mb-3"><label for="merk" class="form-label">Merk/Type<span class="required-star">*</span></label><input type="text" id="merk" name="merk" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label for="serialnumber" class="form-label">Serial Number</label><input type="text" id="serialnumber" name="serialnumber" class="form-control"></div>
                  </div>
                </div>
              </div>
              
              <div class="card form-section-card">
                  <div class="card-body">
                      <h5 class="mb-3">Parameter Inspeksi Monitor</h5>
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label for="casing_lap" class="form-label">Casing<span class="required-star">*</span></label>
                              <select id="casing_lap" name="casing_lap" class="form-select" required>
                                  <option value="">--- Pilih ---</option>
                                  <?php $options = fetchData("ins_casing_lap"); foreach ($options as $opt) { echo '<option value="' . $opt['casing_lap_score'] . '">' . $opt['casing_lap_name'] . '</option>'; } ?>
                              </select>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label for="layar_lap" class="form-label">Layar<span class="required-star">*</span></label>
                              <select id="layar_lap" name="layar_lap" class="form-select" required>
                                  <option value="">--- Pilih ---</option>
                                  <?php $options = fetchData("ins_layar_lap"); foreach ($options as $opt) { echo '<option value="' . $opt['layar_lap_score'] . '">' . $opt['layar_lap_name'] . '</option>'; } ?>
                              </select>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="card form-section-card">
                <div class="card-body">
                  <h5 class="mb-3">Laporan & Bukti</h5>
                  <div class="mb-3">
                    <label for="informasi_keluhan" class="form-label">Complaints / Issues<span class="required-star">*</span></label>
                    <textarea id="informasi_keluhan" name="informasi_keluhan" class="form-textarea" required></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="hasil_pemeriksaan" class="form-label">Examination Findings<span class="required-star">*</span></label>
                    <textarea id="hasil_pemeriksaan" name="hasil_pemeriksaan" class="form-textarea" required></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="screenshot_file" class="form-label">Screenshot Evidence</label>
                    <div id="file-uploader" class="file-drop-zone">
                        <i class="ti ti-cloud-upload file-drop-zone-icon"></i>
                        <p>Drag & drop files here or click to browse</p>
                        <input type="file" id="screenshot_file" name="screenshot_file[]" accept="image/*" multiple hidden>
                    </div>
                    <div id="screenshot_preview_container"></div>
                    <button type="button" id="reset_button" class="btn btn-outline-danger mt-3" style="display: none;">Reset Images</button>
                  </div>
                   <div class="mb-3">
                    <label for="rekomendasi" class="form-label">Recommendation<span class="required-star">*</span></label>
                    <textarea id="rekomendasi" name="rekomendasi" class="form-textarea" required></textarea>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-secondary">Reset Form</button>
                <button type="submit" class="btn btn-primary">Submit Inspection</button>
              </div>
          </form>
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
        
        var submenuToggles = document.querySelectorAll('.sidebar-item > a[href="#"]');
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var parentItem = this.closest('.sidebar-item');
                if (parentItem) {
                    document.querySelectorAll('.sidebar-item.active').forEach(function(activeItem) {
                        if (activeItem !== parentItem) activeItem.classList.remove('active');
                    });
                    parentItem.classList.toggle('active');
                }
            });
        });

        const userInfos = <?php echo json_encode($userInfos); ?>;
        const companyOptions = <?php echo json_encode($companyOptions); ?>;

        document.getElementById('name').addEventListener('change', function() {
            const selectedName = this.value;
            const statusInput = document.getElementById('status');
            const lokasiInput = document.getElementById('lokasi');

            if (selectedName && userInfos[selectedName]) {
                statusInput.value = userInfos[selectedName].divisi || 'N/A';
                const companyKey = userInfos[selectedName].company;
                lokasiInput.value = companyOptions[companyKey] || companyKey || 'N/A';
            } else {
                statusInput.value = '';
                lokasiInput.value = '';
            }
        });

        const uploader = document.getElementById('file-uploader');
        const fileInput = document.getElementById('screenshot_file');
        const previewContainer = document.getElementById('screenshot_preview_container');
        const resetButton = document.getElementById('reset_button');

        uploader.addEventListener('click', () => fileInput.click());
        uploader.addEventListener('dragover', (e) => { e.preventDefault(); uploader.style.backgroundColor = '#e9ecef'; });
        uploader.addEventListener('dragleave', () => { uploader.style.backgroundColor = '#f8f9fa'; });
        uploader.addEventListener('drop', (e) => {
            e.preventDefault();
            uploader.style.backgroundColor = '#f8f9fa';
            fileInput.files = e.dataTransfer.files;
            handleFiles(fileInput.files);
        });
        fileInput.addEventListener('change', () => handleFiles(fileInput.files));

        function handleFiles(files) {
            previewContainer.innerHTML = '';
            resetButton.style.display = files.length > 0 ? 'inline-block' : 'none';

            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'preview-image-wrapper';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    wrapper.appendChild(img);
                    previewContainer.appendChild(wrapper);
                }
                reader.readAsDataURL(file);
            }
        }

        resetButton.addEventListener('click', () => {
            fileInput.value = '';
            previewContainer.innerHTML = '';
            resetButton.style.display = 'none';
        });
    });
  </script>
</body>
</html>