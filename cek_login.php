<?php
// cek_login.php - taruh di bagian atas setiap halaman yang wajib login
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
