<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah user
require_user();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Ambil statistik user
$stats_query = "SELECT 
    COUNT(*) as total_recipes,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_recipes,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_recipes,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_recipes
    FROM recipes 
    WHERE user_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil resep terbaru user (5 resep terakhir)
$recent_query = "SELECT r.id, r.title, r.status, r.created_at,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
     FROM recipe_categories rc 
     JOIN categories c ON rc.category_id = c.id 
     WHERE rc.recipe_id = r.id) as categories
    FROM recipes r
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5";

$stmt_recent = $conn->prepare($recent_query);
$stmt_recent->bind_param('i', $user_id);
$stmt_recent->execute();
$recent_recipes = $stmt_recent->get_result();
$stmt_recent->close();

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard User
        </h1>
        <p class="text-gray-600">Selamat datang kembali, <span class="font-semibold text-[#708238]"><?php echo htmlspecialchars($user_name); ?></span>! 👋</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="add_recipe.php" class="bg-gradient-to-r from-[#708238] to-[#5a6a2e] text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Quick Action</p>
                    <h3 class="text-xl font-bold mt-1">Tambah Resep</h3>
                </div>
                <i class="fas fa-plus-circle text-4xl opacity-80"></i>
            </div>
        </a>

        <a href="my_recipes.php" class="bg-gradient-to-r from-[#4b3b2b] to-[#3a2d21] text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Lihat Semua</p>
                    <h3 class="text-xl font-bold mt-1">Resep Saya</h3>
                </div>
                <i class="fas fa-book text-4xl opacity-80"></i>
            </div>
        </a>

        <a href="../recipes.php" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Eksplor</p>
                    <h3 class="text-xl font-bold mt-1">Semua Resep</h3>
                </div>
                <i class="fas fa-compass text-4xl opacity-80"></i>
            </div>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Resep -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Resep</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_recipes']; ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-utensils text-2xl text-blue-500"></i>
                </div>
            </div>
        </div>

        <!-- Resep Disetujui -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Disetujui</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['approved_recipes']; ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-check-circle text-2xl text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Resep Pending -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Menunggu Review</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['pending_recipes']; ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-clock text-2xl text-yellow-500"></i>
                </div>
            </div>
        </div>

        <!-- Resep Ditolak -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Ditolak</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['rejected_recipes']; ?></p>
                </div>
                <div class="bg-red-100 rounded-full p-4">
                    <i class="fas fa-times-circle text-2xl text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Recipes Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-[#4b3b2b]">
                <i class="fas fa-history mr-2"></i>Resep Terbaru Saya
            </h2>
            <a href="my_recipes.php" class="text-[#708238] hover:text-[#5a6a2e] font-medium text-sm">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if ($recent_recipes->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Judul Resep</th>
                        <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Kategori</th>
                        <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Tanggal</th>
                        <th class="text-center py-3 px-4 font-semibold text-sm text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($recipe = $recent_recipes->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($recipe['title']); ?></p>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-sm text-gray-600">
                                <?php echo $recipe['categories'] ? htmlspecialchars($recipe['categories']) : '-'; ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <?php
                            $status_classes = [
                                'approved' => 'bg-green-100 text-green-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'rejected' => 'bg-red-100 text-red-800'
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
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_classes[$status]; ?>">
                                <i class="fas <?php echo $status_icons[$status]; ?> mr-1"></i>
                                <?php echo $status_text[$status]; ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-sm text-gray-600">
                                <?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../recipe_detail.php?id=<?php echo $recipe['id']; ?>" class="text-green-600 hover:text-green-800" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">Belum ada resep yang dibuat</p>
            <a href="add_recipe.php" class="inline-block bg-[#708238] text-white px-6 py-3 rounded-lg hover:bg-[#5a6a2e] transition">
                <i class="fas fa-plus-circle mr-2"></i>Buat Resep Pertama
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tips Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
            <h3 class="font-bold text-lg text-blue-900 mb-3">
                <i class="fas fa-lightbulb mr-2"></i>Tips Resep Berkualitas
            </h3>
            <ul class="space-y-2 text-sm text-blue-800">
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Tulis langkah-langkah dengan jelas dan detail</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Sertakan foto yang menarik dan berkualitas</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Cantumkan takaran bahan dengan akurat</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Pilih kategori yang sesuai</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
            <h3 class="font-bold text-lg text-green-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>Status Resep
            </h3>
            <ul class="space-y-2 text-sm text-green-800">
                <li><i class="fas fa-clock text-yellow-600 mr-2"></i><strong>Pending:</strong> Menunggu review admin</li>
                <li><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Approved:</strong> Resep sudah dipublikasikan</li>
                <li><i class="fas fa-times-circle text-red-600 mr-2"></i><strong>Rejected:</strong> Resep perlu diperbaiki</li>
            </ul>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
