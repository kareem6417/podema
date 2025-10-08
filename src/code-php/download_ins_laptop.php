<?php
session_start();
require_once('../fpdf/fpdf.php'); // Pastikan path ke FPDF benar

if (!isset($_SESSION['nik']) || empty($_SESSION['nik'])) {
    header("location: ./index.php");
    exit();
}

// 1. Ambil 'no' (nomor inspeksi) dari URL. Ini sekarang sudah benar.
$no_inspeksi = isset($_GET['no']) ? (int)$_GET['no'] : 0;
if ($no_inspeksi <= 0) {
    die("Error: Nomor inspeksi tidak valid atau tidak diberikan.");
}

function clean_text($string) { /* ... fungsi clean_text ... */ }

// 2. Koneksi ke Database
$host = "mandiricoal.net"; $user = "podema"; $pass = "Jam10pagi#"; $db = "podema";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

// 3. Query Lengkap - Ambil SEMUA kemungkinan kolom inspeksi
$sql = "SELECT fi.*, 
            age.age_name, age.age_score, cl.casing_lap_name, cl.casing_lap_score,
            ip.layar_lap_name, ip.layar_lap_score, el.engsel_lap_name, el.engsel_lap_score,
            kl.keyboard_lap_name, kl.keyboard_lap_score, tl.touchpad_lap_name, tl.touchpad_lap_score,
            bl.booting_lap_name, bl.booting_lap_score, ml.multi_lap_name, ml.multi_lap_score,
            tampung.tampung_lap_name, tampung.tampung_lap_score, il.isi_lap_name, il.isi_lap_score,
            pl.port_lap_name, pl.port_lap_score, al.audio_lap_name, al.audio_lap_score,
            sl.software_lap_name, sl.software_lap_score, ink.ink_pad_name, ink.ink_pad_score
        FROM form_inspeksi fi 
        LEFT JOIN device_age_laptop age ON fi.age = age.age_id
        LEFT JOIN ins_casing_lap cl ON fi.casing_lap = cl.casing_lap_id
        LEFT JOIN ins_layar_lap ip ON fi.layar_lap = ip.layar_lap_id
        LEFT JOIN ins_engsel_lap el ON fi.engsel_lap = el.engsel_lap_id
        LEFT JOIN ins_keyboard_lap kl ON fi.keyboard_lap = kl.keyboard_lap_id
        LEFT JOIN ins_touchpad_lap tl ON fi.touchpad_lap = tl.touchpad_lap_id
        LEFT JOIN ins_booting_lap bl ON fi.booting_lap = bl.booting_lap_id
        LEFT JOIN ins_multi_lap ml ON fi.multi_lap = ml.multi_lap_id
        LEFT JOIN ins_tampung_lap tampung ON fi.tampung_lap = tampung.tampung_lap_id
        LEFT JOIN ins_isi_lap il ON fi.isi_lap = il.isi_lap_id
        LEFT JOIN ins_port_lap pl ON fi.port_lap = pl.port_lap_id
        LEFT JOIN ins_audio_lap al ON fi.audio_lap = al.audio_lap_id
        LEFT JOIN ins_software_lap sl ON fi.software_lap = sl.software_lap_id
        LEFT JOIN ins_ink_pad ink ON fi.ink_pad = ink.ink_pad_id
        WHERE fi.no = ?"; // Query sekarang mencari berdasarkan 'no' spesifik

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $no_inspeksi);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) { die("Data inspeksi tidak ditemukan."); }
$stmt->close();
$conn->close();

// 4. Class FPDF (pastikan Anda memiliki class FPDF yang lengkap di sini)
class MYPDF extends FPDF {
    private $screenshot_files;
    private $nomorInspeksi;

    public function __construct($screenshot_files, $nomorInspeksi) {
        parent::__construct();
        $this->screenshot_files = $screenshot_files;
        $this->nomorInspeksi = $nomorInspeksi;
    }

    function Header() {
        $this->Image('../assets/images/logos/mandiri.png',10,8,33);
        $this->addHeaderContent($this->nomorInspeksi);
    }

    function addHeaderContent($nomorInspeksi) {
        $logo_height = 33;
        $this->SetFont('helvetica', 'B', 15);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 13);
        $this->Cell(83, 5, 'INSPEKSI PERANGKAT', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', '', 9);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 22);
        $this->Cell(80, 5, 'Divisi Teknologi Informasi', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', '', 7);
        $this->SetXY(($this->GetPageWidth() - 20) / 2, 13);
        $this->Cell(100, 5, 'Form: MIP/FRM/ITE/005', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->SetXY(($this->GetPageWidth() - 20) / 2, 18);
        $this->Cell(100, 5, 'Revisi: 00', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->SetLineWidth(0.5);
        $this->Line(10, -2 + $logo_height + 0, $this->GetPageWidth() - 10, -2 + $logo_height + 0);
        $this->SetLineWidth(0.2);

        $this->SetFont('helvetica', '', 11);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 40);
        $this->Cell(80, -2, 'No Inspeksi: ' . $nomorInspeksi, 0, false, 'C', 0, '', 0, false, 'M', 'M');        
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica','I',8);
        $this->Cell(0,10,''.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    function AddScreenshots($target_screenshot_dir, $current_inspection_id) {
        $screenshotTitleShown = false; 
        $conn = new mysqli($GLOBALS['host'], $GLOBALS['user'], $GLOBALS['pass'], $GLOBALS['db']);
        $query = $conn->prepare("SELECT screenshot_name FROM screenshots WHERE form_no = ?");
        $query->bind_param("i", $current_inspection_id);
        $query->execute();
        $result = $query->get_result();
    
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $screenshot = $row['screenshot_name'];
                $screenshot_path = $target_screenshot_dir . $screenshot;
    
                if (!$screenshotTitleShown) { 
                    $this->SetFont('helvetica', 'B', 11);
                    $this->Cell(0, 10, 'Screenshot:', 0, 1, 'L');
                    $this->Ln(5);
                    $screenshotTitleShown = true;
                }
                $this->resizeAndInsertImage($screenshot_path);
            }
        }
        $query->close();
        $conn->close();
    }     
    
    function resizeAndInsertImage($imagePath) {
        list($width, $height) = getimagesize($imagePath);
        $maxWidth = 184; // Mengurangi lebar gambar
        $maxHeight = 152; // Mengurangi tinggi gambar
        $ratio = $width / $height;
    
        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $ratio;
        }
    
        $this->Image($imagePath, 10, null, $newWidth, $newHeight);
    }

    private $columnWidths = array(30, 140, 20);

    function addTableRow($item, $detail, $skor) {
        $fill = $this->RowNeedsFill();
        $this->Cell($this->columnWidths[0], 10, $item, 1, 0, 'C', $fill);
        $this->Cell($this->columnWidths[1], 10, $detail, 1, 0, 'C', $fill);
        $this->Cell($this->columnWidths[2], 10, $skor, 1, 1, 'C', $fill);
    }

    function RowNeedsFill() {
        return $this->GetY() % 20 === 0;
    }
}

$pdf = new MYPDF('P', 'mm', 'A4');
$pdf->setInspectionData($row);
$pdf->AliasNbPages();
$pdf->AddPage();

$pageWidth = $pdf->GetPageWidth();
$cellWidth = $pageWidth / 4;
$maxTableWidth = $pageWidth * 0.90; 
$cellWidth = $maxTableWidth / 4; 
$pdf->SetLineWidth(0); 

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
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Informasi Keluhan/Permasalahan yang disampaikan:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
// Ubah parameter ke-4 dari 1 menjadi 'B'
$pdf->MultiCell(0, 7, clean_text($row['informasi_keluhan']), 'B', 'L');

// Hasil Pemeriksaan
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Hasil Pemeriksaan:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
// Ubah parameter ke-4 dari 1 menjadi 'B'
$pdf->MultiCell(0, 7, clean_text($row['hasil_pemeriksaan']), 'B', 'L');
$pdf->Ln(5);

$pdf->Ln(5);

// 5. MEMILIH DAN MENAMPILKAN ITEM INSPEKSI YANG SESUAI
$pdf->SetFont('Arial','B',10);
$pdf->Cell(45, 7, 'Item', 1, 0, 'C');
$pdf->Cell(115, 7, 'Detail', 1, 0, 'C');
$pdf->Cell(30, 7, 'Skor', 1, 1, 'C');
$pdf->SetFont('Arial','',9);

$device_type = $row['jenis'];
$inspection_items = [];

// ================================================================= //
// **SESUAIKAN BAGIAN INI** untuk setiap file download               //
// ================================================================= //
if ($device_type == 'Laptop') {
    $inspection_items = [
        ['Usia Perangkat', $row['age_name'], $row['age_score']], ['Casing', $row['casing_lap_name'], $row['casing_lap_score']],
        ['Layar', $row['layar_lap_name'], $row['layar_lap_score']], ['Engsel', $row['engsel_lap_name'], $row['engsel_lap_score']],
        // ... (tambahkan semua item laptop lainnya) ...
    ];
} elseif ($device_type == 'PC Desktop') {
    $inspection_items = [
        ['Usia Perangkat', $row['age_name'], $row['age_score']], ['Casing', $row['casing_lap_name'], $row['casing_lap_score']],
        // ... (tambahkan semua item PC lainnya, PC tidak punya 'Engsel') ...
    ];
} elseif ($device_type == 'Monitor') {
    $inspection_items = [
        ['Casing', $row['casing_lap_name'], $row['casing_lap_score']],
        ['Layar', $row['layar_lap_name'], $row['layar_lap_score']],
    ];
} elseif ($device_type == 'Printer') {
     $inspection_items = [
        ['Casing', $row['casing_lap_name'], $row['casing_lap_score']],
        ['Ink Pad', $row['ink_pad_name'], $row['ink_pad_score']],
    ];
}
// ================================================================= //

foreach ($inspection_items as $item) {
    if (!empty($item[1])) { // Hanya tampilkan jika deskripsinya ada
        $pdf->Cell(45, 6, $item[0], 1);
        $pdf->Cell(115, 6, clean_text($item[1]), 1);
        $pdf->Cell(30, 6, $item[2], 1, 1, 'C');
    }
}

// Tampilkan Total Skor dari database
$pdf->SetFont('Arial','B',10);
$pdf->Cell(160, 7, 'Total Skor', 1, 0, 'C');
$pdf->Cell(30, 7, $row['score'], 1, 1, 'C');

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