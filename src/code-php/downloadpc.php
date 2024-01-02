<?php

require('../fpdf/fpdf.php');

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);

$result = mysqli_query($conn, "SELECT assess_pc.*, pctype_pc.pctype_name, operating_sistem_pc.os_name, processor_pc.processor_name, vga_pc.vga_name, device_age_pc.age_name, 
                            issue_software_pc.issue_name, ram_pc.ram_name, 
                            storage_pc.storage_name, typemonitor_pc.monitor_name, sizemonitor_pc.size_name
                              FROM assess_pc
                              JOIN pctype_pc ON assess_pc.typepc = pctype_pc.pctype_score
                              JOIN operating_sistem_pc ON assess_pc.os = operating_sistem_pc.os_score
                              JOIN processor_pc ON assess_pc.processor = processor_pc.processor_score
                              JOIN vga_pc ON assess_pc.vga = vga_pc.vga_score
                              JOIN device_age_pc ON assess_pc.age = device_age_pc.age_score
                              JOIN issue_software_pc ON assess_pc.issue = issue_software_pc.issue_score
                              JOIN ram_pc ON assess_pc.ram = ram_pc.ram_score
                              JOIN storage_pc ON assess_pc.storage = storage_pc.storage_score
                              JOIN typemonitor_pc ON assess_pc.typemonitor = typemonitor_pc.monitor_score
                              JOIN sizemonitor_pc ON assess_pc.sizemonitor = sizemonitor_pc.size_score
                              ORDER BY assess_pc.id DESC
                              LIMIT 1");

$query = mysqli_fetch_array($result);

class PDF extends FPDF {
    function Header() {
        $this->Image('../assets/images/logos/mandiri.png',10,8,33);
        $this->SetFont('helvetica','B',16);
        $this->Cell(0,10,'PC DESKTOP REPLACEMENT ASSESSMENT',0,1,'C');
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

$totalScore = $query['typepc'] + $query['os'] + $query['processor'] + $query['vga'] + $query['ram'] + $query['storage'] + $query['age'] + $query['typemonitor'] + $query['sizemonitor'] + $query['issue'];

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);


$data = [
    ['Name', $query['name'], 'Date', $query['date']],
    ['Company', $query['company'], 'Type/Merk', $query['merk']],
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
    ['PC Type', $query['pctype_name'], $query['typepc']],
    ['Operating System', $query['os_name'], $query['os']],
    ['Processor', $query['processor_name'], $query['processor']],
    ['VGA', $query['vga_name'], $query['vga']],
    ['RAM', $query['ram_name'], $query['ram']],
    ['Storage', $query['storage_name'], $query['storage']],
    ['Device Age', $query['age_name'], $query['age']],
    ['Type Monitor', $query['monitor_name'], $query['typemonitor']],
    ['Size Monitor', $query['size_name'], $query['sizemonitor']],
    ['Issue Related Software', $query['issue_name'], $query['issue']],
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
    $recommendation = 'Berdasarkan pada hasil diatas, dinyatakan bahwa perangkat Anda masih dapat digunakan.<br>' . "\n" .
                      'Oleh karena itu, tim IT akan melakukan peningkatan sesuai dengan kebutuhan perangkat Anda.';
}

$pdf->MultiCell(0, 10, $recommendation);

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

$pdf->Ln(20); 

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

$filename = "Assessment-for-PC-Replacement-{$query['name']}.pdf";
$pdf->Output($filename, 'D');

?>
