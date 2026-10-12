<?php
include 'database.php';
include 'cek_login.php';

// Hanya menerima permintaan POST (dari tombol Hapus di transaksi.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // "AND user_id = ?" memastikan user tidak bisa menghapus data orang lain
    $stmt = mysqli_prepare($conn, "DELETE FROM transaksi WHERE id_transaksi = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: transaksi.php?pesan=hapus");
    exit;
}

header("Location: transaksi.php");
exit;
