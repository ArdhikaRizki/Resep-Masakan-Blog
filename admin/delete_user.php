<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

// Validasi ID user
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['user_action_error'] = 'ID user tidak valid!';
    header('Location: manage_users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Prevent admin dari menghapus diri sendiri
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['user_action_error'] = 'Anda tidak bisa menghapus akun Anda sendiri!';
    header('Location: manage_users.php');
    exit();
}

// Cek apakah user ada
$stmt_check = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmt_check->bind_param('i', $user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    $_SESSION['user_action_error'] = 'User tidak ditemukan!';
    $stmt_check->close();
    header('Location: manage_users.php');
    exit();
}

$user = $result->fetch_assoc();

// Prevent menghapus admin lain
if ($user['role'] == 'admin') {
    $_SESSION['user_action_error'] = 'Tidak bisa menghapus akun admin!';
    $stmt_check->close();
    header('Location: manage_users.php');
    exit();
}

$stmt_check->close();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Ambil semua foto resep user untuk dihapus
    $stmt_photos = $conn->prepare("SELECT photo FROM recipes WHERE user_id = ? AND photo IS NOT NULL");
    $stmt_photos->bind_param('i', $user_id);
    $stmt_photos->execute();
    $photos_result = $stmt_photos->get_result();
    $photos = [];
    while ($row = $photos_result->fetch_assoc()) {
        $photos[] = $row['photo'];
    }
    $stmt_photos->close();
    
    // Hapus recipe_categories untuk resep user ini
    $stmt_rc = $conn->prepare("DELETE FROM recipe_categories WHERE recipe_id IN (SELECT id FROM recipes WHERE user_id = ?)");
    $stmt_rc->bind_param('i', $user_id);
    $stmt_rc->execute();
    $stmt_rc->close();
    
    // Hapus semua resep user
    $stmt_recipes = $conn->prepare("DELETE FROM recipes WHERE user_id = ?");
    $stmt_recipes->bind_param('i', $user_id);
    $stmt_recipes->execute();
    $stmt_recipes->close();
    
    // Hapus user
    $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt_user->bind_param('i', $user_id);
    $stmt_user->execute();
    $stmt_user->close();
    
    // Commit transaksi
    $conn->commit();
    
    // Hapus file foto
    foreach ($photos as $photo) {
        $photo_path = __DIR__ . '/../assets/images/' . $photo;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }
    
    // Set pesan sukses
    $_SESSION['user_action_success'] = 'User "' . $user['name'] . '" dan semua resepnya berhasil dihapus!';
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    $_SESSION['user_action_error'] = 'Gagal menghapus user: ' . $e->getMessage();
}

// Redirect kembali
header('Location: manage_users.php');
exit();
?>
