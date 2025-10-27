<?php
require_once __DIR__ . '/../config/config.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../dashboard.php');
    }
    exit();
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Query untuk mencari user berdasarkan email menggunakan Prepared Statement
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    
    // 's' = string (tipe data email)
    $stmt->bind_param('s', $email);
    
    // Jalankan query
    $stmt->execute();
    
    // Ambil hasil query
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (menggunakan MD5 sesuai database yang ada)
        if (md5($password) == $user['password']) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Redirect sesuai role
            if ($user['role'] == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Email atau password salah!';
    }
    
    // Tutup statement
    $stmt->close();
}

// Return error untuk ditampilkan di UI
return $error;
?>
