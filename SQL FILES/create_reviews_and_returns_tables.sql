-- ============================================================================
-- Reviews and Ratings System
-- Run this SQL file ONCE to create the necessary tables
-- If tables already exist, you'll get errors - this is normal
-- ============================================================================

-- Create reviews table
-- Note: Rating validation is handled in application code
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create review_images table
CREATE TABLE IF NOT EXISTS `review_images` (
  `review_image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_order` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_image_id`),
  KEY `review_id` (`review_id`),
  CONSTRAINT `fk_review_images_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update products table to add rating fields
-- Note: If you get "Duplicate column name" errors, the columns already exist
-- Comment out the ALTER TABLE section below if columns already exist

-- Uncomment the ALTER TABLE statements below if needed:
/*
ALTER TABLE `products` 
ADD COLUMN `average_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
ADD COLUMN `total_reviews` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_5_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_4_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_3_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_2_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_1_count` int(11) NOT NULL DEFAULT 0;
*/

-- Similarly for order_items:
/*
ALTER TABLE `order_items`
ADD COLUMN `review_submitted` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `return_requested` tinyint(1) NOT NULL DEFAULT 0;
*/

-- ============================================================================
-- Returns and Refunds System  
-- ============================================================================

-- Create returns table
CREATE TABLE IF NOT EXISTS `returns` (
  `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`return_id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_returns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_returns_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create return_images table
CREATE TABLE IF NOT EXISTS `return_images` (
  `return_image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_order` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`return_image_id`),
  KEY `return_id` (`return_id`),
  CONSTRAINT `fk_return_images_return` FOREIGN KEY (`return_id`) REFERENCES `returns` (`return_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


