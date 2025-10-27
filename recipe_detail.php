<?php
require_once __DIR__ . '/config/config.php';

// Validasi ID resep
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: recipes.php');
    exit();
}

$recipe_id = (int)$_GET['id'];

// Ambil detail resep dengan informasi user
$query = "SELECT r.*, u.name as author_name,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
     FROM recipe_categories rc 
     JOIN categories c ON rc.category_id = c.id 
     WHERE rc.recipe_id = r.id) as categories
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
    LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: recipes.php');
    exit();
}

$recipe = $result->fetch_assoc();
$stmt->close();

// Cek apakah user yang login adalah pemilik resep
$is_owner = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']) {
    $is_owner = true;
}

// Jika resep tidak approved, hanya pemilik dan admin yang bisa lihat
if ($recipe['status'] != 'approved') {
    if (!isset($_SESSION['user_id'])) {
        // Guest user tidak bisa lihat resep yang belum approved
        header('Location: recipes.php');
        exit();
    }
    
    // Bukan owner dan bukan admin
    if (!$is_owner && $_SESSION['role'] != 'admin') {
        header('Location: recipes.php');
        exit();
    }
}

// Ambil resep lain dari author yang sama
$other_recipes_query = "SELECT id, title, photo, status FROM recipes 
    WHERE user_id = ? AND id != ? AND status = 'approved'
    ORDER BY created_at DESC 
    LIMIT 4";
$stmt_other = $conn->prepare($other_recipes_query);
$stmt_other->bind_param('ii', $recipe['user_id'], $recipe_id);
$stmt_other->execute();
$other_recipes = $stmt_other->get_result();
$stmt_other->close();

// Format ingredients dan steps untuk ditampilkan
$ingredients_array = explode("\n", $recipe['ingredients']);
$steps_array = explode("\n", $recipe['steps']);

include 'includes/header.php';
?>

<main class="flex-grow bg-gray-50">
    <!-- Hero Section with Image -->
    <div class="relative h-96 bg-gradient-to-r from-[#708238] to-[#5a6a2e] overflow-hidden">
        <?php if ($recipe['photo']): ?>
        <img src="assets/images/<?php echo htmlspecialchars($recipe['photo']); ?>" 
             alt="<?php echo htmlspecialchars($recipe['title']); ?>"
             class="w-full h-full object-cover opacity-30">
        <?php endif; ?>
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 right-0 container mx-auto px-6 pb-8">
            <!-- Status Badge (if not approved) -->
            <?php if ($recipe['status'] != 'approved'): ?>
            <div class="mb-4">
                <?php
                $status_classes = [
                    'pending' => 'bg-yellow-500',
                    'rejected' => 'bg-red-500'
                ];
                $status_text = [
                    'pending' => 'Menunggu Persetujuan',
                    'rejected' => 'Ditolak'
                ];
                ?>
                <span class="inline-block <?php echo $status_classes[$recipe['status']]; ?> text-white px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fas fa-info-circle mr-2"></i><?php echo $status_text[$recipe['status']]; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 drop-shadow-lg">
                <?php echo htmlspecialchars($recipe['title']); ?>
            </h1>
            
            <div class="flex flex-wrap items-center gap-4 text-white/90">
                <div class="flex items-center">
                    <i class="fas fa-user-circle text-2xl mr-2"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($recipe['author_name']); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span><?php echo date('d F Y', strtotime($recipe['created_at'])); ?></span>
                </div>
                <?php if ($recipe['categories']): ?>
                <div class="flex items-center">
                    <i class="fas fa-tags mr-2"></i>
                    <span><?php echo htmlspecialchars($recipe['categories']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content (Left Side) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Recipe Photo (if exists) -->
                <?php if ($recipe['photo']): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="assets/images/<?php echo htmlspecialchars($recipe['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                         class="w-full h-auto object-cover">
                </div>
                <?php endif; ?>

                <!-- Bahan-bahan Section -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-[#4b3b2b] mb-6 flex items-center">
                        <div class="bg-[#708238] text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        Bahan-bahan
                    </h2>
                    
                    <div class="bg-gray-50 rounded-lg p-6">
                        <ul class="space-y-3">
                            <?php foreach ($ingredients_array as $ingredient): ?>
                                <?php 
                                $ingredient = trim($ingredient);
                                if (!empty($ingredient)): 
                                ?>
                                <li class="flex items-start text-gray-700">
                                    <i class="fas fa-check-circle text-[#708238] mr-3 mt-1"></i>
                                    <span class="flex-1"><?php echo htmlspecialchars($ingredient); ?></span>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Langkah-langkah Section -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-[#4b3b2b] mb-6 flex items-center">
                        <div class="bg-[#708238] text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        Langkah-langkah Memasak
                    </h2>
                    
                    <div class="space-y-4">
                        <?php 
                        $step_number = 1;
                        foreach ($steps_array as $step): 
                            $step = trim($step);
                            if (!empty($step)): 
                        ?>
                        <div class="flex items-start">
                            <div class="bg-[#708238] text-white w-8 h-8 rounded-full flex items-center justify-center mr-4 flex-shrink-0 font-bold">
                                <?php echo $step_number; ?>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700 leading-relaxed"><?php echo htmlspecialchars($step); ?></p>
                            </div>
                        </div>
                        <?php 
                            $step_number++;
                            endif; 
                        endforeach; 
                        ?>
                    </div>
                </div>

                <!-- Action Buttons (if owner) -->
                <?php if ($is_owner): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="font-bold text-blue-900 mb-4">
                        <i class="fas fa-tools mr-2"></i>Kelola Resep Anda
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="user/edit_recipe.php?id=<?php echo $recipe['id']; ?>" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-flex items-center">
                            <i class="fas fa-edit mr-2"></i>Edit Resep
                        </a>
                        <a href="user/delete_recipe.php?id=<?php echo $recipe['id']; ?>" 
                           class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition inline-flex items-center"
                           onclick="return confirm('Yakin ingin menghapus resep ini?')">
                            <i class="fas fa-trash mr-2"></i>Hapus Resep
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar (Right Side) -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Author Card -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="font-bold text-lg text-[#4b3b2b] mb-4">
                        <i class="fas fa-user mr-2"></i>Tentang Penulis
                    </h3>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-[#708238] to-[#5a6a2e] rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user text-3xl text-white"></i>
                        </div>
                        <h4 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($recipe['author_name']); ?></h4>
                        <p class="text-sm text-gray-500 mt-1">Chef Contributor</p>
                    </div>
                </div>

                <!-- Recipe Info Card -->
                <div class="bg-gradient-to-br from-[#708238] to-[#5a6a2e] text-white rounded-lg shadow-lg p-6">
                    <h3 class="font-bold text-lg mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Resep
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-white/20">
                            <span class="opacity-90">Dibuat</span>
                            <span class="font-semibold"><?php echo date('d M Y', strtotime($recipe['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-white/20">
                            <span class="opacity-90">Terakhir Update</span>
                            <span class="font-semibold"><?php echo date('d M Y', strtotime($recipe['updated_at'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="opacity-90">Status</span>
                            <?php
                            $status_badges = [
                                'approved' => '<span class="bg-green-500 px-3 py-1 rounded-full text-xs font-bold">Disetujui</span>',
                                'pending' => '<span class="bg-yellow-500 px-3 py-1 rounded-full text-xs font-bold">Pending</span>',
                                'rejected' => '<span class="bg-red-500 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>'
                            ];
                            echo $status_badges[$recipe['status']];
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Categories Card -->
                <?php if ($recipe['categories']): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="font-bold text-lg text-[#4b3b2b] mb-4">
                        <i class="fas fa-tags mr-2"></i>Kategori
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $categories = explode(', ', $recipe['categories']);
                        foreach ($categories as $category): 
                        ?>
                        <span class="bg-[#f5f0e6] text-[#4b3b2b] px-3 py-1 rounded-full text-sm font-medium border border-[#708238]">
                            <?php echo htmlspecialchars($category); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share Card -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="font-bold text-lg text-[#4b3b2b] mb-4">
                        <i class="fas fa-share-alt mr-2"></i>Bagikan Resep
                    </h3>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank"
                           class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition text-center">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($recipe['title']); ?>" 
                           target="_blank"
                           class="flex-1 bg-sky-500 text-white py-3 rounded-lg hover:bg-sky-600 transition text-center">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($recipe['title'] . ' - ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank"
                           class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition text-center">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Back Button -->
                <a href="recipes.php" class="block w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 transition text-center font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Resep
                </a>
            </div>
        </div>

        <!-- Other Recipes from Same Author -->
        <?php if ($other_recipes->num_rows > 0): ?>
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-[#4b3b2b] mb-8 text-center">
                <i class="fas fa-book-open mr-2"></i>Resep Lain dari <?php echo htmlspecialchars($recipe['author_name']); ?>
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($other = $other_recipes->fetch_assoc()): ?>
                <a href="recipe_detail.php?id=<?php echo $other['id']; ?>" 
                   class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2 duration-300">
                    <?php if ($other['photo']): ?>
                    <img src="assets/images/<?php echo htmlspecialchars($other['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($other['title']); ?>"
                         class="w-full h-48 object-cover">
                    <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-br from-[#708238] to-[#5a6a2e] flex items-center justify-center">
                        <i class="fas fa-utensils text-6xl text-white opacity-50"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-gray-800 line-clamp-2"><?php echo htmlspecialchars($other['title']); ?></h3>
                        <p class="text-sm text-[#708238] mt-2 font-medium">
                            Lihat Resep <i class="fas fa-arrow-right ml-1"></i>
                        </p>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
