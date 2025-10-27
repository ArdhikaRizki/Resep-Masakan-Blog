<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

$success = $_SESSION['category_action_success'] ?? '';
unset($_SESSION['category_action_success']);

$error = $_SESSION['category_action_error'] ?? '';
unset($_SESSION['category_action_error']);

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');
    
    if (empty($category_name)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        // Cek duplikat
        $stmt_check = $conn->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $stmt_check->bind_param('s', $category_name);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = 'Kategori sudah ada!';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param('s', $category_name);
            
            if ($stmt->execute()) {
                $success = 'Kategori "' . $category_name . '" berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan kategori: ' . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    $category_name = trim($_POST['category_name'] ?? '');
    
    if (empty($category_name)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        // Cek duplikat (exclude current category)
        $stmt_check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1");
        $stmt_check->bind_param('si', $category_name, $category_id);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = 'Kategori dengan nama tersebut sudah ada!';
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param('si', $category_name, $category_id);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diupdate!';
            } else {
                $error = 'Gagal update kategori: ' . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

// Ambil semua kategori dengan count resep
$categories_query = "SELECT c.id, c.name, 
    (SELECT COUNT(*) FROM recipe_categories WHERE category_id = c.id) as recipe_count
    FROM categories c
    ORDER BY c.name ASC";

$categories = $conn->query($categories_query);

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
                <i class="fas fa-tags mr-2"></i>Kelola Kategori
            </h1>
            <p class="text-gray-600">Tambah, edit, atau hapus kategori resep</p>
        </div>
        <a href="dashboard.php" class="mt-4 md:mt-0 inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add Category Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                <h2 class="text-xl font-bold text-[#4b3b2b] mb-6">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Kategori Baru
                </h2>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="category_name" 
                            name="category_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                            placeholder="Contoh: Makanan Pembuka"
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        name="add_category"
                        class="w-full bg-[#708238] text-white px-6 py-3 rounded-lg hover:bg-[#5a6a2e] transition font-semibold"
                    >
                        <i class="fas fa-plus mr-2"></i>Tambah Kategori
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Kategori digunakan untuk mengelompokkan resep agar lebih mudah ditemukan oleh user.
                    </p>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-[#4b3b2b]">
                        <i class="fas fa-list mr-2"></i>Daftar Kategori
                    </h2>
                    <span class="bg-purple-100 text-purple-800 px-4 py-2 rounded-full font-semibold">
                        Total: <?php echo $categories->num_rows; ?>
                    </span>
                </div>

                <?php if ($categories->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-400 transition bg-gradient-to-br from-white to-purple-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800 mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-utensils mr-1"></i>
                                    <?php echo $category['recipe_count']; ?> resep menggunakan kategori ini
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                            <button 
                                onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')"
                                class="flex-1 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition text-sm font-medium"
                            >
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <a 
                                href="delete_category.php?id=<?php echo $category['id']; ?>"
                                onclick="return confirm('Hapus kategori ini? Kategori akan dihapus dari semua resep yang menggunakannya.')"
                                class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition text-sm font-medium text-center"
                            >
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-tags text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada kategori</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-[#4b3b2b] mb-6">
            <i class="fas fa-edit mr-2"></i>Edit Kategori
        </h3>
        
        <form method="POST" action="">
            <input type="hidden" id="edit_category_id" name="category_id">
            
            <div class="mb-6">
                <label for="edit_category_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="edit_category_name" 
                    name="category_name" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                >
            </div>
            
            <div class="flex gap-4">
                <button 
                    type="button" 
                    onclick="closeEditModal()"
                    class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition font-semibold"
                >
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button 
                    type="submit" 
                    name="edit_category"
                    class="flex-1 bg-[#708238] text-white px-6 py-3 rounded-lg hover:bg-[#5a6a2e] transition font-semibold"
                >
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
