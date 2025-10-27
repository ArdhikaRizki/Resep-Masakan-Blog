<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

$admin_name = $_SESSION['name'];

// Ambil statistik keseluruhan
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM recipes) as total_recipes,
    (SELECT COUNT(*) FROM recipes WHERE status = 'pending') as pending_recipes,
    (SELECT COUNT(*) FROM recipes WHERE status = 'approved') as approved_recipes,
    (SELECT COUNT(*) FROM recipes WHERE status = 'rejected') as rejected_recipes,
    (SELECT COUNT(*) FROM categories) as total_categories";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Ambil resep pending terbaru (untuk review)
$pending_query = "SELECT r.id, r.title, r.created_at, u.name as author_name, u.email as author_email,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
     FROM recipe_categories rc 
     JOIN categories c ON rc.category_id = c.id 
     WHERE rc.recipe_id = r.id) as categories
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at ASC
    LIMIT 5";

$pending_recipes = $conn->query($pending_query);

// Ambil user terbaru
$recent_users_query = "SELECT id, name, email, created_at FROM users 
    WHERE role = 'user' 
    ORDER BY created_at DESC 
    LIMIT 5";

$recent_users = $conn->query($recent_users_query);

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
            <i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard
        </h1>
        <p class="text-gray-600">Selamat datang kembali, <span class="font-semibold text-[#708238]"><?php echo htmlspecialchars($admin_name); ?></span>! 👨‍💼</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <a href="manage_users.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Kelola</p>
                    <h3 class="text-xl font-bold mt-1">Users</h3>
                </div>
                <i class="fas fa-users text-4xl opacity-80"></i>
            </div>
        </a>

        <a href="manage_recipes.php" class="bg-gradient-to-r from-[#708238] to-[#5a6a2e] text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Kelola</p>
                    <h3 class="text-xl font-bold mt-1">Resep</h3>
                </div>
                <i class="fas fa-utensils text-4xl opacity-80"></i>
            </div>
        </a>

        <a href="manage_categories.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Kelola</p>
                    <h3 class="text-xl font-bold mt-1">Kategori</h3>
                </div>
                <i class="fas fa-tags text-4xl opacity-80"></i>
            </div>
        </a>

        <a href="../index.php" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Lihat</p>
                    <h3 class="text-xl font-bold mt-1">Website</h3>
                </div>
                <i class="fas fa-globe text-4xl opacity-80"></i>
            </div>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total User</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-users text-xl text-blue-500"></i>
                </div>
            </div>
        </div>

        <!-- Total Recipes -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Resep</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_recipes']; ?></p>
                </div>
                <div class="bg-indigo-100 rounded-full p-3">
                    <i class="fas fa-book text-xl text-indigo-500"></i>
                </div>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['pending_recipes']; ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-clock text-xl text-yellow-500"></i>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Approved</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['approved_recipes']; ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-xl text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Rejected</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['rejected_recipes']; ?></p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-xl text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Kategori</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['total_categories']; ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-tags text-xl text-purple-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Columns Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Pending Recipes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-[#4b3b2b]">
                    <i class="fas fa-hourglass-half text-yellow-500 mr-2"></i>Resep Menunggu Review
                </h2>
                <a href="manage_recipes.php?status=pending" class="text-[#708238] hover:text-[#5a6a2e] font-medium text-sm">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if ($pending_recipes->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($recipe = $pending_recipes->fetch_assoc()): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-[#708238] transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($recipe['author_name']); ?>
                                <span class="mx-2">•</span>
                                <i class="fas fa-clock mr-1"></i><?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
                            </p>
                            <?php if ($recipe['categories']): ?>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-tags mr-1"></i><?php echo htmlspecialchars($recipe['categories']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <a href="../recipe_detail.php?id=<?php echo $recipe['id']; ?>" 
                           class="ml-4 bg-[#708238] text-white px-4 py-2 rounded-lg hover:bg-[#5a6a2e] transition text-sm">
                            <i class="fas fa-eye mr-1"></i>Review
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-6xl text-green-300 mb-3"></i>
                <p class="text-gray-500">Tidak ada resep yang menunggu review</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-[#4b3b2b]">
                    <i class="fas fa-user-plus text-blue-500 mr-2"></i>User Terbaru
                </h2>
                <a href="manage_users.php" class="text-[#708238] hover:text-[#5a6a2e] font-medium text-sm">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if ($recent_users->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($user = $recent_users->fetch_assoc()): ?>
                <div class="flex items-center justify-between border border-gray-200 rounded-lg p-4 hover:border-blue-400 transition">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-calendar mr-1"></i>Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-users text-6xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Belum ada user terdaftar</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Tips -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
            <h3 class="font-bold text-lg text-blue-900 mb-3">
                <i class="fas fa-shield-alt mr-2"></i>Keamanan
            </h3>
            <ul class="space-y-2 text-sm text-blue-800">
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Ganti password secara berkala</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Jangan share kredensial admin</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Monitor aktivitas user</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
            <h3 class="font-bold text-lg text-green-900 mb-3">
                <i class="fas fa-clipboard-check mr-2"></i>Review Resep
            </h3>
            <ul class="space-y-2 text-sm text-green-800">
                <li><i class="fas fa-check text-green-600 mr-2"></i>Periksa kelengkapan bahan</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Pastikan langkah jelas</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Validasi foto berkualitas</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
            <h3 class="font-bold text-lg text-purple-900 mb-3">
                <i class="fas fa-chart-line mr-2"></i>Statistik
            </h3>
            <ul class="space-y-2 text-sm text-purple-800">
                <li><i class="fas fa-check text-purple-600 mr-2"></i>Monitor pertumbuhan user</li>
                <li><i class="fas fa-check text-purple-600 mr-2"></i>Pantau kualitas konten</li>
                <li><i class="fas fa-check text-purple-600 mr-2"></i>Analisa kategori populer</li>
            </ul>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
