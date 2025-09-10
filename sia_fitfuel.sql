-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2025 at 02:36 PM
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
-- Database: `sia_fitfuel`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Gym Accessories', 'Lifting Gear, Recovery Tools and Hydration & Storage', '2025-09-05 05:35:15', '2025-09-05 06:09:57'),
(2, 'Gym Equipments', 'Professional gym equipment and accessories', '2025-09-05 05:35:15', '2025-09-05 06:09:45'),
(3, 'Gym Supplements', 'Protein powders, Pre-workout Boosters and Vitamins', '2025-09-05 05:35:15', '2025-09-05 06:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `change_type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `change_type`, `quantity`, `reference_id`, `created_by`, `created_at`) VALUES
(6, 164, 'adjustment', 10, NULL, 4, '2025-09-08 11:29:07'),
(7, 164, 'adjustment', 4, NULL, 4, '2025-09-08 11:29:14'),
(8, 166, 'adjustment', 15, NULL, 4, '2025-09-08 11:29:24'),
(9, 164, 'adjustment', 19, NULL, 4, '2025-09-08 11:30:33'),
(10, 166, 'adjustment', 20, NULL, 4, '2025-09-08 11:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_delivery_date` date DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `stock_deducted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_stock_level` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `category_id`, `subcategory_id`, `images`, `stock_quantity`, `status`, `is_popular`, `is_best_seller`, `created_at`, `updated_at`, `min_stock_level`) VALUES
(143, 'Weightlifting Gloves', 'Padded gloves for better grip and hand protection during heavy lifts.', 990.00, 1, 11, '[\"uploads\\/products\\/68bf1f27d1309_1757355815.jpg\"]', 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:23:35', 10),
(144, 'Wrist Straps', 'Durable straps to support your wrists during intense workouts.', 650.00, 1, 11, '[\"uploads\\/products\\/68bf2201e4d33_1757356545.jpg\"]', 40, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:35:45', 10),
(145, 'Weightlifting Belt', 'Provides back support for heavy lifting and powerlifting.', 1200.00, 1, 11, '[\"uploads\\/products\\/68bf1f4f13aa4_1757355855.jpg\"]', 30, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:24:15', 10),
(146, 'Chalk Ball', 'Enhance grip and reduce sweat with high-quality gym chalk. Perfect for lifting, climbing, and CrossFit.', 190.00, 1, 11, '[\"uploads\\/products\\/68bf1f1817428_1757355800.jpg\"]', 100, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:23:20', 10),
(147, 'Barbell Pads', 'Protect your joints during intense workouts with padded barbell support.', 750.00, 1, 11, '[\"uploads\\/products\\/68bf1fa1cbbf9_1757355937.jpg\"]', 45, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:25:37', 10),
(148, 'Massage Gun', 'Deep tissue massage tool for faster muscle recovery.', 3500.00, 1, 12, '[\"uploads\\/products\\/68bf1fca48f21_1757355978.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:26:18', 10),
(149, 'Gel Pack', 'Hot and cold gel pack for muscle relief.', 450.00, 1, 12, '[\"uploads\\/products\\/68bf1fdb5ef45_1757355995.jpg\"]', 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:26:35', 10),
(150, 'Compression Sleeves', 'Enhances blood flow and reduces soreness.', 700.00, 1, 12, '[\"uploads\\/products\\/68bf1fe3cca49_1757356003.jpg\"]', 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:26:43', 10),
(151, 'Stretching Strap', 'Assists in improving flexibility and stretching.', 400.00, 1, 12, NULL, 60, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(152, 'Resistance Band', 'Multi-purpose band for rehab and warm-up routines.', 300.00, 1, 12, NULL, 80, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(153, 'Shaker Bottle', 'Durable shaker for protein shakes and supplements.', 350.00, 1, 13, '[\"uploads\\/products\\/68bf2003cfd3d_1757356035.jpg\"]', 100, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:15', 10),
(154, 'Duffle Bag', 'Spacious gym bag for gear and clothes.', 1500.00, 1, 13, '[\"uploads\\/products\\/68bf200cdd91c_1757356044.jpg\"]', 40, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:24', 10),
(155, 'Meal Prep Box', 'Keeps meals fresh and organized for fitness diets.', 800.00, 1, 13, '[\"uploads\\/products\\/68bf201c32d80_1757356060.jpg\"]', 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:40', 10),
(156, 'Cooling Towel', 'Stay cool during intense workouts with this quick-dry towel.', 450.00, 1, 13, '[\"uploads\\/products\\/68bf20859727c_1757356165.jpg\"]', 60, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:29:25', 10),
(157, 'Electrolyte Tablets', 'Replenishes lost minerals during heavy sweating.', 300.00, 1, 13, '[\"uploads\\/products\\/68bf207c0a68a_1757356156.jpg\"]', 70, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:29:16', 10),
(158, 'Dumbbell Set', 'High-quality dumbbells for home or gym use which is ideal for strength, toning, and full-body workouts.', 3500.00, 2, 14, '[\"uploads\\/products\\/68bf202ba629f_1757356075.jpg\"]', 25, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:55', 10),
(159, 'Kettlebell', 'Durable kettlebell for swings, squats, and functional training.', 1750.00, 2, 14, '[\"uploads\\/products\\/68bf20353a176_1757356085.jpg\"]', 30, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:05', 10),
(160, 'Barbell', 'Olympic and standard barbells for heavy lifting.', 2200.00, 2, 14, '[\"uploads\\/products\\/68bf2047c35f2_1757356103.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:23', 10),
(161, 'Weight Plates', 'Plates for Olympic and standard barbells.', 2800.00, 2, 14, '[\"uploads\\/products\\/68bf2055b3c7e_1757356117.jpg\"]', 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:37', 10),
(162, 'Medicine Ball', 'Perfect for strength and core training exercises.', 1200.00, 2, 14, '[\"uploads\\/products\\/68bf206938a61_1757356137.jpg\"]', 30, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:57', 10),
(163, 'Jump Rope', 'Adjustable speed rope for cardio and endurance.', 400.00, 2, 15, '[\"uploads\\/products\\/68bf214535d73_1757356357.jpg\"]', 60, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:32:37', 10),
(164, 'Parallette Bars', 'Perfect for calisthenics and bodyweight training.', 2200.00, 2, 15, '[\"uploads\\/products\\/68bf20ee48d21_1757356270.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:31:10', 10),
(165, 'Dip Belts', 'Adds extra weight for dips and pull-ups.', 1500.00, 2, 15, '[\"uploads\\/products\\/68bf21a0ddab8_1757356448.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:34:08', 10),
(166, 'Pull-up Bar', 'Lockable pull-up bar for doorway strength training.', 2500.00, 2, 15, '[\"uploads\\/products\\/68bf20ca4f738_1757356234.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:30:34', 10),
(167, 'Gymnastic Rings', 'Adjustable rings for advanced bodyweight exercises.', 1800.00, 2, 15, '[\"uploads\\/products\\/68bf20ba61d06_1757356218.jpg\"]', 20, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:30:18', 10),
(168, 'Foam Roller', 'Helps relieve muscle tension and improve mobility.', 900.00, 2, 16, '[\"uploads\\/products\\/68bf215a4ddd2_1757356378.jpg\"]', 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:32:58', 10),
(169, 'Massage Stick', 'Portable tool for deep tissue massage.', 600.00, 2, 16, '[\"uploads\\/products\\/68bf2110b53a7_1757356304.jpg\"]', 40, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:31:44', 10),
(170, 'Mobility Ball', 'Small ball for targeted muscle release.', 300.00, 2, 16, '[\"uploads\\/products\\/68bf212524638_1757356325.jpg\"]', 70, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:32:05', 10),
(171, 'Stretching Strap', 'Assists in deep stretches for flexibility.', 400.00, 2, 16, NULL, 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(172, 'Yoga Mat', 'Non-slip mat for yoga, pilates, and stretching.', 1200.00, 2, 16, '[\"uploads\\/products\\/68bf20d9d9219_1757356249.jpg\"]', 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:30:49', 10),
(173, 'Whey Protein', 'High-quality whey protein for muscle recovery and growth.', 2200.00, 3, 17, NULL, 40, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(174, 'Casein Protein', 'Slow-digesting protein perfect for nighttime recovery.', 2300.00, 3, 17, NULL, 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(175, 'Plant-Based Protein', 'Vegan protein blend for clean nutrition.', 2400.00, 3, 17, NULL, 30, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(176, 'Isolate Whey', 'Ultra-pure whey isolate with fast absorption.', 2500.00, 3, 17, NULL, 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(177, 'Mass Gainer', 'High-calorie protein blend for bulking.', 2600.00, 3, 17, NULL, 25, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(178, 'Pre-workout Booster', 'Energy and focus enhancer for improved workout performance.', 1500.00, 3, 18, NULL, 50, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(179, 'Caffeine Booster', 'Fast-acting energy formula for intense training.', 1200.00, 3, 18, NULL, 45, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(180, 'Beta-Alanine Formula', 'Improves endurance and reduces fatigue.', 1300.00, 3, 18, NULL, 40, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(181, 'Nitric Oxide Booster', 'Enhances blood flow and pumps during workouts.', 1400.00, 3, 18, NULL, 35, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(182, 'Creatine Monohydrate', 'Boost strength and power during high-intensity workouts.', 1200.00, 3, 18, NULL, 45, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(183, 'Multivitamins', 'Daily vitamins to support overall health and wellness.', 800.00, 3, 19, NULL, 60, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(184, 'Vitamin D3', 'Supports bone and immune health.', 500.00, 3, 19, NULL, 70, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(185, 'Vitamin C', 'Boosts immunity and reduces fatigue.', 400.00, 3, 19, NULL, 80, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(186, 'Omega-3 Fish Oil', 'Supports heart and brain health.', 900.00, 3, 19, NULL, 55, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(187, 'B-Complex Vitamins', 'Helps energy production and nervous system health.', 650.00, 3, 19, NULL, 65, 'active', 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `address_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(11, 1, 'Lifting Gear', 'Gloves, straps, belts, and chalk for safer lifting', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(12, 1, 'Recovery Tools', 'Massage guns, gel packs, and compression sleeves', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(13, 1, 'Hydration & Storage', 'Shaker bottles, meal prep boxes, and duffle bags', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(14, 2, 'Weights', 'Dumbbells, kettlebells, plates, and medicine balls', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(15, 2, 'Calisthenic Equipment', 'Jump ropes, dip belts, and pull-up bars', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(16, 2, 'Mobility Tools', 'Foam rollers, massage sticks, and yoga mats', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(17, 3, 'Protein Powders', 'Whey, casein, and plant-based protein options', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(18, 3, 'Pre-workout Boosters', 'Supplements for energy, pump, and endurance', '2025-09-05 06:15:09', '2025-09-05 06:15:09'),
(19, 3, 'Vitamins', 'Essential vitamins to support overall health', '2025-09-05 06:15:09', '2025-09-05 06:15:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` enum('admin','manager','staff','customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `google_id`, `role`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'karl', 'blockstockkc@gmail.com', '$2y$10$ZG5QGe1kwUNuwtODCeJIfuTmqSygTtLLysaVUyoTvP3ZiAEN0ICcK', NULL, 'customer', 'active', '2025-09-05 07:47:00', '2025-09-08 19:25:15', '2025-09-05 07:48:41'),
(2, 'customer', 'customer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'customer', 'active', '2025-09-05 04:51:51', '2025-09-05 04:51:51', NULL),
(4, 'admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 'active', '2025-09-05 04:58:17', '2025-09-10 12:19:48', '2025-09-10 12:19:48'),
(8, 'karl2003', 'blockstockkc123@gmail.com', '$2y$10$NVD1MhjK3UTq9W1.7yRV/uD4S81sanCuMh/Q6ler5BHWLRSezbO6.', NULL, 'customer', 'active', '2025-09-08 17:03:31', '2025-09-09 04:04:21', '2025-09-09 04:04:21'),
(9, 'emmanadmin', 'emmanadmin@gmail.com', '$2y$10$FueQzeltWy15uvefERs8Au75iBgbuNAYVTsqxrJE0eH.GV2I6yRSq', NULL, 'admin', 'active', '2025-09-09 04:07:49', '2025-09-10 09:54:31', NULL),
(10, 'emman', 'emmancutiexd@gmail.com', '$2y$10$aCMxD41/QpN0KFehEO2gCuE7koPN8Wmb/ss8pxTiwJzU5kFpL6cqK', NULL, 'customer', 'active', '2025-09-10 11:44:53', '2025-09-10 11:45:30', '2025-09-10 11:45:30'),
(11, 'karlchristopherblockstock', 'qkcblockstock@tip.edu.ph', NULL, '115213109204080203270', 'customer', 'active', '2025-09-10 12:09:18', '2025-09-10 12:09:52', '2025-09-10 12:09:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_users_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_orders_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_products_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`);

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
