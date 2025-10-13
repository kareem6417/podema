<?php
session_start();
require_once('../fpdf/fpdf.php');

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
            age.age_name, cl.casing_lap_name, ip.layar_lap_name, el.engsel_lap_name,
            kl.keyboard_lap_name, tl.touchpad_lap_name, bl.booting_lap_name, ml.multi_lap_name,
            tampung.tampung_lap_name, il.isi_lap_name, pl.port_lap_name, al.audio_lap_name,
            sl.software_lap_name
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

// 4. Query terpisah untuk mengambil semua screenshot terkait
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

class MYPDF extends FPDF {
    function Header() {
        $this->Image('../assets/images/logos/mandiri.png', 10, 8, 33);
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 10, 'INSPEKSI PERANGKAT', 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Divisi Teknologi Informasi', 0, 1, 'C');
        
        $this->SetFont('helvetica', '', 7);
        $this->SetXY($this->GetPageWidth() - 40, 13);
        $this->Cell(30, 5, 'Form: MIP/FRM/ITE/005', 0, 1, 'R');
        $this->SetXY($this->GetPageWidth() - 40, 18);
        $this->Cell(30, 5, 'Revisi: 00', 0, 1, 'R');

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

    function addTableRow($item, $detail, $skor) {
        $this->Cell(40, 10, $item, 1, 0, 'L');
        $this->Cell(110, 10, clean_text($detail), 1, 0, 'L');
        $this->Cell(40, 10, $skor, 1, 1, 'C');
    }

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
                    // Cek sisa ruang di halaman
                    if ($this->GetY() + 80 > $this->PageBreakTrigger) {
                        $this->AddPage();
                    }
                    $this->Image($screenshot_path, $this->GetX(), $this->GetY(), 180); // Lebar gambar 180mm
                    $this->Ln(80); // Sesuaikan spasi setelah gambar
                }
            }
        }
    }
}

// ================================================================= //
// PEMBUATAN DOKUMEN PDF                                             //
// ================================================================= //
$pdf = new MYPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Header Info
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'No Inspeksi: ' . $row['no'], 0, 1, 'C');
$pdf->Ln(3);

// Tabel Info Utama
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Tanggal', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(47.5, 7, $row['date'], 1);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Nama Pengguna', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(47.5, 7, clean_text($row['nama_user']), 1, 1);
// Baris berikutnya
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Tipe Perangkat', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(47.5, 7, clean_text($row['jenis']), 1);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Divisi', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(47.5, 7, clean_text($row['status']), 1, 1);
// Baris berikutnya
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Merk/Nomor Serial', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(142.5, 7, clean_text($row['merk'] . ' / ' . $row['serialnumber']), 1, 1);
// Baris berikutnya
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(47.5, 7, 'Lokasi Penggunaan', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(142.5, 7, clean_text($row['lokasi']), 1, 1);
$pdf->Ln(8);

// MultiCell untuk Keluhan, Pemeriksaan, Rekomendasi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Informasi Keluhan/Permasalahan:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['informasi_keluhan']), 0, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Hasil Pemeriksaan:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['hasil_pemeriksaan']), 0, 'L');
$pdf->Ln(8);

// Tabel Detail Inspeksi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(40, 10, 'Item', 1, 0, 'C', true);
$pdf->Cell(110, 10, 'Detail', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Skor', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->addTableRow('Usia Perangkat', $row['age_name'], $row['age']);
$pdf->addTableRow('Casing', $row['casing_lap_name'], $row['casing_lap']);
$pdf->addTableRow('Layar', $row['layar_lap_name'], $row['layar_lap']);
$pdf->addTableRow('Engsel', $row['engsel_lap_name'], $row['engsel_lap']);
$pdf->addTableRow('Keyboard', $row['keyboard_lap_name'], $row['keyboard_lap']);
$pdf->addTableRow('Touchpad', $row['touchpad_lap_name'], $row['touchpad_lap']);
$pdf->addTableRow('Proses Booting', $row['booting_lap_name'], $row['booting_lap']);
$pdf->addTableRow('Multitasking Apps', $row['multi_lap_name'], $row['multi_lap']);
$pdf->addTableRow('Kapasitas Baterai', $row['tampung_lap_name'], $row['tampung_lap']);
$pdf->addTableRow('Waktu Charging', $row['isi_lap_name'], $row['isi_lap']);
$pdf->addTableRow('Port', $row['port_lap_name'], $row['port_lap']);
$pdf->addTableRow('Audio', $row['audio_lap_name'], $row['audio_lap']);
$pdf->addTableRow('Software', $row['software_lap_name'], $row['software_lap']);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(150, 10, 'Total Skor', 1, 0, 'C');
$pdf->Cell(40, 10, $row['score'], 1, 1, 'C');
$pdf->Ln(8);

// Rekomendasi
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Rekomendasi:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(190, 5, clean_text($row['rekomendasi']), 0, 'L');

// Panggil fungsi untuk menambahkan screenshot
$pdf->AddScreenshots($screenshot_files);

// Tanda Tangan
$pdf->Ln(15); 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(95, 7, 'ITE Division', 0, 0, 'C');
$pdf->Cell(95, 7, 'Nama Pengguna', 0, 1, 'C');
$pdf->Ln(20);
$pdf->Cell(95, 7, '(______________________)', 0, 0, 'C');
$pdf->Cell(95, 7, '( ' . clean_text($row['nama_user']) . ' )', 0, 1, 'C');


// Output PDF
$filename = "Inspection-Laptop-" . clean_text($row['nama_user']) . "-" . $row['date'] . ".pdf";
$pdf->Output('D', $filename);
exit;
?>