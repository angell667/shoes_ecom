-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2026 at 06:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shoes_ecom`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Running Shoes', 'running-shoes', 'High-performance running shoes designed for comfort and speed.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop', '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(2, 'Basketball Shoes', 'basketball-shoes', 'Professional basketball shoes with excellent ankle support and grip.', 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=400&h=400&fit=crop', '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(3, 'Casual Shoes', 'casual-shoes', 'Stylish everyday shoes for casual occasions.', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop', '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(4, 'Formal Shoes', 'formal-shoes', 'Elegant formal shoes for business and special occasions.', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=400&h=400&fit=crop', '2026-03-28 15:05:51', '2026-03-28 15:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_28_184824_create_categories_table', 1),
(5, '2026_03_28_184825_create_orders_table', 1),
(6, '2026_03_28_184825_create_products_table', 1),
(7, '2026_03_28_184826_create_order_items_table', 1),
(8, '2026_03_28_184827_create_cart_items_table', 1),
(9, '2026_03_28_200331_create_personal_access_tokens_table', 2),
(10, '2026_03_28_200528_add_fields_to_users_table', 3),
(11, '2026_03_28_202347_add_is_admin_to_users_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `subtotal`, `tax`, `shipping`, `total`, `shipping_address`, `billing_address`, `payment_method`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-UDLREMTM0D', 'processing', 129.99, 13.00, 0.00, 142.99, 'test address', 'test address', 'cod', 'pending', NULL, '2026-03-28 16:31:18', '2026-03-28 16:31:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `size`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Nike Air Max 270', 129.99, 1, '8', NULL, '2026-03-28 16:31:18', '2026-03-28 16:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'admin_token', 'f9c8ab82976f61d8212a3023418d3d120f4a70685b914aa8d1ec9cf21faf5573', '[\"*\"]', NULL, NULL, '2026-03-28 16:25:49', '2026-03-28 16:25:49'),
(2, 'App\\Models\\User', 1, 'auth_token', '241615d2985a5315abee54476ec01ba14b60cb076508a737df0b9d6b789e5310', '[\"*\"]', '2026-03-28 16:32:12', NULL, '2026-03-28 16:26:35', '2026-03-28 16:32:12'),
(3, 'App\\Models\\User', 2, 'auth_token', 'b2521b368982c61f18841b88ce3e85bfee353655d7eb4796da0366368db19d0c', '[\"*\"]', '2026-03-28 16:31:56', NULL, '2026-03-28 16:30:41', '2026-03-28 16:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `brand` varchar(255) DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `image`, `images`, `brand`, `sku`, `stock`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nike Air Max 270', 'nike-air-max-270', 'The Nike Air Max 270 delivers visible cushioning under every step. Updated for modern comfort, it features Nike\'s biggest heel Air unit yet for a super-soft ride that feels as impossible as it looks.', 150.00, 129.99, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop', NULL, 'Nike', 'NK-AM270-BLK-001', 49, 1, 1, '2026-03-28 15:05:51', '2026-03-28 16:31:18'),
(2, 1, 'Adidas Ultraboost 22', 'adidas-ultraboost-22', 'These running shoes deliver incredible energy return. The BOOST midsole cushions every stride while the linear energy push increases your motion control.', 180.00, NULL, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&h=600&fit=crop', NULL, 'Adidas', 'AD-UB22-WHT-001', 35, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(3, 1, 'New Balance 990v5', 'new-balance-990v5', 'The Made in USA 990v5 is an iconic sneaker that combines iconic style with unmatched comfort. Premium materials and a perfect fit make this a must-have.', 184.99, NULL, 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600&h=600&fit=crop', NULL, 'New Balance', 'NB-990V5-GRY-001', 25, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(4, 1, 'Asics Gel-Kayano 28', 'asics-gel-kayano-28', 'The GEL-KAYANO 28 running shoe creates a stable stride with a supportive platform. It\'s designed for long-distance runners who need extra stability.', 160.00, 139.99, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&h=600&fit=crop', NULL, 'Asics', 'AS-GK28-BLU-001', 40, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(5, 2, 'Nike Air Jordan 1 Retro', 'nike-air-jordan-1-retro', 'The Air Jordan 1 Retro High OG returns in a premium leather construction. This iconic silhouette continues to set the standard for basketball style.', 170.00, NULL, 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=600&h=600&fit=crop', NULL, 'Nike', 'NK-AJ1-RED-001', 30, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(6, 2, 'Nike LeBron 19', 'nike-lebron-19', 'The LeBron 19 is built for one of the most powerful players in the game. It features a Max Air unit for impact protection and a secure fit.', 200.00, 179.99, 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600&h=600&fit=crop', NULL, 'Nike', 'NK-LBJ19-PUR-001', 20, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(7, 2, 'Adidas Harden Vol. 6', 'adidas-harden-vol-6', 'Designed for James Harden\'s explosive style of play, the Harden Vol. 6 features responsive cushioning and exceptional traction.', 140.00, NULL, 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600&h=600&fit=crop', NULL, 'Adidas', 'AD-HV6-BLK-001', 28, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(8, 3, 'Nike Air Force 1', 'nike-air-force-1', 'The radiance lives on in the Nike Air Force 1, the basketball original that puts a fresh spin on what you know best: durably stitched overlays, clean finishes and the perfect amount of flash.', 110.00, NULL, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&h=600&fit=crop', NULL, 'Nike', 'NK-AF1-WHT-001', 100, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(9, 3, 'Vans Old Skool', 'vans-old-skool', 'The Old Skool is the first Vans shoe to feature the iconic side stripe. Durable canvas and suede uppers for a classic look.', 65.00, 54.99, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=600&h=600&fit=crop', NULL, 'Vans', 'VN-OS-BLK-001', 75, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(10, 3, 'Converse Chuck Taylor All Star', 'converse-chuck-taylor-all-star', 'The iconic Chuck Taylor All Star continues to be a staple in casual fashion. Features the classic canvas upper and rubber toe cap.', 55.00, NULL, 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=600&h=600&fit=crop', NULL, 'Converse', 'CV-CTA-WHT-001', 90, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(11, 3, 'Puma RS-X', 'puma-rs-x', 'The RS-X takes Running System technology into the future with a bold design and modern color blocking. Perfect for the streets.', 110.00, 89.99, 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?w=600&h=600&fit=crop', NULL, 'Puma', 'PM-RSX-MUL-001', 45, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(12, 4, 'Cole Haan Zerogrand Oxford', 'cole-haan-zerogrand-oxford', 'The Grand.OS technology in these oxfords provides athletic shoe comfort in a dress shoe design. Features a full-grain leather upper.', 200.00, 169.99, 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=600&h=600&fit=crop', NULL, 'Cole Haan', 'CH-ZGO-BRN-001', 35, 1, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(13, 4, 'Clarks Tilden Cap', 'clarks-tilden-cap', 'A classic wingtip oxford with premium leather and cushion soft technology for all-day comfort. Perfect for the office or formal events.', 130.00, NULL, 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?w=600&h=600&fit=crop', NULL, 'Clarks', 'CL-TC-BLK-001', 40, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51'),
(14, 4, 'Johnston & Murphy Melton', 'johnston-murphy-melton', 'The Melton cap toe oxford is crafted from premium calfskin leather. Features TRIFIT technology for superior comfort and support.', 195.00, NULL, 'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?w=600&h=600&fit=crop', NULL, 'Johnston & Murphy', 'JM-MLT-TAN-001', 25, 0, 1, '2026-03-28 15:05:51', '2026-03-28 15:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `is_admin`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@shoestore.com', '+1 234 567 890', NULL, 1, NULL, '$2y$12$0TLf5jQH85vTGKbuJKTuH.IGHRkHUnvhDYOqqLXEv3J4CQ8o4CwpO', NULL, '2026-03-28 15:05:50', '2026-03-28 15:27:43'),
(2, 'Test User', 'test@test.com', NULL, NULL, 0, NULL, '$2y$12$Maqyg.qt4P9C1cgZfuo8purv1Iz.6x.lMZ2KVMPOTOhbi/UrGNKdC', NULL, '2026-03-28 15:05:51', '2026-03-28 15:05:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_user_id_product_id_size_color_unique` (`user_id`,`product_id`,`size`,`color`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
