<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah user
require_user();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil semua kategori untuk dropdown
$categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $selected_categories = $_POST['categories'] ?? [];
    
    // Validasi input
    if (empty($title)) {
        $error = 'Judul resep harus diisi!';
    } elseif (empty($ingredients)) {
        $error = 'Bahan-bahan harus diisi!';
    } elseif (empty($steps)) {
        $error = 'Langkah-langkah harus diisi!';
    } elseif (empty($selected_categories)) {
        $error = 'Pilih minimal 1 kategori!';
    } else {
        // Handle upload foto
        $photo_name = null;
        
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
                $photo_name = 'recipe_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = __DIR__ . '/../assets/images/' . $photo_name;
                
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $error = 'Gagal upload foto!';
                    $photo_name = null;
                }
            }
        }
        
        // Jika tidak ada error, simpan ke database
        if (empty($error)) {
            $conn->begin_transaction();
            
            try {
                // Insert resep dengan status pending (menunggu approval admin)
                $stmt = $conn->prepare("INSERT INTO recipes (user_id, title, ingredients, steps, photo, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
                $stmt->bind_param('issss', $user_id, $title, $ingredients, $steps, $photo_name);
                
                if (!$stmt->execute()) {
                    throw new Exception('Gagal menyimpan resep: ' . $stmt->error);
                }
                
                $recipe_id = $stmt->insert_id;
                $stmt->close();
                
                // Insert kategori resep
                $stmt_cat = $conn->prepare("INSERT INTO recipe_categories (recipe_id, category_id) VALUES (?, ?)");
                
                foreach ($selected_categories as $category_id) {
                    $stmt_cat->bind_param('ii', $recipe_id, $category_id);
                    if (!$stmt_cat->execute()) {
                        throw new Exception('Gagal menyimpan kategori: ' . $stmt_cat->error);
                    }
                }
                
                $stmt_cat->close();
                
                // Commit transaction
                $conn->commit();
                
                // Redirect dengan pesan sukses
                $_SESSION['add_recipe_success'] = 'Resep berhasil ditambahkan! Menunggu persetujuan admin.';
                header('Location: my_recipes.php');
                exit();
                
            } catch (Exception $e) {
                // Rollback jika ada error
                $conn->rollback();
                $error = $e->getMessage();
                
                // Hapus foto jika sudah diupload
                if ($photo_name && file_exists(__DIR__ . '/../assets/images/' . $photo_name)) {
                    unlink(__DIR__ . '/../assets/images/' . $photo_name);
                }
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
            <i class="fas fa-plus-circle mr-2"></i>Tambah Resep Baru
        </h1>
        <p class="text-gray-600">Bagikan resep masakan favorit Anda dengan komunitas</p>
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
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
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
                            $categories_result->data_seek(0); // Reset pointer
                            while ($cat = $categories_result->fetch_assoc()): 
                                $checked = isset($_POST['categories']) && in_array($cat['id'], $_POST['categories']) ? 'checked' : '';
                            ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="categories[]" 
                                    value="<?php echo $cat['id']; ?>"
                                    <?php echo $checked; ?>
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
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                        >
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: JPG, PNG, WEBP. Maksimal 5MB
                        </p>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-4 hidden">
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
                            placeholder="Tulis bahan-bahan, satu per baris. Contoh:&#10;- 2 porsi nasi putih&#10;- 2 butir telur&#10;- 3 siung bawang putih&#10;- Kecap manis secukupnya"
                        ><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
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
                            placeholder="Tulis langkah-langkah memasak dengan detail. Contoh:&#10;1. Panaskan minyak di wajan&#10;2. Tumis bawang putih hingga harum&#10;3. Masukkan telur, orak-arik&#10;4. Tambahkan nasi, aduk rata"
                        ><?php echo htmlspecialchars($_POST['steps'] ?? ''); ?></textarea>
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
                            <i class="fas fa-save mr-2"></i>Simpan Resep
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Tips -->
        <div class="lg:col-span-1">
            <!-- Tips Card -->
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 mb-6">
                <h3 class="font-bold text-lg text-blue-900 mb-4">
                    <i class="fas fa-lightbulb mr-2"></i>Tips Menulis Resep
                </h3>
                <ul class="space-y-3 text-sm text-blue-800">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Gunakan judul yang menarik dan deskriptif</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Tulis bahan dengan takaran yang jelas</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Jelaskan langkah-langkah secara detail</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Upload foto yang menarik dan berkualitas</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Pilih kategori yang sesuai</span>
                    </li>
                </ul>
            </div>

            <!-- Info Card -->
            <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                <h3 class="font-bold text-lg text-yellow-900 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Proses Persetujuan
                </h3>
                <p class="text-sm text-yellow-800 leading-relaxed">
                    Resep yang Anda submit akan direview oleh admin terlebih dahulu. Resep yang disetujui akan tampil di halaman utama untuk dilihat pengguna lain.
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
