<?php

require('../fpdf/fpdf.php');

function clean_text($string) {
    if (empty($string)) return '';
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

$result = mysqli_query($conn, "SELECT assess_laptop.*, operating_sistem_laptop.os_name, processor_laptop.processor_name, batterylife_laptop.battery_name, device_age_laptop.age_name, issue_software_laptop.issue_name, ram_laptop.ram_name, vga_pc.vga_name,
                              storage_laptop.storage_name, keyboard_laptop.keyboard_name, screen_laptop.screen_name, touchpad_laptop.touchpad_name, audio_laptop.audio_name, body_laptop.body_name
                              FROM assess_laptop
                              LEFT JOIN operating_sistem_laptop ON assess_laptop.os = operating_sistem_laptop.os_score
                              LEFT JOIN processor_laptop ON assess_laptop.processor = processor_laptop.processor_score
                              LEFT JOIN batterylife_laptop ON assess_laptop.batterylife = batterylife_laptop.battery_score
                              LEFT JOIN device_age_laptop ON assess_laptop.age = device_age_laptop.age_score
                              LEFT JOIN issue_software_laptop ON assess_laptop.issue = issue_software_laptop.issue_score
                              LEFT JOIN ram_laptop ON assess_laptop.ram = ram_laptop.ram_score
                              LEFT JOIN vga_pc ON assess_laptop.vga = vga_pc.vga_score
                              LEFT JOIN storage_laptop ON assess_laptop.storage = storage_laptop.storage_score
                              LEFT JOIN keyboard_laptop ON assess_laptop.keyboard = keyboard_laptop.keyboard_score
                              LEFT JOIN screen_laptop ON assess_laptop.screen = screen_laptop.screen_score
                              LEFT JOIN touchpad_laptop ON assess_laptop.touchpad = touchpad_laptop.touchpad_score
                              LEFT JOIN audio_laptop ON assess_laptop.audio = audio_laptop.audio_score
                              LEFT JOIN body_laptop ON assess_laptop.body = body_laptop.body_score
                              ORDER BY assess_laptop.id DESC
                              LIMIT 1");

if (!$result) {
    die("Error pada query: " . mysqli_error($conn));
}

$query = mysqli_fetch_array($result);

// Setelah data didapat, koneksi bisa langsung ditutup
$conn->close();

if (!$query) {
    die("Data assessment tidak ditemukan di database.");
}

class PDF extends FPDF {
    var $widths;
    var $aligns;

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

    function SetWidths($w) {
        $this->widths = $w;
    }

    function SetAligns($a) {
        $this->aligns = $a;
    }

    function Row($data) {
        $nb = 0;
        for($i=0; $i<count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));

        $h = 9 * $nb; 
        
        $this->CheckPageBreak($h);
        
        for($i=0; $i<count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            
            $x = $this->GetX();
            $y = $this->GetY();
            
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 9, $data[$i], 0, $a);
            
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt) {
        // (Fungsi ini tidak berubah)
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);

$totalScore = $query['os'] + $query['processor'] + $query['batterylife'] + $query['age'] + $query['issue'] + $query['ram'] + $query['vga'] + $query['storage'] + $query['keyboard'] + $query['screen'] + $query['touchpad'] + $query['audio'] + $query['body'];

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

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

$pdf->SetX(20);
$pdf->SetWidths([40, 75, 40]);
$pdf->SetAligns(['C', 'C', 'C']);

// Header tabel
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(176, 224, 230);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(0.15);
$pdf->Cell(40, 10, 'Detail', 1, 0, 'C', true);
$pdf->Cell(75, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Score', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 10);
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
];

foreach ($dataTable as $row) {
    $pdf->SetX(20);
    $pdf->Row($row);
}

$pdf->SetX(20);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(115, 10, 'Total Score', 1, 0, 'C'); // Gabungan kolom 1 dan 2
$pdf->Cell(40, 10, $totalScore, 1, 1, 'C');


$pdf->Ln(5);

$recommendation = ($totalScore > 100)
    ? 'Berdasarkan pada hasil diatas, direkomendasikan untuk mengganti perangkat Anda dengan yang baru.'
    : 'Berdasarkan pada hasil diatas, dinyatakan bahwa perangkat Anda masih dapat digunakan. Oleh karena itu, tim IT akan melakukan peningkatan sesuai dengan kebutuhan perangkat Anda.';

$pdf->SetFont('helvetica', '', 10);
$pdf->SetX(15);
$pdf->MultiCell(0, 5, clean_text($recommendation));

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

$pdf->Ln(10); 

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