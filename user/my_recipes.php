<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah user
require_user();

$user_id = $_SESSION['user_id'];

// Ambil pesan sukses dari session
$success = $_SESSION['add_recipe_success'] ?? $_SESSION['edit_recipe_success'] ?? $_SESSION['delete_recipe_success'] ?? '';
unset($_SESSION['add_recipe_success']);
unset($_SESSION['edit_recipe_success']);
unset($_SESSION['delete_recipe_success']);

// Ambil pesan error dari session
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Filter berdasarkan status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk count total
$count_query = "SELECT COUNT(*) as total FROM recipes WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

if ($status_filter != 'all') {
    $count_query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $count_query .= " AND title LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$stmt_count = $conn->prepare($count_query);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_recipes = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_recipes / $limit);

// Query untuk ambil data resep
$query = "SELECT r.id, r.title, r.status, r.created_at, r.updated_at,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
     FROM recipe_categories rc 
     JOIN categories c ON rc.category_id = c.id 
     WHERE rc.recipe_id = r.id) as categories
    FROM recipes r
    WHERE r.user_id = ?";

$params = [$user_id];
$types = 'i';

if ($status_filter != 'all') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND r.title LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$recipes = $stmt->get_result();
$stmt->close();

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Success Message -->
    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
                <i class="fas fa-book mr-2"></i>Resep Saya
            </h1>
            <p class="text-gray-600">Kelola semua resep yang telah Anda buat</p>
        </div>
        <a href="add_recipe.php" class="mt-4 md:mt-0 inline-block bg-[#708238] text-white px-6 py-3 rounded-lg hover:bg-[#5a6a2e] transition shadow-lg">
            <i class="fas fa-plus-circle mr-2"></i>Tambah Resep Baru
        </a>
    </div>

    <!-- Filter and Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Cari Resep
                </label>
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Masukkan judul resep..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i>Status
                </label>
                <select 
                    name="status" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                >
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end">
                <button 
                    type="submit" 
                    class="w-full bg-[#708238] text-white px-6 py-2 rounded-lg hover:bg-[#5a6a2e] transition"
                >
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="mb-4">
        <p class="text-gray-600">
            Menampilkan <span class="font-semibold"><?php echo $recipes->num_rows; ?></span> dari 
            <span class="font-semibold"><?php echo $total_recipes; ?></span> resep
        </p>
    </div>

    <!-- Recipes Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if ($recipes->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#708238] text-white">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold">Judul Resep</th>
                        <th class="text-left py-4 px-6 font-semibold">Kategori</th>
                        <th class="text-left py-4 px-6 font-semibold">Status</th>
                        <th class="text-left py-4 px-6 font-semibold">Dibuat</th>
                        <th class="text-left py-4 px-6 font-semibold">Diupdate</th>
                        <th class="text-center py-4 px-6 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($recipe = $recipes->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($recipe['title']); ?></p>
                        </td>
                        <td class="py-4 px-6">
                            <?php if ($recipe['categories']): ?>
                                <?php 
                                $cats = explode(', ', $recipe['categories']);
                                foreach ($cats as $cat): 
                                ?>
                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1">
                                    <?php echo htmlspecialchars($cat); ?>
                                </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6">
                            <?php
                            $status_classes = [
                                'approved' => 'bg-green-100 text-green-800 border-green-300',
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'rejected' => 'bg-red-100 text-red-800 border-red-300'
                            ];
                            $status_icons = [
                                'approved' => 'fa-check-circle',
                                'pending' => 'fa-clock',
                                'rejected' => 'fa-times-circle'
                            ];
                            $status_text = [
                                'approved' => 'Disetujui',
                                'pending' => 'Pending',
                                'rejected' => 'Ditolak'
                            ];
                            $status = $recipe['status'];
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?php echo $status_classes[$status]; ?>">
                                <i class="fas <?php echo $status_icons[$status]; ?> mr-1"></i>
                                <?php echo $status_text[$status]; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-sm text-gray-600">
                                <?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-sm text-gray-600">
                                <?php echo date('d M Y', strtotime($recipe['updated_at'])); ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="../recipe_detail.php?id=<?php echo $recipe['id']; ?>" 
                                   class="text-green-600 hover:text-green-800 transition" 
                                   title="Lihat Detail">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 transition" 
                                   title="Edit">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" 
                                   class="text-red-600 hover:text-red-800 transition" 
                                   title="Hapus"
                                   onclick="return confirm('Yakin ingin menghapus resep ini?')">
                                    <i class="fas fa-trash text-lg"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-chevron-left mr-1"></i>Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 <?php echo $i == $page ? 'bg-[#708238] text-white' : 'bg-white border border-gray-300 hover:bg-gray-100'; ?> rounded-lg transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                            Next<i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-16">
            <i class="fas fa-inbox text-8xl text-gray-300 mb-6"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada resep ditemukan</h3>
            <p class="text-gray-500 mb-6">
                <?php if (!empty($search) || $status_filter != 'all'): ?>
                    Coba ubah filter pencarian Anda
                <?php else: ?>
                    Mulai buat resep pertama Anda!
                <?php endif; ?>
            </p>
            <a href="add_recipe.php" class="inline-block bg-[#708238] text-white px-8 py-3 rounded-lg hover:bg-[#5a6a2e] transition">
                <i class="fas fa-plus-circle mr-2"></i>Tambah Resep Baru
            </a>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
