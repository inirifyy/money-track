<?php
session_start();
include 'database.php';

// Kalau sudah login, tidak perlu melihat halaman login lagi
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // Prepared statement: mencegah SQL Injection
        $stmt = mysqli_prepare($conn, "SELECT id, nama, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // password_verify membandingkan input dengan hash di database
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - MonTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="halaman-login">

<div class="kartu-kaca p-4 p-md-5">
    <h2 class="fw-bold mb-1">MonTrack</h2>
    <p class="mb-4">Catat pemasukan dan pengeluaranmu.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= h($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['daftar'])): ?>
        <div class="alert alert-success py-2">Akun berhasil dibuat. Silakan login.</div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <input type="text" name="username" class="form-control input-bulat" placeholder="Username" value="<?= h($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="mb-4">
            <input type="password" name="password" class="form-control input-bulat" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-light rounded-pill w-100 py-2 fw-semibold">Masuk</button>
    </form>

    <p class="text-center mt-4 mb-0">Belum punya akun? <a href="register.php" class="link-light fw-semibold">Daftar</a></p>
</div>

</body>
</html>
