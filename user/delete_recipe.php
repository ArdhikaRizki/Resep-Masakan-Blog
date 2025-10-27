<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah user
require_user();

$user_id = $_SESSION['user_id'];

// Validasi ID resep
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ID resep tidak valid!';
    header('Location: my_recipes.php');
    exit();
}

$recipe_id = (int)$_GET['id'];

// Cek apakah resep milik user yang login
$stmt = $conn->prepare("SELECT photo FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii', $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Resep tidak ditemukan atau Anda tidak memiliki akses!';
    $stmt->close();
    header('Location: my_recipes.php');
    exit();
}

$recipe = $result->fetch_assoc();
$photo = $recipe['photo'];
$stmt->close();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Hapus kategori resep
    $stmt_cat = $conn->prepare("DELETE FROM recipe_categories WHERE recipe_id = ?");
    $stmt_cat->bind_param('i', $recipe_id);
    $stmt_cat->execute();
    $stmt_cat->close();
    
    // Hapus resep
    $stmt_recipe = $conn->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
    $stmt_recipe->bind_param('ii', $recipe_id, $user_id);
    $stmt_recipe->execute();
    $stmt_recipe->close();
    
    // Commit transaksi
    $conn->commit();
    
    // Hapus file foto jika ada
    if ($photo && file_exists(__DIR__ . '/../assets/images/' . $photo)) {
        unlink(__DIR__ . '/../assets/images/' . $photo);
    }
    
    // Set pesan sukses
    $_SESSION['delete_recipe_success'] = 'Resep berhasil dihapus!';
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    $_SESSION['error'] = 'Gagal menghapus resep: ' . $e->getMessage();
}

// Redirect kembali ke my_recipes
header('Location: my_recipes.php');
exit();
?>
