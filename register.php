<?php
session_start();
include 'database.php';

$error = "";
$nama = $username = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        $error = "Semua kolom wajib diisi.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        // Cek apakah username sudah dipakai
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);

        if (mysqli_fetch_assoc(mysqli_stmt_get_result($cek))) {
            $error = "Username sudah dipakai, coba yang lain.";
        } else {
            // Password diubah menjadi hash sebelum disimpan
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $nama, $username, $hash);
            mysqli_stmt_execute($stmt);

            header("Location: login.php?daftar=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="halaman-login">

<div class="kartu-kaca p-4 p-md-5">
    <h2 class="fw-bold mb-1">Buat Akun</h2>
    <p class="mb-4">Daftar untuk mulai memakai MonTrack.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <input type="text" name="nama" class="form-control input-bulat" placeholder="Nama lengkap" value="<?= h($nama) ?>" required>
        </div>
        <div class="mb-3">
            <input type="text" name="username" class="form-control input-bulat" placeholder="Username" value="<?= h($username) ?>" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control input-bulat" placeholder="Password (min. 6 karakter)" required>
        </div>
        <div class="mb-4">
            <input type="password" name="konfirmasi" class="form-control input-bulat" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn btn-light rounded-pill w-100 py-2 fw-semibold">Daftar</button>
    </form>

    <p class="text-center mt-4 mb-0">Sudah punya akun? <a href="login.php" class="link-light fw-semibold">Masuk</a></p>
</div>

</body>
</html>
