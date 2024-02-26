<?php

require('../fpdf/fpdf.php');

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);

$result = mysqli_query($conn, "SELECT assess_laptop.*, operating_sistem_laptop.os_name, processor_laptop.processor_name, batterylife_laptop.battery_name, device_age_laptop.age_name, issue_software_laptop.issue_name, ram_laptop.ram_name, 
                              storage_laptop.storage_name, keyboard_laptop.keyboard_name, screen_laptop.screen_name, touchpad_laptop.touchpad_name, audio_laptop.audio_name, body_laptop.body_name
                              FROM assess_laptop
                              JOIN operating_sistem_laptop ON assess_laptop.os = operating_sistem_laptop.os_score
                              JOIN processor_laptop ON assess_laptop.processor = processor_laptop.processor_score
                              JOIN batterylife_laptop ON assess_laptop.batterylife = batterylife_laptop.battery_score
                              JOIN device_age_laptop ON assess_laptop.age = device_age_laptop.age_score
                              JOIN issue_software_laptop ON assess_laptop.issue = issue_software_laptop.issue_score
                              JOIN ram_laptop ON assess_laptop.ram = ram_laptop.ram_score
                              JOIN storage_laptop ON assess_laptop.storage = storage_laptop.storage_score
                              JOIN keyboard_laptop ON assess_laptop.keyboard = keyboard_laptop.keyboard_score
                              JOIN screen_laptop ON assess_laptop.screen = screen_laptop.screen_score
                              JOIN touchpad_laptop ON assess_laptop.touchpad = touchpad_laptop.touchpad_score
                              JOIN audio_laptop ON assess_laptop.audio = audio_laptop.audio_score
                              JOIN body_laptop ON assess_laptop.body = body_laptop.body_score
                              ORDER BY assess_laptop.id DESC
                              LIMIT 1");

$query = mysqli_fetch_array($result);

class PDF extends FPDF {
    function Header() {
        $this->Image('../assets/images/logos/mandiri.png',10,8,33);
        $this->SetFont('helvetica','B',16);
        $this->Cell(0,10,'LAPTOP REPLACEMENT ASSESSMENT',0,1,'C');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY() + 15, 200, $this->GetY() + 15); 
        $this->SetLineWidth(0.2);
        $this->Ln(20);
    }    

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }    

    private $data; 

    public function AddCustomRow($title, $description, $score) {
        $this->SetFont('helvetica', '', 7);
        $this->Cell(80, 10, $title, 0);
        $this->Cell(80, 10, $description, 0);
        $this->Cell(40, 10, $score, 0, 1, 'C');
    }
    
}

$pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetFont('helvetica', 'B', 10);

$name = $query['name'];

$totalScore = $query['os'] + $query['processor'] + $query['batterylife'] + $query['age'] + $query['ram'] + $query['storage'] + $query['keyboard'] + $query['screen'] + $query['issue'] + $query['touchpad'] + $query['audio'] + $query['body'];

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);


$data = [
    ['Name', $query['name'], 'Date', $query['date']],
    ['Company', $query['company'], 'Type/Merk', $query['type']],
    ['Division', $query['divisi'], 'Serial Number', $query['serialnumber']],
];

$columnWidth = 38; // Lebar kolom
$rowHeight = 5; // Tinggi baris

for ($i = 0; $i < count($data); $i++) {
    // Kolom 1
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetX(15); // Sesuaikan posisi X untuk kolom 1
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][0], 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($columnWidth, $rowHeight, $data[$i][1], 0, 0);

    $pdf->Cell(10);

    // Kolom 2
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
$columnAlignments = ['C', 'C', 'C']; 
$header = ['Detail', 'Description', 'Score'];
$pdf->SetFillColor(176, 224, 230); 
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(0.15); 

for ($i = 0; $i < count($header); $i++) {
    $pdf->Cell($columnWidths[$i], 10, $header[$i], 1, 0, $columnAlignments[$i], true);
}
$pdf->Ln();

$data = [
    ['Operating System', $query['os_name'], $query['os']],
    ['Processor', $query['processor_name'], $query['processor']],
    ['Battery Life', $query['battery_name'], $query['batterylife']],
    ['Device Age', $query['age_name'], $query['age']],
    ['Issue Related Software', $query['issue_name'], $query['issue']],
    ['RAM', $query['ram_name'], $query['ram']],
    ['Storage', $query['storage_name'], $query['storage']],
    ['Keyboard', $query['keyboard_name'], $query['keyboard']],
    ['Screen', $query['screen_name'], $query['screen']],
    ['Touchpad', $query['touchpad_name'], $query['touchpad']],
    ['Audio', $query['audio_name'], $query['audio']],
    ['Body', $query['body_name'], $query['body']],
    ['Total Score', '', $totalScore]
];

foreach ($data as $row) {
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

$pdf->SetFont('helvetica', '', 10);
$pdf->SetX(15);
if ($totalScore > 100) {
    $recommendation = 'Berdasarkan pada hasil diatas, direkomendasikan untuk mengganti perangkat Anda dengan yang baru.';
} else {
    $recommendation = 'Berdasarkan pada hasil diatas, dinyatakan bahwa perangkat Anda masih dapat digunakan.' . "\n" .
                      'Oleh karena itu, tim IT akan melakukan peningkatan sesuai dengan kebutuhan perangkat Anda.';
}

$pdf->MultiCell(0, 10, $recommendation);

$pdf->Ln(5); 

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
$pdf->Cell(47.5, 10, 'Diperiksa Oleh', 'T', 0, 'L');
$pdf->Cell(5, 10, '', 0, 0, 'C'); 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetX(95);
$pdf->Cell(47.5, 10, 'Nama Pengguna', 'T', 1, 'C');
$pdf->AliasNbPages();

$filename = "Assessment-for-Laptop-Replacement-{$query['name']}.pdf";
$pdf->Output($filename, 'D');

?>
