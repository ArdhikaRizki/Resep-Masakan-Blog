<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah user
require_user();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Validasi ID resep
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: my_recipes.php');
    exit();
}

$recipe_id = (int)$_GET['id'];

// Ambil data resep (pastikan milik user yang login)
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii', $recipe_id, $user_id);
$stmt->execute();
$recipe_result = $stmt->get_result();

if ($recipe_result->num_rows == 0) {
    $_SESSION['error'] = 'Resep tidak ditemukan atau Anda tidak memiliki akses!';
    header('Location: my_recipes.php');
    exit();
}

$recipe = $recipe_result->fetch_assoc();
$stmt->close();

// Ambil kategori resep yang sudah dipilih
$stmt_cat = $conn->prepare("SELECT category_id FROM recipe_categories WHERE recipe_id = ?");
$stmt_cat->bind_param('i', $recipe_id);
$stmt_cat->execute();
$selected_categories_result = $stmt_cat->get_result();
$selected_categories = [];
while ($cat = $selected_categories_result->fetch_assoc()) {
    $selected_categories[] = $cat['category_id'];
}
$stmt_cat->close();

// Ambil semua kategori untuk checkbox
$categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);

// Proses update resep
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $new_categories = $_POST['categories'] ?? [];
    
    // Validasi
    if (empty($title)) {
        $error = 'Judul resep harus diisi!';
    } elseif (empty($ingredients)) {
        $error = 'Bahan-bahan harus diisi!';
    } elseif (empty($steps)) {
        $error = 'Langkah-langkah harus diisi!';
    } elseif (empty($new_categories)) {
        $error = 'Pilih minimal 1 kategori!';
    } else {
        // Handle upload foto baru (opsional)
        $photo_name = $recipe['photo']; // Keep old photo by default
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['photo']['type'];
            $file_size = $_FILES['photo']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Format foto tidak valid! Gunakan JPG, PNG, atau WEBP.';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran foto maksimal 5MB!';
            } else {
                // Generate unique filename
                $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $new_photo_name = 'recipe_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = __DIR__ . '/../assets/images/' . $new_photo_name;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    // Hapus foto lama jika ada
                    if ($photo_name && file_exists(__DIR__ . '/../assets/images/' . $photo_name)) {
                        unlink(__DIR__ . '/../assets/images/' . $photo_name);
                    }
                    $photo_name = $new_photo_name;
                } else {
                    $error = 'Gagal upload foto baru!';
                }
            }
        }
        
        // Jika tidak ada error, update database
        if (empty($error)) {
            $conn->begin_transaction();
            
            try {
                // Update resep (status kembali ke pending jika ada perubahan)
                $stmt = $conn->prepare("UPDATE recipes SET title = ?, ingredients = ?, steps = ?, photo = ?, status = 'pending', updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->bind_param('ssssii', $title, $ingredients, $steps, $photo_name, $recipe_id, $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception('Gagal update resep: ' . $stmt->error);
                }
                $stmt->close();
                
                // Hapus kategori lama
                $stmt_del = $conn->prepare("DELETE FROM recipe_categories WHERE recipe_id = ?");
                $stmt_del->bind_param('i', $recipe_id);
                $stmt_del->execute();
                $stmt_del->close();
                
                // Insert kategori baru
                $stmt_cat = $conn->prepare("INSERT INTO recipe_categories (recipe_id, category_id) VALUES (?, ?)");
                foreach ($new_categories as $category_id) {
                    $stmt_cat->bind_param('ii', $recipe_id, $category_id);
                    if (!$stmt_cat->execute()) {
                        throw new Exception('Gagal update kategori: ' . $stmt_cat->error);
                    }
                }
                $stmt_cat->close();
                
                // Commit transaction
                $conn->commit();
                
                // Redirect dengan pesan sukses
                $_SESSION['edit_recipe_success'] = 'Resep berhasil diupdate! Menunggu persetujuan ulang dari admin.';
                header('Location: my_recipes.php');
                exit();
                
            } catch (Exception $e) {
                // Rollback jika ada error
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
            <i class="fas fa-edit mr-2"></i>Edit Resep
        </h1>
        <p class="text-gray-600">Update resep masakan Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Info Status -->
                <?php if ($recipe['status'] == 'rejected'): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Resep ini ditolak.</strong> Silakan perbaiki dan submit ulang untuk direview.
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Judul Resep -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heading mr-2"></i>Judul Resep <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?php echo htmlspecialchars($_POST['title'] ?? $recipe['title']); ?>"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                            placeholder="Contoh: Nasi Goreng Spesial"
                        >
                    </div>

                    <!-- Kategori -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tags mr-2"></i>Kategori <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <?php 
                            $categories_result->data_seek(0);
                            while ($cat = $categories_result->fetch_assoc()): 
                                // Check if category is selected (from POST or from database)
                                $is_checked = false;
                                if (isset($_POST['categories'])) {
                                    $is_checked = in_array($cat['id'], $_POST['categories']);
                                } else {
                                    $is_checked = in_array($cat['id'], $selected_categories);
                                }
                            ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="categories[]" 
                                    value="<?php echo $cat['id']; ?>"
                                    <?php echo $is_checked ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-[#708238] border-gray-300 rounded focus:ring-[#708238]"
                                >
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($cat['name']); ?></span>
                            </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Foto Resep -->
                    <div class="mb-6">
                        <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-image mr-2"></i>Foto Resep
                        </label>
                        
                        <!-- Current Photo -->
                        <?php if ($recipe['photo']): ?>
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 mb-2">Foto Saat Ini:</p>
                            <img src="../assets/images/<?php echo htmlspecialchars($recipe['photo']); ?>" 
                                 alt="Current Photo" 
                                 class="max-w-full h-48 object-cover rounded-lg border border-gray-300">
                        </div>
                        <?php endif; ?>
                        
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                        >
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG, WEBP. Maksimal 5MB
                        </p>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-4 hidden">
                            <p class="text-sm text-gray-600 mb-2">Preview Foto Baru:</p>
                            <img src="" alt="Preview" class="max-w-full h-48 object-cover rounded-lg border border-gray-300">
                        </div>
                    </div>

                    <!-- Bahan-bahan -->
                    <div class="mb-6">
                        <label for="ingredients" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-list-ul mr-2"></i>Bahan-bahan <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="ingredients" 
                            name="ingredients" 
                            rows="8"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                            placeholder="Tulis bahan-bahan, satu per baris"
                        ><?php echo htmlspecialchars($_POST['ingredients'] ?? $recipe['ingredients']); ?></textarea>
                    </div>

                    <!-- Langkah-langkah -->
                    <div class="mb-6">
                        <label for="steps" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clipboard-list mr-2"></i>Langkah-langkah Memasak <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="steps" 
                            name="steps" 
                            rows="10"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                            placeholder="Tulis langkah-langkah memasak dengan detail"
                        ><?php echo htmlspecialchars($_POST['steps'] ?? $recipe['steps']); ?></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="my_recipes.php" class="text-gray-600 hover:text-gray-800 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <button 
                            type="submit" 
                            class="bg-[#708238] text-white px-8 py-3 rounded-lg hover:bg-[#5a6a2e] transition font-semibold shadow-lg"
                        >
                            <i class="fas fa-save mr-2"></i>Update Resep
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <!-- Status Card -->
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-md mb-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Informasi Resep
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <?php
                        $status_classes = [
                            'approved' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $status_text = [
                            'approved' => 'Disetujui',
                            'pending' => 'Pending',
                            'rejected' => 'Ditolak'
                        ];
                        ?>
                        <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_classes[$recipe['status']]; ?>">
                            <?php echo $status_text[$recipe['status']]; ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="ml-2 font-medium"><?php echo date('d M Y', strtotime($recipe['created_at'])); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Terakhir Update:</span>
                        <span class="ml-2 font-medium"><?php echo date('d M Y', strtotime($recipe['updated_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Warning Card -->
            <div class="bg-orange-50 rounded-lg p-6 border border-orange-200">
                <h3 class="font-bold text-lg text-orange-900 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Perhatian
                </h3>
                <p class="text-sm text-orange-800 leading-relaxed">
                    Setelah Anda update resep, status akan kembali menjadi <strong>"Pending"</strong> dan perlu direview ulang oleh admin.
                </p>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript untuk Image Preview -->
<script>
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
