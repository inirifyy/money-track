<?php
// sidebar.php - di-include oleh semua halaman setelah login
// basename(...) dipakai untuk menandai menu yang sedang aktif
$halaman = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="brand">MonTrack</div>
    <div class="user-info mb-4">Halo, <?= h($_SESSION['nama']) ?></div>

    <a href="index.php"     class="<?= $halaman == 'index.php' ? 'aktif' : '' ?>">Dashboard</a>
    <a href="transaksi.php" class="<?= in_array($halaman, ['transaksi.php', 'edit.php']) ? 'aktif' : '' ?>">Data Transaksi</a>
    <a href="tambah.php"    class="<?= $halaman == 'tambah.php' ? 'aktif' : '' ?>">Tambah Transaksi</a>
    <a href="laporan.php"   class="<?= $halaman == 'laporan.php' ? 'aktif' : '' ?>">Laporan</a>

    <a href="logout.php" class="logout mt-auto" onclick="return confirm('Halaman akan keluar, anda yakin?')">Logout</a>
</div>
