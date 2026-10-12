<?php
require 'vendor/autoload.php';   
include 'database.php';
include 'cek_login.php';

use Dompdf\Dompdf;

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT t.tanggal, t.jenis_transaksi, k.nama_kategori, t.nominal, t.keterangan
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id_kategori
    WHERE t.user_id = ?
    ORDER BY t.tanggal DESC, t.id_transaksi DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

// Susun isi PDF sebagai HTML biasa
$total_masuk = 0;
$total_keluar = 0;
$no = 1;
$baris_tabel = "";

while ($row = mysqli_fetch_assoc($hasil)) {
    if ($row['jenis_transaksi'] == 'pemasukan') {
        $total_masuk += $row['nominal'];
    } else {
        $total_keluar += $row['nominal'];
    }

    $baris_tabel .= "<tr>
        <td>" . $no++ . "</td>
        <td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>
        <td>" . h(ucfirst($row['jenis_transaksi'])) . "</td>
        <td>" . h($row['nama_kategori']) . "</td>
        <td style='text-align:right'>Rp " . number_format($row['nominal'], 0, ',', '.') . "</td>
        <td>" . h($row['keterangan']) . "</td>
    </tr>";
}

if ($baris_tabel == "") {
    $baris_tabel = "<tr><td colspan='6' style='text-align:center'>Belum ada transaksi.</td></tr>";
}

$saldo = $total_masuk - $total_keluar;

$html = "
<style>
    body  { font-family: Arial, sans-serif; font-size: 12px; }
    h2    { margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #999; padding: 6px; }
    th    { background: #e5e7eb; }
</style>

<h2>Laporan Transaksi MonTrack</h2>
<p>Nama: " . h($_SESSION['nama']) . "<br>Tanggal cetak: " . date('d/m/Y') . "</p>

<table>
    <tr>
        <th>No</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th>
    </tr>
    $baris_tabel
</table>

<p>
    <b>Total Pemasukan:</b> Rp " . number_format($total_masuk, 0, ',', '.') . "<br>
    <b>Total Pengeluaran:</b> Rp " . number_format($total_keluar, 0, ',', '.') . "<br>
    <b>Saldo:</b> Rp " . number_format($saldo, 0, ',', '.') . "
</p>";

// Ubah HTML menjadi PDF dan kirim ke browser
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('laporan-montrack.pdf', ['Attachment' => true]);
exit;
