<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-[#708238] to-[#5a6b2d] text-white py-12">
  <div class="container mx-auto px-6">
    <h1 class="text-4xl font-bold mb-2">
      <i class="fas fa-info-circle mr-3"></i> Tentang Kami
    </h1>
    <p class="text-white/90">Mengenal lebih dekat Resep Nusantara</p>
  </div>
</section>

<!-- About Content -->
<section class="py-16">
  <div class="container mx-auto px-6">
    <div class="max-w-4xl mx-auto">
      
      <!-- Mission Section -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="flex items-center mb-4">
          <div class="bg-[#708238] text-white w-12 h-12 rounded-full flex items-center justify-center text-2xl mr-4">
            <i class="fas fa-bullseye"></i>
          </div>
          <h2 class="text-2xl font-bold text-[#4b3b2b]">Misi Kami</h2>
        </div>
        <p class="text-gray-700 leading-relaxed">
          Resep Nusantara hadir sebagai platform untuk melestarikan dan membagikan kekayaan kuliner Indonesia. 
          Kami percaya bahwa setiap resep memiliki cerita dan kenangan yang patut dibagikan kepada generasi berikutnya. 
          Melalui platform ini, kami ingin memudahkan siapa saja untuk menemukan, membuat, dan membagikan resep masakan nusantara.
        </p>
      </div>

      <!-- Features Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-4xl mb-4 text-center text-[#708238]">
            <i class="fas fa-book-open"></i>
          </div>
          <h3 class="text-xl font-bold text-[#4b3b2b] mb-3 text-center">Koleksi Resep Lengkap</h3>
          <p class="text-gray-600 text-center">
            Ribuan resep masakan nusantara dari berbagai daerah, mulai dari makanan tradisional hingga modern.
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-4xl mb-4 text-center text-[#708238]">
            <i class="fas fa-users"></i>
          </div>
          <h3 class="text-xl font-bold text-[#4b3b2b] mb-3 text-center">Komunitas Chef</h3>
          <p class="text-gray-600 text-center">
            Bergabung dengan komunitas pecinta masak dan berbagi resep favorit Anda dengan ribuan pengguna lainnya.
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-4xl mb-4 text-center text-[#708238]">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3 class="text-xl font-bold text-[#4b3b2b] mb-3 text-center">Resep Terverifikasi</h3>
          <p class="text-gray-600 text-center">
            Setiap resep telah melewati proses verifikasi untuk memastikan kualitas dan keakuratan informasi.
          </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-4xl mb-4 text-center text-[#708238]">
            <i class="fas fa-mobile-alt"></i>
          </div>
          <h3 class="text-xl font-bold text-[#4b3b2b] mb-3 text-center">Mudah Diakses</h3>
          <p class="text-gray-600 text-center">
            Akses resep favorit Anda kapan saja dan di mana saja melalui perangkat apapun dengan tampilan yang responsif.
          </p>
        </div>

      </div>

      <!-- Contact Section -->
      <div class="bg-gradient-to-r from-[#4b3b2b] to-[#3a2d20] text-white rounded-lg shadow-lg p-8 text-center">
        <h2 class="text-2xl font-bold mb-4">
          <i class="fas fa-envelope mr-2"></i> Hubungi Kami
        </h2>
        <p class="text-white/90 mb-6">
          Punya pertanyaan atau saran? Kami senang mendengar dari Anda!
        </p>
        <div class="flex flex-col md:flex-row justify-center items-center space-y-4 md:space-y-0 md:space-x-8">
          <div class="flex items-center">
            <i class="fas fa-envelope-open text-xl mr-2"></i>
            <span>info@resepnusantara.com</span>
          </div>
          <div class="flex items-center">
            <i class="fas fa-phone text-xl mr-2"></i>
            <span>+62 812-3456-7890</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
