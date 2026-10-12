<?php
include 'database.php';
include 'cek_login.php';

$user_id = $_SESSION['user_id'];

// Ambil kategori untuk dropdown
$daftar_kategori = mysqli_fetch_all(
    mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori"),
    MYSQLI_ASSOC
);

// Nilai awal form
$error       = "";
$tanggal     = date('Y-m-d');
$jenis       = "pengeluaran";
$kategori_id = "";
$nominal     = "";
$keterangan  = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal     = $_POST['tanggal'] ?? '';
    $jenis       = $_POST['jenis_transaksi'] ?? '';
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $nominal     = $_POST['nominal'] ?? '';
    $keterangan  = trim($_POST['keterangan'] ?? '');

    // --- Validasi input ---
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    $tanggal_valid = $d && $d->format('Y-m-d') === $tanggal;

    $id_kategori_ada = in_array($kategori_id, array_column($daftar_kategori, 'id_kategori'));

    if (!$tanggal_valid) {
        $error = "Tanggal tidak valid.";
    } elseif (!in_array($jenis, ['pemasukan', 'pengeluaran'])) {
        $error = "Jenis transaksi tidak valid.";
    } elseif (!$id_kategori_ada) {
        $error = "Pilih kategori terlebih dahulu.";
    } elseif (!is_numeric($nominal) || $nominal <= 0) {
        $error = "Nominal harus berupa angka lebih dari 0.";
    } elseif (strlen($keterangan) > 255) {
        $error = "Keterangan maksimal 255 karakter.";
    } else {
        $nominal = (float)$nominal;

        $stmt = mysqli_prepare($conn, "
            INSERT INTO transaksi (user_id, kategori_id, jenis_transaksi, nominal, tanggal, keterangan)
            VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisdss", $user_id, $kategori_id, $jenis, $nominal, $tanggal, $keterangan);
        mysqli_stmt_execute($stmt);

        header("Location: transaksi.php?pesan=tambah");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Transaksi - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="konten">
    <h3 class="fw-bold mb-4">Tambah Transaksi</h3>

    <div class="kartu p-4" style="max-width: 600px;">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= h($tanggal) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Transaksi</label>
                <select name="jenis_transaksi" class="form-select" required>
                    <option value="pemasukan"   <?= $jenis == 'pemasukan'   ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="pengeluaran" <?= $jenis == 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="kategori_id" class="form-select" required>
                    <option value="">-- Pilih kategori --</option>
                    <?php foreach ($daftar_kategori as $k): ?>
                        <option value="<?= $k['id_kategori'] ?>" <?= $kategori_id == $k['id_kategori'] ? 'selected' : '' ?>>
                            <?= h($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nominal (Rp)</label>
                <input type="number" name="nominal" class="form-control" min="1" step="any" value="<?= h($nominal) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" class="form-control" maxlength="255" value="<?= h($keterangan) ?>">
            </div>

            <button type="submit" class="btn btn-utama">Simpan</button>
            <a href="transaksi.php" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </form>
    </div>
</div>

</body>
</html>
