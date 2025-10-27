<?php
require_once __DIR__ . '/../config/config.php';

// Ambil pesan error dari session (jika ada dari redirect)
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

// Proses register dilakukan di file process
$result = include '../process/register_process.php';

// Jika ada error dari result dan belum ada error dari session
if (!empty($result['error']) && empty($error)) {
    $error = $result['error'];
}

// Include header untuk UI
include '../includes/header.php';
?>

<main class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-[#4b3b2b]">
            <i class="fas fa-user-plus mr-2"></i>Daftar Akun
        </h2>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-2">
                    <i class="fas fa-user mr-2"></i>Nama Lengkap
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Masukkan nama lengkap"
                >
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Masukkan email"
                >
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Minimal 6 karakter"
                >
            </div>
            
            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>Konfirmasi Password
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#708238]"
                    placeholder="Ulangi password"
                >
            </div>
            
            <button 
                type="submit" 
                class="w-full bg-[#708238] text-white py-2 px-4 rounded-lg hover:bg-[#5a6b2d] transition font-medium"
            >
                <i class="fas fa-user-plus mr-2"></i>Daftar
            </button>
        </form>
        
        <p class="text-center mt-4 text-sm">
            Sudah punya akun? 
            <a href="login.php" class="text-[#708238] hover:underline font-medium">
                <i class="fas fa-sign-in-alt mr-1"></i>Login di sini
            </a>
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
