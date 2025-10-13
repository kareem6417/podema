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

function clean_text($string) {
    if ($string === null) return '';
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
}

// 2. Koneksi ke Database
$host = "mandiricoal.net"; $user = "podema"; $pass = "Jam10pagi#"; $db = "podema";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 3. Query utama untuk mengambil data inspeksi
$sql = "SELECT fi.*, 
            age.age_name, cl.casing_lap_name, ll.layar_lap_name, el.engsel_lap_name,
            kl.keyboard_lap_name, tl.touchpad_lap_name, bl.booting_lap_name, ml.multi_lap_name,
            tampung.tampung_lap_name, il.isi_lap_name, pl.port_lap_name, al.audio_lap_name,
            sl.software_lap_name
        FROM form_inspeksi fi 
        LEFT JOIN device_age_laptop age ON fi.age = age.age_score
        LEFT JOIN ins_casing_lap cl ON fi.casing_lap = cl.casing_lap_score
        LEFT JOIN ins_layar_lap ll ON fi.layar_lap = ll.layar_lap_score
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
if ($stmt === false) { die("Query preparation failed: " . $conn->error); }

$stmt->bind_param("i", $no_inspeksi);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Tidak ada data ditemukan untuk nomor inspeksi: " . htmlspecialchars($no_inspeksi));
}
$stmt->close();

// 4. Query terpisah untuk mengambil SEMUA screenshot yang relevan
$screenshot_files = [];
$stmt_ss = $conn->prepare("SELECT screenshot_name FROM screenshots WHERE form_no = ?");
$stmt_ss->bind_param("i", $no_inspeksi);
$stmt_ss->execute();
$result_ss = $stmt_ss->get_result();
if ($result_ss) {
    while($ss_row = $result_ss->fetch_assoc()) {
        $screenshot_files[] = $ss_row['screenshot_name'];
    }
}
$stmt_ss->close();
$conn->close();

// ================================================================= //
// KELAS PDF YANG TELAH DIPERBAIKI                                   //
// ================================================================= //
class MYPDF extends FPDF {
    var $widths;
    var $aligns;

    function Header() {
        $this->Image('../assets/images/logos/mandiri.png', 10, 8, 33);
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 10, 'INSPEKSI PERANGKAT', 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Divisi Teknologi Informasi', 0, 1, 'C');
        
        $this->SetFont('helvetica', '', 7);
        $this->SetXY($this->GetPageWidth() - 40, 13); $this->Cell(30, 5, 'Form: MIP/FRM/ITE/005', 0, 1, 'R');
        $this->SetXY($this->GetPageWidth() - 40, 18); $this->Cell(30, 5, 'Revisi: 00', 0, 1, 'R');

        $this->SetLineWidth(0.5);
        $this->Line(10, 32, $this->GetPageWidth() - 10, 32);
        $this->SetLineWidth(0.2);
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    // Fungsi untuk membuat baris tabel yang rapi dan bisa word-wrap
    function SetWidths($w) { $this->widths = $w; }
    function Row($data, $border=1, $fill=false) {
        $nb = 0;
        for($i=0;$i<count($data);$i++) $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, $data[$i], 0, $a, $fill);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }
    function CheckPageBreak($h) { if($this->GetY()+$h>$this->PageBreakTrigger) $this->AddPage($this->CurOrientation); }
    function NbLines($w, $txt) { $cw = &$this->CurrentFont['cw']; if($w==0) $w=$this->w-$this->rMargin-$this->x; $wmax=($w-2*$this->cMargin)*1000/$this->FontSize; $s=str_replace("\r",'',$txt); $nb=strlen($s); if($nb>0 and $s[$nb-1]=="\n") $nb--; $sep=-1; $i=0; $j=0; $l=0; $nl=1; while($i<$nb){ $c=$s[$i]; if($c=="\n"){ $i++; $sep=-1; $j=$i; $l=0; $nl++; continue; } if($c==' ') $sep=$i; $l+=$cw[$c]; if($l>$wmax){ if($sep==-1){ if($i==$j) $i++; } else $i=$sep+1; $sep=-1; $j=$i; $l=0; $nl++; } else $i++; } return $nl; }

    function AddScreenshots($screenshot_files) {
        // Path ke folder screenshot, PASTIKAN INI BENAR
        $target_screenshot_dir = $_SERVER['DOCUMENT_ROOT'] . "/dev-podema/src/screenshot/";
        
        if (!empty($screenshot_files)) {
            $this->Ln(5);
            $this->SetFont('helvetica', 'B', 11);
            $this->Cell(0, 10, 'Bukti Screenshot:', 0, 1, 'L');
            
            foreach ($screenshot_files as $filename) {
                $screenshot_path = $target_screenshot_dir . $filename;
                if (file_exists($screenshot_path)) {
                    if ($this->GetY() + 85 > $this->PageBreakTrigger) { // Check sisa ruang
                        $this->AddPage();
                    }
                    $this->Image($screenshot_path, $this->GetX(), $this->GetY(), 180); // Lebar gambar 180mm
                    $this->Ln(85); // Beri ruang vertikal untuk gambar
                }
            }
        }
    }
}

// ================================================================= //
// PEMBUATAN DOKUMEN PDF                                             //
// ================================================================= //
$pdf = new MYPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Header Info
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'No Inspeksi: ' . $row['no'], 0, 1, 'C');
$pdf->Ln(3);

// Tabel Info Utama
$pdf->SetWidths([47.5, 47.5, 47.5, 47.5]);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Row(['Tanggal', clean_text($row['date']), 'Nama Pengguna', clean_text($row['nama_user'])], 1);
$pdf->Row(['Tipe Perangkat', clean_text($row['jenis']), 'Divisi', clean_text($row['status'])], 1);

$pdf->SetWidths([47.5, 142.5]);
$pdf->Row(['Merk/Nomor Serial', clean_text($row['merk'] . ' / ' . $row['serialnumber'])], 1);
$pdf->Row(['Lokasi Penggunaan', clean_text($row['lokasi'])], 1);
$pdf->Ln(8);

// MultiCell untuk Keluhan, Pemeriksaan, Rekomendasi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Informasi Keluhan/Permasalahan:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['informasi_keluhan']), 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Hasil Pemeriksaan:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['hasil_pemeriksaan']), 1, 'L');
$pdf->Ln(8);

// Tabel Detail Inspeksi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetWidths([40, 110, 40]);
$pdf->Row(['Item', 'Detail', 'Skor'], 1, true);

$pdf->SetFont('helvetica', '', 9);
$pdf->Row(['Usia Perangkat', clean_text($row['age_name']), $row['age']]);
$pdf->Row(['Casing', clean_text($row['casing_lap_name']), $row['casing_lap']]);
$pdf->Row(['Layar', clean_text($row['layar_lap_name']), $row['layar_lap']]);
$pdf->Row(['Engsel', clean_text($row['engsel_lap_name']), $row['engsel_lap']]);
$pdf->Row(['Keyboard', clean_text($row['keyboard_lap_name']), $row['keyboard_lap']]);
$pdf->Row(['Touchpad', clean_text($row['touchpad_lap_name']), $row['touchpad_lap']]);
$pdf->Row(['Proses Booting', clean_text($row['booting_lap_name']), $row['booting_lap']]);
$pdf->Row(['Multitasking Apps', clean_text($row['multi_lap_name']), $row['multi_lap']]);
$pdf->Row(['Kapasitas Baterai', clean_text($row['tampung_lap_name']), $row['tampung_lap']]);
$pdf->Row(['Waktu Charging', clean_text($row['isi_lap_name']), $row['isi_lap']]);
$pdf->Row(['Port', clean_text($row['port_lap_name']), $row['port_lap']]);
$pdf->Row(['Audio', clean_text($row['audio_lap_name']), $row['audio_lap']]);
$pdf->Row(['Software', clean_text($row['software_lap_name']), $row['software_lap']]);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetWidths([150, 40]);
$pdf->Row(['Total Skor', $row['score']], 1);
$pdf->Ln(8);

// Rekomendasi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Rekomendasi:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['rekomendasi']), 1, 'L');

// ================================================================= //
// MEMANGGIL FUNGSI SCREENSHOT YANG SUDAH DIPERBAIKI                 //
// ================================================================= //
$pdf->AddScreenshots($screenshot_files);


// Tanda Tangan
$pdf->SetY($pdf->GetPageHeight() - 50); // Posisi absolut dari bawah
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(95, 7, 'ITE Division', 0, 0, 'C');
$pdf->Cell(95, 7, 'Nama Pengguna', 0, 1, 'C');
$pdf->Ln(15);
$pdf->Cell(95, 7, '(______________________)', 0, 0, 'C');
$pdf->Cell(95, 7, '( ' . clean_text($row['nama_user']) . ' )', 0, 1, 'C');


// Output PDF
$filename = "Inspection-Laptop-" . clean_text($row['nama_user']) . "-" . $row['date'] . ".pdf";
$pdf->Output('D', $filename);
exit;
?>