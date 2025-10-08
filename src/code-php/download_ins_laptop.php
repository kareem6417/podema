<?php
session_start();
require_once('../fpdf/fpdf.php'); // Pastikan path ke FPDF benar

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
    header("location: ./index.php");
    exit();
}

// 1. Ambil 'no' (nomor inspeksi) dari URL dan validasi
$no_inspeksi = isset($_GET['no']) ? (int)$_GET['no'] : 0;
if ($no_inspeksi <= 0) {
    die("Error: Nomor inspeksi tidak valid atau tidak diberikan.");
}

// Fungsi untuk membersihkan teks agar aman untuk PDF
function clean_text($string) {
    if ($string === null) return '';
    // Mengonversi karakter non-standar ke padanan terdekatnya
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
}

// 2. Koneksi ke Database
$host = "mandiricoal.net";
$user = "podema";
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 3. Query SQL yang benar: JOIN berdasarkan SKOR
$sql = "SELECT fi.*, 
            age.age_name, age.age_score,
            cl.casing_lap_name, cl.casing_lap_score,
            ip.layar_lap_name, ip.layar_lap_score,
            el.engsel_lap_name, el.engsel_lap_score,
            kl.keyboard_lap_name, kl.keyboard_lap_score,
            tl.touchpad_lap_name, tl.touchpad_lap_score,
            bl.booting_lap_name, bl.booting_lap_score,
            ml.multi_lap_name, ml.multi_lap_score,
            tampung.tampung_lap_name, tampung.tampung_lap_score,
            il.isi_lap_name, il.isi_lap_score,
            pl.port_lap_name, pl.port_lap_score,
            al.audio_lap_name, al.audio_lap_score,
            sl.software_lap_name, sl.software_lap_score
        FROM form_inspeksi fi 
        LEFT JOIN device_age_laptop age ON fi.age = age.age_score
        LEFT JOIN ins_casing_lap cl ON fi.casing_lap = cl.casing_lap_score
        LEFT JOIN ins_layar_lap ip ON fi.layar_lap = ip.layar_lap_score
        LEFT JOIN ins_engsel_lap el ON fi.engsel_lap = el.engsel_lap_score
        LEFT JOIN ins_keyboard_lap kl ON fi.keyboard_lap = kl.keyboard_lap_score
        LEFT JOIN ins_touchpad_lap tl ON fi.touchpad_lap = tl.touchpad_lap_score
        LEFT JOIN ins_booting_lap bl ON fi.booting_lap = bl.booting_lap_score
        LEFT JOIN ins_multi_lap ml ON fi.multi_lap = ml.multi_lap_score
        LEFT JOIN ins_tampung_lap tampung ON fi.tampung_lap = tampung.tampung_lap_score
        LEFT JOIN ins_isi_lap il ON fi.isi_lap = il.isi_lap_score
        LEFT JOIN ins_port_lap pl ON fi.port_lap = pl.port_lap_score
        LEFT JOIN ins_audio_lap al ON fi.audio_lap = al.audio_lap_score
        LEFT JOIN ins_software_lap sl ON fi.software_lap = sl.software_lap_score
        WHERE fi.no = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $no_inspeksi);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Tidak ada data ditemukan untuk nomor inspeksi: " . htmlspecialchars($no_inspeksi));
}
$stmt->close();
// Koneksi ditutup nanti setelah semua query selesai

// Class FPDF yang sudah disederhanakan
class MYPDF extends FPDF {
    var $inspection_data;

    public function setInspectionData($data) {
        $this->inspection_data = $data;
    }

    function Header() {
        $this->Image('../assets/images/logos/mandiri.png', 10, 8, 33);
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 7, 'INSPEKSI PERANGKAT', 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Divisi Teknologi Informasi', 0, 1, 'C');
        
        $this->SetFont('helvetica', 'B', 9);
        $this->SetXY(140, 12);
        $this->Cell(30, 5, 'Form:', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(30, 5, 'MIP/FRM/ITE/005', 0, 1, 'L');
        
        $this->SetXY(140, 17);
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(30, 5, 'Revisi:', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(30, 5, '00', 0, 1, 'L');

        $this->SetLineWidth(0.5);
        $this->Line(10, 35, $this->GetPageWidth() - 10, 35);
        $this->SetLineWidth(0.2);
        $this->Ln(5);

        // Menampilkan Nomor Inspeksi yang benar dari data
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'No Inspeksi: ' . clean_text($this->inspection_data['no']), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // Fungsi untuk membuat baris tabel dengan wrap-text
    function MultiCellRow($data, $widths, $aligns) {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($widths[$i], $data[$i]));
        }
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $widths[$i];
            $a = isset($aligns[$i]) ? $aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }
}

$pdf = new MYPDF($screenshot_files, $nomorInspeksi);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf = new MYPDF('P', 'mm', 'A4');
$pdf->setInspectionData($row); // Mengirim data ke class untuk digunakan di header
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth, 10, 'Tanggal:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth, 10, $row['date'], 1, 0, 'L', false);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth, 10, 'Nama pengguna:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth, 10, $row['nama_user'], 1, 1, 'L', false); 

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth, 10, 'Tipe Perangkat:', 1, 0, 'L', false); 
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth, 10, $row['jenis'], 1, 0, 'L', false);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth, 10, 'Divisi:', 1, 0, 'L', false); 
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth, 10, $row['status'], 1, 1, 'L', false); 

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth * 2, 10, 'Merk/Nomor Serial:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth * 2, 10, $row['merk'] . ' / ' . $row['serialnumber'], 1, 1, 'L', false); 

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth * 2, 10, 'Lokasi/Area Penggunaan:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth * 2, 10, $row['lokasi'], 1, 1, 'L', false);
$pdf->Ln(3);

// Informasi Keluhan
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'Informasi Keluhan/Permasalahan yang disampaikan:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, clean_text($row['informasi_keluhan']), 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'Hasil Pemeriksaan:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, clean_text($row['hasil_pemeriksaan']), 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(173, 216, 230);
$pdf->addTableRow('Item', 'Detail', 'Skor');

$pdf->SetFont('helvetica', '', 9);

$pdf->addTableRow('Usia Perangkat', $row['age_name'], $row['age_score']);
$pdf->addTableRow('Casing', $row['casing_lap_name'], $row['casing_lap_score']);
$pdf->addTableRow('Layar', $row['layar_lap_name'], $row['layar_lap_score']);
$pdf->addTableRow('Engsel', $row['engsel_lap_name'], $row['engsel_lap_score']);
$pdf->addTableRow('Keyboard', $row['keyboard_lap_name'], $row['keyboard_lap_score']);
$pdf->addTableRow('Touchpad', $row['touchpad_lap_name'], $row['touchpad_lap_score']);
$pdf->addTableRow('Proses Booting', $row['booting_lap_name'], $row['booting_lap_score']);
$pdf->addTableRow('Multitasking Apps', $row['multi_lap_name'], $row['multi_lap_score']);
$pdf->addTableRow('Kapasitas Baterai', $row['tampung_lap_name'], $row['tampung_lap_score']);
$pdf->addTableRow('Waktu Charging', $row['isi_lap_name'], $row['isi_lap_score']);
$pdf->addTableRow('Port', $row['port_lap_name'], $row['port_lap_score']);
$pdf->addTableRow('Audio', $row['audio_lap_name'], $row['audio_lap_score']);
$pdf->addTableRow('Software', $row['software_lap_name'], $row['software_lap_score']);

$totalScore = $row['age_score'] + $row['casing_lap_score'] + $row['layar_lap_score'] + $row['engsel_lap_score'] +$row['keyboard_lap_score'] + $row['touchpad_lap_score'] + $row['booting_lap_score'] + $row['multi_lap_score'] + $row['tampung_lap_score'] + $row['isi_lap_score'] + $row['port_lap_score'] + $row['audio_lap_score'] + $row['software_lap_score'];
$pdf->SetFont('helvetica', 'B', 11);
$pdf->addTableRow('Total Skor', '', $totalScore);

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'Rekomendasi:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, clean_text($row['rekomendasi']), 1, 'L');
$pdf->Ln(10);

$pdf->Ln(5);
$pdf->AddScreenshots($target_screenshot_dir, $row['no']);

$current_inspection_id = $row['no'];
$target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";

// Mendapatkan daftar file screenshot terbaru di direktori
$latest_screenshot = null;
$latest_timestamp = 0;

if ($handle = opendir($target_screenshot_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $screenshot_path = $target_screenshot_dir . $entry;
            $timestamp = filemtime($screenshot_path);

            if ($timestamp > $latest_timestamp) {
                $latest_timestamp = $timestamp;
                $latest_screenshot = $entry;
            }
        }
    }
    closedir($handle);
}

if ($latest_screenshot !== null && !in_array($latest_screenshot, $screenshot_files)) {
    $screenshot_path = $target_screenshot_dir . $latest_screenshot;
    list($width, $height) = getimagesize($screenshot_path);
    $maxWidth = 84; // Mengurangi lebar gambar
    $maxHeight = 52; // Mengurangi tinggi gambar
    $ratio = $width / $height;

    if ($width > $height) {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    } else {
        $newHeight = $maxHeight;
        $newWidth = $maxHeight * $ratio;
    }

    $pdf->Image($screenshot_path, 10, null, $newWidth, $newHeight);
    $screenshot_files[] = $latest_screenshot;
}

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 10);
$location = '    Jakarta,'; 
$currentDate = date('d F Y'); 
$locationWidth = $pdf->GetStringWidth($location);
$dateWidth = $pdf->GetStringWidth($currentDate);
$totalWidth = $locationWidth + $dateWidth + 5; 

$pdf->SetX(10); 
$pdf->Cell($locationWidth, 5, $location, 0, 0, 'L'); 
$pdf->Cell(1, 1, '', 0, 0, 'C'); 
$pdf->Cell($dateWidth, 5, $currentDate, 0, 1, 'L'); 

$pdf->Ln(15); 

$pdf->Cell(95, 10, '', 0, 0, 'C');
$pdf->Cell(5, 10, '', 0, 0, 'C'); 
$pdf->Cell(95, 10, '', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX(15);
$pdf->Cell(47.5, 7, 'ITE Division', 'T', 0, 'L');
$pdf->Cell(5, 10, '', 0, 0, 'C'); 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX(95);
$pdf->Cell(47.5, 7, $row['nama_user'], 'T', 1, 'L');
$pdf->AliasNbPages();

$filename = "Inspection-Devices.pdf";
$pdf->Output($filename, 'D');
echo '<a href="Inspection-Devices.pdf">Download</a>';
exit;
?>