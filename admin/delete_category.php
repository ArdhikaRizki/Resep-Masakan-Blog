<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

// Validasi ID kategori
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['category_action_error'] = 'ID kategori tidak valid!';
    header('Location: manage_categories.php');
    exit();
}

$category_id = (int)$_GET['id'];

// Cek apakah kategori ada
$stmt_check = $conn->prepare("SELECT id, name FROM categories WHERE id = ? LIMIT 1");
$stmt_check->bind_param('i', $category_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    $_SESSION['category_action_error'] = 'Kategori tidak ditemukan!';
    $stmt_check->close();
    header('Location: manage_categories.php');
    exit();
}

$category = $result->fetch_assoc();
$stmt_check->close();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Hapus dari recipe_categories (relasi dengan resep)
    $stmt_rc = $conn->prepare("DELETE FROM recipe_categories WHERE category_id = ?");
    $stmt_rc->bind_param('i', $category_id);
    $stmt_rc->execute();
    $stmt_rc->close();
    
    // Hapus kategori
    $stmt_cat = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt_cat->bind_param('i', $category_id);
    $stmt_cat->execute();
    $stmt_cat->close();
    
    // Commit transaksi
    $conn->commit();
    
    // Set pesan sukses
    $_SESSION['category_action_success'] = 'Kategori "' . $category['name'] . '" berhasil dihapus!';
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    $_SESSION['category_action_error'] = 'Gagal menghapus kategori: ' . $e->getMessage();
}

// Redirect kembali
header('Location: manage_categories.php');
exit();
?>
