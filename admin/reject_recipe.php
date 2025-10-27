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

// Cek apakah resep ada
$stmt_check = $conn->prepare("SELECT id, title FROM recipes WHERE id = ? LIMIT 1");
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
$stmt_check->close();

// Update status menjadi rejected
$stmt = $conn->prepare("UPDATE recipes SET status = 'rejected', updated_at = NOW() WHERE id = ?");
$stmt->bind_param('i', $recipe_id);

if ($stmt->execute()) {
    $_SESSION['recipe_action_success'] = 'Resep "' . $recipe['title'] . '" telah di-reject. User dapat memperbaiki dan submit ulang.';
} else {
    $_SESSION['recipe_action_error'] = 'Gagal reject resep: ' . $stmt->error;
}

$stmt->close();

// Redirect kembali
header('Location: manage_recipes.php');
exit();
?>
