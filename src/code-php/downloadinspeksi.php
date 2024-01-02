<?php

require('../fpdf/fpdf.php');

$host = "mandiricoal.net";
$user = "podema"; 
$pass = "Jam10pagi#";
$db = "podema";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$result = mysqli_query($conn, "SELECT form_inspeksi.*, ins_audio_lap.audio_lap_name, ins_booting_lap.booting_lap_name, ins_casing_lap.casing_lap_name, ins_engsel_lap.engsel_lap_name, ins_ink_pad.ink_pad_name, ins_isi_lap.isi_lap_name, ins_keyboard_lap.keyboard_lap_name,
                                ins_layar_lap.layar_lap_name, ins_multi_lap.multi_lap_name, ins_port_lap.port_lap_name, ins_software_lap.software_lap_name, ins_tampung_lap.tampung_lap_name, ins_touchpad_lap.touchpad_lap_name
                                FROM form_inspeksi
                                JOIN ins_audio_lap ON form_inspeksi.audio_lap = ins_audio_lap.audio_lap_score
                                JOIN ins_booting_lap ON form_inspeksi.booting_lap = ins_booting_lap.booting_lap_score
                                JOIN ins_casing_lap ON form_inspeksi.casing_lap = ins_casing_lap.casing_lap_score
                                JOIN ins_engsel_lap ON form_inspeksi.engsel_lap = ins_engsel_lap.engsel_lap_score
                                JOIN ins_ink_pad ON form_inspeksi.ink_pad = ins_ink_pad.ink_pad_score
                                JOIN ins_isi_lap ON form_inspeksi.isi_lap = ins_isi_lap.isi_lap_score
                                JOIN ins_keyboard_lap ON form_inspeksi.keyboard_lap = ins_keyboard_lap.keyboard_lap_score
                                JOIN ins_layar_lap ON form_inspeksi.layar_lap = ins_layar_lap.layar_lap_score
                                JOIN ins_multi_lap ON form_inspeksi.multi_lap = ins_multi_lap.multi_lap_score
                                JOIN ins_port_lap ON form_inspeksi.port_lap = ins_port_lap.port_lap_score
                                JOIN ins_software_lap ON form_inspeksi.software_lap = ins_software_lap.software_lap_score
                                JOIN ins_tampung_lap ON form_inspeksi.tampung_lap = ins_tampung_lap.tampung_lap_score
                                JOIN ins_touchpad_lap ON form_inspeksi.touchpad_lap = ins_touchpad_lap.touchpad_lap_score
                                ORDER BY form_inspeksi.no DESC 
                                LIMIT 1");

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$row = mysqli_fetch_array($result);

if ($row && is_array($row)) {
    // Sekarang Anda dapat mengakses elemen dari $row tanpa menyebabkan kesalahan.
} else {
    die("Data tidak ditemukan atau terjadi kesalahan lainnya.");
}

$runningNumber = $row['no'] + 1;

$createDate = date('m/Y', strtotime($row['date']));

$nomorInspeksi = sprintf("%03d", $runningNumber) . "/MIP/INS/" . $createDate;

class PDF extends FPDF {
    private $currentPage = 1;
    private $jenis; 

    function __construct($jenis) {
        $this->jenis = $jenis;
        parent::__construct();
    }

    function Header() {
        $this->Image('../assets/images/logos/mandiri.png',10,8,33);
    }    

    function addHeaderContent($nomorInspeksi) {
        $logo_height = 33;
        $this->SetFont('helvetica', 'B', 15);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 13);
        $this->Cell(83, 5, 'FORMULIR PEMERIKSAAN PERANGKAT', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', '', 9);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 22);
        $this->Cell(80, 5, 'Divisi Teknologi Informasi', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', '', 7);
        $this->SetXY(($this->GetPageWidth() - 20) / 2, 13);
        $this->Cell(100, 5, 'Formulir: MIP/FRM/ITE/005', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->SetXY(($this->GetPageWidth() - 20) / 2, 18);
        $this->Cell(100, 5, 'Revisi: 00', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->SetLineWidth(0.5);
        $this->Line(10, -2 + $logo_height + 0, $this->GetPageWidth() - 10, -2 + $logo_height + 0);
        $this->SetLineWidth(0.2);

        $this->SetFont('helvetica', '', 11);
        $this->SetXY(($this->GetPageWidth() - 80) / 2, 40);
        $this->Cell(80, -2, 'Nomor Pemeriksaan: ' . $nomorInspeksi, 0, false, 'C', 0, '', 0, false, 'M', 'M');        
        $this->Ln(2);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }    

    function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false) {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->currentPage++;

        if ($this->jenis == "Laptop") {
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
            $engsel_lap = isset($_POST["engsel_lap"]) ? $_POST["engsel_lap"] : '';
            $keyboard_lap = isset($_POST["keyboard_lap"]) ? $_POST["keyboard_lap"] : '';
            $touchpad_lap = isset($_POST["touchpad_lap"]) ? $_POST["touchpad_lap"] : '';
            $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
            $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
            $tampung_lap = isset($_POST["tampung_lap"]) ? $_POST["tampung_lap"] : '';
            $isi_lap = isset($_POST["isi_lap"]) ? $_POST["isi_lap"] : '';
            $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
            $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
            $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';
        } elseif ($this->jenis == "PC Desktop") {
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
            $keyboard_lap = isset($_POST["keyboard_lap"]) ? $_POST["keyboard_lap"] : '';
            $booting_lap = isset($_POST["booting_lap"]) ? $_POST["booting_lap"] : '';
            $multi_lap = isset($_POST["multi_lap"]) ? $_POST["multi_lap"] : '';
            $port_lap = isset($_POST["port_lap"]) ? $_POST["port_lap"] : '';
            $audio_lap = isset($_POST["audio_lap"]) ? $_POST["audio_lap"] : '';
            $software_lap = isset($_POST["software_lap"]) ? $_POST["software_lap"] : '';        
        } elseif ($this->jenis == "Monitor") {
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $layar_lap = isset($_POST["layar_lap"]) ? $_POST["layar_lap"] : '';
        } elseif ($this->jenis == "Printer") {
            $casing_lap = isset($_POST["casing_lap"]) ? $_POST["casing_lap"] : '';
            $ink_pad = isset($_POST["ink_pad"]) ? $_POST["ink_pad"] : '';
        }
    }
}

$jenis = $row['jenis']; 
$pdf = new PDF($jenis); 
$pdf->AddPage(); 
$pdf->addHeaderContent($nomorInspeksi);

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
$pdf->Cell($cellWidth, 10, 'Nama:', 1, 0, 'L', false);
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
$pdf->Cell($cellWidth * 2, 10, 'Merk/Nomor Seri:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth * 2, 10, $row['merk'] . ' / ' . $row['serialnumber'], 1, 1, 'L', false); 

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($cellWidth * 2, 10, 'Lokasi/Area Penggunaan:', 1, 0, 'L', false);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell($cellWidth * 2, 10, $row['lokasi'], 1, 1, 'L', false);
$pdf->Ln(10);

if ($jenis == "Laptop") {
    $pdf->Cell(80, 10, 'casing_lap: ' . $casing_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'layar_lap: ' . $layar_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'engsel_lap: ' . $engsel_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'keyboard_lap: ' . $keyboard_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'touchpad_lap: ' . $touchpad_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'booting_lap: ' . $booting_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'multi_lap: ' . $multi_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'isi_lap: ' . $isi_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'port_lap: ' . $port_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'audio_lap: ' . $audio_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'software_lap: ' . $software_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'tampung_lap: ' . $tampung_lap, 0, 1, 'L', false);
} elseif ($jenis == "PC Desktop") {
    $pdf->Cell(80, 10, 'casing_lap: ' . $casing_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'layar_lap: ' . $layar_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'keyboard_lap: ' . $keyboard_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'booting_lap: ' . $booting_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'multi_lap: ' . $multi_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'port_lap: ' . $port_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'audio_lap: ' . $audio_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'software_lap: ' . $software_lap, 0, 1, 'L', false);
} elseif ($jenis == "Monitor") {
    $pdf->Cell(80, 10, 'casing_lap: ' . $casing_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'layar_lap: ' . $layar_lap, 0, 1, 'L', false);
} elseif ($jenis == "Printer") {
    $pdf->Cell(80, 10, 'casing_lap: ' . $casing_lap, 0, 1, 'L', false);
    $pdf->Cell(80, 10, 'ink_pad: ' . $ink_pad, 0, 1, 'L', false);
}

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

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(47.5, 10, 'Checked By', 'T', 0, 'L');
$pdf->Cell(5, 10, '', 0, 0, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(47.5, 10, 'Device Owner', 'T', 1, 'C');

$filename = "Inspection-Devices.pdf";
$pdf->Output($filename, 'D');
echo '<a href="Inspection-Devices.pdf">Download</a>';
exit;
?>