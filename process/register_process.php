<?php
require_once __DIR__ . '/../config/config.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Proses register HANYA jika ada POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil dan sanitasi input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek apakah email sudah ada menggunakan Prepared Statement
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        
        if (!$stmt) {
            $error = 'Error database: ' . $conn->error;
        } else {
            // 's' = string (tipe data email)
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = 'Email sudah terdaftar! Silakan gunakan email lain atau login.';
                $stmt->close();
            } else {
                $stmt->close();
                
                // Hash password menggunakan MD5 (sesuai database yang ada)
                $hashed_password = md5($password);
                
                // Insert user baru dengan role 'user' menggunakan Prepared Statement
                $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                
                if (!$stmt_insert) {
                    $error = 'Error database: ' . $conn->error;
                } else {
                    // 's' = string (name)
                    // 's' = string (email)
                    // 's' = string (password)
                    $stmt_insert->bind_param('sss', $name, $email, $hashed_password);
                    
                    if ($stmt_insert->execute()) {
                        // Registrasi SUKSES
                        $_SESSION['register_success'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                        $stmt_insert->close();
                        
                        // REDIRECT ke halaman login
                        header('Location: ../auth/login.php');
                        exit();
                    } else {
                        $error = 'Terjadi kesalahan saat menyimpan data: ' . $stmt_insert->error;
                        $stmt_insert->close();
                    }
                }
            }
        }
    }
}

// Jika ada error, simpan ke session untuk ditampilkan
if (!empty($error)) {
    $_SESSION['register_error'] = $error;
}

// Return data untuk ditampilkan di UI (jika tidak redirect)
return [
    'error' => $error,
    'success' => $success
];
?>
