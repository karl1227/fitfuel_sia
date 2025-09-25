-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2025 at 06:07 AM
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
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID of the user who performed the action',
  `username` varchar(100) DEFAULT NULL COMMENT 'Username for quick reference',
  `action_type` enum('login_success','login_failed','logout','password_change','password_reset_request','password_reset_complete','profile_update','user_create','user_update','user_delete','user_status_change','product_create','product_update','product_delete','product_status_change','inventory_adjustment','order_create','order_update','order_status_change','order_cancel','order_refund','payment_process','payment_refund','promo_create','promo_update','promo_delete','promo_status_change','category_create','category_update','category_delete','subcategory_create','subcategory_update','subcategory_delete','shipping_fee_update','system_settings_change','admin_access','data_export','data_import','content_create','content_update','content_delete','content_archive','content_publish','other') NOT NULL COMMENT 'Type of action performed',
  `module` varchar(50) NOT NULL COMMENT 'Module/feature where action occurred (e.g., users, products, orders)',
  `description` text NOT NULL COMMENT 'Detailed description of the action',
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON of old values before change',
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON of new values after change',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the user',
  `user_agent` text DEFAULT NULL COMMENT 'User agent string',
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID of the record being modified (e.g., product_id, order_id)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type of reference (e.g., product, order, user)',
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'low' COMMENT 'Severity level of the action',
  `status` enum('success','failed','warning') NOT NULL DEFAULT 'success' COMMENT 'Status of the action',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit trail for all system actions';

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_id`, `user_id`, `username`, `action_type`, `module`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `reference_id`, `reference_type`, `severity`, `status`, `created_at`) VALUES
(1, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-24 19:38:45'),
(2, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-24 19:38:49'),
(3, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-24 19:38:51'),
(4, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-24 19:39:07'),
(5, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-24 19:39:12'),
(6, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-09-24 19:39:39'),
(7, 15, 'karlblockstock1', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-09-25 04:02:23'),
(8, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-25 04:02:27'),
(9, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-25 04:03:29');

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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`, `updated_at`) VALUES
(6, 4, '2025-09-10 14:53:45', '2025-09-10 14:53:45'),
(7, 8, '2025-09-10 14:58:41', '2025-09-10 14:58:41'),
(14, 14, '2025-09-21 15:56:02', '2025-09-21 15:56:02'),
(15, 15, '2025-09-21 16:04:25', '2025-09-21 16:04:25'),
(22, 16, '2025-09-23 08:36:21', '2025-09-23 08:36:21'),
(23, 17, '2025-09-23 16:50:05', '2025-09-23 16:50:05');

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

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `added_at`) VALUES
(6, 6, 143, 6, '2025-09-10 14:53:45'),
(7, 6, 145, 4, '2025-09-10 14:56:41'),
(8, 6, 144, 3, '2025-09-10 14:56:44'),
(85, 6, 161, 2, '2025-09-10 19:46:42'),
(86, 7, 161, 3, '2025-09-10 19:56:09'),
(118, 23, 143, 2, '2025-09-24 07:37:19'),
(119, 23, 161, 2, '2025-09-24 07:37:22'),
(120, 23, 158, 1, '2025-09-24 07:37:25'),
(121, 23, 144, 1, '2025-09-24 08:28:11'),
(122, 14, 161, 3, '2025-09-24 17:10:56'),
(123, 14, 143, 1, '2025-09-24 19:33:56'),
(124, 14, 145, 1, '2025-09-24 19:33:58');

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
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('page','banner','homepage','faq') NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `body` longtext DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_keywords` varchar(500) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `schedule_start` datetime DEFAULT NULL,
  `schedule_end` datetime DEFAULT NULL,
  `placement` varchar(100) DEFAULT NULL,
  `author_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `slug` varchar(255) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`content_id`, `title`, `type`, `status`, `body`, `seo_title`, `seo_description`, `seo_keywords`, `image_path`, `link_url`, `schedule_start`, `schedule_end`, `placement`, `author_user_id`, `created_at`, `updated_at`, `slug`, `image`, `description`, `short_description`) VALUES
(44, 'JUST DO IT', 'homepage', 'published', '', NULL, NULL, NULL, 'uploads/content/68d446fb810794.83890367_1758742267.png', NULL, NULL, NULL, NULL, 4, '2025-09-24 19:31:07', '2025-09-24 19:31:07', 'just-do-it', 'uploads/content/68d446fb810794.83890367_1758742267.png', NULL, 'Make your dreams come true'),
(45, 'FUEL YOUR DAY', 'homepage', 'published', '', NULL, NULL, NULL, 'uploads/content/68d4475e50f029.79337802_1758742366.png', NULL, NULL, NULL, NULL, 4, '2025-09-24 19:32:36', '2025-09-24 19:32:46', 'fuel-your-day', 'uploads/content/68d4475e50f029.79337802_1758742366.png', NULL, 'Power up with healthy choices that keep you going.'),
(46, 'FIT STARTS HERE', 'homepage', 'published', '', NULL, NULL, NULL, 'uploads/content/68d44776b478f3.88915800_1758742390.png', NULL, NULL, NULL, NULL, 4, '2025-09-24 19:33:10', '2025-09-24 19:33:10', 'fit-starts-here', 'uploads/content/68d44776b478f3.88915800_1758742390.png', NULL, 'Small steps lead to big results.'),
(47, 'MOVE WITH PURPOSE', 'homepage', 'published', '', NULL, NULL, NULL, 'uploads/content/68d44789a44430.50804613_1758742409.png', NULL, NULL, NULL, NULL, 4, '2025-09-24 19:33:29', '2025-09-24 19:33:29', 'move-with-purpose', 'uploads/content/68d44789a44430.50804613_1758742409.png', NULL, 'Every rep takes you closer to your goal.');

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
(10, 166, 'adjustment', 20, NULL, 4, '2025-09-08 11:30:56'),
(11, 143, 'stock_out', 8, 3, 4, '2025-09-10 19:29:26'),
(12, 165, 'adjustment', 10, NULL, 4, '2025-09-24 17:46:06'),
(13, 154, 'adjustment', 30, NULL, 4, '2025-09-24 17:46:24'),
(14, 146, 'stock_out', 1, 21, 15, '2025-09-25 03:57:15'),
(15, 147, 'stock_out', 1, 21, 15, '2025-09-25 03:57:15'),
(16, 144, 'stock_out', 1, 22, 15, '2025-09-25 04:00:07'),
(17, 143, 'stock_out', 4, 23, 15, '2025-09-25 04:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `custom_order_id` varchar(20) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_delivery_date` date DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `stock_deducted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `custom_order_id`, `user_id`, `status`, `payment_method`, `payment_status`, `payment_reference`, `shipping_address`, `total_amount`, `estimated_delivery_date`, `return_reason`, `refund_amount`, `stock_deducted`, `created_at`, `updated_at`) VALUES
(12, 'FF-20250910-SD5JR', 8, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Karl Blackstack\",\"phone\":\"09765123456\",\"address\":\"Testing St. Brgy Test Angono, Rizal\",\"city\":\"Angono\",\"state\":\"South Luzon\",\"postal_code\":\"1930\"}', 2050.00, '2025-09-14', NULL, NULL, 0, '2025-09-10 20:05:43', '2025-09-10 20:05:43'),
(13, 'FF-20250921-IPUQ6', 14, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Karl Test\",\"phone\":\"09765700300\",\"address\":\"R. Testing St.\",\"city\":\"Antipolo\",\"state\":\"South Luzon\",\"postal_code\":\"1940\"}', 6680.00, '2025-09-25', NULL, NULL, 0, '2025-09-21 16:12:06', '2025-09-22 14:10:53'),
(14, 'FF-20250923-HR4JB', 16, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 Bulubok\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 1740.00, '2025-09-26', NULL, NULL, 0, '2025-09-23 10:55:07', '2025-09-23 10:55:07'),
(16, 'FF-20250923-HCTQN', 17, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:51:24', '2025-09-23 16:51:24'),
(17, 'FF-20250923-9VBF9', 17, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2390.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:52:08', '2025-09-23 16:52:08'),
(18, 'FF-20250923-4ICC6', 17, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:53:20', '2025-09-23 16:53:20'),
(19, 'FF-20250923-QM7MC', 17, 'pending', 'paypal', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 18:25:05', '2025-09-23 18:25:05'),
(20, 'FF-20250923-OFCAR', 17, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 1200.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 18:25:36', '2025-09-23 18:25:36'),
(21, 'FF-20250925-T7KZK', 15, 'pending', 'paypal', 'pending', NULL, '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address\":\"Testing St. Hehe\",\"city\":\"City of Manila\",\"state\":\"NCR\",\"postal_code\":\"1012\"}', 1040.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 03:57:15', '2025-09-25 03:57:15'),
(22, 'FF-20250925-WCLQR', 15, 'pending', 'paypal', 'pending', NULL, '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address_line1\":\"Testing St. Hehe\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"City of Manila\",\"state\":\"Not applicable\",\"postal_code\":\"1012\",\"country\":\"Philippines\"}', 750.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 04:00:07', '2025-09-25 04:00:07'),
(23, 'FF-20250925-A0EVR', 15, 'pending', 'paypal', 'pending', '5JG15145VU699244U', '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address_line1\":\"Testing St. Hehe\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"City of Manila\",\"state\":\"Not applicable\",\"postal_code\":\"1012\",\"country\":\"Philippines\"}', 4060.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 04:01:38', '2025-09-25 04:01:49');

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(18, 12, 144, 3, 650.00),
(19, 13, 143, 1, 990.00),
(20, 13, 161, 1, 2800.00),
(21, 13, 144, 1, 650.00),
(22, 13, 145, 1, 1200.00),
(23, 13, 146, 1, 190.00),
(24, 13, 147, 1, 750.00),
(25, 14, 143, 1, 990.00),
(26, 14, 144, 1, 650.00),
(28, 16, 161, 1, 2800.00),
(29, 17, 143, 1, 990.00),
(30, 17, 144, 2, 650.00),
(31, 18, 161, 1, 2800.00),
(32, 19, 161, 1, 2800.00),
(33, 20, 144, 1, 650.00),
(34, 20, 149, 1, 450.00),
(35, 21, 146, 1, 190.00),
(36, 21, 147, 1, 750.00),
(37, 22, 144, 1, 650.00),
(38, 23, 143, 4, 990.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_promo_codes`
--

CREATE TABLE `order_promo_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `promo_id` bigint(20) UNSIGNED NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `sale_percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_stock_level` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `category_id`, `subcategory_id`, `images`, `stock_quantity`, `status`, `is_popular`, `is_best_seller`, `sale_percentage`, `created_at`, `updated_at`, `min_stock_level`) VALUES
(143, 'Weightlifting Gloves', 'Padded gloves for better grip and hand protection during heavy lifts.', 990.00, 1, 11, '[\"uploads\\/products\\/68bf1f27d1309_1757355815.jpg\"]', 21, 'active', 1, 0, 20, '2025-09-06 14:37:21', '2025-09-25 04:01:38', 10),
(144, 'Wrist Straps', 'Durable straps to support your wrists during intense workouts.', 650.00, 1, 11, '[\"uploads\\/products\\/68bf2201e4d33_1757356545.jpg\"]', 26, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-25 04:00:07', 10),
(145, 'Weightlifting Belt', 'Provides back support for heavy lifting and powerlifting.', 1200.00, 1, 11, '[\"uploads\\/products\\/68bf1f4f13aa4_1757355855.jpg\"]', 23, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-21 16:12:06', 10),
(146, 'Chalk Ball', 'Enhance grip and reduce sweat with high-quality gym chalk. Perfect for lifting, climbing, and CrossFit.', 190.00, 1, 11, '[\"uploads\\/products\\/68bf1f1817428_1757355800.jpg\"]', 95, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-25 03:57:15', 10),
(147, 'Barbell Pads', 'Protect your joints during intense workouts with padded barbell support.', 750.00, 1, 11, '[\"uploads\\/products\\/68bf1fa1cbbf9_1757355937.jpg\"]', 43, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-25 03:57:15', 10),
(148, 'Massage Gun', 'Deep tissue massage tool for faster muscle recovery.', 3500.00, 1, 12, '[\"uploads\\/products\\/68bf1fca48f21_1757355978.jpg\"]', 18, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-10 19:47:24', 10),
(149, 'Gel Pack', 'Hot and cold gel pack for muscle relief.', 450.00, 1, 12, '[\"uploads\\/products\\/68bf1fdb5ef45_1757355995.jpg\"]', 47, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-23 18:25:36', 10),
(150, 'Compression Sleeves', 'Enhances blood flow and reduces soreness.', 700.00, 1, 12, '[\"uploads\\/products\\/68bf1fe3cca49_1757356003.jpg\"]', 35, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:26:43', 10),
(151, 'Stretching Strap', 'Assists in improving flexibility and stretching.', 400.00, 1, 12, NULL, 60, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(152, 'Resistance Band', 'Multi-purpose band for rehab and warm-up routines.', 300.00, 1, 12, NULL, 80, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(153, 'Shaker Bottle', 'Durable shaker for protein shakes and supplements.', 350.00, 1, 13, '[\"uploads\\/products\\/68bf2003cfd3d_1757356035.jpg\"]', 100, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:15', 10),
(154, 'Duffle Bag', 'Spacious gym bag for gear and clothes.', 1500.00, 1, 13, '[\"uploads\\/products\\/68bf200cdd91c_1757356044.jpg\"]', 10, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-24 17:46:24', 10),
(155, 'Meal Prep Box', 'Keeps meals fresh and organized for fitness diets.', 800.00, 1, 13, '[\"uploads\\/products\\/68bf201c32d80_1757356060.jpg\"]', 50, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:40', 10),
(156, 'Cooling Towel', 'Stay cool during intense workouts with this quick-dry towel.', 450.00, 1, 13, '[\"uploads\\/products\\/68bf20859727c_1757356165.jpg\"]', 60, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:29:25', 10),
(157, 'Electrolyte Tablets', 'Replenishes lost minerals during heavy sweating.', 300.00, 1, 13, '[\"uploads\\/products\\/68bf207c0a68a_1757356156.jpg\"]', 70, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:29:16', 10),
(158, 'Dumbbell Set', 'High-quality dumbbells for home or gym use which is ideal for strength, toning, and full-body workouts.', 3500.00, 2, 14, '[\"uploads\\/products\\/68bf202ba629f_1757356075.jpg\"]', 25, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:27:55', 10),
(159, 'Kettlebell', 'Durable kettlebell for swings, squats, and functional training.', 1750.00, 2, 14, '[\"uploads\\/products\\/68bf20353a176_1757356085.jpg\"]', 30, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:05', 10),
(160, 'Barbell', 'Olympic and standard barbells for heavy lifting.', 2200.00, 2, 14, '[\"uploads\\/products\\/68bf2047c35f2_1757356103.jpg\"]', 20, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:23', 10),
(161, 'Weight Plates', 'Plates for Olympic and standard barbells.', 2800.00, 2, 14, '[\"uploads\\/products\\/68bf2055b3c7e_1757356117.jpg\"]', 27, 'active', 0, 1, 0, '2025-09-06 14:37:21', '2025-09-23 18:25:05', 10),
(162, 'Medicine Ball', 'Perfect for strength and core training exercises.', 1200.00, 2, 14, '[\"uploads\\/products\\/68bf206938a61_1757356137.jpg\"]', 30, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:28:57', 10),
(163, 'Jump Rope', 'Adjustable speed rope for cardio and endurance.', 400.00, 2, 15, '[\"uploads\\/products\\/68bf214535d73_1757356357.jpg\"]', 60, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:32:37', 10),
(164, 'Parallette Bars', 'Perfect for calisthenics and bodyweight training.', 2200.00, 2, 15, '[\"uploads\\/products\\/68bf20ee48d21_1757356270.jpg\"]', 20, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:31:10', 10),
(165, 'Dip Belts', 'Adds extra weight for dips and pull-ups.', 1500.00, 2, 15, '[\"uploads\\/products\\/68bf21a0ddab8_1757356448.jpg\"]', 10, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-24 17:46:06', 10),
(166, 'Pull-up Bar', 'Lockable pull-up bar for doorway strength training.', 2500.00, 2, 15, '[\"uploads\\/products\\/68bf20ca4f738_1757356234.jpg\"]', 20, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:30:34', 10),
(167, 'Gymnastic Rings', 'Adjustable rings for advanced bodyweight exercises.', 1800.00, 2, 15, '[\"uploads\\/products\\/68bf20ba61d06_1757356218.jpg\"]', 20, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:30:18', 10),
(168, 'Foam Roller', 'Helps relieve muscle tension and improve mobility.', 900.00, 2, 16, '[\"uploads\\/products\\/68bf215a4ddd2_1757356378.jpg\"]', 35, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:32:58', 10),
(169, 'Massage Stick', 'Portable tool for deep tissue massage.', 600.00, 2, 16, '[\"uploads\\/products\\/68bf2110b53a7_1757356304.jpg\"]', 40, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-08 18:31:44', 10),
(170, 'Mobility Ball', 'Small ball for targeted muscle release.', 300.00, 2, 16, '[\"uploads\\/products\\/68bf212524638_1757356325.jpg\"]', 69, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-10 19:36:30', 10),
(171, 'Stretching Strap', 'Assists in deep stretches for flexibility.', 400.00, 2, 16, NULL, 50, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(172, 'Yoga Mat', 'Non-slip mat for yoga, pilates, and stretching.', 1200.00, 2, 16, '[\"uploads\\/products\\/68bf20d9d9219_1757356249.jpg\"]', 49, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-10 19:36:30', 10),
(173, 'Whey Protein', 'High-quality whey protein for muscle recovery and growth.', 2200.00, 3, 17, NULL, 40, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(174, 'Casein Protein', 'Slow-digesting protein perfect for nighttime recovery.', 2300.00, 3, 17, NULL, 35, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(175, 'Plant-Based Protein', 'Vegan protein blend for clean nutrition.', 2400.00, 3, 17, NULL, 30, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(176, 'Isolate Whey', 'Ultra-pure whey isolate with fast absorption.', 2500.00, 3, 17, NULL, 35, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(177, 'Mass Gainer', 'High-calorie protein blend for bulking.', 2600.00, 3, 17, NULL, 25, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(178, 'Pre-workout Booster', 'Energy and focus enhancer for improved workout performance.', 1500.00, 3, 18, NULL, 50, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(179, 'Caffeine Booster', 'Fast-acting energy formula for intense training.', 1200.00, 3, 18, NULL, 45, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(180, 'Beta-Alanine Formula', 'Improves endurance and reduces fatigue.', 1300.00, 3, 18, NULL, 40, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(181, 'Nitric Oxide Booster', 'Enhances blood flow and pumps during workouts.', 1400.00, 3, 18, NULL, 35, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(182, 'Creatine Monohydrate', 'Boost strength and power during high-intensity workouts.', 1200.00, 3, 18, NULL, 45, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(183, 'Multivitamins', 'Daily vitamins to support overall health and wellness.', 800.00, 3, 19, NULL, 60, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(184, 'Vitamin D3', 'Supports bone and immune health.', 500.00, 3, 19, NULL, 70, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(185, 'Vitamin C', 'Boosts immunity and reduces fatigue.', 400.00, 3, 19, NULL, 80, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(186, 'Omega-3 Fish Oil', 'Supports heart and brain health.', 900.00, 3, 19, NULL, 55, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10),
(187, 'B-Complex Vitamins', 'Helps energy production and nervous system health.', 650.00, 3, 19, NULL, 65, 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-09-06 14:37:21', 10);

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `promo_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`promo_id`, `code`, `description`, `discount_type`, `discount_value`, `minimum_amount`, `maximum_discount`, `usage_limit`, `used_count`, `is_active`, `valid_from`, `valid_until`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 500.00, 200.00, 100, 0, 1, '2025-09-11 02:58:54', '2026-09-11 02:58:54', '2025-09-10 18:58:54', '2025-09-10 18:58:54'),
(2, 'SAVE50', 'Fixed discount for orders above 1000', 'fixed', 50.00, 1000.00, NULL, 50, 0, 1, '2025-09-11 02:58:54', '2026-03-11 02:58:54', '2025-09-10 18:58:54', '2025-09-10 18:58:54'),
(3, 'FITNESS20', 'Fitness enthusiast discount', 'percentage', 20.00, 800.00, 300.00, 200, 0, 1, '2025-09-11 02:58:54', '2025-12-11 02:58:54', '2025-09-10 18:58:54', '2025-09-10 18:58:54');

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
  `address_line3` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `barangay_code` varchar(10) DEFAULT NULL,
  `barangay_name` varchar(100) DEFAULT NULL,
  `city_muni_code` varchar(10) DEFAULT NULL,
  `city_muni_name` varchar(100) DEFAULT NULL,
  `province_code` varchar(10) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `region_name` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`address_id`, `user_id`, `full_name`, `phone`, `address_line1`, `address_line2`, `address_line3`, `city`, `state`, `postal_code`, `barangay_code`, `barangay_name`, `city_muni_code`, `city_muni_name`, `province_code`, `province_name`, `region_code`, `region_name`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 8, 'Karl Blackstack', '09765123456', 'Testing St. Brgy Test Angono, Rizal', NULL, NULL, 'Angono', 'South Luzon', '1930', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 1, '2025-09-10 19:26:40', '2025-09-10 19:26:40'),
(3, 16, 'Michelle Angeles', '09084742498', '55 Farmers 1', NULL, NULL, NULL, NULL, '1800', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 1, '2025-09-23 10:54:58', '2025-09-23 11:24:31'),
(4, 17, 'Michelle Angeles', '09084742498', '225 bulubok st.', NULL, NULL, NULL, NULL, '1860', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 1, '2025-09-23 16:51:18', '2025-09-24 09:20:14'),
(5, 17, 'Michelle Angeles', '09123456789', 'Sample Street 123', NULL, NULL, 'Quezon City', 'National Capital Region', '1100', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 0, '2025-09-24 09:11:17', '2025-09-24 09:11:17'),
(6, 17, 'Michelle Angeles', '09123456789', 'Sample Street 123', 'Unit 2B', 'Landmark XYZ', 'Quezon City', 'National Capital Region', '1100', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 0, '2025-09-24 09:20:07', '2025-09-24 09:20:14'),
(11, 14, 'Karl Test', '09765700300', 'R. Testing St.', NULL, NULL, 'Angono', 'CALABARZON', '1940', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 0, '2025-09-24 17:33:45', '2025-09-24 17:35:33'),
(12, 14, 'Emman Cutie', '09123456789', 'R. Tolentino', NULL, NULL, 'Angono', 'CALABARZON', '1940', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 1, '2025-09-24 17:35:30', '2025-09-24 17:44:03'),
(13, 14, 'Karl Cutie', '09123456789', 'R. Tolentino St', NULL, NULL, 'Angono', 'CALABARZON', '1930', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 0, '2025-09-24 17:42:10', '2025-09-24 17:44:03'),
(14, 15, 'Karl Blockstock', '09765725123', 'R. Tolentino St. Brgy San Isidro', NULL, NULL, 'Angono', 'CALABARZON', '1930', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 0, '2025-09-25 03:53:20', '2025-09-25 03:53:20'),
(15, 15, 'Emman Cutie', '09123456789', 'Testing St. Hahahaha', NULL, NULL, 'City of Legazpi', 'Bicol Region', '0122', '050506033', 'Bgy. 34 - Oro Site-Magallanes St. (Pob.)', '050506000', 'City of Legazpi', '050500000', 'Albay', '050000000', 'Bicol Region', 'Philippines', 0, '2025-09-25 03:55:37', '2025-09-25 03:57:01'),
(16, 15, 'Nina Landicho', '09123456789', 'Testing St. Hehe', NULL, NULL, 'City of Manila', 'NCR', '1012', '133901106', 'Barangay 106', '133900000', 'City of Manila', '', 'Not applicable', '130000000', 'NCR', 'Philippines', 1, '2025-09-25 03:56:45', '2025-09-25 03:57:01');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_fees`
--

CREATE TABLE `shipping_fees` (
  `shipping_id` bigint(20) UNSIGNED NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_fees`
--

INSERT INTO `shipping_fees` (`shipping_id`, `region`, `province`, `city`, `fee`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Metro Manila', '', '', 50.00, 1, '2025-09-10 18:58:54', '2025-09-10 19:23:49'),
(2, 'Mindanao', '', '', 80.00, 1, '2025-09-10 18:58:54', '2025-09-10 19:24:07'),
(3, 'North Luzon', '', '', 60.00, 1, '2025-09-10 18:58:54', '2025-09-10 19:24:39'),
(4, 'South Luzon', '', '', 50.00, 1, '2025-09-10 18:58:54', '2025-09-10 19:24:50'),
(5, 'Visayas', '', '', 100.00, 1, '2025-09-10 18:58:54', '2025-09-10 19:25:10');

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
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` enum('admin','manager','staff','customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `date_of_birth`, `password_hash`, `google_id`, `role`, `status`, `created_at`, `updated_at`, `last_login`, `first_name`, `last_name`, `profile_picture`) VALUES
(1, 'karl', 'blockstockkc@gmail.com', NULL, NULL, '$2y$10$ZG5QGe1kwUNuwtODCeJIfuTmqSygTtLLysaVUyoTvP3ZiAEN0ICcK', '106499120974501913190', 'customer', 'active', '2025-09-05 07:47:00', '2025-09-10 15:04:00', '2025-09-10 15:04:00', NULL, NULL, NULL),
(2, 'customer', 'customer@gmail.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'customer', 'active', '2025-09-05 04:51:51', '2025-09-05 04:51:51', NULL, NULL, NULL, NULL),
(4, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 'active', '2025-09-05 04:58:17', '2025-09-25 04:02:27', '2025-09-25 04:02:27', NULL, NULL, NULL),
(8, 'karl2003', 'blockstockkc123@gmail.com', NULL, NULL, '$2y$10$NVD1MhjK3UTq9W1.7yRV/uD4S81sanCuMh/Q6ler5BHWLRSezbO6.', NULL, 'customer', 'active', '2025-09-08 17:03:31', '2025-09-10 20:05:34', '2025-09-10 20:05:34', NULL, NULL, NULL),
(9, 'emmanadmin', 'emmanadmin@gmail.com', NULL, NULL, '$2y$10$FueQzeltWy15uvefERs8Au75iBgbuNAYVTsqxrJE0eH.GV2I6yRSq', NULL, 'admin', 'active', '2025-09-09 04:07:49', '2025-09-10 09:54:31', NULL, NULL, NULL, NULL),
(10, 'emman', 'emmancutiexd@gmail.com', NULL, NULL, '$2y$10$aCMxD41/QpN0KFehEO2gCuE7koPN8Wmb/ss8pxTiwJzU5kFpL6cqK', NULL, 'customer', 'active', '2025-09-10 11:44:53', '2025-09-10 11:45:30', '2025-09-10 11:45:30', NULL, NULL, NULL),
(11, 'karlchristopherblockstock', 'qkcblockstock@tip.edu.ph', NULL, NULL, NULL, '115213109204080203270', 'customer', 'active', '2025-09-10 12:09:18', '2025-09-10 12:09:52', '2025-09-10 12:09:52', NULL, NULL, NULL),
(12, 'karlchristopherdenievablockstock', 'kdblockstock9221ant@student.fatima.edu.ph', NULL, NULL, NULL, '115755974582208244511', 'customer', 'active', '2025-09-10 15:04:41', '2025-09-10 15:04:41', NULL, NULL, NULL, NULL),
(13, 'harizzzx', 'hari.zxc33@gmail.com', NULL, NULL, NULL, '106813422058620151416', 'customer', 'active', '2025-09-10 15:05:56', '2025-09-10 15:05:56', NULL, NULL, NULL, NULL),
(14, 'karlblockstock', 'kcblockstockpogi@gmail.com', '09765725385', '2003-12-27', NULL, '100385737798619516808', 'customer', 'active', '2025-09-21 15:55:55', '2025-09-24 19:39:25', '2025-09-24 19:39:25', 'Karl', 'Blockstock', 'uploads/profile/u14_1758731950_e8fa2b7e.jpg'),
(15, 'karlblockstock1', 'karlblockstock27@gmail.com', NULL, NULL, NULL, '108103448522066236518', 'customer', 'active', '2025-09-21 16:04:20', '2025-09-25 03:52:17', '2025-09-25 03:52:17', NULL, NULL, NULL),
(16, 'michelleangeles', 'angelesmich09@gmail.com', NULL, NULL, NULL, '108570352098358224048', 'customer', 'active', '2025-09-23 08:36:00', '2025-09-23 09:12:25', '2025-09-23 09:12:25', NULL, NULL, NULL),
(17, 'mich0303', 'qmasamar@tip.edu.ph', '09123456789', '2003-12-27', '$2y$10$t2.GNMloV5cZ9NQIiQfAJ.KMMBrTNcr.3dxPl9WwOnJ5Aqi9BaNqG', NULL, 'customer', 'active', '2025-09-23 15:06:02', '2025-09-24 09:02:09', '2025-09-24 08:47:32', 'Michelle', 'Angeles', 'uploads/profile/u17_1758702327_45104a57.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_reference` (`reference_id`,`reference_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_status` (`status`);

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
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`content_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_type_status` (`type`,`status`),
  ADD KEY `idx_updated` (`updated_at`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_custom_order_id` (`custom_order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_promo_codes`
--
ALTER TABLE `order_promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `promo_id` (`promo_id`);

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
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `valid_from` (`valid_from`),
  ADD KEY `valid_until` (`valid_until`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping_fees`
--
ALTER TABLE `shipping_fees`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `region` (`region`),
  ADD KEY `province` (`province`),
  ADD KEY `city` (`city`),
  ADD KEY `is_active` (`is_active`);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `content_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `order_promo_codes`
--
ALTER TABLE `order_promo_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `shipping_fees`
--
ALTER TABLE `shipping_fees`
  MODIFY `shipping_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
-- Constraints for table `order_promo_codes`
--
ALTER TABLE `order_promo_codes`
  ADD CONSTRAINT `fk_order_promo_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_promo_promo` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`promo_id`) ON DELETE CASCADE;

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
