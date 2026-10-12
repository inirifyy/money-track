<?php
include 'database.php';
include 'cek_login.php';

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT
        COALESCE(SUM(CASE WHEN jenis_transaksi = 'pemasukan'   THEN nominal END), 0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis_transaksi = 'pengeluaran' THEN nominal END), 0) AS total_keluar
    FROM transaksi
    WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$total_masuk  = $total['total_masuk'];
$total_keluar = $total['total_keluar'];
$saldo        = $total_masuk - $total_keluar;

$stmt = mysqli_prepare($conn, "
    SELECT t.*, k.nama_kategori
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id_kategori
    WHERE t.user_id = ?
    ORDER BY t.tanggal DESC, t.id_transaksi DESC
    LIMIT 5");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$terbaru = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,600,700" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Gudea:400,400i,700|Poppins:300,400,500,600,700|Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="konten">
    <h3 class="fw-bold mb-4">Dashboard</h3>

    <!-- Kartu ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kartu kartu-ringkas">
                <div class="label">Total Pemasukan</div>
                <div class="angka text-success">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kartu kartu-ringkas">
                <div class="label">Total Pengeluaran</div>
                <div class="angka text-danger">Rp <?= number_format($total_keluar, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kartu kartu-ringkas">
                <div class="label">Saldo</div>
                <div class="angka">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- Transaksi terbaru -->
    <div class="kartu p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Transaksi Terbaru</h5>
            <a href="transaksi.php" class="btn btn-sm btn-outline-secondary rounded-pill">Lihat semua</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($terbaru) == 0): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi. <a href="tambah.php">Tambah transaksi pertama</a>.</td></tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($terbaru)): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= h($row['nama_kategori']) ?></td>
                        <td><?= h($row['keterangan']) ?></td>
                        <td class="text-end fw-semibold <?= $row['jenis_transaksi'] == 'pemasukan' ? 'text-success' : 'text-danger' ?>">
                            <?= $row['jenis_transaksi'] == 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
