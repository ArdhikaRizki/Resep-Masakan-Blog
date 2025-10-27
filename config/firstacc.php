<?php
/**
 * First Account Initialization
 * File untuk membuat akun admin dan user default pertama kali
 * Hanya dijalankan sekali saat setup awal
 */

require_once __DIR__ . '/config.php';

// Flag untuk mengecek apakah sudah ada user
$check_query = "SELECT COUNT(*) as total FROM users";
$check_result = mysqli_query($conn, $check_query);
$user_count = mysqli_fetch_assoc($check_result)['total'];

// Jika sudah ada user, stop eksekusi
if ($user_count > 0) {
    echo "Akun sudah ada! Total user: {$user_count}\n";
    echo "File ini hanya untuk inisiasi pertama kali.\n";
    echo " Jika ingin reset, hapus semua data di tabel users terlebih dahulu.\n";
    exit;
}

echo "Memulai inisiasi akun...\n\n";

// ==========================================
// AKUN ADMIN DEFAULT
// ==========================================
$admin_name = "Administrator";
$admin_email = "admin@resepmasakan.com";
$admin_password = "admin123"; // Password: admin123
$admin_password_hash = md5($admin_password);
$admin_role = "admin";

$stmt_admin = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt_admin->bind_param('ssss', $admin_name, $admin_email, $admin_password_hash, $admin_role);

if ($stmt_admin->execute()) {
    echo " Akun ADMIN berhasil dibuat:\n";
    echo "    Email    : {$admin_email}\n";
    echo "    Password : {$admin_password}\n";
    echo "    Role     : {$admin_role}\n\n";
} else {
    echo " Gagal membuat akun admin: " . $stmt_admin->error . "\n\n";
}
$stmt_admin->close();

// ==========================================
// AKUN USER DEFAULT
// ==========================================
$user_name = "Dika Pratama";
$user_email = "dika@example.com";
$user_password = "12345"; // Password: 12345
$user_password_hash = md5($user_password);
$user_role = "user";

$stmt_user = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt_user->bind_param('ssss', $user_name, $user_email, $user_password_hash, $user_role);

if ($stmt_user->execute()) {
    echo " Akun USER berhasil dibuat:\n";
    echo "    Email    : {$user_email}\n";
    echo "    Password : {$user_password}\n";
    echo "    Role     : {$user_role}\n\n";
} else {
    echo "❌ Gagal membuat akun user: " . $stmt_user->error . "\n\n";
}
$stmt_user->close();

// ==========================================
// SUMMARY
// ==========================================
echo "═══════════════════════════════════════════════════════════\n";
echo " INISIASI AKUN SELESAI!\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo " KREDENSIAL LOGIN:\n\n";

echo "┌─────────────────────────────────────────────────────────┐\n";
echo "│  ADMIN ACCOUNT                                        │\n";
echo "├─────────────────────────────────────────────────────────┤\n";
echo "│ Email    : admin@resepmasakan.com                       │\n";
echo "│ Password : admin123                                     │\n";
echo "│ Role     : Administrator                                │\n";
echo "└─────────────────────────────────────────────────────────┘\n\n";

echo "┌─────────────────────────────────────────────────────────┐\n";
echo "│  USER ACCOUNT                                          │\n";
echo "├─────────────────────────────────────────────────────────┤\n";
echo "│ Email    : dika@example.com                             │\n";
echo "│ Password : 12345                                        │\n";
echo "│ Role     : User                                         │\n";
echo "└─────────────────────────────────────────────────────────┘\n\n";

echo "  PENTING:\n";
echo "   • Ganti password default setelah login pertama kali!\n";
echo "   • Jangan share kredensial ini ke publik!\n";
echo "   • File ini sebaiknya dihapus setelah setup selesai.\n\n";

echo " Akses aplikasi:\n";
echo "   • Login  : http://localhost/sertifikasi/resep_masakan/auth/login.php\n";
echo "   • Home   : http://localhost/sertifikasi/resep_masakan/index.php\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "Happy Cooking! \n";
echo "═══════════════════════════════════════════════════════════\n";

mysqli_close($conn);
?>
