<?php
include 'database.php';
include 'cek_login.php';

$user_id = $_SESSION['user_id'];

// Nilai pencarian & filter dari URL (method GET)
$cari = trim($_GET['cari'] ?? '');
$kat  = (int)($_GET['kategori'] ?? 0);   // 0 = semua kategori
$like = "%" . $cari . "%";

// Daftar kategori untuk dropdown filter
$daftar_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori");

// Ambil transaksi milik user, urut tanggal terbaru
// (? = 0 OR ...) artinya: kalau kategori = 0, tampilkan semua kategori
$stmt = mysqli_prepare($conn, "
    SELECT t.*, k.nama_kategori
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id_kategori
    WHERE t.user_id = ?
        AND t.keterangan LIKE ?
        AND (? = 0 OR t.kategori_id = ?)
    ORDER BY t.tanggal DESC, t.id_transaksi DESC");
mysqli_stmt_bind_param($stmt, "isii", $user_id, $like, $kat, $kat);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

// Pesan notifikasi setelah tambah/edit/hapus
$pesan_list = [
    'tambah' => 'Transaksi berhasil ditambahkan.',
    'edit'   => 'Transaksi berhasil diperbarui.',
    'hapus'  => 'Transaksi berhasil dihapus.',
];
$pesan = $pesan_list[$_GET['pesan'] ?? ''] ?? '';
$no = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Transaksi - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="konten">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Data Transaksi</h3>
        <a href="tambah.php" class="btn btn-utama">+ Tambah</a>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-success"><?= h($pesan) ?></div>
    <?php endif; ?>

    <!-- Form pencarian & filter -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="cari" class="form-control rounded-pill" placeholder="Cari keterangan..." value="<?= h($cari) ?>">
        </div>
        <div class="col-md-4">
            <select name="kategori" class="form-select rounded-pill">
                <option value="0">Semua kategori</option>
                <?php while ($k = mysqli_fetch_assoc($daftar_kategori)): ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= $kat == $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= h($k['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-utama flex-fill">Cari</button>
            <a href="transaksi.php" class="btn btn-outline-secondary rounded-pill">Reset</a>
        </div>
    </form>

    <div class="kartu p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($hasil) == 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi yang cocok.</td></tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <?php if ($row['jenis_transaksi'] == 'pemasukan'): ?>
                                <span class="badge bg-success">Pemasukan</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pengeluaran</span>
                            <?php endif; ?>
                        </td>
                        <td><?= h($row['nama_kategori']) ?></td>
                        <td><?= h($row['keterangan']) ?></td>
                        <td class="text-end">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td class="text-center text-nowrap">
                            <a href="edit.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-warning rounded-pill">Edit</a>

                            <!-- Hapus memakai POST agar lebih aman daripada link biasa -->
                            <form action="hapus.php" method="post" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                <input type="hidden" name="id" value="<?= $row['id_transaksi'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger rounded-pill">Hapus</button>
                            </form>
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
