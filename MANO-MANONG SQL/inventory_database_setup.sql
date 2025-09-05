-- FitFuel Inventory Management Database Setup
-- Run this SQL script to create the inventory table

USE sia_fitfuel;

-- ========================================
-- INVENTORY LOG
-- ========================================
CREATE TABLE `inventory` (
  `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `change_type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`inventory_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraints separately
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

-- Note: min_stock_level column already exists in products table

-- Insert sample inventory logs for existing products
INSERT INTO `inventory` (`product_id`, `change_type`, `quantity`, `created_by`) VALUES
(6, 'stock_in', 50, 4),
(7, 'stock_in', 100, 4);

-- Update products with minimum stock levels (using existing product IDs)
UPDATE `products` SET `min_stock_level` = 15 WHERE `product_id` = 6;
UPDATE `products` SET `min_stock_level` = 20 WHERE `product_id` = 7;
