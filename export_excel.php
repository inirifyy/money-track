<?php
require 'vendor/autoload.php';   // dibuat otomatis oleh Composer
include 'database.php';
include 'cek_login.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$user_id = $_SESSION['user_id'];

// Ambil semua transaksi milik user
$stmt = mysqli_prepare($conn, "
    SELECT t.tanggal, t.jenis_transaksi, k.nama_kategori, t.nominal, t.keterangan
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id_kategori
    WHERE t.user_id = ?
    ORDER BY t.tanggal DESC, t.id_transaksi DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

// Buat file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transaksi');

// Judul
$sheet->setCellValue('A1', 'Laporan Transaksi MonTrack - ' . $_SESSION['nama']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Header tabel (baris 3)
$sheet->setCellValue('A3', 'No');
$sheet->setCellValue('B3', 'Tanggal');
$sheet->setCellValue('C3', 'Jenis');
$sheet->setCellValue('D3', 'Kategori');
$sheet->setCellValue('E3', 'Nominal');
$sheet->setCellValue('F3', 'Keterangan');
$sheet->getStyle('A3:F3')->getFont()->setBold(true);

// Isi data mulai baris 4
$baris = 4;
$no = 1;
$total_masuk = 0;
$total_keluar = 0;

while ($row = mysqli_fetch_assoc($hasil)) {
    $sheet->setCellValue('A' . $baris, $no++);
    $sheet->setCellValue('B' . $baris, date('d/m/Y', strtotime($row['tanggal'])));
    $sheet->setCellValue('C' . $baris, ucfirst($row['jenis_transaksi']));
    $sheet->setCellValue('D' . $baris, $row['nama_kategori']);
    $sheet->setCellValue('E' . $baris, (float)$row['nominal']);
    $sheet->setCellValue('F' . $baris, $row['keterangan']);

    if ($row['jenis_transaksi'] == 'pemasukan') {
        $total_masuk += $row['nominal'];
    } else {
        $total_keluar += $row['nominal'];
    }
    $baris++;
}

// Ringkasan di bawah tabel
$baris++;
$sheet->setCellValue('D' . $baris, 'Total Pemasukan');
$sheet->setCellValue('E' . $baris, $total_masuk);
$sheet->setCellValue('D' . ($baris + 1), 'Total Pengeluaran');
$sheet->setCellValue('E' . ($baris + 1), $total_keluar);
$sheet->setCellValue('D' . ($baris + 2), 'Saldo');
$sheet->setCellValue('E' . ($baris + 2), $total_masuk - $total_keluar);
$sheet->getStyle('D' . $baris . ':E' . ($baris + 2))->getFont()->setBold(true);

// Format angka dan lebar kolom otomatis
$sheet->getStyle('E4:E' . ($baris + 2))->getNumberFormat()->setFormatCode('#,##0');
foreach (range('A', 'F') as $kolom) {
    $sheet->getColumnDimension($kolom)->setAutoSize(true);
}

// Kirim ke browser sebagai file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan-montrack.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
