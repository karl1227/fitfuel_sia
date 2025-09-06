-- FitFuel Product Management Database Setup
-- Run this SQL script to create the product-related tables

USE sia_fitfuel;

-- ========================================
-- CATEGORIES & SUBCATEGORIES
-- ========================================
CREATE TABLE categories (
  category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE subcategories (
  subcategory_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- ========================================
-- PRODUCTS
-- ========================================
CREATE TABLE products (
  product_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category_id BIGINT UNSIGNED,
  subcategory_id BIGINT UNSIGNED,
  images JSON,
  stock_quantity INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  is_popular BOOLEAN NOT NULL DEFAULT FALSE,
  is_best_seller BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(category_id),
  FOREIGN KEY (subcategory_id) REFERENCES subcategories(subcategory_id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Gym Equipment', 'Professional gym equipment and accessories'),
('Supplements', 'Nutritional supplements and protein powders'),
('Accessories', 'Fitness accessories and workout gear');

-- Insert sample subcategories
INSERT INTO subcategories (category_id, name, description) VALUES
(1, 'Dumbbells', 'Various types of dumbbells'),
(1, 'Barbells', 'Olympic and standard barbells'),
(1, 'Cardio Equipment', 'Treadmills, bikes, and cardio machines'),
(2, 'Protein Powder', 'Whey protein and plant-based proteins'),
(2, 'Pre-Workout', 'Energy and performance supplements'),
(2, 'Post-Workout', 'Recovery and muscle building supplements'),
(3, 'Gym Gloves', 'Workout gloves and hand protection'),
(3, 'Resistance Bands', 'Elastic resistance training bands'),
(3, 'Yoga Mats', 'Exercise and yoga mats');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, subcategory_id, stock_quantity, status, is_popular, is_best_seller) VALUES
('Adjustable Dumbbells Set', 'Professional adjustable dumbbells with weight plates', 299.99, 1, 1, 50, 'active', TRUE, TRUE),
('Whey Protein Powder', 'Premium whey protein isolate for muscle building', 49.99, 2, 4, 100, 'active', TRUE, FALSE),
('Pre-Workout Energy', 'High-energy pre-workout supplement', 29.99, 2, 5, 75, 'active', FALSE, TRUE),
('Gym Gloves', 'Professional workout gloves with grip', 19.99, 3, 7, 200, 'active', FALSE, FALSE),
('Resistance Band Set', 'Complete resistance band training set', 39.99, 3, 8, 150, 'active', TRUE, FALSE);
