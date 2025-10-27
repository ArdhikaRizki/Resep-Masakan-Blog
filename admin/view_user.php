<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

// Validasi ID user
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Ambil detail user
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: manage_users.php');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Ambil statistik resep user
$stats_query = "SELECT 
    COUNT(*) as total_recipes,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM recipes 
    WHERE user_id = ?";

$stmt_stats = $conn->prepare($stats_query);
$stmt_stats->bind_param('i', $user_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// Ambil semua resep user
$recipes_query = "SELECT r.id, r.title, r.status, r.created_at,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
     FROM recipe_categories rc 
     JOIN categories c ON rc.category_id = c.id 
     WHERE rc.recipe_id = r.id) as categories
    FROM recipes r
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC";

$stmt_recipes = $conn->prepare($recipes_query);
$stmt_recipes->bind_param('i', $user_id);
$stmt_recipes->execute();
$recipes = $stmt_recipes->get_result();
$stmt_recipes->close();

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-[#4b3b2b]">
            <i class="fas fa-user-circle mr-2"></i>Detail User
        </h1>
        <a href="manage_users.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-5xl text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="inline-block mt-3 px-4 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>

                <div class="border-t pt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600"><i class="fas fa-calendar-alt mr-2"></i>Bergabung</span>
                        <span class="font-semibold"><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600"><i class="fas fa-hashtag mr-2"></i>User ID</span>
                        <span class="font-mono font-semibold">#<?php echo $user['id']; ?></span>
                    </div>
                </div>

                <?php if ($user['role'] != 'admin'): ?>
                <div class="mt-6 pt-6 border-t">
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                       class="block w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition text-center font-semibold"
                       onclick="return confirm('HAPUS user ini dan semua resepnya?')">
                        <i class="fas fa-trash mr-2"></i>Hapus User
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recipe Statistics -->
            <div class="bg-gradient-to-br from-[#708238] to-[#5a6a2e] text-white rounded-lg shadow-lg p-6 mt-6">
                <h3 class="font-bold text-lg mb-4">
                    <i class="fas fa-chart-pie mr-2"></i>Statistik Resep
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-white/20">
                        <span>Total Resep</span>
                        <span class="font-bold text-lg"><?php echo $stats['total_recipes']; ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/20">
                        <span><i class="fas fa-check-circle mr-1"></i>Approved</span>
                        <span class="font-bold"><?php echo $stats['approved']; ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-white/20">
                        <span><i class="fas fa-clock mr-1"></i>Pending</span>
                        <span class="font-bold"><?php echo $stats['pending']; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                        <span class="font-bold"><?php echo $stats['rejected']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipes List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-[#4b3b2b] mb-6">
                    <i class="fas fa-list mr-2"></i>Semua Resep User
                </h2>

                <?php if ($recipes->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($recipe = $recipes->fetch_assoc()): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-[#708238] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                
                                <?php if ($recipe['categories']): ?>
                                <div class="mb-2">
                                    <?php 
                                    $cats = explode(', ', $recipe['categories']);
                                    foreach ($cats as $cat): 
                                    ?>
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span>
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
                                            'approved' => 'Approved',
                                            'pending' => 'Pending',
                                            'rejected' => 'Rejected'
                                        ];
                                        $status = $recipe['status'];
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_classes[$status]; ?>">
                                            <i class="fas <?php echo $status_icons[$status]; ?> mr-1"></i>
                                            <?php echo $status_text[$status]; ?>
                                        </span>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="ml-4 flex gap-2">
                                <a href="../recipe_detail.php?id=<?php echo $recipe['id']; ?>" 
                                   class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 transition text-sm"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($recipe['status'] != 'approved'): ?>
                                <a href="approve_recipe.php?id=<?php echo $recipe['id']; ?>" 
                                   class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600 transition text-sm"
                                   title="Approve"
                                   onclick="return confirm('Approve resep ini?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                                
                                <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" 
                                   class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition text-sm"
                                   title="Hapus"
                                   onclick="return confirm('Hapus resep ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">User ini belum membuat resep apapun</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
