<?php
require_once 'config/config.php';

// Query untuk statistik (publik - tanpa autentikasi)
$total_recipes_query = "SELECT COUNT(*) as total FROM recipes WHERE status = 'approved'";
$total_recipes_result = mysqli_query($conn, $total_recipes_query);
$total_recipes = mysqli_fetch_assoc($total_recipes_result)['total'];

$total_categories_query = "SELECT COUNT(*) as total FROM categories";
$total_categories_result = mysqli_query($conn, $total_categories_query);
$total_categories = mysqli_fetch_assoc($total_categories_result)['total'];

$total_users_query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

// Query untuk resep terbaru yang sudah approved
$recent_recipes_query = "SELECT r.*, u.name as author_name 
                         FROM recipes r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.status = 'approved'
                         ORDER BY r.created_at DESC 
                         LIMIT 6";
$recent_recipes_result = mysqli_query($conn, $recent_recipes_query);

// Query untuk kategori populer
$popular_categories_query = "SELECT c.*, COUNT(rc.recipe_id) as recipe_count
                             FROM categories c
                             LEFT JOIN recipe_categories rc ON c.id = rc.category_id
                             LEFT JOIN recipes r ON rc.recipe_id = r.id AND r.status = 'approved'
                             GROUP BY c.id
                             ORDER BY recipe_count DESC
                             LIMIT 4";
$popular_categories_result = mysqli_query($conn, $popular_categories_query);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-[#708238] to-[#5a6b2d] text-white py-16">
  <div class="container mx-auto px-6 text-center">
    <h1 class="text-4xl md:text-5xl font-bold mb-4">
      <i class="fas fa-utensils mr-3"></i> Selamat Datang di Resep Nusantara
    </h1>
    <p class="text-xl md:text-2xl text-white/90 mb-8">
      Temukan dan Bagikan Resep Masakan Nusantara Favoritmu
    </p>
    <div class="flex justify-center space-x-4">
      <a href="recipes.php" class="bg-white text-[#708238] px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-flex items-center">
        <i class="fas fa-search mr-2"></i> Jelajahi Resep
      </a>
      <?php if(!isset($_SESSION['user_id'])): ?>
      <a href="auth/register.php" class="bg-[#4b3b2b] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#3a2d20] transition inline-flex items-center">
        <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
      </a>
      <?php else: ?>
      <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>" class="bg-[#4b3b2b] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#3a2d20] transition inline-flex items-center">
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Saya
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Statistics Section -->
<section class="py-12">
  <div class="container mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <!-- Total Resep -->
      <div class="bg-white rounded-lg shadow-lg p-8 text-center transform hover:scale-105 transition">
        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h3 class="text-4xl font-bold text-[#4b3b2b] mb-2"><?php echo $total_recipes; ?></h3>
        <p class="text-gray-600 font-medium">Resep Tersedia</p>
      </div>

      <!-- Total Kategori -->
      <div class="bg-white rounded-lg shadow-lg p-8 text-center transform hover:scale-105 transition">
        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
          </svg>
        </div>
        <h3 class="text-4xl font-bold text-[#4b3b2b] mb-2"><?php echo $total_categories; ?></h3>
        <p class="text-gray-600 font-medium">Kategori</p>
      </div>

      <!-- Total Chef -->
      <div class="bg-white rounded-lg shadow-lg p-8 text-center transform hover:scale-105 transition">
        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <h3 class="text-4xl font-bold text-[#4b3b2b] mb-2"><?php echo $total_users; ?></h3>
        <p class="text-gray-600 font-medium">Chef Bergabung</p>
      </div>

    </div>
  </div>
</section>

<!-- Popular Categories -->
<section class="py-12 bg-white">
  <div class="container mx-auto px-6">
    <h2 class="text-3xl font-bold text-[#4b3b2b] text-center mb-8">
      <i class="fas fa-fire text-orange-500 mr-2"></i> Kategori Populer
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php 
      $category_icons = [
        'fas fa-bowl-rice',
        'fas fa-pizza-slice', 
        'fas fa-cookie-bite',
        'fas fa-mug-hot',
        'fas fa-ice-cream',
        'fas fa-burger'
      ];
      $icon_index = 0;
      mysqli_data_seek($popular_categories_result, 0); // Reset pointer
      while($category = mysqli_fetch_assoc($popular_categories_result)): 
      ?>
      <div class="bg-[#f5f0e6] rounded-lg p-6 hover:shadow-lg transition text-center border-2 border-transparent hover:border-[#708238]">
        <div class="text-[#708238] text-5xl mb-4">
          <i class="<?php echo $category_icons[$icon_index % count($category_icons)]; ?>"></i>
        </div>
        <h3 class="text-xl font-bold text-[#4b3b2b] mb-2">
          <?php echo htmlspecialchars($category['name']); ?>
        </h3>
        <p class="text-gray-600 text-sm">
          <i class="fas fa-book-open mr-1"></i> <?php echo $category['recipe_count']; ?> Resep
        </p>
      </div>
      <?php 
      $icon_index++;
      endwhile; 
      ?>
    </div>
  </div>
</section>

<!-- Recent Recipes -->
<section class="py-12">
  <div class="container mx-auto px-6">
    <div class="flex justify-between items-center mb-8">
      <h2 class="text-3xl font-bold text-[#4b3b2b]">
        <i class="fas fa-clock text-[#708238] mr-2"></i> Resep Terbaru
      </h2>
      <a href="recipes.php" class="text-[#708238] hover:underline font-medium flex items-center">
        Lihat Semua <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php while($recipe = mysqli_fetch_assoc($recent_recipes_result)): ?>
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
  </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-[#4b3b2b] to-[#3a2d20] text-white">
  <div class="container mx-auto px-6 text-center">
    <h2 class="text-3xl md:text-4xl font-bold mb-4">
      <i class="fas fa-lightbulb text-yellow-400 mr-2"></i> Punya Resep Favorit?
    </h2>
    <p class="text-xl text-white/90 mb-8">
      Bagikan resep masakan Anda dan inspirasi ribuan orang lainnya!
    </p>
    <?php if(!isset($_SESSION['user_id'])): ?>
    <a href="auth/register.php" class="inline-block bg-[#708238] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#5a6b2d] transition">
      <i class="fas fa-share-alt mr-2"></i> Mulai Berbagi Resep
    </a>
    <?php else: ?>
    <a href="user/add_recipe.php" class="inline-block bg-[#708238] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#5a6b2d] transition">
      <i class="fas fa-plus-circle mr-2"></i> Tambah Resep Sekarang
    </a>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
