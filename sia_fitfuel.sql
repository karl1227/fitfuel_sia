-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 09:06 AM
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
(1, 6, 'stock_in', 50, NULL, 4, '2025-09-05 06:38:28'),
(2, 7, 'stock_in', 100, NULL, 4, '2025-09-05 06:38:28');

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
(6, 'Chalk Ball', 'Enhance grip and reduce sweat with high-quality gym chalk. Perfect for lifting, climbing, and CrossFit.', 190.00, 1, 11, '[]', 0, 'active', 0, 0, '2025-09-05 06:16:53', '2025-09-05 06:38:28', 15),
(7, 'Dumbbell', 'High-quality dumbbells for home or gym use which is deal for strength, toning, and full-body workouts.', 1990.00, 2, 14, '[\"uploads\\/products\\/68ba80bf32b36_1757053119.png\"]', 0, 'active', 1, 1, '2025-09-05 06:18:39', '2025-09-05 06:38:28', 20);

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
(2, 'customer', 'customer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'customer', 'active', '2025-09-05 04:51:51', '2025-09-05 04:51:51', NULL),
(4, 'admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 'active', '2025-09-05 04:58:17', '2025-09-05 05:41:57', '2025-09-05 05:41:57');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`);

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
