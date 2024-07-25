<?php

require_once('../fpdf/fpdf.php');

$host = "mandiricoal.net";
$user = "podema";
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);

$query = $conn->prepare("SELECT fi.*, 
                            a.age_name, a.age_score,
                            cl.casing_lap_name, cl.casing_lap_score,
                            ip.layar_lap_name, ip.layar_lap_score,
                            el.engsel_lap_name, el.engsel_lap_score,
                            kl.keyboard_lap_name, kl.keyboard_lap_score,
                            tl1.touchpad_lap_name, tl1.touchpad_lap_score,
                            bl.booting_lap_name, bl.booting_lap_score,
                            ml.multi_lap_name, ml.multi_lap_score,
                            tl2.tampung_lap_name, tl2.tampung_lap_score,
                            il.isi_lap_name, il.isi_lap_score,
                            pl.port_lap_name, pl.port_lap_score,
                            al.audio_lap_name, al.audio_lap_score,
                            sl.software_lap_name, sl.software_lap_score
                    FROM form_inspeksi fi 
                    JOIN device_age_laptop a ON fi.age = a.age_score
                    JOIN ins_casing_lap cl ON fi.casing_lap = cl.casing_lap_score
                    JOIN ins_layar_lap ip ON fi.layar_lap = ip.layar_lap_score
                    JOIN ins_engsel_lap el ON fi.engsel_lap = el.engsel_lap_score
                    JOIN ins_keyboard_lap kl ON fi.keyboard_lap = kl.keyboard_lap_score
                    JOIN ins_touchpad_lap tl1 ON fi.touchpad_lap = tl1.touchpad_lap_score
                    JOIN ins_booting_lap bl ON fi.booting_lap = bl.booting_lap_score
                    JOIN ins_multi_lap ml ON fi.multi_lap = ml.multi_lap_score
                    JOIN ins_tampung_lap tl2 ON fi.tampung_lap = tl2.tampung_lap_score
                    JOIN ins_isi_lap il ON fi.isi_lap = il.isi_lap_score
                    JOIN ins_port_lap pl ON fi.port_lap = pl.port_lap_score
                    JOIN ins_audio_lap al ON fi.audio_lap = al.audio_lap_score
                    JOIN ins_software_lap sl ON fi.software_lap = sl.software_lap_score
                    WHERE fi.no = (SELECT MAX(no) FROM form_inspeksi)");

if (!$query) {
    die("Error in query preparation: " . $conn->error);
}

$query->execute();

if ($query->error) {
    die("Query execution failed: " . $query->error);
}

if ($query->error) {
    die("Query failed: " . $query->error);
}

$result = $query->get_result();

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("No data found in the form_inspeksi table.");
    }
} else {
    die("Result set error: " . $conn->error);
}

$runningNumber = $row['no'] + 1;

$createDate = date('m/Y');

$nomorInspeksi = sprintf("%03d", $runningNumber) . "/MIP/INS/" . $createDate;

$screenshot_files = [];
$target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";

if ($handle = opendir($target_screenshot_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $screenshot_files[] = $entry;
        }
    }
    closedir($handle);
}

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
        $maxWidth = 184; // Maximum width for the image
        $maxHeight = 120; // Maximum height for the image

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        $this->Image($imagePath, 13, null, $newWidth, $newHeight);
        $this->Ln(10); // Add some space after each image
    }
    
    var $widths;
    var $aligns;

    function SetWidths($w) {
        $this->widths=$w;
    }

    function SetAligns($a) {
        $this->aligns=$a;
    }

    function Row($data) {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x=$this->GetX();
            $y=$this->GetY();
            $this->Rect($x,$y,$w,$h);
            $this->MultiCell($w,5,$data[$i],0,$a);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt) {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

    var $columnWidths = [10, 150, 20]; // Set the column widths

    function addTableRow($item, $detail, $skor) {
        $fill = $this->RowNeedsFill();
        $this->Cell($this->columnWidths[0], 10, $item, 1, 0, 'C', $fill);
        $this->MultiCell($this->columnWidths[1], 10, $detail, 1, 'C', $fill);
        $this->Cell($this->columnWidths[2], 10, $skor, 1, 1, 'C', $fill);
    }

    function RowNeedsFill() {
        static $fill = false;
        $fill = !$fill;
        return $fill;
    }
}

$pdf = new MYPDF($screenshot_files, $nomorInspeksi);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetWidths(array(10, 150, 20));
$pdf->SetFont('Arial','',8);
$pdf->SetFillColor(200,220,255);

$headers = array('No', 'Deskripsi', 'Skor');
$widths = array(10, 150, 20);

$pdf->Row($headers);

$pdf->addTableRow('1', $row['age_name'], $row['age_score']);
$pdf->addTableRow('2', $row['casing_lap_name'], $row['casing_lap_score']);
$pdf->addTableRow('3', $row['layar_lap_name'], $row['layar_lap_score']);
$pdf->addTableRow('4', $row['engsel_lap_name'], $row['engsel_lap_score']);
$pdf->addTableRow('5', $row['keyboard_lap_name'], $row['keyboard_lap_score']);
$pdf->addTableRow('6', $row['touchpad_lap_name'], $row['touchpad_lap_score']);
$pdf->addTableRow('7', $row['booting_lap_name'], $row['booting_lap_score']);
$pdf->addTableRow('8', $row['multi_lap_name'], $row['multi_lap_score']);
$pdf->addTableRow('9', $row['tampung_lap_name'], $row['tampung_lap_score']);
$pdf->addTableRow('10', $row['isi_lap_name'], $row['isi_lap_score']);
$pdf->addTableRow('11', $row['port_lap_name'], $row['port_lap_score']);
$pdf->addTableRow('12', $row['audio_lap_name'], $row['audio_lap_score']);
$pdf->addTableRow('13', $row['software_lap_name'], $row['software_lap_score']);

$pdf->AddScreenshots($target_screenshot_dir, $row['no']);

$pdf->Output();
?>
