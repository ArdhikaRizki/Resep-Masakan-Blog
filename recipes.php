<?php
require_once 'config/config.php';

// Pagination
$limit = 9; // Resep per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter kategori
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Query total resep (untuk pagination)
$count_query = "SELECT COUNT(*) as total FROM recipes WHERE status = 'approved'";
if($category_filter > 0) {
    $count_query .= " AND id IN (SELECT recipe_id FROM recipe_categories WHERE category_id = $category_filter)";
}
$count_result = mysqli_query($conn, $count_query);
$total_recipes = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_recipes / $limit);

// Query resep dengan pagination
$recipes_query = "SELECT r.*, u.name as author_name 
                  FROM recipes r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.status = 'approved'";

if($category_filter > 0) {
    $recipes_query .= " AND r.id IN (SELECT recipe_id FROM recipe_categories WHERE category_id = $category_filter)";
}

$recipes_query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$recipes_result = mysqli_query($conn, $recipes_query);

// Query semua kategori
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-[#708238] to-[#5a6b2d] text-white py-12">
  <div class="container mx-auto px-6">
    <h1 class="text-4xl font-bold mb-2">
      <i class="fas fa-book-open mr-3"></i> Jelajahi Resep
    </h1>
    <p class="text-white/90">Temukan resep masakan nusantara favoritmu</p>
  </div>
</section>

<!-- Filter Section -->
<section class="py-8 bg-white shadow-sm">
  <div class="container mx-auto px-6">
    <div class="flex flex-wrap items-center gap-4">
      <span class="font-semibold text-[#4b3b2b]">
        <i class="fas fa-filter mr-2"></i> Filter Kategori:
      </span>
      <a href="recipes.php" class="px-4 py-2 rounded-lg <?php echo $category_filter == 0 ? 'bg-[#708238] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
        <i class="fas fa-th mr-1"></i> Semua
      </a>
      <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
      <a href="recipes.php?category=<?php echo $category['id']; ?>" class="px-4 py-2 rounded-lg <?php echo $category_filter == $category['id'] ? 'bg-[#708238] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
        <?php echo htmlspecialchars($category['name']); ?>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Recipes Grid -->
<section class="py-12">
  <div class="container mx-auto px-6">
    
    <?php if(mysqli_num_rows($recipes_result) > 0): ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php while($recipe = mysqli_fetch_assoc($recipes_result)): ?>
      <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1">
        
        <!-- Recipe Image -->
        <div class="h-48 bg-gradient-to-br from-[#708238] to-[#5a6b2d] flex items-center justify-center">
          <?php if($recipe['photo']): ?>
          <img src="assets/images/<?php echo htmlspecialchars($recipe['photo']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-full object-cover">
          <?php else: ?>
          <i class="fas fa-utensils text-white text-6xl"></i>
          <?php endif; ?>
        </div>

        <!-- Recipe Content -->
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#4b3b2b] mb-2 line-clamp-2">
            <?php echo htmlspecialchars($recipe['title']); ?>
          </h3>
          
          <p class="text-gray-600 text-sm mb-4 line-clamp-3">
            <?php echo htmlspecialchars(substr($recipe['steps'], 0, 100)) . '...'; ?>
          </p>

          <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
            <span>
              <i class="fas fa-user-circle mr-1"></i>
              <?php echo htmlspecialchars($recipe['author_name']); ?>
            </span>
            <span>
              <i class="fas fa-calendar-alt mr-1"></i>
              <?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
            </span>
          </div>

          <a href="recipe_detail.php?id=<?php echo $recipe['id']; ?>" class="block w-full text-center bg-[#708238] text-white py-2 rounded-lg hover:bg-[#5a6b2d] transition">
            <i class="fas fa-eye mr-2"></i> Lihat Resep
          </a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div class="mt-12 flex justify-center space-x-2">
      <?php if($page > 1): ?>
      <a href="recipes.php?page=<?php echo $page - 1; ?><?php echo $category_filter > 0 ? '&category='.$category_filter : ''; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
        ← Prev
      </a>
      <?php endif; ?>

      <?php for($i = 1; $i <= $total_pages; $i++): ?>
      <a href="recipes.php?page=<?php echo $i; ?><?php echo $category_filter > 0 ? '&category='.$category_filter : ''; ?>" class="px-4 py-2 <?php echo $i == $page ? 'bg-[#708238] text-white' : 'bg-white border border-gray-300 hover:bg-gray-100'; ?> rounded-lg transition">
        <?php echo $i; ?>
      </a>
      <?php endfor; ?>

      <?php if($page < $total_pages): ?>
      <a href="recipes.php?page=<?php echo $page + 1; ?><?php echo $category_filter > 0 ? '&category='.$category_filter : ''; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
        Next →
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty State -->
    <div class="text-center py-16">
      <i class="fas fa-search text-gray-300 text-8xl mb-4"></i>
      <h3 class="text-2xl font-bold text-[#4b3b2b] mb-2">Resep Tidak Ditemukan</h3>
      <p class="text-gray-600 mb-6">Belum ada resep di kategori ini</p>
      <a href="recipes.php" class="inline-block bg-[#708238] text-white px-6 py-2 rounded-lg hover:bg-[#5a6b2d] transition">
        <i class="fas fa-arrow-left mr-2"></i> Lihat Semua Resep
      </a>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
