-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Okt 2025 pada 02.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resep_masakan_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Makanan Berat', '2025-10-27 00:15:59', '2025-10-27 00:15:59'),
(2, 'Makanan Ringan', '2025-10-27 00:15:59', '2025-10-27 00:15:59'),
(3, 'Minuman', '2025-10-27 00:15:59', '2025-10-27 00:15:59'),
(4, 'Makanan Penutup', '2025-10-27 00:15:59', '2025-10-27 00:15:59'),
(5, 'Kue Tradisional', '2025-10-27 00:15:59', '2025-10-27 00:15:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `ingredients` text NOT NULL,
  `steps` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `title`, `ingredients`, `steps`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nasi Goreng Spesial', '• Nasi putih\n• Telur\n• Kecap manis\n• Bawang merah\n• Bawang putih\n• Garam dan merica', '1. Tumis bumbu hingga harum.\n2. Masukkan telur, aduk rata.\n3. Tambahkan nasi dan bumbu lain.\n4. Sajikan hangat.', 'nasgor.webp', 'approved', '2025-10-27 00:15:59', '2025-10-27 11:15:33'),
(2, 2, 'Kue Klepon Klaten', 'Untuk adonan:\r\n\r\n250 gram tepung ketan putih\r\n\r\n2 sdm tepung beras (agar tekstur lebih kenyal tapi lembut)\r\n\r\n200 ml air hangat\r\n\r\nPasta pandan secukupnya (atau 10 lembar daun pandan diblender dengan air, disaring)\r\n\r\nSejumput garam\r\n\r\nUntuk isi:\r\n\r\n100 gram gula merah (iris halus atau sisir)\r\n\r\nUntuk pelapis:\r\n\r\n100 gram kelapa parut muda\r\n\r\nSejumput garam', 'Siapkan pelapis kelapa:\r\n\r\nKukus kelapa parut dengan sedikit garam selama ±10 menit supaya tidak cepat basi.\r\n\r\nSisihkan untuk pelapis nanti.\r\n\r\nBuat adonan klepon:\r\n\r\nCampurkan tepung ketan, tepung beras, dan garam dalam wadah.\r\n\r\nTuangkan air pandan sedikit demi sedikit sambil diuleni hingga adonan kalis (tidak lengket di tangan dan bisa dipulung).\r\n\r\nBentuk bola klepon:\r\n\r\nAmbil sedikit adonan, pipihkan di telapak tangan.\r\n\r\nIsi dengan gula merah sisir secukupnya, lalu bulatkan kembali hingga rapat (supaya gula tidak bocor saat direbus).\r\n\r\nRebus klepon:\r\n\r\nDidihkan air dalam panci.\r\n\r\nMasukkan klepon satu per satu.\r\n\r\nTunggu sampai klepon mengapung — itu tandanya sudah matang.\r\n\r\nAngkat dengan sendok berlubang dan tiriskan.\r\n\r\nBaluri dengan kelapa:\r\n\r\nGulingkan klepon panas-panas ke dalam kelapa parut yang sudah dikukus tadi hingga tertutup rata', 'recipe_1761615271_69001da7e2e8e.jpg', 'approved', '2025-10-28 08:34:31', '2025-10-28 08:36:16'),
(3, 5, 'nasi bakar', 'nasi', 'nasi dibakar', 'recipe_1761620758_69003316eb0a3.jpg', 'approved', '2025-10-28 10:05:58', '2025-10-28 10:07:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `recipe_categories`
--

CREATE TABLE `recipe_categories` (
  `recipe_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `recipe_categories`
--

INSERT INTO `recipe_categories` (`recipe_id`, `category_id`) VALUES
(1, 1),
(2, 5),
(3, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@resepmasakan.com', '0192023a7bbd73250516f069df18b500', 'admin', '2025-10-27 00:15:58', '2025-10-27 00:15:58'),
(2, 'Dika', 'dika@example.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', '2025-10-27 00:15:59', '2025-10-27 00:15:59'),
(3, 'joy dede', 'joy@gamil', 'b0203240cd391618f8cc3c78185e0cfe', 'user', '2025-10-27 11:48:00', '2025-10-27 11:48:00'),
(4, 'hasih', 'hasih@gmail.com', '9cb87e489dc6ce9f437cd180bb4d1848', 'user', '2025-10-27 12:02:49', '2025-10-27 12:02:49'),
(5, 'rizki', 'rizki@gmail.com', '2df483b552108a4d48d57ce319f2920a', 'user', '2025-10-28 10:03:59', '2025-10-28 10:03:59');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_recipe` (`user_id`);

--
-- Indeks untuk tabel `recipe_categories`
--
ALTER TABLE `recipe_categories`
  ADD PRIMARY KEY (`recipe_id`,`category_id`),
  ADD KEY `fk_category_recipe` (`category_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_user_recipe` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `recipe_categories`
--
ALTER TABLE `recipe_categories`
  ADD CONSTRAINT `fk_category_recipe` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recipe_category` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
