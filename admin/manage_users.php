<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Pastikan yang akses adalah admin
require_admin();

$success = $_SESSION['user_action_success'] ?? '';
unset($_SESSION['user_action_success']);

$error = $_SESSION['user_action_error'] ?? '';
unset($_SESSION['user_action_error']);

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk count total users (exclude admin)
$count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
if (!empty($search)) {
    $count_query .= " AND (name LIKE ? OR email LIKE ?)";
}

$stmt_count = $conn->prepare($count_query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt_count->bind_param('ss', $search_param, $search_param);
}
$stmt_count->execute();
$total_users = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_users / $limit);

// Query untuk ambil data users
$query = "SELECT u.id, u.name, u.email, u.created_at,
    (SELECT COUNT(*) FROM recipes WHERE user_id = u.id) as total_recipes,
    (SELECT COUNT(*) FROM recipes WHERE user_id = u.id AND status = 'approved') as approved_recipes
    FROM users u
    WHERE u.role = 'user'";

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
}

$query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param('ssii', $search_param, $search_param, $limit, $offset);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

include '../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#4b3b2b] mb-2">
                <i class="fas fa-users mr-2"></i>Kelola User
            </h1>
            <p class="text-gray-600">Manage semua user yang terdaftar di sistem</p>
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

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="" class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Cari User
                </label>
                <input 
                    type="text" 
                    name="search" 
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Cari berdasarkan nama atau email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                >
            </div>
            <div class="flex items-end">
                <button 
                    type="submit" 
                    class="bg-[#708238] text-white px-8 py-2 rounded-lg hover:bg-[#5a6a2e] transition"
                >
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="mb-4">
        <p class="text-gray-600">
            Total User: <span class="font-semibold text-lg text-[#708238]"><?php echo $total_users; ?></span>
        </p>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if ($users->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#708238] text-white">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold">ID</th>
                        <th class="text-left py-4 px-6 font-semibold">Nama</th>
                        <th class="text-left py-4 px-6 font-semibold">Email</th>
                        <th class="text-center py-4 px-6 font-semibold">Total Resep</th>
                        <th class="text-center py-4 px-6 font-semibold">Approved</th>
                        <th class="text-left py-4 px-6 font-semibold">Terdaftar</th>
                        <th class="text-center py-4 px-6 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6">
                            <span class="font-mono text-sm text-gray-600">#<?php echo $user['id']; ?></span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-700"><?php echo htmlspecialchars($user['email']); ?></span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo $user['total_recipes']; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo $user['approved_recipes']; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-sm text-gray-600">
                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="view_user.php?id=<?php echo $user['id']; ?>" 
                                   class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition text-sm"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition text-sm"
                                   title="Hapus User"
                                   onclick="return confirm('HAPUS user ini? Semua resep user juga akan terhapus!')">
                                    <i class="fas fa-trash"></i>
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
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-chevron-left mr-1"></i>Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="px-4 py-2 <?php echo $i == $page ? 'bg-[#708238] text-white' : 'bg-white border border-gray-300 hover:bg-gray-100'; ?> rounded-lg transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
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
            <i class="fas fa-user-slash text-8xl text-gray-300 mb-6"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada user ditemukan</h3>
            <?php if (!empty($search)): ?>
            <p class="text-gray-500 mb-4">Coba ubah kata kunci pencarian</p>
            <a href="manage_users.php" class="inline-block bg-[#708238] text-white px-6 py-2 rounded-lg hover:bg-[#5a6a2e] transition">
                <i class="fas fa-redo mr-2"></i>Reset Pencarian
            </a>
            <?php else: ?>
            <p class="text-gray-500">Belum ada user yang terdaftar</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
