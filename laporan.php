<?php
include 'database.php';
include 'cek_login.php';

$user_id = $_SESSION['user_id'];

// Total pemasukan & pengeluaran (untuk grafik)
$stmt = mysqli_prepare($conn, "
    SELECT
        COALESCE(SUM(CASE WHEN jenis_transaksi = 'pemasukan'   THEN nominal END), 0) AS total_masuk,
        COALESCE(SUM(CASE WHEN jenis_transaksi = 'pengeluaran' THEN nominal END), 0) AS total_keluar
    FROM transaksi
    WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$total_masuk  = (float)$total['total_masuk'];
$total_keluar = (float)$total['total_keluar'];

// Ringkasan per kategori
$stmt = mysqli_prepare($conn, "
    SELECT k.nama_kategori, t.jenis_transaksi, COUNT(*) AS jumlah, SUM(t.nominal) AS total
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id_kategori
    WHERE t.user_id = ?
    GROUP BY k.nama_kategori, t.jenis_transaksi
    ORDER BY total DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$per_kategori = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="konten">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h3 class="fw-bold mb-0">Laporan</h3>
        <div class="d-flex gap-2">
            <a href="export_excel.php" class="btn btn-success rounded-pill">Ekspor Excel</a>
            <a href="export_pdf.php" class="btn btn-danger rounded-pill">Ekspor PDF</a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Grafik -->
        <div class="col-lg-6">
            <div class="kartu p-3">
                <h5>Pemasukan vs Pengeluaran</h5>
                <canvas id="grafik"></canvas>
            </div>
        </div>

        <!-- Ringkasan per kategori -->
        <div class="col-lg-6">
            <div class="kartu p-3">
                <h5>Ringkasan per Kategori</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($per_kategori) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
                        <?php endif; ?>

                        <?php while ($row = mysqli_fetch_assoc($per_kategori)): ?>
                            <tr>
                                <td><?= h($row['nama_kategori']) ?></td>
                                <td><?= h(ucfirst($row['jenis_transaksi'])) ?></td>
                                <td><?= $row['jumlah'] ?>x</td>
                                <td class="text-end">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const totalMasuk  = <?= $total_masuk ?>;
    const totalKeluar = <?= $total_keluar ?>;

    new Chart(document.getElementById('grafik'), {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                label: 'Total (Rp)',
                data: [totalMasuk, totalKeluar],
                backgroundColor: ['#16a34a', '#dc2626'],
                borderRadius: 10
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

</body>
</html>
