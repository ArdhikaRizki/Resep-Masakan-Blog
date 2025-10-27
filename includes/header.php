<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resep Nusantara</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Line clamp utility for text truncation */
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .line-clamp-3 {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-[#f5f0e6] text-[#4b3b2b] h-full flex flex-col">

<?php
        // Deteksi path saat ini untuk menentukan base URL
        $current_path = $_SERVER['PHP_SELF'];
        $base_url = '';
        
        // Jika di folder auth/ atau process/, maka base_url = '../'
        if (strpos($current_path, '/auth/') !== false || strpos($current_path, '/process/') !== false) {
            $base_url = '../';
        }
        // Jika di folder admin/ atau user/, maka base_url = '../'
        elseif (strpos($current_path, '/admin/') !== false || strpos($current_path, '/user/') !== false) {
            $base_url = '../';
        }
        // Jika di root, maka base_url = ''
        else {
            $base_url = '';
        }
      ?>
  <!-- Header / Navbar -->
  <header class="bg-[#f5f0e6] shadow-md">
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
      <h1 class="text-2xl font-bold">
        <a href="<?php echo $base_url; ?>index.php" class="hover:text-[#708238] transition">
          Resep Nusantara
        </a>
      </h1>
      <nav>
        <ul class="flex space-x-6 items-center">
          <li><a href="<?php echo $base_url; ?>index.php" class="hover:text-[#708238] transition">Beranda</a></li>
          <li><a href="<?php echo $base_url; ?>recipes.php" class="hover:text-[#708238] transition">Resep</a></li>
          <li><a href="<?php echo $base_url; ?>about.php" class="hover:text-[#708238] transition">Tentang</a></li>
          
          <?php if(isset($_SESSION['user_id'])): ?>
            <li>
              <a href="<?php echo $base_url . ($_SESSION['role'] == 'admin' ? 'admin/' : 'user/'); ?>dashboard.php" class="hover:text-[#708238] transition">
                Dashboard
              </a>
            </li>
            <li>
              <span class="text-sm bg-[#708238] text-white px-3 py-1 rounded-full">
                <?php echo htmlspecialchars($_SESSION['name']); ?>
              </span>
            </li>
            <li>
              <a href="<?php echo $base_url; ?>auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                Logout
              </a>
            </li>
          <?php else: ?>
            <li><a href="<?php echo $base_url; ?>auth/login.php" class="hover:text-[#708238] transition">Login</a></li>
            <li><a href="<?php echo $base_url; ?>auth/register.php" class="bg-[#708238] text-white px-4 py-2 rounded-lg hover:bg-[#5a6b2d] transition">Daftar</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Wrapper untuk content agar footer di bawah -->
  <div class="flex-grow">
