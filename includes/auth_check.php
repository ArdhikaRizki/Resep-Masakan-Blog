<?php
// File untuk mengecek apakah user sudah login
// Include file ini di halaman yang memerlukan autentikasi

require_once __DIR__ . '/../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fungsi untuk cek role admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Fungsi untuk cek role user
function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'user';
}

// Fungsi untuk require admin role
function require_admin() {
    if (!is_admin()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

// Fungsi untuk require user role
function require_user() {
    if (!is_user()) {
        header('Location: ../auth/login.php');
        exit();
    }
}
?>
