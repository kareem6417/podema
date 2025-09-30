<?php

require('../fpdf/fpdf.php');

// Fungsi bantuan untuk membersihkan teks sebelum dimasukkan ke PDF
function clean_text($string) {
    // Mengonversi encoding ke yang didukung FPDF dan mengganti karakter yang tidak valid
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
}

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!$conn->ping()) {
    $conn->close();
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Koneksi ulang gagal: " . $conn->connect_error);
    }
}

$result = mysqli_query($conn, "SELECT assess_laptop.*, operating_sistem_laptop.os_name, processor_laptop.processor_name, batterylife_laptop.battery_name, device_age_laptop.age_name, issue_software_laptop.issue_name, ram_laptop.ram_name, vga_pc.vga_name,
                              storage_laptop.storage_name, keyboard_laptop.keyboard_name, screen_laptop.screen_name, touchpad_laptop.touchpad_name, audio_laptop.audio_name, body_laptop.body_name
                              FROM assess_laptop
                              JOIN operating_sistem_laptop ON assess_laptop.os = operating_sistem_laptop.os_score
                              JOIN processor_laptop ON assess_laptop.processor = processor_laptop.processor_score
                              JOIN batterylife_laptop ON assess_laptop.batterylife = batterylife_laptop.battery_score
                              JOIN device_age_laptop ON assess_laptop.age = device_age_laptop.age_score
                              JOIN issue_software_laptop ON assess_laptop.issue = issue_software_laptop.issue_score
                              JOIN ram_laptop ON assess_laptop.ram = ram_laptop.ram_score
                              JOIN vga_pc ON assess_laptop.vga = vga_pc.vga_score
                              JOIN storage_laptop ON assess_laptop.storage = storage_laptop.storage_score
                              JOIN keyboard_laptop ON assess_laptop.keyboard = keyboard_laptop.keyboard_score
                              JOIN screen_laptop ON assess_laptop.screen = screen_laptop.screen_score
                              JOIN touchpad_laptop ON assess_laptop.touchpad = touchpad_laptop.touchpad_score
                              JOIN audio_laptop ON assess_laptop.audio = audio_laptop.audio_score
                              JOIN body_laptop ON assess_laptop.body = body_laptop.body_score
                              ORDER BY assess_laptop.id DESC
                              LIMIT 1");

if (!$result) {
    die("Error pada query: " . mysqli_error($conn));
}

$query = mysqli_fetch_array($result);

if (!$query) {
    die("Data assessment tidak ditemukan di database.");
}


class PDF extends FPDF {
    // ================================================================= //
    // FUNGSI HEADER DIMODIFIKASI - SEMUA KODE GAMBAR/LOGO DIHAPUS     //
    // ================================================================= //
    function Header() {
        $this->SetFont('helvetica','B',16);
        $this->Cell(0,10,'LAPTOP REPLACEMENT ASSESSMENT',0,1,'C');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY() + 5, 200, $this->GetY() + 5); 
        $this->SetLineWidth(0.2);
        $this->Ln(15); // Memberi jarak dari header
    }    

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}


$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);

$totalScore = $query['os'] + $query['processor'] + $query['batterylife'] + $query['age'] + $query['issue'] + $query['ram'] + $query['vga'] + $query['storage'] + $query['keyboard'] + $query['screen'] + $query['touchpad'] + $query['audio'] + $query['body'];

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// Menggunakan fungsi clean_text untuk data dari database
$data = [
    ['Name', clean_text($query['name']), 'Date', $query['date']],
    ['Company', clean_text($query['company']), 'Type/Merk', clean_text($query['type'])],
    ['Division', clean_text($query['divisi']), 'Serial Number', clean_text($query['serialnumber'])],
];

$columnWidth = 38;
$rowHeight = 5;

for ($i = 0; $i < count($data); $i++) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetX(15);
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][0], 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][1], 0, 0);
    $pdf->Cell(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][2], 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][3], 0, 1); 
    $pdf->Ln(); 
}

$pdf->Ln(4);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetX(20);
$columnWidths = [40, 75, 40]; 
$header = ['Detail', 'Description', 'Score'];
$pdf->SetFillColor(176, 224, 230); 
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(0.15); 

for ($i = 0; $i < count($header); $i++) {
    $pdf->Cell($columnWidths[$i], 10, $header[$i], 1, 0, 'C', true);
}
$pdf->Ln();

// Menggunakan fungsi clean_text untuk data dari database
$dataTable = [
    ['Operating System', clean_text($query['os_name']), $query['os']],
    ['Processor', clean_text($query['processor_name']), $query['processor']],
    ['Battery Life', clean_text($query['battery_name']), $query['batterylife']],
    ['Device Age', clean_text($query['age_name']), $query['age']],
    ['Issue Related Software', clean_text($query['issue_name']), $query['issue']],
    ['RAM', clean_text($query['ram_name']), $query['ram']],
    ['VGA', clean_text($query['vga_name']), $query['vga']],
    ['Storage', clean_text($query['storage_name']), $query['storage']],
    ['Keyboard', clean_text($query['keyboard_name']), $query['keyboard']],
    ['Screen', clean_text($query['screen_name']), $query['screen']],
    ['Touchpad', clean_text($query['touchpad_name']), $query['touchpad']],
    ['Audio', clean_text($query['audio_name']), $query['audio']],
    ['Body', clean_text($query['body_name']), $query['body']],
    ['Total Score', '', $totalScore]
];

foreach ($dataTable as $row) {
    $pdf->SetX(20); 
    if ($row[0] == 'Total Score') {
        $pdf->Cell($columnWidths[0] + $columnWidths[1], 10, $row[0], 1, 0, 'C');
        $pdf->Cell($columnWidths[2], 10, $row[2], 1, 1, 'C');
    } else {
        $pdf->Cell($columnWidths[0], 10, $row[0], 1, 0, 'C');
        $pdf->Cell($columnWidths[1], 10, $row[1], 1, 0, 'C');
        $pdf->Cell($columnWidths[2], 10, $row[2], 1, 1, 'C');
    }
}

$pdf->Ln(5);

$recommendation = ($totalScore > 100)
    ? 'Berdasarkan pada hasil diatas, direkomendasikan untuk mengganti perangkat Anda dengan yang baru.'
    : 'Berdasarkan pada hasil diatas, dinyatakan bahwa perangkat Anda masih dapat digunakan. Oleh karena itu, tim IT akan melakukan peningkatan sesuai dengan kebutuhan perangkat Anda.';

$pdf->SetFont('helvetica', '', 10);
$pdf->SetX(15);
$pdf->MultiCell(0, 5, clean_text($recommendation));

$pdf->Ln(5); 

$pdf->SetFont('helvetica', 'B', 10);
$location = 'Jakarta, '; 
$currentDate = date('d F Y');
$pdf->SetX(15);
$pdf->Cell(0, 5, $location . $currentDate, 0, 1, 'L');

$pdf->Ln(15); 

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX(15);
$pdf->Cell(60, 5, 'Diperiksa Oleh,', 0, 0, 'L');

$pdf->SetX(-75);
$pdf->Cell(60, 5, 'Nama Pengguna,', 0, 1, 'L');

$pdf->Ln(15);

$pdf->SetFont('helvetica', 'BU', 10);
$pdf->SetX(15);
$pdf->Cell(60, 5, 'IT Support', 0, 0, 'L');

$pdf->SetX(-75);
$pdf->Cell(60, 5, clean_text($query['name']), 0, 1, 'L');

$pdf->AliasNbPages();

$filename = "Assessment-for-Laptop-Replacement-" . clean_text($query['name']) . ".pdf";
$pdf->Output($filename, 'D');

?>