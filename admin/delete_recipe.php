<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

// Validasi ID resep
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['recipe_action_error'] = 'ID resep tidak valid!';
    header('Location: manage_recipes.php');
    exit();
}

$recipe_id = (int)$_GET['id'];

// Cek apakah resep ada dan ambil foto
$stmt_check = $conn->prepare("SELECT id, title, photo FROM recipes WHERE id = ? LIMIT 1");
$stmt_check->bind_param('i', $recipe_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    $_SESSION['recipe_action_error'] = 'Resep tidak ditemukan!';
    $stmt_check->close();
    header('Location: manage_recipes.php');
    exit();
}

$recipe = $result->fetch_assoc();
$photo = $recipe['photo'];
$stmt_check->close();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Hapus kategori resep
    $stmt_cat = $conn->prepare("DELETE FROM recipe_categories WHERE recipe_id = ?");
    $stmt_cat->bind_param('i', $recipe_id);
    $stmt_cat->execute();
    $stmt_cat->close();
    
    // Hapus resep
    $stmt_recipe = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt_recipe->bind_param('i', $recipe_id);
    $stmt_recipe->execute();
    $stmt_recipe->close();
    
    // Commit transaksi
    $conn->commit();
    
    // Hapus file foto jika ada
    if ($photo && file_exists(__DIR__ . '/../assets/images/' . $photo)) {
        unlink(__DIR__ . '/../assets/images/' . $photo);
    }
    
    // Set pesan sukses
    $_SESSION['recipe_action_success'] = 'Resep "' . $recipe['title'] . '" berhasil dihapus permanen!';
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    $_SESSION['recipe_action_error'] = 'Gagal menghapus resep: ' . $e->getMessage();
}

// Redirect kembali
header('Location: manage_recipes.php');
exit();
?>
