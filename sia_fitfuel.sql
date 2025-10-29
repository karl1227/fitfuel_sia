-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 06:42 PM
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
(9, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-25 04:03:29'),
(10, 17, 'mich0303', 'profile_update', 'users', 'User profile updated', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758702327_45104a57.jpg\"}', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758814312_435ed3e9.png\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'medium', 'success', '2025-09-25 15:31:52'),
(11, 17, 'mich0303', 'profile_update', 'users', 'User profile updated', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758814312_435ed3e9.png\"}', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758814465_7d18d537.jpg\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'medium', 'success', '2025-09-25 15:34:25'),
(12, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-25 15:52:52'),
(13, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-25 15:52:54'),
(14, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-26 19:13:42'),
(15, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-26 19:14:51'),
(16, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-26 19:14:53'),
(17, 17, 'mich0303', 'profile_update', 'users', 'User profile updated', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758814465_7d18d537.jpg\"}', '{\"email\":\"qmasamar@tip.edu.ph\",\"phone\":\"09123456789\",\"date_of_birth\":\"2003-12-27\",\"first_name\":\"Michelle\",\"last_name\":\"Angeles\",\"profile_picture\":\"uploads\\/profile\\/u17_1758814465_7d18d537.jpg\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'medium', 'success', '2025-09-26 21:27:16'),
(18, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-26 22:30:13'),
(19, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-26 22:30:15'),
(20, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-27 17:17:47'),
(21, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-27 17:17:52'),
(22, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-27 17:18:05'),
(23, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-27 17:18:06'),
(24, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-27 17:18:51'),
(25, 17, 'mich0303', 'order_create', 'profile_update', 'User updated profile.', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, NULL, 'low', 'success', '2025-09-27 18:23:58'),
(26, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 05:48:21'),
(27, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 05:48:25'),
(28, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 05:49:03'),
(29, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 05:49:12'),
(30, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 05:49:56'),
(31, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 05:50:01'),
(32, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 17:29:08'),
(33, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 17:29:17'),
(34, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-28 17:31:14'),
(35, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 17:33:20'),
(36, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-28 17:33:26'),
(37, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-28 17:33:35'),
(38, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-28 17:33:42'),
(39, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 17:33:48'),
(40, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:01:03'),
(41, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:01:09'),
(42, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:03:18'),
(43, 4, 'admin', 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-28 19:03:34'),
(44, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:03:39'),
(45, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:03:47'),
(46, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"mich_admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-09-28 19:03:53'),
(47, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:03:58'),
(48, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:11:27'),
(49, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-09-28 19:18:29'),
(50, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:25:39'),
(51, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:25:42'),
(52, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:25:47'),
(53, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:25:58'),
(54, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:31:52'),
(55, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:31:57'),
(56, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 100 (Stock increase)', '{\"stock\":0}', '{\"stock\":100}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 167, 'product', 'medium', 'success', '2025-09-28 19:35:46'),
(57, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:56:00'),
(58, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:56:04'),
(59, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-28 19:57:27'),
(60, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-28 19:57:33'),
(61, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-30 10:51:21'),
(62, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-30 10:51:27'),
(63, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-30 10:53:41'),
(64, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-30 10:53:50'),
(65, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-30 10:54:27'),
(66, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-30 10:54:32'),
(67, 17, 'mich0303', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-30 10:55:25'),
(68, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-30 10:55:30'),
(69, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 93 → 43 (Stock decrease)', '{\"stock\":93}', '{\"stock\":43}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-09-30 10:55:57'),
(70, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-09-30 10:56:03'),
(71, 17, 'mich0303', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"mich0303\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 17, 'user', 'low', 'success', '2025-09-30 10:56:09'),
(72, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-01 15:23:26'),
(73, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-01 15:25:06'),
(74, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-01 16:15:10'),
(75, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-01 16:15:14'),
(76, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 99 (Stock decrease)', '{\"stock\":100}', '{\"stock\":99}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 167, 'product', 'medium', 'success', '2025-10-01 16:16:50'),
(77, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 199 → 99 (Stock decrease)', '{\"stock\":199}', '{\"stock\":99}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 151, 'product', 'medium', 'success', '2025-10-01 16:17:14'),
(78, 15, 'karlblockstock1', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 12:04:01'),
(79, 15, 'karlblockstock1', 'product_status_change', 'profile_update', 'User updated profile.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, NULL, 'low', 'success', '2025-10-05 12:05:00'),
(80, 15, 'karlblockstock1', 'product_status_change', 'profile_update', 'User updated profile.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, NULL, 'low', 'success', '2025-10-05 12:05:21'),
(81, 15, 'karlblockstock1', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 12:08:14'),
(82, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:08:18'),
(83, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:10:06'),
(84, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:10:08'),
(85, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-05 12:10:15'),
(86, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-05 12:10:31'),
(87, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-05 12:10:50'),
(88, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-05 12:10:56'),
(89, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-05 12:11:02'),
(90, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:14:01'),
(91, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:14:37'),
(92, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-05 12:15:22'),
(93, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"karlblockstock27@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-05 13:38:32'),
(94, 15, 'karlblockstock1', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 13:40:11'),
(95, 15, 'karlblockstock1', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 13:41:16'),
(96, 15, 'karlblockstock1', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 13:41:45'),
(97, 15, 'karlblockstock1', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 15, 'user', 'low', 'success', '2025-10-05 13:43:00'),
(98, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"karlblockstock27@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-05 13:52:44'),
(99, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"karlblockstock27@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-05 13:56:18'),
(100, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"karlblockstock27@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-05 13:58:03'),
(101, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"Pogiako123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-05 14:10:54'),
(102, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"Pogiako123\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-05 14:11:04'),
(103, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"Pogiako123@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-05 14:11:39'),
(104, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"karlblockstock27@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-05 14:12:38'),
(105, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-05 14:39:54'),
(106, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-05 14:41:45'),
(107, 20, 'indyiniratake', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"indyiniratake\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 20, 'user', 'low', 'success', '2025-10-12 18:35:13'),
(108, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"atakeindyinir@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-13 05:42:05'),
(109, 20, 'indyiniratake', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"indyiniratake\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 20, 'user', 'low', 'success', '2025-10-13 05:42:37'),
(110, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-13 06:22:42'),
(111, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-13 07:41:06'),
(112, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-13 07:41:10'),
(113, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-13 09:41:38'),
(114, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-14 14:20:35'),
(115, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-14 14:21:35'),
(116, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-14 14:21:52'),
(117, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-14 14:30:26'),
(118, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-14 14:30:29'),
(119, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-14 14:42:41'),
(120, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-16 05:23:39'),
(121, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-16 05:24:04'),
(122, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-16 05:24:09'),
(123, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-16 05:27:03'),
(124, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-18 04:01:58'),
(125, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-18 04:02:10'),
(126, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-18 04:02:11'),
(127, 11, 'karlchristopherblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlchristopherblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 11, 'user', 'low', 'success', '2025-10-27 08:09:42'),
(128, 11, 'karlchristopherblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 11, 'user', 'low', 'success', '2025-10-27 08:18:42'),
(129, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 08:18:45'),
(130, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 08:22:04'),
(131, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 08:22:06'),
(132, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 08:27:49'),
(133, NULL, NULL, 'password_reset_request', 'authentication', 'Password reset requested', NULL, '{\"email\":\"blockstockkc@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'success', '2025-10-27 08:28:11'),
(134, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-27 08:45:27'),
(135, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-27 08:46:13'),
(136, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 08:46:16'),
(137, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 08:46:23'),
(138, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 09:00:47'),
(139, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-27 09:01:11'),
(140, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-27 12:24:55'),
(141, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 12:24:58'),
(142, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"emmanadmin\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"admin\"}', '{\"username\":\"emmanthemanager\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"manager\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 9, 'user', 'medium', 'success', '2025-10-27 12:30:11'),
(143, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"emmanthemanager\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"manager\"}', '{\"username\":\"emmanthemanager\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"manager\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 9, 'user', 'medium', 'success', '2025-10-27 12:30:15'),
(144, 4, 'admin', 'user_create', 'users', 'New user created', NULL, '{\"username\":\"karltheinventorystaff\",\"email\":\"karladmin@gmail.com\",\"role\":\"staff\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 21, 'user', 'medium', 'success', '2025-10-27 12:30:39'),
(145, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 12:31:04'),
(146, 9, 'emmanthemanager', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"emmanthemanager\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 9, 'user', 'low', 'success', '2025-10-27 12:31:10'),
(147, 9, 'emmanthemanager', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 9, 'user', 'low', 'success', '2025-10-27 12:32:59'),
(148, 21, 'karltheinventorystaff', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karltheinventorystaff\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 21, 'user', 'low', 'success', '2025-10-27 12:33:04'),
(149, 21, 'karltheinventorystaff', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 21, 'user', 'low', 'success', '2025-10-27 12:33:30'),
(150, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 12:33:33'),
(151, 4, 'admin', 'user_create', 'users', 'New user created', NULL, '{\"username\":\"ninatheadmin\",\"email\":\"ninaadmin@gmail.com\",\"role\":\"admin\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 23, 'user', 'medium', 'success', '2025-10-27 12:34:00'),
(152, 4, 'admin', 'user_create', 'users', 'New user created', NULL, '{\"username\":\"michtheadmin\",\"email\":\"michadmin@gmail.com\",\"role\":\"admin\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 25, 'user', 'medium', 'success', '2025-10-27 12:34:26'),
(153, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"admin\",\"email\":\"admin@gmail.com\",\"status\":\"active\",\"role\":\"admin\"}', '{\"username\":\"admin\",\"email\":\"admin@gmail.com\",\"status\":\"active\",\"role\":\"admin\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'medium', 'success', '2025-10-27 12:34:44'),
(154, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 12:35:02'),
(155, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-27 12:35:10'),
(156, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 12:56:28'),
(157, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 12:56:29'),
(158, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 12:56:33'),
(159, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-27 13:00:42');
INSERT INTO `audit_logs` (`audit_id`, `user_id`, `username`, `action_type`, `module`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `reference_id`, `reference_type`, `severity`, `status`, `created_at`) VALUES
(160, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-27 13:12:28'),
(161, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-27 13:12:33'),
(162, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":146}', '{\"product_id\":146,\"name\":\"Chalk Ball\",\"price\":190,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 146, 'product', 'medium', 'success', '2025-10-27 13:13:41'),
(163, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":147}', '{\"product_id\":147,\"name\":\"Barbell Pads\",\"price\":750,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 147, 'product', 'medium', 'success', '2025-10-27 13:13:58'),
(164, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":145}', '{\"product_id\":145,\"name\":\"Weightlifting Belt\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 145, 'product', 'medium', 'success', '2025-10-27 13:14:41'),
(165, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-27 13:14:59'),
(166, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":144}', '{\"product_id\":144,\"name\":\"Wrist Straps\",\"price\":650,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 144, 'product', 'medium', 'success', '2025-10-27 13:15:14'),
(167, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":152}', '{\"product_id\":152,\"name\":\"Resistance Band\",\"price\":300,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 152, 'product', 'medium', 'success', '2025-10-27 13:15:32'),
(168, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":150}', '{\"product_id\":150,\"name\":\"Compression Sleeves\",\"price\":700,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 150, 'product', 'medium', 'success', '2025-10-27 13:15:47'),
(169, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":149}', '{\"product_id\":149,\"name\":\"Gel Pack\",\"price\":450,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 149, 'product', 'medium', 'success', '2025-10-27 13:16:18'),
(170, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":148}', '{\"product_id\":148,\"name\":\"Massage Gun\",\"price\":3500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 148, 'product', 'medium', 'success', '2025-10-27 13:16:30'),
(171, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":151}', '{\"product_id\":151,\"name\":\"Stretching Strap\",\"price\":400,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 151, 'product', 'medium', 'success', '2025-10-27 13:16:53'),
(172, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":156}', '{\"product_id\":156,\"name\":\"Cooling Towel\",\"price\":450,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 156, 'product', 'medium', 'success', '2025-10-27 13:17:13'),
(173, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":154}', '{\"product_id\":154,\"name\":\"Duffle Bag\",\"price\":1500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 154, 'product', 'medium', 'success', '2025-10-27 13:17:25'),
(174, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":157}', '{\"product_id\":157,\"name\":\"Electrolyte Tablets\",\"price\":300,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 157, 'product', 'medium', 'success', '2025-10-27 13:17:36'),
(175, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":155}', '{\"product_id\":155,\"name\":\"Meal Prep Box\",\"price\":800,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 155, 'product', 'medium', 'success', '2025-10-27 13:18:23'),
(176, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":153}', '{\"product_id\":153,\"name\":\"Shaker Bottle\",\"price\":350,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 153, 'product', 'medium', 'success', '2025-10-27 13:18:42'),
(177, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":160}', '{\"product_id\":160,\"name\":\"Barbell\",\"price\":2200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 160, 'product', 'medium', 'success', '2025-10-27 13:19:27'),
(178, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":158}', '{\"product_id\":158,\"name\":\"Dumbbell Set\",\"price\":3500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 158, 'product', 'medium', 'success', '2025-10-27 13:20:08'),
(179, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":159}', '{\"product_id\":159,\"name\":\"Kettlebell\",\"price\":1750,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 159, 'product', 'medium', 'success', '2025-10-27 13:20:17'),
(180, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":162}', '{\"product_id\":162,\"name\":\"Medicine Ball\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 162, 'product', 'medium', 'success', '2025-10-27 13:20:27'),
(181, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":161}', '{\"product_id\":161,\"name\":\"Weight Plates\",\"price\":2800,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 161, 'product', 'medium', 'success', '2025-10-27 13:20:38'),
(182, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":165}', '{\"product_id\":165,\"name\":\"Dip Belts\",\"price\":1500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 165, 'product', 'medium', 'success', '2025-10-27 13:20:50'),
(183, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":165}', '{\"product_id\":165,\"name\":\"Dip Belts\",\"price\":1500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 165, 'product', 'medium', 'success', '2025-10-27 13:21:17'),
(184, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":167}', '{\"product_id\":167,\"name\":\"Gymnastic Rings\",\"price\":1800,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 167, 'product', 'medium', 'success', '2025-10-27 13:21:24'),
(185, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":163}', '{\"product_id\":163,\"name\":\"Jump Rope\",\"price\":400,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 163, 'product', 'medium', 'success', '2025-10-27 13:21:33'),
(186, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":166}', '{\"product_id\":166,\"name\":\"Lockable Pull-up Bar\",\"price\":2500,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 166, 'product', 'medium', 'success', '2025-10-27 13:22:28'),
(187, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":171}', '{\"product_id\":171,\"name\":\"Yoga Strap\",\"price\":400,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 171, 'product', 'medium', 'success', '2025-10-27 13:23:30'),
(188, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":170}', '{\"product_id\":170,\"name\":\"Mobility Ball\",\"price\":300,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 170, 'product', 'medium', 'success', '2025-10-27 13:23:46'),
(189, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":169}', '{\"product_id\":169,\"name\":\"Massage Stick\",\"price\":600,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 169, 'product', 'medium', 'success', '2025-10-27 13:25:08'),
(190, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":168}', '{\"product_id\":168,\"name\":\"Foam Roller\",\"price\":900,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 168, 'product', 'medium', 'success', '2025-10-27 13:25:19'),
(191, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":164}', '{\"product_id\":164,\"name\":\"Parallette Bars\",\"price\":2200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 164, 'product', 'medium', 'success', '2025-10-27 13:26:16'),
(192, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":172}', '{\"product_id\":172,\"name\":\"Yoga Mat\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 172, 'product', 'medium', 'success', '2025-10-27 13:27:06'),
(193, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 02:24:53'),
(194, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":173}', '{\"product_id\":173,\"name\":\"FitFuel Whey Protein\",\"price\":2750,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 173, 'product', 'medium', 'success', '2025-10-28 02:25:31'),
(195, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":174}', '{\"product_id\":174,\"name\":\"FitFuel Nitro Tech Whey Protein\",\"price\":2850,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 174, 'product', 'medium', 'success', '2025-10-28 02:25:58'),
(196, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":175}', '{\"product_id\":175,\"name\":\"FitFuel Gold Standard Whey Protein\",\"price\":2950,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 175, 'product', 'medium', 'success', '2025-10-28 02:26:21'),
(197, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":176}', '{\"product_id\":176,\"name\":\"FitFuel Whey Blend\",\"price\":1550,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 176, 'product', 'medium', 'success', '2025-10-28 02:26:43'),
(198, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":177}', '{\"product_id\":177,\"name\":\"FitFuel Elite 100% Whey Protein\",\"price\":3190,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 177, 'product', 'medium', 'success', '2025-10-28 02:27:00'),
(199, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":178}', '{\"product_id\":178,\"name\":\"FitFuel VAPORX5 Muscle Tech\",\"price\":1250,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 178, 'product', 'medium', 'success', '2025-10-28 02:27:41'),
(200, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":179}', '{\"product_id\":179,\"name\":\"FitFuel Cellucor C4\",\"price\":1550,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 179, 'product', 'medium', 'success', '2025-10-28 02:28:04'),
(201, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":180}', '{\"product_id\":180,\"name\":\"FitFuel Outrage Ultra‑Stim Pre‑Workout\",\"price\":1300,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 180, 'product', 'medium', 'success', '2025-10-28 02:28:38'),
(202, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":181}', '{\"product_id\":181,\"name\":\"FitFuel Dr. Jekyll Pre‐Workout\",\"price\":1650,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 181, 'product', 'medium', 'success', '2025-10-28 02:29:06'),
(203, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":182}', '{\"product_id\":182,\"name\":\"FitFuel Nitraflex Extreme Sport\",\"price\":1650,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 182, 'product', 'medium', 'success', '2025-10-28 02:29:23'),
(204, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":183}', '{\"product_id\":183,\"name\":\"FitFuel Multivitamins Tablet\",\"price\":400,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 183, 'product', 'medium', 'success', '2025-10-28 02:30:04'),
(205, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":187}', '{\"product_id\":187,\"name\":\"maxvit Multivitamins\",\"price\":150,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 187, 'product', 'medium', 'success', '2025-10-28 02:30:36'),
(206, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":184}', '{\"product_id\":184,\"name\":\"Pharex Vitamin Tablet\",\"price\":140,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 184, 'product', 'medium', 'success', '2025-10-28 02:31:06'),
(207, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":185}', '{\"product_id\":185,\"name\":\"BioTechUSA B-Complex Food Supplement\",\"price\":950,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 185, 'product', 'medium', 'success', '2025-10-28 02:31:33'),
(208, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":186}', '{\"product_id\":186,\"name\":\"FitFuel Vitamin C 500mg\",\"price\":550,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 186, 'product', 'medium', 'success', '2025-10-28 02:32:10'),
(209, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 02:33:23'),
(210, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:43:46'),
(211, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 16:48:12'),
(212, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 16:48:43'),
(213, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:48:55'),
(214, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 16:49:18'),
(215, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 16:49:26'),
(216, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:49:31'),
(217, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 16:49:41'),
(218, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:49:45'),
(219, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 16:50:02'),
(220, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 16:50:14'),
(221, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:50:18'),
(222, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 16:50:32'),
(223, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 16:50:36'),
(224, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 16:59:15'),
(225, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:59:19'),
(226, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 16:59:40'),
(227, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 16:59:58'),
(228, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"1\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:00:02'),
(229, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"0\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:04:36'),
(230, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"0\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"0\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:04:45'),
(231, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:04:47'),
(232, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 17:05:10'),
(233, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-28 17:05:49'),
(234, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:05:53'),
(235, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"0\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"1\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:06:00'),
(236, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:06:02'),
(237, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:06:10'),
(238, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"1\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:06:14'),
(239, 14, 'karlblockstock', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 17:10:56'),
(240, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:16:19'),
(241, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-28 17:18:06'),
(242, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:18:15'),
(243, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:19:29'),
(244, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:26:29'),
(245, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:26:55'),
(246, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"after\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:27:28'),
(247, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"after\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:27:38'),
(248, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:27:42'),
(249, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"1\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:27:46');
INSERT INTO `audit_logs` (`audit_id`, `user_id`, `username`, `action_type`, `module`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `reference_id`, `reference_type`, `severity`, `status`, `created_at`) VALUES
(250, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"USD\",\"currency_symbol\":\"$\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:28:17'),
(251, 4, 'admin', '', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:29:03'),
(252, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:29:21'),
(253, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:30:47'),
(254, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:30:54'),
(255, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:30:55'),
(256, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:05'),
(257, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:11'),
(258, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:13'),
(259, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:15'),
(260, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:18'),
(261, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:31:19'),
(262, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:34:36'),
(263, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:34:42'),
(264, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:34:45'),
(265, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:11'),
(266, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:20'),
(267, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:22'),
(268, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:25'),
(269, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:38'),
(270, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:42'),
(271, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:46'),
(272, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:36:54'),
(273, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:38:53'),
(274, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:38:54'),
(275, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:39:02'),
(276, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:39:07'),
(277, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:39:09'),
(278, 4, 'admin', 'system_settings_change', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"after\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:39:34'),
(279, 4, 'admin', 'system_settings_change', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"after\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:39:36'),
(280, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:39:38'),
(281, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:40:02'),
(282, 4, 'admin', 'system_settings_change', 'settings', 'Platform settings updated', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"shipping_enabled\":\"1\",\"free_shipping_threshold\":\"1000\",\"shipping_rate\":\"100\",\"paypal_enabled\":\"1\",\"paypal_client_id\":\"\",\"paypal_secret\":\"\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\",\"min_order_amount\":\"100\",\"max_order_amount\":\"50000\"}', '{\"currency_code\":\"PHP\",\"currency_symbol\":\"₱\",\"currency_position\":\"before\",\"site_name\":\"FitFuel\",\"site_email\":\"info@fitfuel.com\",\"site_phone\":\"+63 123 456 7890\",\"paypal_enabled\":\"1\",\"cash_on_delivery_enabled\":\"1\",\"bank_transfer_enabled\":\"0\",\"tax_enabled\":\"0\",\"tax_rate\":\"0.12\",\"maintenance_mode\":\"0\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'settings', 'high', 'success', '2025-10-28 17:40:18'),
(283, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:40:19'),
(284, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:25'),
(285, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:38'),
(286, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:41'),
(287, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:48'),
(288, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:52'),
(289, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-28 17:47:53'),
(290, 4, 'admin', 'order_cancel', 'orders', 'Order cancelled', NULL, '{\"cancelled\":true,\"reason\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 24, 'order', 'high', 'success', '2025-10-28 17:48:16'),
(291, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-28 17:49:34'),
(292, 4, 'admin', 'order_refund', 'orders', 'Order refunded: ₱1,000.00 - Return approved: User didn\'t like it', NULL, '{\"refund_amount\":1000,\"reason\":\"Return approved: User didn\'t like it\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 26, 'order', 'high', 'success', '2025-10-29 06:58:41'),
(293, 4, 'admin', 'order_refund', 'orders', 'Order refunded: ₱1,950.00 - Return approved: User didn\'t like it', NULL, '{\"refund_amount\":1950,\"reason\":\"Return approved: User didn\'t like it\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 26, 'order', 'high', 'success', '2025-10-29 06:58:51'),
(294, 14, 'karlblockstock', 'order_create', 'orders', 'New order created', NULL, '{\"user_id\":14,\"payment_method\":\"cod\",\"total_amount\":290,\"custom_order_id\":\"FF-20251029-29NPF\",\"items_count\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 27, 'order', 'medium', 'success', '2025-10-29 07:30:14'),
(295, 14, 'karlblockstock', 'order_create', 'orders', 'New order created', NULL, '{\"user_id\":14,\"payment_method\":\"cod\",\"total_amount\":5830,\"custom_order_id\":\"FF-20251029-XDF1B\",\"items_count\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 28, 'order', 'medium', 'success', '2025-10-29 07:42:50'),
(296, 14, 'karlblockstock', 'order_create', 'orders', 'New order created', NULL, '{\"user_id\":14,\"payment_method\":\"paypal\",\"total_amount\":1600,\"custom_order_id\":\"FF-20251029-FSZQM\",\"items_count\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 29, 'order', 'medium', 'success', '2025-10-29 07:43:32'),
(297, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-29 07:45:54'),
(298, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-29 08:04:30'),
(299, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-29 08:13:00'),
(300, NULL, NULL, 'login_failed', 'authentication', 'Failed login attempt', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'user', 'medium', 'failed', '2025-10-29 15:45:55'),
(301, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-29 15:46:01'),
(302, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 10 (Stock increase)', '{\"stock\":0}', '{\"stock\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 185, 'product', 'medium', 'success', '2025-10-29 15:50:40'),
(303, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 10 (Stock increase)', '{\"stock\":0}', '{\"stock\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 165, 'product', 'medium', 'success', '2025-10-29 15:50:47'),
(304, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 15 (Stock increase)', '{\"stock\":0}', '{\"stock\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 179, 'product', 'medium', 'success', '2025-10-29 15:50:54'),
(305, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 15 (Stock increase)', '{\"stock\":0}', '{\"stock\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 181, 'product', 'medium', 'success', '2025-10-29 15:51:00'),
(306, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 30 (Stock increase)', '{\"stock\":0}', '{\"stock\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 173, 'product', 'medium', 'success', '2025-10-29 15:51:05'),
(307, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 15 (Stock increase)', '{\"stock\":0}', '{\"stock\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 168, 'product', 'medium', 'success', '2025-10-29 15:51:10'),
(308, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 35 (Stock increase)', '{\"stock\":0}', '{\"stock\":35}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 163, 'product', 'medium', 'success', '2025-10-29 15:51:14'),
(309, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 35 (Stock increase)', '{\"stock\":0}', '{\"stock\":35}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 176, 'product', 'medium', 'success', '2025-10-29 15:51:23'),
(310, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 20 (Stock increase)', '{\"stock\":0}', '{\"stock\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 166, 'product', 'medium', 'success', '2025-10-29 15:51:27'),
(311, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 20 (Stock increase)', '{\"stock\":0}', '{\"stock\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 169, 'product', 'medium', 'success', '2025-10-29 15:51:31'),
(312, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 20 (Stock increase)', '{\"stock\":0}', '{\"stock\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 187, 'product', 'medium', 'success', '2025-10-29 15:51:37'),
(313, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 20 (Stock increase)', '{\"stock\":0}', '{\"stock\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 170, 'product', 'medium', 'success', '2025-10-29 15:51:45'),
(314, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 20 (Stock increase)', '{\"stock\":0}', '{\"stock\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 164, 'product', 'medium', 'success', '2025-10-29 15:51:53'),
(315, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 30 (Stock increase)', '{\"stock\":0}', '{\"stock\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 172, 'product', 'medium', 'success', '2025-10-29 15:51:57'),
(316, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 30 (Stock increase)', '{\"stock\":0}', '{\"stock\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 171, 'product', 'medium', 'success', '2025-10-29 15:52:01'),
(317, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 99 → 100 (Stock increase)', '{\"stock\":99}', '{\"stock\":100}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 147, 'product', 'medium', 'success', '2025-10-29 15:52:12'),
(318, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 40 (Stock decrease)', '{\"stock\":100}', '{\"stock\":40}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 161, 'product', 'medium', 'success', '2025-10-29 15:52:21'),
(319, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 50 (Stock decrease)', '{\"stock\":100}', '{\"stock\":50}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 153, 'product', 'medium', 'success', '2025-10-29 15:52:26'),
(320, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 50 (Stock decrease)', '{\"stock\":100}', '{\"stock\":50}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 162, 'product', 'medium', 'success', '2025-10-29 15:52:30'),
(321, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 50 (Stock decrease)', '{\"stock\":100}', '{\"stock\":50}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 148, 'product', 'medium', 'success', '2025-10-29 15:52:34'),
(322, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 80 (Stock decrease)', '{\"stock\":100}', '{\"stock\":80}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 155, 'product', 'medium', 'success', '2025-10-29 15:52:41'),
(323, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 50 (Stock decrease)', '{\"stock\":100}', '{\"stock\":50}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 158, 'product', 'medium', 'success', '2025-10-29 15:52:50'),
(324, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 100 → 75 (Stock decrease)', '{\"stock\":100}', '{\"stock\":75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 147, 'product', 'medium', 'success', '2025-10-29 15:52:54'),
(325, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":160}', '{\"product_id\":160,\"name\":\"Olympic Barbell\",\"price\":2200,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 160, 'product', 'medium', 'success', '2025-10-29 15:53:32'),
(326, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 30 (Stock increase)', '{\"stock\":0}', '{\"stock\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 180, 'product', 'medium', 'success', '2025-10-29 15:53:56'),
(327, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 30 (Stock increase)', '{\"stock\":0}', '{\"stock\":30}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 184, 'product', 'medium', 'success', '2025-10-29 15:53:59'),
(328, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 15 (Stock increase)', '{\"stock\":0}', '{\"stock\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 175, 'product', 'medium', 'success', '2025-10-29 15:54:04'),
(329, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 40 (Stock increase)', '{\"stock\":0}', '{\"stock\":40}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 183, 'product', 'medium', 'success', '2025-10-29 15:54:09'),
(330, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 80 (Stock increase)', '{\"stock\":0}', '{\"stock\":80}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 186, 'product', 'medium', 'success', '2025-10-29 15:54:15'),
(331, 4, 'admin', 'inventory_adjustment', 'inventory', 'Inventory adjusted: 0 → 10 (Stock increase)', '{\"stock\":0}', '{\"stock\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 177, 'product', 'medium', 'success', '2025-10-29 15:54:22'),
(332, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"emmanthemanager\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"manager\"}', '{\"username\":\"emmanthemanager\",\"email\":\"emmanadmin@gmail.com\",\"status\":\"active\",\"role\":\"manager\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 9, 'user', 'medium', 'success', '2025-10-29 15:55:15'),
(333, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"karltheinventorystaff\",\"email\":\"karladmin@gmail.com\",\"status\":\"active\",\"role\":\"staff\"}', '{\"username\":\"karltheinventorystaff\",\"email\":\"karladmin@gmail.com\",\"status\":\"active\",\"role\":\"staff\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 21, 'user', 'medium', 'success', '2025-10-29 15:55:19'),
(334, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"ninatheadmin\",\"email\":\"ninaadmin@gmail.com\",\"status\":\"active\",\"role\":\"admin\"}', '{\"username\":\"ninatheadmin\",\"email\":\"ninaadmin@gmail.com\",\"status\":\"active\",\"role\":\"admin\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 23, 'user', 'medium', 'success', '2025-10-29 15:55:24'),
(335, 4, 'admin', 'user_update', 'users', 'User information updated', '{\"username\":\"michtheadmin\",\"email\":\"michadmin@gmail.com\",\"status\":\"active\",\"role\":\"admin\"}', '{\"username\":\"michtheadmin\",\"email\":\"michadmin@gmail.com\",\"status\":\"active\",\"role\":\"admin\",\"password_changed\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 25, 'user', 'medium', 'success', '2025-10-29 15:55:28'),
(336, 4, 'admin', 'admin_access', 'admin', 'Admin access: Viewed audit logs - Accessed audit trail page', NULL, '{\"action\":\"Viewed audit logs\",\"details\":\"Accessed audit trail page\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'admin', 'medium', 'success', '2025-10-29 15:55:32'),
(337, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:03:26'),
(338, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:03:31'),
(339, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-29 16:03:37'),
(340, 14, 'karlblockstock', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"karlblockstock\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 14, 'user', 'low', 'success', '2025-10-29 16:04:23'),
(341, 4, 'admin', 'login_success', 'authentication', 'User logged in successfully', NULL, '{\"username\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-29 16:07:41'),
(342, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:08:36'),
(343, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:20:20'),
(344, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:31:10'),
(345, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":143}', '{\"product_id\":143,\"name\":\"Weightlifting Gloves\",\"price\":990,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 143, 'product', 'medium', 'success', '2025-10-29 16:31:44'),
(346, 4, 'admin', 'product_create', 'products', 'Product added successfully', NULL, '{\"product_id\":\"189\",\"name\":\"Cj Product\",\"price\":10000,\"category_id\":1,\"status\":\"inactive\",\"additional_images_count\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 189, 'product', 'medium', 'success', '2025-10-29 16:38:24'),
(347, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":189}', '{\"product_id\":189,\"name\":\"Cj Product\",\"price\":10000,\"status\":\"inactive\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 189, 'product', 'medium', 'success', '2025-10-29 16:40:53'),
(348, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":189}', '{\"product_id\":189,\"name\":\"Cj Product\",\"price\":10000,\"status\":\"inactive\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 189, 'product', 'medium', 'success', '2025-10-29 16:40:57'),
(349, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":189}', '{\"product_id\":189,\"name\":\"Cj Product\",\"price\":19000,\"status\":\"inactive\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 189, 'product', 'medium', 'success', '2025-10-29 16:41:02'),
(350, 4, 'admin', 'product_delete', 'products', 'Product deleted successfully', '{\"product_id\":189}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 189, 'product', 'high', 'success', '2025-10-29 16:48:59'),
(351, 4, 'admin', 'product_create', 'products', 'Product added successfully', NULL, '{\"product_id\":\"190\",\"name\":\"Testing\",\"price\":1220,\"category_id\":3,\"status\":\"inactive\",\"additional_images_count\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 190, 'product', 'medium', 'success', '2025-10-29 16:50:42'),
(352, 4, 'admin', 'product_delete', 'products', 'Product deleted successfully', '{\"product_id\":190}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 190, 'product', 'high', 'success', '2025-10-29 16:52:17'),
(353, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":146}', '{\"product_id\":146,\"name\":\"Chalk Ball\",\"price\":190,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 146, 'product', 'medium', 'success', '2025-10-29 17:05:57'),
(354, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":146}', '{\"product_id\":146,\"name\":\"Chalk Ball\",\"price\":190,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 146, 'product', 'medium', 'success', '2025-10-29 17:06:04'),
(355, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":146}', '{\"product_id\":146,\"name\":\"Chalk Ball\",\"price\":190,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 146, 'product', 'medium', 'success', '2025-10-29 17:06:19'),
(356, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":147}', '{\"product_id\":147,\"name\":\"Barbell Pads\",\"price\":750,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 147, 'product', 'medium', 'success', '2025-10-29 17:07:03'),
(357, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":145}', '{\"product_id\":145,\"name\":\"Weightlifting Belt\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":2,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 145, 'product', 'medium', 'success', '2025-10-29 17:07:43'),
(358, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":144}', '{\"product_id\":144,\"name\":\"Wrist Straps\",\"price\":650,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 144, 'product', 'medium', 'success', '2025-10-29 17:08:37'),
(359, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":144}', '{\"product_id\":144,\"name\":\"Wrist Straps\",\"price\":650,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 144, 'product', 'medium', 'success', '2025-10-29 17:10:43'),
(360, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":152}', '{\"product_id\":152,\"name\":\"Resistance Band\",\"price\":300,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 152, 'product', 'medium', 'success', '2025-10-29 17:11:12'),
(361, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":150}', '{\"product_id\":150,\"name\":\"Compression Sleeves\",\"price\":700,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 150, 'product', 'medium', 'success', '2025-10-29 17:11:45'),
(362, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":149}', '{\"product_id\":149,\"name\":\"Gel Pack\",\"price\":450,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 149, 'product', 'medium', 'success', '2025-10-29 17:12:09'),
(363, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":148}', '{\"product_id\":148,\"name\":\"Massage Gun\",\"price\":3500,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 148, 'product', 'medium', 'success', '2025-10-29 17:12:43'),
(364, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":149}', '{\"product_id\":149,\"name\":\"Gel Pack\",\"price\":450,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 149, 'product', 'medium', 'success', '2025-10-29 17:14:18'),
(365, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":156}', '{\"product_id\":156,\"name\":\"Cooling Towel\",\"price\":450,\"status\":\"active\",\"additional_images_added\":2,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 156, 'product', 'medium', 'success', '2025-10-29 17:14:44'),
(366, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":154}', '{\"product_id\":154,\"name\":\"Duffle Bag\",\"price\":1500,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 154, 'product', 'medium', 'success', '2025-10-29 17:15:07'),
(367, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":157}', '{\"product_id\":157,\"name\":\"Electrolyte Tablets\",\"price\":300,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 157, 'product', 'medium', 'success', '2025-10-29 17:15:51'),
(368, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":155}', '{\"product_id\":155,\"name\":\"Meal Prep Box\",\"price\":800,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 155, 'product', 'medium', 'success', '2025-10-29 17:16:23'),
(369, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":153}', '{\"product_id\":153,\"name\":\"Shaker Bottle\",\"price\":350,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 153, 'product', 'medium', 'success', '2025-10-29 17:16:48'),
(370, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":160}', '{\"product_id\":160,\"name\":\"Olympic Barbell\",\"price\":2200,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 160, 'product', 'medium', 'success', '2025-10-29 17:17:39'),
(371, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":158}', '{\"product_id\":158,\"name\":\"Dumbbell Set\",\"price\":14995,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 158, 'product', 'medium', 'success', '2025-10-29 17:18:41'),
(372, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":159}', '{\"product_id\":159,\"name\":\"Kettlebell\",\"price\":1750,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 159, 'product', 'medium', 'success', '2025-10-29 17:19:27'),
(373, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":162}', '{\"product_id\":162,\"name\":\"Medicine Ball\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 162, 'product', 'medium', 'success', '2025-10-29 17:19:59');
INSERT INTO `audit_logs` (`audit_id`, `user_id`, `username`, `action_type`, `module`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `reference_id`, `reference_type`, `severity`, `status`, `created_at`) VALUES
(374, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":161}', '{\"product_id\":161,\"name\":\"Weight Plates\",\"price\":2800,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 161, 'product', 'medium', 'success', '2025-10-29 17:21:43'),
(375, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":165}', '{\"product_id\":165,\"name\":\"Dip Belts\",\"price\":1500,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 165, 'product', 'medium', 'success', '2025-10-29 17:22:17'),
(376, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":167}', '{\"product_id\":167,\"name\":\"Gymnastic Rings\",\"price\":1800,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 167, 'product', 'medium', 'success', '2025-10-29 17:23:03'),
(377, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":163}', '{\"product_id\":163,\"name\":\"Jump Rope\",\"price\":400,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 163, 'product', 'medium', 'success', '2025-10-29 17:23:37'),
(378, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":166}', '{\"product_id\":166,\"name\":\"Lockable Pull-up Bar\",\"price\":2500,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 166, 'product', 'medium', 'success', '2025-10-29 17:24:06'),
(379, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":164}', '{\"product_id\":164,\"name\":\"Parallette Bars\",\"price\":2200,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 164, 'product', 'medium', 'success', '2025-10-29 17:24:47'),
(380, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":168}', '{\"product_id\":168,\"name\":\"Foam Roller\",\"price\":900,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 168, 'product', 'medium', 'success', '2025-10-29 17:25:29'),
(381, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":169}', '{\"product_id\":169,\"name\":\"Massage Stick\",\"price\":600,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 169, 'product', 'medium', 'success', '2025-10-29 17:25:55'),
(382, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":170}', '{\"product_id\":170,\"name\":\"Mobility Ball\",\"price\":300,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 170, 'product', 'medium', 'success', '2025-10-29 17:26:46'),
(383, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":171}', '{\"product_id\":171,\"name\":\"Yoga Strap\",\"price\":400,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 171, 'product', 'medium', 'success', '2025-10-29 17:27:22'),
(384, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":172}', '{\"product_id\":172,\"name\":\"Yoga Mat\",\"price\":1200,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 172, 'product', 'medium', 'success', '2025-10-29 17:28:10'),
(385, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":173}', '{\"product_id\":173,\"name\":\"FitFuel Whey Protein\",\"price\":2750,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 173, 'product', 'medium', 'success', '2025-10-29 17:28:51'),
(386, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":174}', '{\"product_id\":174,\"name\":\"FitFuel Nitro Tech Whey Protein\",\"price\":2850,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 174, 'product', 'medium', 'success', '2025-10-29 17:29:28'),
(387, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":175}', '{\"product_id\":175,\"name\":\"FitFuel Gold Standard Whey Protein\",\"price\":2950,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 175, 'product', 'medium', 'success', '2025-10-29 17:30:15'),
(388, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":176}', '{\"product_id\":176,\"name\":\"FitFuel Whey Blend\",\"price\":1550,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 176, 'product', 'medium', 'success', '2025-10-29 17:30:55'),
(389, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":177}', '{\"product_id\":177,\"name\":\"FitFuel Elite 100% Whey Protein\",\"price\":3190,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 177, 'product', 'medium', 'success', '2025-10-29 17:31:58'),
(390, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":178}', '{\"product_id\":178,\"name\":\"FitFuel VAPORX5 Muscle Tech\",\"price\":1250,\"status\":\"active\",\"additional_images_added\":2,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 178, 'product', 'medium', 'success', '2025-10-29 17:32:45'),
(391, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":179}', '{\"product_id\":179,\"name\":\"FitFuel Cellucor C4\",\"price\":1550,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 179, 'product', 'medium', 'success', '2025-10-29 17:33:13'),
(392, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":180}', '{\"product_id\":180,\"name\":\"FitFuel Outrage Ultra‑Stim Pre‑Workout\",\"price\":1750,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 180, 'product', 'medium', 'success', '2025-10-29 17:34:05'),
(393, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":181}', '{\"product_id\":181,\"name\":\"FitFuel Dr. Jekyll Pre‐Workout\",\"price\":1650,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 181, 'product', 'medium', 'success', '2025-10-29 17:34:39'),
(394, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":182}', '{\"product_id\":182,\"name\":\"FitFuel Nitraflex Extreme Sport\",\"price\":1650,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 182, 'product', 'medium', 'success', '2025-10-29 17:35:10'),
(395, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":183}', '{\"product_id\":183,\"name\":\"FitFuel Multivitamins Tablet\",\"price\":400,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 183, 'product', 'medium', 'success', '2025-10-29 17:35:50'),
(396, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":184}', '{\"product_id\":184,\"name\":\"Pharex Vitamin Tablet\",\"price\":140,\"status\":\"active\",\"additional_images_added\":2,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 184, 'product', 'medium', 'success', '2025-10-29 17:36:24'),
(397, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":185}', '{\"product_id\":185,\"name\":\"BioTechUSA B-Complex Food Supplement\",\"price\":950,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 185, 'product', 'medium', 'success', '2025-10-29 17:37:03'),
(398, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":186}', '{\"product_id\":186,\"name\":\"FitFuel Vitamin C 500mg\",\"price\":550,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 186, 'product', 'medium', 'success', '2025-10-29 17:37:30'),
(399, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":187}', '{\"product_id\":187,\"name\":\"maxvit Multivitamins\",\"price\":150,\"status\":\"active\",\"additional_images_added\":3,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 187, 'product', 'medium', 'success', '2025-10-29 17:37:57'),
(400, 4, 'admin', 'product_update', 'products', 'Product updated successfully', '{\"product_id\":151}', '{\"product_id\":151,\"name\":\"Stretching Strap\",\"price\":400,\"status\":\"active\",\"additional_images_added\":0,\"images_deleted\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 151, 'product', 'medium', 'success', '2025-10-29 17:40:00'),
(401, 4, 'admin', 'logout', 'authentication', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 4, 'user', 'low', 'success', '2025-10-29 17:42:10');

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
(23, 17, '2025-09-23 16:50:05', '2025-09-23 16:50:05'),
(24, 11, '2025-10-27 08:10:09', '2025-10-27 08:10:09');

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
(86, 7, 161, 3, '2025-09-10 19:56:09'),
(120, 23, 158, 1, '2025-09-24 07:37:25'),
(121, 23, 144, 1, '2025-09-24 08:28:11'),
(125, 23, 145, 1, '2025-09-27 19:01:11'),
(127, 15, 143, 3, '2025-10-05 12:04:11'),
(128, 15, 144, 2, '2025-10-05 12:04:13'),
(129, 15, 145, 2, '2025-10-05 12:04:14'),
(135, 24, 143, 3, '2025-10-27 08:10:09'),
(137, 24, 144, 3, '2025-10-27 08:14:16'),
(148, 14, 161, 1, '2025-10-29 08:02:37'),
(149, 14, 144, 6, '2025-10-29 08:02:37'),
(150, 14, 143, 1, '2025-10-29 08:02:38');

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
(17, 143, 'stock_out', 4, 23, 15, '2025-09-25 04:01:38'),
(18, 161, 'stock_out', 100, 24, 17, '2025-09-28 19:26:44'),
(19, 167, 'adjustment', 100, NULL, 4, '2025-09-28 19:35:46'),
(20, 143, 'stock_out', 7, 25, 17, '2025-09-30 10:53:31'),
(21, 143, 'adjustment', 50, NULL, 4, '2025-09-30 10:55:57'),
(22, 167, 'adjustment', 1, NULL, 4, '2025-10-01 16:16:50'),
(23, 151, 'adjustment', 100, NULL, 4, '2025-10-01 16:17:14'),
(24, 144, 'stock_out', 1, 26, 14, '2025-10-14 14:25:06'),
(25, 145, 'stock_out', 1, 26, 14, '2025-10-14 14:25:06'),
(26, 161, 'stock_in', 100, 24, 4, '2025-10-28 17:48:16'),
(27, 144, 'stock_in', 1, 26, 4, '2025-10-29 06:55:02'),
(28, 145, 'stock_in', 1, 26, 4, '2025-10-29 06:55:02'),
(29, 146, 'stock_out', 1, 27, 14, '2025-10-29 07:30:14'),
(30, 143, 'stock_out', 2, 28, 14, '2025-10-29 07:42:50'),
(31, 144, 'stock_out', 1, 28, 14, '2025-10-29 07:42:50'),
(32, 147, 'stock_out', 1, 28, 14, '2025-10-29 07:42:50'),
(33, 145, 'stock_out', 1, 28, 14, '2025-10-29 07:42:50'),
(34, 149, 'stock_out', 1, 28, 14, '2025-10-29 07:42:50'),
(35, 150, 'stock_out', 1, 28, 14, '2025-10-29 07:42:50'),
(36, 154, 'stock_out', 1, 29, 14, '2025-10-29 07:43:32'),
(37, 185, 'adjustment', 10, NULL, 4, '2025-10-29 15:50:40'),
(38, 165, 'adjustment', 10, NULL, 4, '2025-10-29 15:50:47'),
(39, 179, 'adjustment', 15, NULL, 4, '2025-10-29 15:50:54'),
(40, 181, 'adjustment', 15, NULL, 4, '2025-10-29 15:51:00'),
(41, 173, 'adjustment', 30, NULL, 4, '2025-10-29 15:51:05'),
(42, 168, 'adjustment', 15, NULL, 4, '2025-10-29 15:51:10'),
(43, 163, 'adjustment', 35, NULL, 4, '2025-10-29 15:51:14'),
(44, 176, 'adjustment', 35, NULL, 4, '2025-10-29 15:51:23'),
(45, 166, 'adjustment', 20, NULL, 4, '2025-10-29 15:51:27'),
(46, 169, 'adjustment', 20, NULL, 4, '2025-10-29 15:51:31'),
(47, 187, 'adjustment', 20, NULL, 4, '2025-10-29 15:51:37'),
(48, 170, 'adjustment', 20, NULL, 4, '2025-10-29 15:51:45'),
(49, 164, 'adjustment', 20, NULL, 4, '2025-10-29 15:51:53'),
(50, 172, 'adjustment', 30, NULL, 4, '2025-10-29 15:51:57'),
(51, 171, 'adjustment', 30, NULL, 4, '2025-10-29 15:52:01'),
(52, 147, 'adjustment', 1, NULL, 4, '2025-10-29 15:52:12'),
(53, 161, 'adjustment', 60, NULL, 4, '2025-10-29 15:52:21'),
(54, 153, 'adjustment', 50, NULL, 4, '2025-10-29 15:52:26'),
(55, 162, 'adjustment', 50, NULL, 4, '2025-10-29 15:52:30'),
(56, 148, 'adjustment', 50, NULL, 4, '2025-10-29 15:52:34'),
(57, 155, 'adjustment', 20, NULL, 4, '2025-10-29 15:52:41'),
(58, 158, 'adjustment', 50, NULL, 4, '2025-10-29 15:52:50'),
(59, 147, 'adjustment', 25, NULL, 4, '2025-10-29 15:52:54'),
(60, 180, 'adjustment', 30, NULL, 4, '2025-10-29 15:53:56'),
(61, 184, 'adjustment', 30, NULL, 4, '2025-10-29 15:53:59'),
(62, 175, 'adjustment', 15, NULL, 4, '2025-10-29 15:54:04'),
(63, 183, 'adjustment', 40, NULL, 4, '2025-10-29 15:54:09'),
(64, 186, 'adjustment', 80, NULL, 4, '2025-10-29 15:54:15'),
(65, 177, 'adjustment', 10, NULL, 4, '2025-10-29 15:54:22');

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
(16, 'FF-20250923-HCTQN', 17, 'shipped', 'cod', 'paid', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:51:24', '2025-09-28 05:49:44'),
(17, 'FF-20250923-9VBF9', 17, 'cancelled', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2390.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:52:08', '2025-09-28 05:47:27'),
(18, 'FF-20250923-4ICC6', 17, 'cancelled', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 16:53:20', '2025-09-28 05:47:20'),
(19, 'FF-20250923-QM7MC', 17, 'cancelled', 'paypal', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":null,\"state\":null,\"postal_code\":\"1860\"}', 2900.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 18:25:05', '2025-09-25 15:41:55'),
(20, 'FF-20250923-OFCAR', 17, 'cancelled', 'cod', 'failed', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address\":\"225 bulubok st.\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"1860\"}', 1200.00, '2025-09-27', NULL, NULL, 0, '2025-09-23 18:25:36', '2025-10-01 15:24:04'),
(21, 'FF-20250925-T7KZK', 15, 'pending', 'paypal', 'pending', NULL, '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address\":\"Testing St. Hehe\",\"city\":\"City of Manila\",\"state\":\"NCR\",\"postal_code\":\"1012\"}', 1040.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 03:57:15', '2025-09-25 03:57:15'),
(22, 'FF-20250925-WCLQR', 15, 'pending', 'paypal', 'pending', NULL, '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address_line1\":\"Testing St. Hehe\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"City of Manila\",\"state\":\"Not applicable\",\"postal_code\":\"1012\",\"country\":\"Philippines\"}', 750.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 04:00:07', '2025-09-25 04:00:07'),
(23, 'FF-20250925-A0EVR', 15, 'pending', 'paypal', 'pending', '5JG15145VU699244U', '{\"full_name\":\"Nina Landicho\",\"phone\":\"09123456789\",\"address_line1\":\"Testing St. Hehe\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"City of Manila\",\"state\":\"Not applicable\",\"postal_code\":\"1012\",\"country\":\"Philippines\"}', 4060.00, '2025-09-28', NULL, NULL, 1, '2025-09-25 04:01:38', '2025-09-25 04:01:49'),
(24, 'FF-20250928-U0SM3', 17, 'cancelled', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address_line1\":\"225 bulubok st.\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"1860\",\"country\":\"Philippines\"}', 280100.00, '2025-10-02', NULL, NULL, 0, '2025-09-28 19:26:44', '2025-10-28 17:48:16'),
(25, 'FF-20250930-UJFM9', 17, 'pending', 'cod', 'pending', NULL, '{\"full_name\":\"Michelle Angeles\",\"phone\":\"09084742498\",\"address_line1\":\"225 bulubok st.\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"\",\"state\":\"\",\"postal_code\":\"1860\",\"country\":\"Philippines\"}', 7030.00, '2025-10-03', NULL, NULL, 1, '2025-09-30 10:53:31', '2025-09-30 10:53:31'),
(26, 'FF-20251014-JBIUU', 14, 'returned', 'paypal', 'refunded', '4AJ187538K314382S', '{\"full_name\":\"Emman Cutie\",\"phone\":\"09123456789\",\"address\":\"\",\"city\":\"Angono\",\"state\":\"Rizal\",\"postal_code\":\"1940\"}', 1950.00, '2025-10-17', 'I don\'t like this item!', 1950.00, 0, '2025-10-14 14:25:06', '2025-10-29 08:10:24'),
(27, 'FF-20251029-29NPF', 14, 'delivered', 'cod', 'paid', NULL, '{\"full_name\":\"Emman Cutie\",\"phone\":\"09123456789\",\"address\":\"\",\"city\":\"Camalig\",\"state\":\"Albay\",\"postal_code\":\"2003\"}', 290.00, '2025-11-01', NULL, NULL, 1, '2025-10-29 07:30:14', '2025-10-29 07:34:23'),
(28, 'FF-20251029-XDF1B', 14, 'returned', 'cod', 'paid', NULL, '{\"full_name\":\"Karl Cutie\",\"phone\":\"09765000555\",\"address_line1\":\"Testing Karl\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"Infanta\",\"state\":\"Quezon\",\"postal_code\":\"1930\",\"country\":\"Philippines\"}', 5830.00, '2025-11-01', 'I don\'t like this item. I want to return it.', 700.00, 1, '2025-10-29 07:42:50', '2025-10-29 08:11:38'),
(29, 'FF-20251029-FSZQM', 14, 'pending', 'paypal', 'pending', '4JT246156A538015A', '{\"full_name\":\"Karl Testing\",\"phone\":\"09765700300\",\"address_line1\":\"R. Testing St.\",\"address_line2\":\"\",\"address_line3\":\"\",\"city\":\"Angono\",\"state\":\"Rizal\",\"postal_code\":\"1940\",\"country\":\"Philippines\"}', 1600.00, '2025-11-01', NULL, NULL, 1, '2025-10-29 07:43:32', '2025-10-29 07:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `return_requested` tinyint(1) NOT NULL DEFAULT 0,
  `review_submitted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`, `return_requested`, `review_submitted`) VALUES
(18, 12, 144, 3, 650.00, 0, 0),
(19, 13, 143, 1, 990.00, 0, 0),
(20, 13, 161, 1, 2800.00, 0, 0),
(21, 13, 144, 1, 650.00, 0, 0),
(22, 13, 145, 1, 1200.00, 0, 0),
(23, 13, 146, 1, 190.00, 0, 0),
(24, 13, 147, 1, 750.00, 0, 0),
(25, 14, 143, 1, 990.00, 0, 0),
(26, 14, 144, 1, 650.00, 0, 0),
(28, 16, 161, 1, 2800.00, 0, 0),
(29, 17, 143, 1, 990.00, 0, 0),
(30, 17, 144, 2, 650.00, 0, 0),
(31, 18, 161, 1, 2800.00, 0, 0),
(32, 19, 161, 1, 2800.00, 0, 0),
(33, 20, 144, 1, 650.00, 0, 0),
(34, 20, 149, 1, 450.00, 0, 0),
(35, 21, 146, 1, 190.00, 0, 0),
(36, 21, 147, 1, 750.00, 0, 0),
(37, 22, 144, 1, 650.00, 0, 0),
(38, 23, 143, 4, 990.00, 0, 0),
(39, 24, 161, 100, 2800.00, 0, 0),
(40, 25, 143, 7, 990.00, 0, 0),
(41, 26, 144, 1, 650.00, 0, 0),
(42, 26, 145, 1, 1200.00, 0, 0),
(43, 27, 146, 1, 190.00, 0, 0),
(44, 28, 143, 2, 990.00, 0, 0),
(45, 28, 144, 1, 650.00, 1, 0),
(46, 28, 147, 1, 750.00, 0, 0),
(47, 28, 145, 1, 1200.00, 0, 0),
(48, 28, 149, 1, 450.00, 0, 0),
(49, 28, 150, 1, 700.00, 1, 0),
(50, 29, 154, 1, 1500.00, 0, 0);

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

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(6, 'karlblockstock27@gmail.com', '31c0d7614d751b05f88497c48341a5712910a48944602c0b934b6157f2beeb11', '2025-10-05 08:38:32', '2025-10-05 13:38:32'),
(7, 'karlblockstock27@gmail.com', 'a24041b7d68b6980d18bd979b7cec004b2a06f8ec8c8b811ca85f0ca9259c179', '2025-10-05 08:52:44', '2025-10-05 13:52:44'),
(8, 'karlblockstock27@gmail.com', '0584f8f0323f51b95f3866fd5fd3f871fb5a72d707385d02938e631f6fff34f5', '2025-10-05 08:56:18', '2025-10-05 13:56:18'),
(9, 'karlblockstock27@gmail.com', 'dbf3cb8a0d32ceda52371a0c7b8862ffa3f64bd54f8cd6be807faf08ee9b4171', '2025-10-05 08:58:03', '2025-10-05 13:58:03'),
(10, 'karlblockstock27@gmail.com', '78a5bb4f9319e06b731393e90deb919f0fdaabf1f7d21f36c5762e0dfffb1435', '2025-10-05 09:12:38', '2025-10-05 14:12:38'),
(11, 'blockstockkc@gmail.com', '22ce71e2421d97ffd03732885f24934956fe6ff13b3e2c771073df0ef31dd45b', '2025-10-27 02:28:11', '2025-10-27 08:28:11');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `sale_percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_stock_level` int(11) NOT NULL DEFAULT 10,
  `average_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews` int(11) NOT NULL DEFAULT 0,
  `rating_5_count` int(11) NOT NULL DEFAULT 0,
  `rating_4_count` int(11) NOT NULL DEFAULT 0,
  `rating_3_count` int(11) NOT NULL DEFAULT 0,
  `rating_2_count` int(11) NOT NULL DEFAULT 0,
  `rating_1_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock`, `category_id`, `subcategory_id`, `images`, `status`, `is_popular`, `is_best_seller`, `sale_percentage`, `created_at`, `updated_at`, `min_stock_level`, `average_rating`, `total_reviews`, `rating_5_count`, `rating_4_count`, `rating_3_count`, `rating_2_count`, `rating_1_count`) VALUES
(143, 'Weightlifting Gloves', 'Prevent calluses and improve grip with padded gloves designed for comfort and durability.', 990.00, 41, 1, 11, '[\"uploads\\/products\\/68bf1f27d1309_1757355815.jpg\"]', 'active', 1, 0, 20, '2025-09-06 14:37:21', '2025-10-29 07:53:05', 10, 5.00, 1, 1, 0, 0, 0, 0),
(144, 'Wrist Straps', 'Boost your lifting power by reducing grip fatigue which is perfect for deadlifts, rows, and heavy pulls.', 650.00, 99, 1, 11, '[\"uploads\\/products\\/69024a15c0196_1761757717.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:08:37', 10, 0.00, 0, 0, 0, 0, 0, 0),
(145, 'Weightlifting Belt', 'Maximize support during heavy lifts. This belt helps stabilize your core and lower back for better performance and safety.', 1200.00, 99, 1, 11, '[\"uploads\\/products\\/68bf1f4f13aa4_1757355855.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 07:42:50', 10, 0.00, 0, 0, 0, 0, 0, 0),
(146, 'Chalk Ball', 'Enhance grip and reduce sweat with high-quality gym chalk. Perfect for lifting, climbing, and CrossFit.', 190.00, 99, 1, 11, '[\"uploads\\/products\\/68bf1f1817428_1757355800.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 07:37:50', 10, 5.00, 1, 1, 0, 0, 0, 0),
(147, 'Barbell Pads', 'Protect your joints during intense workouts with durable, cushioned pads ideal for knees, elbows and floors.', 750.00, 75, 1, 11, '[\"uploads\\/products\\/68bf1fa1cbbf9_1757355937.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 15:52:54', 10, 0.00, 0, 0, 0, 0, 0, 0),
(148, 'Massage Gun', 'Deep tissue massage gun designed to relieve soreness, improve circulation, and promote faster muscle recovery.', 3500.00, 50, 1, 12, '[\"uploads\\/products\\/68bf1fca48f21_1757355978.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 15:52:34', 10, 0.00, 0, 0, 0, 0, 0, 0),
(149, 'Gel Pack', 'Gel packs for targeted relief, soothe sore muscles, reduce inflammation, and speed up recovery.', 450.00, 99, 1, 12, '[\"uploads\\/products\\/68bf1fdb5ef45_1757355995.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 08:02:24', 10, 5.00, 1, 1, 0, 0, 0, 0),
(150, 'Compression Sleeves', 'Improve blood flow and reduce muscle fatigue with breathable, supportive compression sleeves.', 700.00, 99, 1, 12, '[\"uploads\\/products\\/68bf1fe3cca49_1757356003.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 07:42:50', 10, 0.00, 0, 0, 0, 0, 0, 0),
(151, 'Stretching Strap', 'Improve flexibility and mobility with a multi-loop stretching strap, great for yoga, PT, or cool-downs', 400.00, 99, 1, 12, '[\"uploads\\/products\\/69025170a2be2_1761759600.webp\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:40:00', 10, 0.00, 0, 0, 0, 0, 0, 0),
(152, 'Resistance Band', 'Versatile resistance bands for strength training, stretching, or rehabilitation workouts at home or in the gym.', 300.00, 100, 1, 12, '[\"uploads\\/products\\/69024ab0c4f08_1761757872.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:11:12', 10, 0.00, 0, 0, 0, 0, 0, 0),
(153, 'Shaker Bottle', 'Leak-proof shaker bottle with a mixing ball for smooth protein shakes and supplement drinks anytime.', 350.00, 50, 1, 13, '[\"uploads\\/products\\/68bf2003cfd3d_1757356035.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 15:52:26', 10, 0.00, 0, 0, 0, 0, 0, 0),
(154, 'Duffle Bag', 'Spacious and durable dufflebag with multiple compartments to store your gear, clothes, and shoes in style.', 1500.00, 99, 1, 13, '[\"uploads\\/products\\/68bf200cdd91c_1757356044.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 07:43:32', 10, 0.00, 0, 0, 0, 0, 0, 0),
(155, 'Meal Prep Box', 'BPA-free and microwave-safe containers to organize your meals and hit your nutrition goals on the go.', 800.00, 80, 1, 13, '[\"uploads\\/products\\/69024be7b9447_1761758183.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:16:23', 10, 0.00, 0, 0, 0, 0, 0, 0),
(156, 'Cooling Towel', 'Stay cool during workouts with a reusable cooling towel. Just wet, wring, and snap to activate.', 450.00, 100, 1, 13, '[\"uploads\\/products\\/69024b842bf34_1761758084.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:14:44', 10, 0.00, 0, 0, 0, 0, 0, 0),
(157, 'Electrolyte Tablets', 'Replenish lost electrolytes and stay hydrated during intense workouts. Easy to dissolve and refreshing.', 300.00, 100, 1, 13, '[\"uploads\\/products\\/69024bc70f2b4_1761758151.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:15:51', 10, 0.00, 0, 0, 0, 0, 0, 0),
(158, 'Dumbbell Set', 'High-quality dumbbells for home or gym use which is deal for strength, toning, and full-body workouts.', 14995.00, 50, 2, 14, '[\"uploads\\/products\\/69024c7150319_1761758321.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:18:41', 10, 0.00, 0, 0, 0, 0, 0, 0),
(159, 'Kettlebell', 'Versatile and durable kettlebell designed for dynamic strength training, conditioning, and cardio.', 1750.00, 100, 2, 14, '[\"uploads\\/products\\/69024c9f3e983_1761758367.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:19:27', 10, 0.00, 0, 0, 0, 0, 0, 0),
(160, 'Olympic Barbell', 'Heavy-duty barbell built for Olympic lifts, powerlifting, and general strength training.', 2200.00, 100, 2, 14, '[\"uploads\\/products\\/69024c33792be_1761758259.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:17:39', 10, 0.00, 0, 0, 0, 0, 0, 0),
(161, 'Weight Plates', 'Olympic-sized weight plates made of rubber or steel for safe, balanced, and effective lifting.', 2800.00, 40, 2, 14, '[\"uploads\\/products\\/69024d27aabd5_1761758503.png\"]', 'active', 0, 1, 0, '2025-09-06 14:37:21', '2025-10-29 17:21:43', 10, 0.00, 0, 0, 0, 0, 0, 0),
(162, 'Medicine Ball', 'Improve core strength, coordination, and explosive power with a rubber-grip medicine ball.', 1200.00, 50, 2, 14, '[\"uploads\\/products\\/69024cbf50993_1761758399.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:19:59', 10, 0.00, 0, 0, 0, 0, 0, 0),
(163, 'Jump Rope', 'Lightweight, fast-spinning jump rope designed for cardio, endurance, and coordination training.', 400.00, 35, 2, 15, '[\"uploads\\/products\\/69024d99d9a67_1761758617.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:23:37', 10, 0.00, 0, 0, 0, 0, 0, 0),
(164, 'Parallette Bars', 'Heavy-duty parallettes for advanced calisthenics, handstands, L-sits, and bodyweight training.', 2200.00, 20, 2, 15, '[\"uploads\\/products\\/69024ddf29c50_1761758687.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:24:47', 10, 0.00, 0, 0, 0, 0, 0, 0),
(165, 'Dip Belts', 'Add extra weight to dips or pull-ups with a durable chain dip belt for strength progression.', 1500.00, 10, 2, 15, '[\"uploads\\/products\\/69024d490e521_1761758537.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:22:17', 10, 0.00, 0, 0, 0, 0, 0, 0),
(166, 'Lockable Pull-up Bar', 'Lockable pull-up bar for doorway strength training.', 2500.00, 20, 2, 15, '[\"uploads\\/products\\/68bf20ca4f738_1757356234.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 15:51:27', 10, 0.00, 0, 0, 0, 0, 0, 0),
(167, 'Gymnastic Rings', 'Portable and adjustable rings perfect for bodyweight training, strength, and stability exercises.', 1800.00, 99, 2, 15, '[\"uploads\\/products\\/69024d772ab54_1761758583.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:23:03', 10, 0.00, 0, 0, 0, 0, 0, 0),
(168, 'Foam Roller', 'Relieve muscle tension and improve recovery with a high-density foam roller.', 900.00, 15, 2, 16, '[\"uploads\\/products\\/69024e09f04f4_1761758729.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:25:29', 10, 0.00, 0, 0, 0, 0, 0, 0),
(169, 'Massage Stick', 'Portable massage stick to roll out tight muscles, improve blood flow, and ease soreness on the go.', 600.00, 20, 2, 16, '[\"uploads\\/products\\/68bf2110b53a7_1757356304.jpg\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 15:51:31', 10, 0.00, 0, 0, 0, 0, 0, 0),
(170, 'Mobility Ball', 'Target knots and trigger points with a compact mobility ball which is perfect for deep tissue release.', 300.00, 20, 2, 16, '[\"uploads\\/products\\/69024e56840ab_1761758806.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:26:46', 10, 0.00, 0, 0, 0, 0, 0, 0),
(171, 'Yoga Strap', 'Improve flexibility and reach deeper stretches with this soft yet durable yoga strap.', 400.00, 30, 2, 16, '[\"uploads\\/products\\/69024e7a1d6be_1761758842.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:27:22', 10, 0.00, 0, 0, 0, 0, 0, 0),
(172, 'Yoga Mat', 'Non-slip, cushioned yoga mat for balance, support, and comfort during stretching, yoga, or floor exercises.', 1200.00, 30, 2, 16, '[\"uploads\\/products\\/69024eaa3d2a0_1761758890.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:28:10', 10, 0.00, 0, 0, 0, 0, 0, 0),
(173, 'FitFuel Whey Protein', 'High-quality whey blend packed with essential amino acids to support muscle recovery and growth.', 2750.00, 30, 3, 17, '[\"uploads\\/products\\/69024ed34b88b_1761758931.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:28:51', 10, 0.00, 0, 0, 0, 0, 0, 0),
(174, 'FitFuel Nitro Tech Whey Protein', 'Advanced formula with creatine and whey isolate, designed for lean muscle building and strength gains.', 2850.00, 0, 3, 17, '[\"uploads\\/products\\/69024ef851b3a_1761758968.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:29:28', 10, 0.00, 0, 0, 0, 0, 0, 0),
(175, 'FitFuel Gold Standard Whey Protein', 'The gold standard of protein, 100% whey blend with BCAAs for fast absorption and lean muscle support.', 2950.00, 15, 3, 17, '[\"uploads\\/products\\/69024f2710311_1761759015.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:30:15', 10, 0.00, 0, 0, 0, 0, 0, 0),
(176, 'FitFuel Whey Blend', 'Clean and fast-digesting protein blend with zero fillers, ideal for muscle maintenance and recovery.', 1550.00, 35, 3, 17, '[\"uploads\\/products\\/69024f4f37501_1761759055.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:30:55', 10, 0.00, 0, 0, 0, 0, 0, 0),
(177, 'FitFuel Elite 100% Whey Protein', 'Elite-quality whey designed for high-performance athletes, rich in protein and low in sugar and fat.', 3190.00, 10, 3, 17, '[\"uploads\\/products\\/69024f8e951bf_1761759118.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:31:58', 10, 0.00, 0, 0, 0, 0, 0, 0),
(178, 'FitFuel VAPORX5 Muscle Tech', 'All-in-one pre-workout delivering explosive energy, enhanced focus, and superior muscle pumps.', 1250.00, 0, 3, 18, '[\"uploads\\/products\\/69024fbd13018_1761759165.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:32:45', 10, 0.00, 0, 0, 0, 0, 0, 0),
(179, 'FitFuel Cellucor C4', 'Popular pre-workout with just the right kick, contains beta-alanine, creatine, and caffeine for energy and endurance.', 1550.00, 15, 3, 18, '[\"uploads\\/products\\/69024fd961df8_1761759193.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:33:13', 10, 0.00, 0, 0, 0, 0, 0, 0),
(180, 'FitFuel Outrage Ultra‑Stim Pre‑Workout', 'Ultra-intense stimulant pre-workout designed to push your performance and alertness to the limit.', 1750.00, 30, 3, 18, '[\"uploads\\/products\\/6902500d9b99d_1761759245.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:34:05', 10, 0.00, 0, 0, 0, 0, 0, 0),
(181, 'FitFuel Dr. Jekyll Pre‐Workout', 'Low-stim formula with a focus on strength, pump, and performance which is ideal for late-night sessions.', 1650.00, 15, 3, 18, '[\"uploads\\/products\\/6902502f2f037_1761759279.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:34:39', 10, 0.00, 0, 0, 0, 0, 0, 0),
(182, 'FitFuel Nitraflex Extreme Sport', 'Hardcore energy and testosterone-boosting pre-workout to power through your toughest training days.', 1650.00, 0, 3, 18, '[\"uploads\\/products\\/6902504e73451_1761759310.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:35:10', 10, 0.00, 0, 0, 0, 0, 0, 0),
(183, 'FitFuel Multivitamins Tablet', 'Multivitamins to help support overall health and energy.', 400.00, 40, 3, 19, '[\"uploads\\/products\\/69025076105ec_1761759350.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:35:50', 10, 0.00, 0, 0, 0, 0, 0, 0),
(184, 'Pharex Vitamin Tablet', 'Essential vitamins to keep your body strong and active every day.', 140.00, 30, 3, 19, '[\"uploads\\/products\\/690250987f9df_1761759384.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:36:24', 10, 0.00, 0, 0, 0, 0, 0, 0),
(185, 'BioTechUSA B-Complex Food Supplement', 'Boost your energy and metabolism with this B-vitamin complex.', 950.00, 10, 3, 19, '[\"uploads\\/products\\/690250bf15ca9_1761759423.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:37:03', 10, 0.00, 0, 0, 0, 0, 0, 0),
(186, 'FitFuel Vitamin C 500mg', 'Helps to keep your immune system strong and supports daily health with a good dose of vitamin C. (90 tablets)', 550.00, 80, 3, 19, '[\"uploads\\/products\\/690250da06b72_1761759450.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:37:30', 10, 0.00, 0, 0, 0, 0, 0, 0),
(187, 'maxvit Multivitamins', 'Balanced vitamins designed to support your daily wellness needs.', 150.00, 20, 3, 19, '[\"uploads\\/products\\/690250f5bff31_1761759477.png\"]', 'active', 0, 0, 0, '2025-09-06 14:37:21', '2025-10-29 17:37:57', 10, 0.00, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`) VALUES
(4, 143, 'uploads/products/additional_images/6902417017b2d_1761755504_0.png', '2025-10-29 16:31:44'),
(5, 143, 'uploads/products/additional_images/6902417017cf9_1761755504_1.png', '2025-10-29 16:31:44'),
(6, 143, 'uploads/products/additional_images/6902417017e5a_1761755504_2.png', '2025-10-29 16:31:44'),
(10, 146, 'uploads/products/additional_images/6902498bd6a1c_1761757579_0.png', '2025-10-29 17:06:19'),
(11, 146, 'uploads/products/additional_images/6902498bd6c2a_1761757579_1.png', '2025-10-29 17:06:19'),
(12, 146, 'uploads/products/additional_images/6902498bd6e4e_1761757579_2.png', '2025-10-29 17:06:19'),
(13, 147, 'uploads/products/additional_images/690249b714f8d_1761757623_0.png', '2025-10-29 17:07:03'),
(14, 147, 'uploads/products/additional_images/690249b7151b9_1761757623_1.png', '2025-10-29 17:07:03'),
(15, 147, 'uploads/products/additional_images/690249b71538f_1761757623_2.png', '2025-10-29 17:07:03'),
(16, 145, 'uploads/products/additional_images/690249df068dd_1761757663_0.png', '2025-10-29 17:07:43'),
(17, 145, 'uploads/products/additional_images/690249df06b01_1761757663_1.png', '2025-10-29 17:07:43'),
(18, 144, 'uploads/products/additional_images/69024a9338cfe_1761757843_0.png', '2025-10-29 17:10:43'),
(19, 144, 'uploads/products/additional_images/69024a9338f13_1761757843_1.png', '2025-10-29 17:10:43'),
(20, 144, 'uploads/products/additional_images/69024a93390e6_1761757843_2.png', '2025-10-29 17:10:43'),
(21, 152, 'uploads/products/additional_images/69024ab0c510b_1761757872_0.png', '2025-10-29 17:11:12'),
(22, 152, 'uploads/products/additional_images/69024ab0c5380_1761757872_1.png', '2025-10-29 17:11:12'),
(23, 152, 'uploads/products/additional_images/69024ab0c5606_1761757872_2.png', '2025-10-29 17:11:12'),
(24, 150, 'uploads/products/additional_images/69024ad1b82fb_1761757905_0.png', '2025-10-29 17:11:45'),
(25, 150, 'uploads/products/additional_images/69024ad1b84d4_1761757905_1.png', '2025-10-29 17:11:45'),
(26, 150, 'uploads/products/additional_images/69024ad1b865d_1761757905_2.png', '2025-10-29 17:11:45'),
(27, 149, 'uploads/products/additional_images/69024ae922281_1761757929_0.png', '2025-10-29 17:12:09'),
(30, 148, 'uploads/products/additional_images/69024b0bb30ab_1761757963_0.png', '2025-10-29 17:12:43'),
(31, 148, 'uploads/products/additional_images/69024b0bb37d9_1761757963_1.png', '2025-10-29 17:12:43'),
(32, 148, 'uploads/products/additional_images/69024b0bb398a_1761757963_2.png', '2025-10-29 17:12:43'),
(33, 156, 'uploads/products/additional_images/69024b842c27f_1761758084_0.png', '2025-10-29 17:14:44'),
(34, 156, 'uploads/products/additional_images/69024b842c46a_1761758084_1.png', '2025-10-29 17:14:44'),
(35, 154, 'uploads/products/additional_images/69024b9b3ae95_1761758107_0.png', '2025-10-29 17:15:07'),
(36, 154, 'uploads/products/additional_images/69024b9b3b094_1761758107_1.png', '2025-10-29 17:15:07'),
(37, 154, 'uploads/products/additional_images/69024b9b3b265_1761758107_2.png', '2025-10-29 17:15:07'),
(38, 157, 'uploads/products/additional_images/69024bc70f575_1761758151_0.png', '2025-10-29 17:15:51'),
(39, 157, 'uploads/products/additional_images/69024bc70f721_1761758151_1.png', '2025-10-29 17:15:51'),
(40, 157, 'uploads/products/additional_images/69024bc70ff0f_1761758151_2.png', '2025-10-29 17:15:51'),
(41, 155, 'uploads/products/additional_images/69024be7b96d4_1761758183_0.png', '2025-10-29 17:16:23'),
(42, 155, 'uploads/products/additional_images/69024be7b987c_1761758183_1.png', '2025-10-29 17:16:23'),
(43, 155, 'uploads/products/additional_images/69024be7b9a15_1761758183_2.png', '2025-10-29 17:16:23'),
(44, 153, 'uploads/products/additional_images/69024c003b8a3_1761758208_0.png', '2025-10-29 17:16:48'),
(45, 153, 'uploads/products/additional_images/69024c003bbdf_1761758208_1.png', '2025-10-29 17:16:48'),
(46, 153, 'uploads/products/additional_images/69024c003bec1_1761758208_2.png', '2025-10-29 17:16:48'),
(47, 160, 'uploads/products/additional_images/69024c337951b_1761758259_0.png', '2025-10-29 17:17:39'),
(48, 160, 'uploads/products/additional_images/69024c33796d9_1761758259_1.png', '2025-10-29 17:17:39'),
(49, 160, 'uploads/products/additional_images/69024c337988b_1761758259_2.png', '2025-10-29 17:17:39'),
(50, 158, 'uploads/products/additional_images/69024c7150b29_1761758321_0.png', '2025-10-29 17:18:41'),
(51, 158, 'uploads/products/additional_images/69024c7150d2f_1761758321_1.png', '2025-10-29 17:18:41'),
(52, 158, 'uploads/products/additional_images/69024c7150f92_1761758321_2.png', '2025-10-29 17:18:41'),
(53, 159, 'uploads/products/additional_images/69024c9f3ec21_1761758367_0.png', '2025-10-29 17:19:27'),
(54, 159, 'uploads/products/additional_images/69024c9f3ee30_1761758367_1.png', '2025-10-29 17:19:27'),
(55, 159, 'uploads/products/additional_images/69024c9f3efe0_1761758367_2.png', '2025-10-29 17:19:27'),
(56, 162, 'uploads/products/additional_images/69024cbf50b9d_1761758399_0.png', '2025-10-29 17:19:59'),
(57, 162, 'uploads/products/additional_images/69024cbf50d2c_1761758399_1.png', '2025-10-29 17:19:59'),
(58, 162, 'uploads/products/additional_images/69024cbf50f19_1761758399_2.png', '2025-10-29 17:19:59'),
(59, 161, 'uploads/products/additional_images/69024d27aae14_1761758503_0.png', '2025-10-29 17:21:43'),
(60, 161, 'uploads/products/additional_images/69024d27aafd2_1761758503_1.png', '2025-10-29 17:21:43'),
(61, 161, 'uploads/products/additional_images/69024d27ab1b6_1761758503_2.png', '2025-10-29 17:21:43'),
(62, 165, 'uploads/products/additional_images/69024d490e7c4_1761758537_0.png', '2025-10-29 17:22:17'),
(63, 165, 'uploads/products/additional_images/69024d490e967_1761758537_1.png', '2025-10-29 17:22:17'),
(64, 165, 'uploads/products/additional_images/69024d490eaf0_1761758537_2.png', '2025-10-29 17:22:17'),
(65, 167, 'uploads/products/additional_images/69024d772ad50_1761758583_0.png', '2025-10-29 17:23:03'),
(66, 167, 'uploads/products/additional_images/69024d772af28_1761758583_1.png', '2025-10-29 17:23:03'),
(67, 167, 'uploads/products/additional_images/69024d772b0b8_1761758583_2.png', '2025-10-29 17:23:03'),
(68, 163, 'uploads/products/additional_images/69024d99d9cb8_1761758617_0.png', '2025-10-29 17:23:37'),
(69, 163, 'uploads/products/additional_images/69024d99d9ea5_1761758617_1.png', '2025-10-29 17:23:37'),
(70, 163, 'uploads/products/additional_images/69024d99da0b9_1761758617_2.png', '2025-10-29 17:23:37'),
(71, 166, 'uploads/products/additional_images/69024db6cfe70_1761758646_0.png', '2025-10-29 17:24:06'),
(72, 166, 'uploads/products/additional_images/69024db6d00af_1761758646_1.png', '2025-10-29 17:24:06'),
(73, 166, 'uploads/products/additional_images/69024db6d0693_1761758646_2.png', '2025-10-29 17:24:06'),
(74, 164, 'uploads/products/additional_images/69024ddf29f19_1761758687_0.png', '2025-10-29 17:24:47'),
(75, 164, 'uploads/products/additional_images/69024ddf2a0e8_1761758687_1.png', '2025-10-29 17:24:47'),
(76, 164, 'uploads/products/additional_images/69024ddf2ab5d_1761758687_2.png', '2025-10-29 17:24:47'),
(77, 168, 'uploads/products/additional_images/69024e09f0c4d_1761758729_0.png', '2025-10-29 17:25:29'),
(78, 168, 'uploads/products/additional_images/69024e09f0e43_1761758729_1.png', '2025-10-29 17:25:29'),
(79, 168, 'uploads/products/additional_images/69024e09f108a_1761758729_2.png', '2025-10-29 17:25:29'),
(80, 169, 'uploads/products/additional_images/69024e23a5f22_1761758755_0.png', '2025-10-29 17:25:55'),
(81, 169, 'uploads/products/additional_images/69024e23a6123_1761758755_1.png', '2025-10-29 17:25:55'),
(82, 169, 'uploads/products/additional_images/69024e23a6325_1761758755_2.png', '2025-10-29 17:25:55'),
(83, 170, 'uploads/products/additional_images/69024e56842ed_1761758806_0.png', '2025-10-29 17:26:46'),
(84, 170, 'uploads/products/additional_images/69024e5684492_1761758806_1.png', '2025-10-29 17:26:46'),
(85, 170, 'uploads/products/additional_images/69024e56846bc_1761758806_2.png', '2025-10-29 17:26:46'),
(86, 171, 'uploads/products/additional_images/69024e7a1d912_1761758842_0.png', '2025-10-29 17:27:22'),
(87, 171, 'uploads/products/additional_images/69024e7a1db41_1761758842_1.png', '2025-10-29 17:27:22'),
(88, 171, 'uploads/products/additional_images/69024e7a1dce4_1761758842_2.png', '2025-10-29 17:27:22'),
(89, 172, 'uploads/products/additional_images/69024eaa3d512_1761758890_0.png', '2025-10-29 17:28:10'),
(90, 172, 'uploads/products/additional_images/69024eaa3d78f_1761758890_1.png', '2025-10-29 17:28:10'),
(91, 172, 'uploads/products/additional_images/69024eaa3d988_1761758890_2.png', '2025-10-29 17:28:10'),
(92, 173, 'uploads/products/additional_images/69024ed34baed_1761758931_0.png', '2025-10-29 17:28:51'),
(93, 173, 'uploads/products/additional_images/69024ed34bc92_1761758931_1.png', '2025-10-29 17:28:51'),
(94, 173, 'uploads/products/additional_images/69024ed34be88_1761758931_2.png', '2025-10-29 17:28:51'),
(95, 174, 'uploads/products/additional_images/69024ef851de5_1761758968_0.png', '2025-10-29 17:29:28'),
(96, 174, 'uploads/products/additional_images/69024ef852212_1761758968_1.png', '2025-10-29 17:29:28'),
(97, 174, 'uploads/products/additional_images/69024ef8523b5_1761758968_2.png', '2025-10-29 17:29:28'),
(98, 175, 'uploads/products/additional_images/69024f2710523_1761759015_0.png', '2025-10-29 17:30:15'),
(99, 175, 'uploads/products/additional_images/69024f27106b9_1761759015_1.png', '2025-10-29 17:30:15'),
(100, 175, 'uploads/products/additional_images/69024f27108f0_1761759015_2.png', '2025-10-29 17:30:15'),
(101, 176, 'uploads/products/additional_images/69024f4f37751_1761759055_0.png', '2025-10-29 17:30:55'),
(102, 176, 'uploads/products/additional_images/69024f4f37981_1761759055_1.png', '2025-10-29 17:30:55'),
(103, 176, 'uploads/products/additional_images/69024f4f37b1a_1761759055_2.png', '2025-10-29 17:30:55'),
(104, 177, 'uploads/products/additional_images/69024f8e958e1_1761759118_0.png', '2025-10-29 17:31:58'),
(105, 177, 'uploads/products/additional_images/69024f8e95a7b_1761759118_1.png', '2025-10-29 17:31:58'),
(106, 177, 'uploads/products/additional_images/69024f8e95ce1_1761759118_2.png', '2025-10-29 17:31:58'),
(107, 178, 'uploads/products/additional_images/69024fbd1325f_1761759165_0.png', '2025-10-29 17:32:45'),
(108, 178, 'uploads/products/additional_images/69024fbd1340f_1761759165_1.png', '2025-10-29 17:32:45'),
(109, 179, 'uploads/products/additional_images/69024fd96201d_1761759193_0.png', '2025-10-29 17:33:13'),
(110, 179, 'uploads/products/additional_images/69024fd9621c1_1761759193_1.png', '2025-10-29 17:33:13'),
(111, 179, 'uploads/products/additional_images/69024fd962427_1761759193_2.png', '2025-10-29 17:33:13'),
(112, 180, 'uploads/products/additional_images/6902500d9bc63_1761759245_0.png', '2025-10-29 17:34:05'),
(113, 180, 'uploads/products/additional_images/6902500d9be51_1761759245_1.png', '2025-10-29 17:34:05'),
(114, 180, 'uploads/products/additional_images/6902500d9bfe8_1761759245_2.png', '2025-10-29 17:34:05'),
(115, 181, 'uploads/products/additional_images/6902502f2f26c_1761759279_0.png', '2025-10-29 17:34:39'),
(116, 181, 'uploads/products/additional_images/6902502f2f3fe_1761759279_1.png', '2025-10-29 17:34:39'),
(117, 181, 'uploads/products/additional_images/6902502f2f5f6_1761759279_2.png', '2025-10-29 17:34:39'),
(118, 182, 'uploads/products/additional_images/6902504e7370c_1761759310_0.png', '2025-10-29 17:35:10'),
(119, 182, 'uploads/products/additional_images/6902504e738c8_1761759310_1.png', '2025-10-29 17:35:10'),
(120, 182, 'uploads/products/additional_images/6902504e73a6e_1761759310_2.png', '2025-10-29 17:35:10'),
(121, 183, 'uploads/products/additional_images/6902507610842_1761759350_0.png', '2025-10-29 17:35:50'),
(122, 183, 'uploads/products/additional_images/6902507610a9e_1761759350_1.png', '2025-10-29 17:35:50'),
(123, 183, 'uploads/products/additional_images/6902507610ca6_1761759350_2.png', '2025-10-29 17:35:50'),
(124, 184, 'uploads/products/additional_images/690250987fc4e_1761759384_0.png', '2025-10-29 17:36:24'),
(125, 184, 'uploads/products/additional_images/690250987fe03_1761759384_1.png', '2025-10-29 17:36:24'),
(126, 186, 'uploads/products/additional_images/690250da06ded_1761759450_0.png', '2025-10-29 17:37:30'),
(127, 186, 'uploads/products/additional_images/690250da06fe2_1761759450_1.png', '2025-10-29 17:37:30'),
(128, 186, 'uploads/products/additional_images/690250da0719f_1761759450_2.png', '2025-10-29 17:37:30'),
(129, 187, 'uploads/products/additional_images/690250f5c0188_1761759477_0.png', '2025-10-29 17:37:57'),
(130, 187, 'uploads/products/additional_images/690250f5c036f_1761759477_1.png', '2025-10-29 17:37:57'),
(131, 187, 'uploads/products/additional_images/690250f5c051b_1761759477_2.png', '2025-10-29 17:37:57');

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
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `return_reason` text NOT NULL,
  `return_type` enum('return','refund') NOT NULL DEFAULT 'return',
  `status` enum('pending','approved','rejected','processing','completed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_status` enum('pending','processed','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `order_id`, `order_item_id`, `product_id`, `user_id`, `return_reason`, `return_type`, `status`, `admin_notes`, `refund_amount`, `refund_status`, `created_at`, `updated_at`) VALUES
(1, 26, 41, 144, 14, 'I don\'t like this item!', 'return', 'approved', 'Updated via Order Edit: 2025-10-29 16:10:24', 1950.00, 'processed', '2025-10-29 06:32:34', '2025-10-29 08:10:24'),
(3, 28, 49, 150, 14, 'I don\'t like this item. I want to return it.', 'return', 'approved', 'Updated via Order Edit: 2025-10-29 16:11:38\nUpdated via Order Edit: 2025-10-29 16:11:47', 700.00, 'processed', '2025-10-29 07:55:34', '2025-10-29 08:11:47'),
(4, 28, 45, 144, 14, 'I wanna return it', 'return', 'pending', NULL, NULL, 'pending', '2025-10-29 08:13:24', '2025-10-29 08:13:24');

-- --------------------------------------------------------

--
-- Table structure for table `return_images`
--

CREATE TABLE `return_images` (
  `return_image_id` bigint(20) UNSIGNED NOT NULL,
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_order` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(1) UNSIGNED NOT NULL,
  `review_text` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 1,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `order_item_id`, `product_id`, `user_id`, `rating`, `review_text`, `is_verified_purchase`, `helpful_count`, `status`, `created_at`, `updated_at`) VALUES
(2, 27, 43, 146, 14, 5, 'Amazing product! FitFuel is the best!!!', 1, 0, 'approved', '2025-10-29 07:37:50', '2025-10-29 07:37:50'),
(3, 28, 44, 143, 14, 5, 'Nice gloves, very nice quality. Fast delivery.', 1, 0, 'approved', '2025-10-29 07:53:05', '2025-10-29 07:53:05'),
(4, 28, 48, 149, 14, 5, 'Nice product, it works to me', 1, 0, 'approved', '2025-10-29 08:02:24', '2025-10-29 08:02:24');

-- --------------------------------------------------------

--
-- Table structure for table `review_images`
--

CREATE TABLE `review_images` (
  `review_image_id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_order` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_images`
--

INSERT INTO `review_images` (`review_image_id`, `review_id`, `image_path`, `upload_order`, `created_at`) VALUES
(3, 2, 'uploads/reviews/review_2_1_1761723470.webp', 1, '2025-10-29 07:37:50'),
(4, 2, 'uploads/reviews/review_2_2_1761723470.webp', 2, '2025-10-29 07:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` bigint(20) UNSIGNED NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `key_name`, `value`, `updated_at`) VALUES
(1, 'currency_code', 'PHP', '2025-10-28 17:28:17'),
(2, 'currency_symbol', '₱', '2025-10-28 17:28:17'),
(3, 'currency_position', 'before', '2025-10-28 17:39:36'),
(4, 'site_name', 'FitFuel', '2025-10-28 16:47:51'),
(5, 'site_email', 'info@fitfuel.com', '2025-10-28 16:47:51'),
(6, 'site_phone', '+63 123 456 7890', '2025-10-28 16:47:51'),
(7, 'shipping_enabled', '1', '2025-10-28 16:47:51'),
(8, 'free_shipping_threshold', '1000', '2025-10-28 16:47:51'),
(9, 'shipping_rate', '100', '2025-10-28 16:47:51'),
(10, 'paypal_enabled', '1', '2025-10-28 17:06:00'),
(11, 'paypal_client_id', '', '2025-10-28 16:47:51'),
(12, 'paypal_secret', '', '2025-10-28 16:47:51'),
(13, 'cash_on_delivery_enabled', '1', '2025-10-28 16:47:51'),
(14, 'bank_transfer_enabled', '0', '2025-10-28 17:06:00'),
(15, 'tax_enabled', '0', '2025-10-28 17:06:14'),
(16, 'tax_rate', '0.12', '2025-10-28 16:47:51'),
(17, 'maintenance_mode', '0', '2025-10-28 17:27:46'),
(18, 'min_order_amount', '100', '2025-10-28 16:47:51'),
(19, 'max_order_amount', '50000', '2025-10-28 16:47:51');

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
(4, 17, 'Michelle Angeles', '09084742498', '225 bulubok st.', NULL, NULL, NULL, NULL, '1860', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 1, '2025-09-23 16:51:18', '2025-09-27 17:27:47'),
(5, 17, 'Michelle Angeles', '09123456789', 'Sample Street 123', NULL, NULL, 'Quezon City', 'National Capital Region', '1100', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 0, '2025-09-24 09:11:17', '2025-09-27 17:27:47'),
(6, 17, 'Michelle Angeles', '09123456789', 'Sample Street 123', 'Unit 2B', 'Landmark XYZ', 'Quezon City', 'National Capital Region', '1100', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 0, '2025-09-24 09:20:07', '2025-09-26 20:58:17'),
(11, 14, 'Karl Testing', '09765700300', 'R. Testing St.', NULL, NULL, 'Angono', 'CALABARZON', '1940', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 1, '2025-09-24 17:33:45', '2025-10-29 07:27:05'),
(14, 15, 'Karl Blockstock', '09765725123', 'R. Tolentino St. Brgy San Isidro', NULL, NULL, 'Angono', 'CALABARZON', '1930', '045801009', 'San Isidro', '045801000', 'Angono', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 0, '2025-09-25 03:53:20', '2025-09-25 03:53:20'),
(15, 15, 'Emman Cutie', '09123456789', 'Testing St. Hahahaha', NULL, NULL, 'City of Legazpi', 'Bicol Region', '0122', '050506033', 'Bgy. 34 - Oro Site-Magallanes St. (Pob.)', '050506000', 'City of Legazpi', '050500000', 'Albay', '050000000', 'Bicol Region', 'Philippines', 0, '2025-09-25 03:55:37', '2025-09-25 03:57:01'),
(16, 15, 'Nina Landicho', '09123456789', 'Testing St. Hehe', NULL, NULL, 'City of Manila', 'NCR', '1012', '133901106', 'Barangay 106', '133900000', 'City of Manila', '', 'Not applicable', '130000000', 'NCR', 'Philippines', 1, '2025-09-25 03:56:45', '2025-09-25 03:57:01'),
(17, 20, 'Kenn Dacanay', '09765123456', 'Testing St.', NULL, NULL, 'San Mateo', 'CALABARZON', '1940', '045811001', 'Ampid I', '045811000', 'San Mateo', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 1, '2025-10-13 05:44:20', '2025-10-13 05:44:22'),
(18, 20, '123', '123', '123', NULL, NULL, 'San Mateo', 'CALABARZON', '123', '045811001', 'Ampid I', '045811000', 'San Mateo', '045800000', 'Rizal', '040000000', 'CALABARZON', 'Philippines', 0, '2025-10-13 05:44:36', '2025-10-13 05:44:36'),
(19, 20, '123', '123', '123', NULL, NULL, 'City of Vigan', 'Ilocos Region', '123', '012934001', 'Ayusan Norte', '012934000', 'City of Vigan', '012900000', 'Ilocos Sur', '010000000', 'Ilocos Region', 'Philippines', 0, '2025-10-13 05:44:44', '2025-10-13 05:44:44'),
(20, 14, 'Emman Cutie', '09123456789', 'Testing Emman CutiePie XD', NULL, NULL, 'Camalig', 'Bicol Region', '2003', '050502009', 'Bongabong', '050502000', 'Camalig', '050500000', 'Albay', '050000000', 'Bicol Region', 'Philippines', 0, '2025-10-29 07:23:09', '2025-10-29 07:27:05'),
(22, 14, 'Karl Cutie', '09765000555', 'Testing Karl', NULL, NULL, 'Infanta', 'CALABARZON', '1930', '045620004', 'Amolongin', '045620000', 'Infanta', '045600000', 'Quezon', '040000000', 'CALABARZON', 'Philippines', 0, '2025-10-29 07:29:46', '2025-10-29 07:29:46');

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
  `address` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` enum('admin','manager','staff','customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `date_of_birth`, `address`, `password_hash`, `google_id`, `role`, `status`, `created_at`, `updated_at`, `last_login`, `first_name`, `last_name`, `profile_picture`, `otp`, `otp_expiry`) VALUES
(1, 'karl', 'blockstockkc@gmail.com', NULL, NULL, NULL, '$2y$10$ZG5QGe1kwUNuwtODCeJIfuTmqSygTtLLysaVUyoTvP3ZiAEN0ICcK', '106499120974501913190', 'customer', 'active', '2025-09-05 07:47:00', '2025-09-10 15:04:00', '2025-09-10 15:04:00', NULL, NULL, NULL, NULL, NULL),
(2, 'customer', 'customer@gmail.com', NULL, NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'customer', 'active', '2025-09-05 04:51:51', '2025-09-05 04:51:51', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'admin', 'admin@gmail.com', NULL, NULL, NULL, '$2y$10$MDMf6XvOBbdlXhB3LpbPYuZwKeUecarmG8tAcC/liZ4ep5DdCQCFO', NULL, 'admin', 'active', '2025-09-05 04:58:17', '2025-10-29 16:07:41', '2025-10-29 16:07:41', NULL, NULL, NULL, NULL, NULL),
(8, 'karl2003', 'blockstockkc123@gmail.com', NULL, NULL, NULL, '$2y$10$NVD1MhjK3UTq9W1.7yRV/uD4S81sanCuMh/Q6ler5BHWLRSezbO6.', NULL, 'customer', 'active', '2025-09-08 17:03:31', '2025-09-10 20:05:34', '2025-09-10 20:05:34', NULL, NULL, NULL, NULL, NULL),
(9, 'emmanthemanager', 'emmanadmin@gmail.com', NULL, NULL, NULL, '$2y$10$kSFY7rSwEQmfyeNXtUmf6eWXm/PmBFs1pLJeUYq31iLI6hhi6B/4O', NULL, 'manager', 'active', '2025-09-09 04:07:49', '2025-10-29 15:55:15', '2025-10-27 12:31:10', NULL, NULL, NULL, NULL, NULL),
(10, 'emman', 'emmancutiexd@gmail.com', NULL, NULL, NULL, '$2y$10$aCMxD41/QpN0KFehEO2gCuE7koPN8Wmb/ss8pxTiwJzU5kFpL6cqK', NULL, 'customer', 'active', '2025-09-10 11:44:53', '2025-09-10 11:45:30', '2025-09-10 11:45:30', NULL, NULL, NULL, NULL, NULL),
(11, 'karlchristopherblockstock', 'qkcblockstock@tip.edu.ph', NULL, NULL, NULL, NULL, '115213109204080203270', 'customer', 'active', '2025-09-10 12:09:18', '2025-10-27 08:09:42', '2025-10-27 08:09:42', NULL, NULL, NULL, NULL, NULL),
(12, 'karlchristopherdenievablockstock', 'kdblockstock9221ant@student.fatima.edu.ph', NULL, NULL, NULL, NULL, '115755974582208244511', 'customer', 'active', '2025-09-10 15:04:41', '2025-09-10 15:04:41', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'harizzzx', 'hari.zxc33@gmail.com', NULL, NULL, NULL, NULL, '106813422058620151416', 'customer', 'active', '2025-09-10 15:05:56', '2025-10-02 10:03:25', '2025-10-02 10:03:25', NULL, NULL, NULL, NULL, NULL),
(14, 'karlblockstock', 'kcblockstockpogi@gmail.com', '09765725385', '2003-12-27', NULL, NULL, '100385737798619516808', 'customer', 'active', '2025-09-21 15:55:55', '2025-10-29 16:04:23', '2025-10-29 16:04:23', 'Karl', 'Blockstock', 'uploads/profile/u14_1758731950_e8fa2b7e.jpg', NULL, NULL),
(15, 'karlblockstock1', 'karlblockstock27@gmail.com', '09765725123', '2003-12-27', NULL, NULL, '108103448522066236518', 'customer', 'active', '2025-09-21 16:04:20', '2025-10-05 13:59:13', '2025-10-05 13:59:13', 'Karl', 'Blockstock', 'uploads/profile/u15_1759665921_6906177e.jpg', '434570', '2025-10-05 16:04:13'),
(16, 'michelleangeles', 'angelesmich09@gmail.com', NULL, NULL, NULL, NULL, '108570352098358224048', 'customer', 'active', '2025-09-23 08:36:00', '2025-09-23 09:12:25', '2025-09-23 09:12:25', NULL, NULL, NULL, NULL, NULL),
(17, 'mich0303', 'qmasamar@tip.edu.ph', '09123456789', '2003-12-27', NULL, '$2y$10$t2.GNMloV5cZ9NQIiQfAJ.KMMBrTNcr.3dxPl9WwOnJ5Aqi9BaNqG', NULL, 'customer', 'active', '2025-09-23 15:06:02', '2025-09-24 09:02:09', '2025-09-24 08:47:32', 'Michelle', 'Angeles', 'uploads/profile/u17_1758702327_45104a57.jpg', NULL, NULL),
(18, 'emmanuelespeña', 'espena.emman@gmail.com', NULL, NULL, NULL, NULL, '106046174656984709849', 'customer', 'active', '2025-09-27 02:59:56', '2025-10-04 13:07:43', '2025-10-04 13:07:43', NULL, NULL, NULL, NULL, NULL),
(19, 'Pogiako123', 'Pogiako123@gmail.com', NULL, NULL, NULL, '$2y$10$aSlRx6fSU2liwGMJaORgQ.ke9tSZwTkHlGDs.uejaOLqHhDfW9ZEi', NULL, 'customer', 'active', '2025-10-05 14:10:39', '2025-10-05 14:10:39', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'indyiniratake', 'atakeindyinir@gmail.com', NULL, NULL, NULL, NULL, '101975020126837940161', 'customer', 'active', '2025-10-12 18:35:04', '2025-10-13 05:42:37', '2025-10-13 05:42:37', NULL, NULL, NULL, NULL, NULL),
(21, 'karltheinventorystaff', 'karladmin@gmail.com', NULL, NULL, NULL, '$2y$10$X84KPrW/Jmy1CSs3PPuiVeTibI2/24eioUK8rKX1oS3fDj8KwCuL2', NULL, 'staff', 'active', '2025-10-27 12:30:39', '2025-10-29 15:55:19', '2025-10-27 12:33:04', NULL, NULL, NULL, NULL, NULL),
(23, 'ninatheadmin', 'ninaadmin@gmail.com', NULL, NULL, NULL, '$2y$10$gbQMGICZHD9ULS10Wx5g.eRjrPC1ObgVVbajDV64Abf7W.F837CIS', NULL, 'admin', 'active', '2025-10-27 12:34:00', '2025-10-29 15:55:24', NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'michtheadmin', 'michadmin@gmail.com', NULL, NULL, NULL, '$2y$10$u4yCIXkb/GG3pWKTc3hN3.gzOfmtUOgtYyCdSnuhZoMMMyGGKRDqa', NULL, 'admin', 'active', '2025-10-27 12:34:26', '2025-10-29 15:55:28', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_inbox`
--

CREATE TABLE `user_inbox` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_name` varchar(100) DEFAULT 'FitFuel',
  `subject` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_inbox`
--

INSERT INTO `user_inbox` (`id`, `user_id`, `from_name`, `subject`, `body`, `is_read`, `created_at`) VALUES
(1, 17, 'FitFuel', 'Welcome to FitFuel 🎉', 'Hi mich0303! Thanks for joining FitFuel. We’ll use this inbox for order updates, promos, and account notices.', 0, '2025-09-26 21:02:51'),
(2, 14, 'FitFuel', 'Welcome to FitFuel 🎉', 'Hi karlblockstock! Thanks for joining FitFuel. We’ll use this inbox for order updates, promos, and account notices.', 0, '2025-10-01 15:28:57'),
(3, 15, 'FitFuel', 'Welcome to FitFuel 🎉', 'Hi karlblockstock1! Thanks for joining FitFuel. We’ll use this inbox for order updates, promos, and account notices.', 0, '2025-10-05 12:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `user_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `push_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`user_id`, `email_enabled`, `sms_enabled`, `push_enabled`, `updated_at`) VALUES
(11, 1, 0, 1, '2025-10-27 08:09:52'),
(14, 1, 0, 1, '2025-10-01 15:28:52'),
(15, 1, 0, 1, '2025-10-05 12:05:58'),
(17, 0, 1, 0, '2025-09-26 20:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('card','bank') NOT NULL DEFAULT 'card',
  `brand` varchar(50) DEFAULT NULL,
  `last4` varchar(4) DEFAULT NULL,
  `name_on_card` varchar(100) DEFAULT NULL,
  `exp_month` tinyint(4) DEFAULT NULL,
  `exp_year` smallint(6) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_last4` varchar(4) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_payment_methods`
--

INSERT INTO `user_payment_methods` (`id`, `user_id`, `type`, `brand`, `last4`, `name_on_card`, `exp_month`, `exp_year`, `bank_name`, `account_last4`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 17, 'card', '2323', '9210', 'michieeee', 12, 2033, NULL, NULL, 1, '2025-09-26 22:23:58', '2025-09-26 22:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_privacy_settings`
--

CREATE TABLE `user_privacy_settings` (
  `user_id` int(11) NOT NULL,
  `profile_visibility` enum('public','friends','private') NOT NULL DEFAULT 'public',
  `show_email` tinyint(1) NOT NULL DEFAULT 1,
  `show_phone` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_privacy_settings`
--

INSERT INTO `user_privacy_settings` (`user_id`, `profile_visibility`, `show_email`, `show_phone`, `updated_at`) VALUES
(17, 'private', 0, 0, '2025-09-26 22:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`, `created_at`) VALUES
(4, 14, 144, '2025-10-27 09:26:59');

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
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

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
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `return_images`
--
ALTER TABLE `return_images`
  ADD PRIMARY KEY (`return_image_id`),
  ADD KEY `return_id` (`return_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `review_images`
--
ALTER TABLE `review_images`
  ADD PRIMARY KEY (`review_image_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

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
-- Indexes for table `user_inbox`
--
ALTER TABLE `user_inbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_privacy_settings`
--
ALTER TABLE `user_privacy_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

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
  MODIFY `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `order_promo_codes`
--
ALTER TABLE `order_promo_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `return_images`
--
ALTER TABLE `return_images`
  MODIFY `return_image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review_images`
--
ALTER TABLE `review_images`
  MODIFY `review_image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=788;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_inbox`
--
ALTER TABLE `user_inbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_returns_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_returns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `return_images`
--
ALTER TABLE `return_images`
  ADD CONSTRAINT `fk_return_images_return` FOREIGN KEY (`return_id`) REFERENCES `returns` (`return_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `review_images`
--
ALTER TABLE `review_images`
  ADD CONSTRAINT `fk_review_images_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE;

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

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
