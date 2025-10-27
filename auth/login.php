<?php
require_once __DIR__ . '/../config/config.php';

// Ambil pesan sukses dari session (dari redirect register)
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

// Memproses logic login dari folder process
$error = include '../process/login_process.php';

// Include header untuk UI
include '../includes/header.php';
?>

<main class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-[#4b3b2b]">
            <i class="fas fa-sign-in-alt mr-2"></i>Login
        </h2>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Masukkan email"
                >
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Masukkan password"
                >
            </div>
            
            <button 
                type="submit" 
                class="w-full bg-[#708238] text-white py-2 px-4 rounded-lg hover:bg-[#5a6b2d] transition font-medium"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
        
        <p class="text-center mt-4 text-sm">
            Belum punya akun? 
            <a href="register.php" class="text-[#708238] hover:underline font-medium">
                <i class="fas fa-user-plus mr-1"></i>Daftar di sini
            </a>
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
