<?php
// database.php - koneksi PHP ke MySQL (di-include oleh semua halaman)

$host = "localhost";
$user = "root";
$pass = "";          // default XAMPP: kosong
$db   = "montrack";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Fungsi kecil untuk menampilkan teks dengan aman (mencegah XSS)
function h($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
