# 🍳 Recipe Management System (Resep Masakan)

Sistem manajemen resep masakan berbasis web dengan fitur authentication, role-based access control, dan approval workflow. Dibangun dengan PHP Native, MySQL, dan Tailwind CSS.

## 📋 Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [First Run Setup](#first-run-setup)
5. [Default Accounts](#default-accounts)
6. [Folder Structure](#folder-structure)
7. [Database Schema](#database-schema)
8. [Usage Guide](#usage-guide)
9. [Screenshots](#screenshots)
10. [Troubleshooting](#troubleshooting)
11. [FAQ](#faq)

---

## ✨ Features

### 🔐 Authentication & Authorization
- ✅ User Registration dengan validasi email unik
- ✅ Login System dengan session management
- ✅ Role-Based Access Control (Admin & User)
- ✅ Password Hashing (MD5)
- ✅ Protected Routes dengan middleware

### 👨‍🍳 User Features
- ✅ **User Dashboard** - Statistik personal, resep terbaru, quick actions
- ✅ **My Recipes** - List resep pribadi dengan search, filter status, pagination
- ✅ **Add Recipe** - Tambah resep baru dengan upload foto
- ✅ **Edit Recipe** - Update resep dengan preview foto existing
- ✅ **Delete Recipe** - Hapus resep dengan konfirmasi
- ✅ **Recipe Detail** - Tampilan lengkap resep dengan social share
- ✅ **Image Upload** - Upload foto resep dengan validasi

### 👑 Admin Features
- ✅ **Admin Dashboard** - Overview sistem dengan 6 statistik cards
- ✅ **Recipe Approval** - Approve/Reject resep pending dari users
- ✅ **Manage Users** - View, detail, dan delete users
- ✅ **Manage Recipes** - View semua resep, filter, delete
- ✅ **Manage Categories** - CRUD kategori dengan modal popup
- ✅ **View User Detail** - Profile user, statistik, dan semua resepnya
- ✅ **Cascade Delete** - Delete user otomatis hapus semua resepnya

### 🌐 Public Pages
- ✅ **Homepage** - Hero section, featured recipes, statistics
- ✅ **Recipes Gallery** - Browse semua approved recipes dengan filter kategori
- ✅ **Recipe Detail** - Tampilan lengkap dengan bahan, langkah, author info
- ✅ **About Page** - Informasi aplikasi
- ✅ **Responsive Design** - Mobile-friendly dengan Tailwind CSS

### 🔒 Security Features
- ✅ Prepared Statements (SQL Injection Prevention)
- ✅ XSS Protection dengan htmlspecialchars()
- ✅ Session-based Authentication
- ✅ Role-based Middleware
- ✅ Image Upload Validation (type, size)
- ✅ Transaction Safety untuk operasi kritis
- ✅ .htaccess Protection

---

## 💻 Requirements

- **PHP** 7.4 atau lebih tinggi (8.x recommended)
- **MySQL** 5.7 atau lebih tinggi / MariaDB 10.3+
- **Apache** Web Server dengan mod_rewrite
- **XAMPP/WAMP/LAMP** (untuk development lokal)
- **Browser Modern** (Chrome, Firefox, Safari, Edge)
- **Minimal 100MB** disk space untuk images

---

## 📦 Installation

### Step 1: Clone atau Download Project

```bash
# Clone repository (jika menggunakan Git)
git clone <repository-url> resep_masakan

# Atau download ZIP dan extract ke folder htdocs
# Lokasi: C:\xampp\htdocs\resep_masakan
```

### Step 2: Create Database

Buka **phpMyAdmin** atau MySQL command line:

```sql
CREATE DATABASE resep_masakan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Import Database Schema

**Option A: Menggunakan phpMyAdmin**
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Pilih database `resep_masakan_db`
3. Klik tab "Import"
4. Pilih file `resep_masakan_db.sql` dari root folder
5. Klik "Go"

**Option B: Menggunakan Command Line**
```bash
# Windows (XAMPP)
cd C:\xampp\mysql\bin
mysql -u root resep_masakan_db < C:\xampp\htdocs\resep_masakan\resep_masakan_db.sql

# Linux/Mac
mysql -u root resep_masakan_db < /path/to/resep_masakan/resep_masakan_db.sql
```

### Step 4: Configure Database Connection

Edit file **`config/config.php`**:

```php
<?php
// Database Configuration
$db_host = 'localhost';           // Biasanya 'localhost'
$db_user = 'root';                // Username MySQL (default XAMPP: root)
$db_pass = '';                    // Password MySQL (default XAMPP: kosong)
$db_name = 'resep_masakan_db';    // Nama database yang sudah dibuat
```

**Simpan file** setelah diedit.

### Step 5: Setup Folder Permissions

Pastikan folder `assets/images/` dapat di-write (untuk upload foto):

```bash
# Linux/Mac
chmod 755 assets/images/

# Windows - tidak perlu, sudah otomatis
```

---

## 🎯 First Run Setup

### Step 6: Initialize Admin Account

**PENTING:** Jalankan script ini untuk membuat akun admin pertama kali.

**Buka di browser:**
```
http://localhost/resep_masakan/config/firstacc.php
```

**Apa yang terjadi:**
- Script akan cek apakah sudah ada admin
- Jika belum, akan membuat akun admin default
- Menampilkan kredensial di layar

**Output yang muncul:**
```
====================================
    ADMIN ACCOUNT INITIALIZATION
====================================

✓ Admin account created successfully!

Default Admin Credentials:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Email:    admin@resepmasakan.com
Password: admin123
Role:     admin
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ PENTING: Segera ganti password setelah login pertama!

You can now login at:
http://localhost/resep_masakan/auth/login.php
```

### Step 7: Access Application

Buka di browser:
```
http://localhost/resep_masakan/
```

Anda akan melihat **homepage** dengan:
- Hero section dengan search
- Featured recipes (3 resep terbaru yang approved)
- Popular categories
- Statistics (total resep, users, categories)
- Call-to-action untuk register

---

## 🔑 Default Accounts

Setelah menjalankan `firstacc.php`, Anda akan memiliki:

### Admin Account (Default)
```
Email:    admin@resepmasakan.com
Password: admin123
Role:     admin
```

**Admin Capabilities:**
- ✅ Akses Admin Dashboard dengan statistik lengkap
- ✅ Approve/Reject resep dari users
- ✅ Manage semua users (view, detail, delete dengan cascade)
- ✅ Manage semua recipes (view, filter, delete)
- ✅ Manage categories (add, edit, delete dengan modal)
- ✅ View detail user dengan semua resepnya
- ✅ Akses ke semua fitur sistem

### Register User Baru

Pengguna baru bisa register sendiri melalui:
```
http://localhost/resep_masakan/auth/register.php
```

**User Capabilities:**
- ✅ User Dashboard dengan statistik personal
- ✅ Buat resep baru dengan upload foto
- ✅ Edit resep milik sendiri
- ✅ Delete resep milik sendiri
- ✅ View daftar resep dengan filter & search
- ✅ Resep yang dibuat berstatus "Pending" (tunggu approval admin)
- ❌ Tidak bisa akses fitur admin
- ❌ Tidak bisa edit/delete resep user lain

### 🔒 Security Warning

**SEGERA setelah login pertama:**
1. Login sebagai admin
2. Ubah password admin (lewat database atau buat admin baru)
3. Hapus file `config/firstacc.php` untuk keamanan production

---

## 📁 Folder Structure

```
resep_masakan/
│
├── 📂 config/
│   ├── config.php              # ⚙️  Database connection & settings
│   └── firstacc.php            # 🔧 Admin initialization script
│
├── 📂 includes/
│   ├── header.php              # 🎨 Header template (navbar, Tailwind, Font Awesome)
│   ├── footer.php              # 🎨 Footer template (sticky footer)
│   └── auth_check.php          # 🔐 Authentication middleware functions
│
├── 📂 auth/
│   ├── login.php               # 🔑 Login page UI
│   ├── register.php            # 📝 Registration page UI
│   └── logout.php              # 🚪 Logout handler
│
├── 📂 process/
│   ├── login_process.php       # ⚡ Login form processing
│   └── register_process.php   # ⚡ Registration form processing
│
├── 📂 admin/
│   ├── dashboard.php           # 📊 Admin dashboard (6 stat cards, pending recipes, recent users)
│   ├── manage_recipes.php      # 📋 All recipes management (filter, delete)
│   ├── approve_recipe.php      # ✅ Approve recipe handler
│   ├── reject_recipe.php       # ❌ Reject recipe handler
│   ├── delete_recipe.php       # 🗑️ Delete recipe handler (admin)
│   ├── manage_users.php        # 👥 User management (list, search, delete)
│   ├── view_user.php           # 👤 User detail page (profile, stats, recipes)
│   ├── delete_user.php         # ❌ Delete user handler (cascade)
│   ├── manage_categories.php   # 🏷️  Category CRUD (modal popup)
│   └── delete_category.php     # 🗑️ Delete category handler
│
├── 📂 user/
│   ├── dashboard.php           # 📊 User dashboard (stats, recent recipes, tips)
│   ├── my_recipes.php          # 📋 User's recipes (search, filter, pagination)
│   ├── add_recipe.php          # ➕ Add new recipe (upload photo)
│   ├── edit_recipe.php         # ✏️  Edit recipe (update photo optional)
│   └── delete_recipe.php       # ❌ Delete recipe handler
│
├── 📂 assets/
│   ├── 📂 css/                 # Custom CSS files
│   ├── 📂 js/                  # Custom JavaScript files
│   └── 📂 images/              # 📸 Recipe image uploads
│       ├── nasgor.webp         # Sample recipe image
│       └── recipe_*.jpg        # Uploaded recipe images
│
├── 📄 index.php                # 🏠 Homepage (hero, featured recipes, categories)
├── 📄 recipes.php              # 📖 Public recipes gallery (browse, filter)
├── 📄 recipe_detail.php        # 📄 Recipe detail page (ingredients, steps, author)
├── 📄 about.php                # ℹ️  About page
├── 📄 resep_masakan_db.sql     # 🗄️  Database schema & sample data
├── 📄 .htaccess                # 🔒 Apache security rules
└── 📄 README.md                # 📖 This documentation
```

---

## 🗄️ Database Schema

### **users** Table
Menyimpan semua akun pengguna (admin dan user biasa)

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) | Primary key, auto-increment |
| `name` | VARCHAR(100) | Nama lengkap user |
| `email` | VARCHAR(100) | Email (unique, untuk login) |
| `password` | VARCHAR(255) | Password (hashed MD5) |
| `role` | ENUM('admin','user') | Role untuk access control |
| `created_at` | TIMESTAMP | Tanggal registrasi |

**Indexes:**
- Primary key: `id`
- Unique key: `email`
- Index: `role`

### **categories** Table
Kategori resep (Indonesian food, Western, Asian, Dessert, etc.)

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) | Primary key, auto-increment |
| `name` | VARCHAR(100) | Nama kategori (unique) |
| `description` | TEXT | Deskripsi kategori |
| `created_at` | TIMESTAMP | Tanggal dibuat |

**Indexes:**
- Primary key: `id`
- Unique key: `name`

**Sample Data:**
- Indonesian Food
- Western Food
- Asian Food
- Dessert
- Beverages

### **recipes** Table
Data resep masakan dengan approval workflow

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) | Primary key, auto-increment |
| `user_id` | INT(11) | Foreign key ke users.id |
| `category_id` | INT(11) | Foreign key ke categories.id |
| `title` | VARCHAR(200) | Judul resep |
| `description` | TEXT | Deskripsi singkat |
| `ingredients` | TEXT | Bahan-bahan (line separated) |
| `instructions` | TEXT | Langkah-langkah (line separated) |
| `cooking_time` | VARCHAR(50) | Waktu memasak (e.g., "30 menit") |
| `servings` | VARCHAR(50) | Porsi (e.g., "4 orang") |
| `difficulty` | ENUM('Easy','Medium','Hard') | Tingkat kesulitan |
| `image` | VARCHAR(255) | Path foto resep |
| `status` | ENUM('pending','approved','rejected') | Status approval |
| `created_at` | TIMESTAMP | Tanggal dibuat |
| `updated_at` | TIMESTAMP | Tanggal terakhir diupdate |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE CASCADE)
- `category_id` → `categories.id` (ON DELETE SET NULL)

**Indexes:**
- Primary key: `id`
- Foreign key: `user_id`
- Foreign key: `category_id`
- Index: `status`

**Status Workflow:**
1. **Pending** - Resep baru dibuat user, menunggu approval admin
2. **Approved** - Admin sudah approve, resep muncul di public gallery
3. **Rejected** - Admin reject, resep tidak muncul di public

---

## 📖 Usage Guide

### Untuk User Biasa

#### 1. Register Akun Baru
1. Kunjungi homepage
2. Klik tombol "Register" atau "Get Started"
3. Isi form:
   - Nama lengkap (min. 3 karakter)
   - Email (format valid, unique)
   - Password (min. 6 karakter)
   - Konfirmasi password (harus sama)
4. Klik "Register"
5. Otomatis login dan redirect ke User Dashboard

#### 2. Login
1. Klik "Login" di navbar
2. Masukkan email dan password
3. Klik "Sign In"
4. Redirect ke dashboard sesuai role (admin/user)

#### 3. User Dashboard
Setelah login, user melihat:
- **Statistics Cards** - Total resep, approved, pending
- **Quick Actions** - Tambah resep baru, lihat semua, browse public
- **Recent Recipes** - 5 resep terbaru dengan aksi edit/delete
- **Tips** - Tips membuat resep yang baik

#### 4. Tambah Resep Baru (Add Recipe)
1. Dashboard → "Add New Recipe" atau menu "My Recipes" → "Add Recipe"
2. Isi form:
   - **Title** - Nama resep (min. 3 karakter)
   - **Category** - Pilih kategori dari dropdown
   - **Description** - Deskripsi singkat resep
   - **Ingredients** - Bahan-bahan (satu per baris)
   - **Instructions** - Langkah-langkah memasak (satu per baris)
   - **Cooking Time** - Estimasi waktu (e.g., "45 menit")
   - **Servings** - Jumlah porsi (e.g., "4 orang")
   - **Difficulty** - Easy/Medium/Hard
   - **Image** - Upload foto (JPG/PNG/WEBP, max 5MB)
3. Klik "Submit Recipe"
4. Resep tersimpan dengan status "Pending"
5. Success message → redirect ke My Recipes

#### 5. Lihat My Recipes
1. Menu "My Recipes"
2. Fitur yang tersedia:
   - **Search** - Cari berdasarkan judul/deskripsi
   - **Filter Status** - All/Approved/Pending/Rejected
   - **Pagination** - 10 resep per halaman
   - **Actions** - Edit (✏️) dan Delete (🗑️) per resep
3. Klik nomor halaman untuk navigasi

#### 6. Edit Resep
1. My Recipes → Klik icon Edit (✏️)
2. Form pre-filled dengan data existing
3. Ubah field yang diinginkan
4. **Update foto** (optional):
   - Upload foto baru untuk replace
   - Atau biarkan kosong untuk keep foto lama
5. Klik "Update Recipe"
6. Success message → redirect ke My Recipes

**Note:** Edit resep akan reset status ke "Pending" untuk di-review ulang admin.

#### 7. Delete Resep
1. My Recipes → Klik icon Delete (🗑️)
2. Konfirmasi penghapusan (JavaScript alert)
3. Resep terhapus permanent (termasuk foto dari server)
4. Success message di My Recipes

#### 8. Logout
- Klik dropdown user di navbar → "Logout"
- Session destroyed → redirect ke homepage

### Untuk Administrator

#### 1. Admin Dashboard
Setelah login sebagai admin, melihat:

**Statistics Cards (6 cards):**
- Total Recipes
- Pending Approval
- Total Users
- Approved Recipes
- Rejected Recipes
- Total Categories

**Quick Actions:**
- Manage Recipes
- Manage Users
- Manage Categories
- View Public Site

**Pending Recipes Review:**
- List resep dengan status "Pending"
- Tombol Approve (✅) dan Reject (❌) langsung
- Quick action tanpa perlu masuk detail

**Recent Users:**
- 5 user terbaru yang register
- Info nama, email, tanggal registrasi

#### 2. Manage Recipes
1. Admin Dashboard → "Manage Recipes"
2. Fitur:
   - **Search** - Cari berdasarkan title/description/author
   - **Filter Status** - All/Approved/Pending/Rejected
   - **Filter Category** - Semua kategori
   - **Pagination** - 12 resep per halaman
3. Setiap resep menampilkan:
   - Foto thumbnail
   - Title, category, author
   - Status badge
   - Actions: Approve/Reject (jika pending), Delete
4. Actions:
   - **Approve** - Ubah status jadi "approved", resep muncul di public
   - **Reject** - Ubah status jadi "rejected", resep tidak muncul
   - **Delete** - Hapus permanent (dengan foto)

#### 3. Manage Users
1. Admin Dashboard → "Manage Users"
2. Fitur:
   - **Search** - Cari berdasarkan nama/email
   - **Filter Role** - All/Admin/User
3. Table columns:
   - No, Name, Email, Role, Joined Date, Actions
4. Actions per user:
   - **View Detail** (👁️) - Lihat profile lengkap user
   - **Delete** (🗑️) - Hapus user + semua resepnya (cascade)

**Proteksi:** Admin tidak bisa delete diri sendiri

#### 4. View User Detail
1. Manage Users → Klik icon Eye (👁️)
2. Informasi ditampilkan:
   - **Profile Card** - Nama, email, role, member since
   - **Statistics** - Total resep, approved, pending
   - **User's Recipes** - Semua resep user (max 10, ada link "View All")
3. Tombol "Delete User" di bawah (jika bukan diri sendiri)

#### 5. Manage Categories
1. Admin Dashboard → "Manage Categories"
2. Fitur:
   - **Add Category** - Button untuk tambah kategori baru (modal popup)
   - **List Categories** - Table semua kategori
   - **Actions** - Edit (✏️) dan Delete (🗑️)

**Add Category (Modal):**
- Klik "Add New Category"
- Modal popup muncul
- Isi nama dan deskripsi
- Submit → kategori tersimpan

**Edit Category (Modal):**
- Klik icon Edit
- Modal popup dengan data pre-filled
- Ubah nama/deskripsi
- Update → tersimpan

**Delete Category:**
- Klik icon Delete
- Konfirmasi
- Kategori terhapus
- **Note:** Resep dengan kategori ini akan category_id-nya jadi NULL

---

## 📸 Screenshots

### Public Pages

**Homepage:**
- Hero section dengan search bar
- Featured Recipes (3 latest approved)
- Popular Categories (grid)
- Statistics (recipes, users, categories)
- CTA untuk register

**Recipes Gallery (`recipes.php`):**
- Grid layout resep approved
- Filter by category (dropdown)
- Search by title
- Card: foto, title, category badge, author, cooking time, difficulty
- Pagination

**Recipe Detail (`recipe_detail.php`):**
- Hero image fullwidth
- Title, category, author info
- Cooking time, servings, difficulty
- Ingredients list
- Step-by-step instructions
- Social share buttons (Facebook, Twitter, WhatsApp, Copy link)
- Related recipes (same category)

### User Pages

**User Dashboard:**
- Welcome message dengan nama user
- 3 statistics cards
- Quick action cards (3 cards)
- Recent recipes table
- Tips section

**My Recipes:**
- Search & filter form
- Table dengan thumbnail, title, category, status, date, actions
- Pagination
- Empty state jika belum ada resep

**Add/Edit Recipe:**
- Form lengkap dengan semua fields
- Image upload dengan preview (edit)
- Validation client & server-side
- Back button ke My Recipes

### Admin Pages

**Admin Dashboard:**
- 6 statistics cards (2 rows)
- Quick actions (4 cards)
- Pending recipes review table dengan inline approve/reject
- Recent users list

**Manage Recipes:**
- Search & filter (status + category)
- Grid cards dengan foto, info, status badge
- Approve/Reject/Delete buttons
- Pagination

**Manage Users:**
- Search & filter
- Table dengan user info
- View detail & delete actions
- Role badges

**View User:**
- User profile card dengan avatar icon
- Statistics (3 cards)
- User's recipes table
- Delete user button

**Manage Categories:**
- Add button yang trigger modal
- Categories table
- Edit (modal) & Delete actions
- Modal popup untuk add/edit

---

## 🔒 Security Features

### Implemented Security

✅ **SQL Injection Prevention**
```php
// Semua query menggunakan prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
```

✅ **XSS Protection**
```php
// Semua user input di-escape saat ditampilkan
echo htmlspecialchars($recipe['title']);
```

✅ **File Upload Security**
```php
// Validasi tipe file
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
// Validasi ukuran (max 5MB)
// Rename file dengan uniqid()
```

✅ **Session Management**
- Session timeout 3600 detik (1 jam)
- Session regeneration saat login
- Secure session configuration

✅ **Role-Based Access Control**
```php
// Middleware di auth_check.php
require_admin();  // Redirect jika bukan admin
require_user();   // Redirect jika bukan user
```

✅ **Transaction Safety**
```php
// Delete user dengan transaction
$conn->begin_transaction();
try {
    // Delete recipes
    // Delete user
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}
```

✅ **.htaccess Protection**
- Directory listing disabled
- Protect config files
- Rewrite rules

### ⚠️ Security Improvements Needed

**CRITICAL - Password Hashing:**
```php
// Current (MD5 - WEAK!)
$password = md5($_POST['password']);

// Recommended (bcrypt)
// Register:
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Login:
if (password_verify($_POST['password'], $user['password'])) {
    // Success
}
```

**Add CSRF Protection:**
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Form
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validate
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}
```

**Add Rate Limiting:**
- Limit login attempts per IP
- Prevent brute force

**Image Validation Enhancement:**
- Verify image content dengan getimagesize()
- Re-encode uploaded images
- Store outside webroot

### Security Checklist

- [ ] Ganti password admin default
- [ ] Delete file `config/firstacc.php` setelah setup
- [ ] Upgrade MD5 ke bcrypt
- [ ] Add CSRF tokens
- [ ] Implement rate limiting
- [ ] Enable HTTPS di production
- [ ] Set secure cookie flags
- [ ] Regular database backup
- [ ] Update PHP/MySQL versi terbaru
- [ ] Review file permissions (755 folders, 644 files)

---

## 🔧 Troubleshooting

### Database Connection Error

**Error:** `Connection failed: Access denied`

**Solusi:**
1. Cek credentials di `config/config.php`
2. Pastikan MySQL running di XAMPP Control Panel
3. Test koneksi:
   ```bash
   mysql -u root -p
   ```
4. Pastikan database `resep_masakan_db` sudah dibuat

### Can't Create Admin Account

**Error:** `firstacc.php` blank page atau error

**Solusi:**
1. Enable error display di `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Cek PHP error log: `C:\xampp\php\logs\php_error_log`
3. Pastikan database sudah di-import
4. Cek table `users` sudah ada:
   ```sql
   SHOW TABLES FROM resep_masakan_db;
   ```

### Login Tidak Berhasil

**Error:** "Invalid email or password"

**Solusi:**
1. Verifikasi akun ada di database:
   ```sql
   SELECT * FROM users WHERE email = 'admin@resepmasakan.com';
   ```
2. Re-run `firstacc.php` untuk recreate admin
3. Clear browser cache dan cookies
4. Cek password hash di `login_process.php` cocok dengan yang di database

### Image Upload Gagal

**Error:** Gambar tidak tersimpan atau error

**Solusi:**
1. Cek folder `assets/images/` ada dan writable
2. Windows: klik kanan folder → Properties → pastikan tidak Read-only
3. Linux/Mac: `chmod 755 assets/images/`
4. Cek ukuran file (max 5MB)
5. Cek tipe file (jpg, jpeg, png, webp only)
6. Cek PHP upload settings di `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

### Permission Denied ke Admin Pages

**Error:** Redirect ke login atau user dashboard

**Solusi:**
1. Pastikan login sebagai admin
2. Cek role di database:
   ```sql
   SELECT role FROM users WHERE email = 'your@email.com';
   ```
3. Jika perlu, ubah role:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
   ```
4. Logout dan login lagi
5. Clear session:
   ```php
   // Add di halaman test
   session_start();
   var_dump($_SESSION);
   ```

### Resep Tidak Muncul di Public

**Error:** Resep sudah dibuat tapi tidak muncul di `recipes.php`

**Solusi:**
1. Cek status resep di database:
   ```sql
   SELECT id, title, status FROM recipes WHERE id = <recipe_id>;
   ```
2. Hanya resep dengan status "approved" yang muncul di public
3. Admin perlu approve resep di Manage Recipes
4. Clear browser cache

### Pagination Tidak Jalan

**Error:** Tidak bisa navigasi halaman

**Solusi:**
1. Cek query count total items
2. Cek parameter `page` di URL
3. Debug:
   ```php
   var_dump($total_recipes, $total_pages, $page);
   ```
4. Pastikan ada resep di database

### Category Dropdown Kosong

**Error:** Dropdown kategori kosong di add/edit recipe

**Solusi:**
1. Pastikan ada data di table `categories`:
   ```sql
   SELECT * FROM categories;
   ```
2. Jika kosong, insert manual atau lewat admin panel:
   ```sql
   INSERT INTO categories (name, description) VALUES
   ('Indonesian Food', 'Traditional Indonesian recipes'),
   ('Western Food', 'Western style cooking');
   ```

---

## ❓ FAQ

### Q: Apa perbedaan status Pending, Approved, Rejected?
**A:**
- **Pending** - Resep baru dibuat user, menunggu review admin
- **Approved** - Admin approve, resep muncul di public gallery
- **Rejected** - Admin reject, resep tidak muncul di public (tapi masih ada di My Recipes user)

### Q: Apakah user bisa langsung publish resep tanpa approval?
**A:** Tidak. Semua resep user berstatus "Pending" dan perlu di-approve admin dulu. Ini untuk menjaga kualitas konten.

### Q: Bagaimana cara menambah kategori baru?
**A:** 
- Login sebagai admin
- Ke Manage Categories
- Klik "Add New Category"
- Isi nama dan deskripsi di modal
- Submit

### Q: Apakah bisa upload video?
**A:** Saat ini hanya support image (JPG, PNG, WEBP). Untuk video perlu modifikasi:
1. Update validation di `add_recipe.php`
2. Tambah field `video` di table `recipes`
3. Support embed YouTube/Vimeo lebih praktis

### Q: Bagaimana cara backup database?
**A:**
```bash
# Command line
mysqldump -u root resep_masakan_db > backup_resep_$(date +%Y%m%d).sql

# phpMyAdmin
Select database → Export → Go
```

### Q: Bisa deploy ke hosting?
**A:** Ya, langkah-langkah:
1. Upload semua file ke public_html
2. Export database lokal
3. Import database ke hosting (via cPanel/phpMyAdmin)
4. Edit `config/config.php` dengan kredensial hosting
5. Set permissions folder `assets/images/` (755)
6. Ganti password admin
7. Delete `config/firstacc.php`
8. Test semua fitur

### Q: Bagaimana cara menambah field baru di resep (misal: harga)?
**A:**
1. Alter table:
   ```sql
   ALTER TABLE recipes ADD COLUMN price DECIMAL(10,2) DEFAULT 0 AFTER servings;
   ```
2. Update `add_recipe.php` - tambah input field
3. Update INSERT query
4. Update `edit_recipe.php` - tambah input field
5. Update UPDATE query
6. Update display di `my_recipes.php`, `recipe_detail.php`

### Q: Apakah responsive di mobile?
**A:** Ya, menggunakan Tailwind CSS yang mobile-first. Sudah ditest di:
- Desktop (1920px+)
- Tablet (768px - 1024px)
- Mobile (320px - 767px)

### Q: Bagaimana cara menambah role baru (misal: Moderator)?
**A:**
1. Alter table:
   ```sql
   ALTER TABLE users MODIFY role ENUM('admin','user','moderator');
   ```
2. Tambah function di `auth_check.php`:
   ```php
   function require_moderator() { /* ... */ }
   ```
3. Update navigation di `header.php`
4. Buat halaman khusus moderator

### Q: Apakah bisa export resep ke PDF?
**A:** Belum ada fitur bawaan. Bisa ditambahkan dengan:
- Library: TCPDF atau mPDF
- Buat endpoint `recipe_pdf.php?id=X`
- Generate PDF dari data resep

---

## 📚 Useful Queries

### Statistics Queries

```sql
-- Total resep per kategori
SELECT 
    c.name as category,
    COUNT(r.id) as total_recipes
FROM categories c
LEFT JOIN recipes r ON c.id = r.category_id
GROUP BY c.id
ORDER BY total_recipes DESC;

-- Top 5 user dengan resep terbanyak
SELECT 
    u.name,
    u.email,
    COUNT(r.id) as total_recipes,
    SUM(CASE WHEN r.status = 'approved' THEN 1 ELSE 0 END) as approved
FROM users u
LEFT JOIN recipes r ON u.id = r.user_id
WHERE u.role = 'user'
GROUP BY u.id
ORDER BY total_recipes DESC
LIMIT 5;

-- Resep per status
SELECT 
    status,
    COUNT(*) as count
FROM recipes
GROUP BY status;

-- User tanpa resep
SELECT u.* 
FROM users u
LEFT JOIN recipes r ON u.id = r.user_id
WHERE r.id IS NULL AND u.role = 'user';
```

### Maintenance Queries

```sql
-- Hapus resep rejected lebih dari 30 hari
DELETE FROM recipes 
WHERE status = 'rejected' 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Reset password user
UPDATE users 
SET password = MD5('newpassword123') 
WHERE email = 'user@example.com';

-- Promote user jadi admin
UPDATE users 
SET role = 'admin' 
WHERE email = 'user@example.com';

-- Hapus user tanpa resep
DELETE FROM users 
WHERE role = 'user' 
AND id NOT IN (SELECT DISTINCT user_id FROM recipes);

-- Bulk approve semua resep pending dari user tertentu
UPDATE recipes 
SET status = 'approved' 
WHERE user_id = 5 AND status = 'pending';
```

---

## 🎯 Quick Start Checklist

Gunakan checklist ini untuk setup project baru:

**Database Setup:**
- [ ] Create database `resep_masakan_db`
- [ ] Import file `resep_masakan_db.sql`
- [ ] Verify tables created (users, categories, recipes)

**Configuration:**
- [ ] Edit `config/config.php` dengan credentials database
- [ ] Test database connection
- [ ] Buka `config/firstacc.php` di browser
- [ ] Catat kredensial admin yang muncul

**First Login:**
- [ ] Login dengan admin account
- [ ] Test akses admin dashboard
- [ ] Test approve/reject resep (jika ada pending)
- [ ] Test manage categories
- [ ] Ganti password admin (via database atau buat admin baru)

**User Testing:**
- [ ] Register akun user baru
- [ ] Login sebagai user
- [ ] Test add recipe dengan upload foto
- [ ] Test edit recipe
- [ ] Test delete recipe
- [ ] Verify foto tersimpan di `assets/images/`

**Public Pages:**
- [ ] Test homepage
- [ ] Test recipes gallery
- [ ] Test recipe detail
- [ ] Test search functionality
- [ ] Test filter by category

**Security:**
- [ ] Delete `config/firstacc.php` (untuk production)
- [ ] Verify .htaccess aktif
- [ ] Set folder permissions correct
- [ ] Test unauthorized access (akses admin page tanpa login)

**Ready for Production:**
- [ ] Backup database
- [ ] Change all default passwords
- [ ] Enable HTTPS
- [ ] Optimize images
- [ ] Test on mobile devices
- [ ] SEO optimization (meta tags, etc.)

---

## 📝 Development Notes

### Tech Stack
- **Backend:** PHP 7.4+ (Native, no framework)
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** HTML5, Tailwind CSS 3.x (CDN)
- **Icons:** Font Awesome 6.4.0 (CDN)
- **JavaScript:** Vanilla JS (minimal usage)
- **Server:** Apache with mod_rewrite

### Code Structure
- **MVC-like pattern** - Views (pages), Controllers (process), Models (implicit in queries)
- **Prepared Statements** - All queries untuk prevent SQL injection
- **Middleware Pattern** - `auth_check.php` untuk access control
- **Session-based Auth** - Traditional PHP sessions
- **Transaction Support** - Critical operations use transactions

### Future Enhancements
- [ ] Upgrade password hashing (MD5 → bcrypt)
- [ ] Add CSRF protection
- [ ] Add email verification
- [ ] Add password reset functionality
- [ ] Add recipe rating system
- [ ] Add comments on recipes
- [ ] Add favorite/bookmark recipes
- [ ] Add recipe print view
- [ ] Add recipe sharing to social media (auto-post)
- [ ] Add nutritional information
- [ ] Add ingredient shopping list
- [ ] Add recipe search by ingredients
- [ ] Add multi-language support
- [ ] Add dark mode
- [ ] API endpoints untuk mobile app

---

## 📞 Support

Untuk pertanyaan atau issue:
1. Cek bagian Troubleshooting di atas
2. Cek FAQ
3. Review code comments di file

---

## 📜 License

Free to use untuk project personal maupun commercial.

---

**Selamat Memasak! 🍳👨‍🍳**

*Last Updated: October 28, 2025*
*Version: 1.0.0*
*Built with ❤️ and PHP*
