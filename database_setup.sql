-- FitFuel Database Setup
-- Run this SQL script to create the users table

CREATE DATABASE IF NOT EXISTS sia_fitfuel;
USE sia_fitfuel;

-- ========================================
-- USERS (Admin, Staff, Manager, Customer)
-- ========================================
CREATE TABLE users (
  user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,       -- Used for login
  email VARCHAR(150) UNIQUE NOT NULL,          -- Used for notifications & Google SSO
  password_hash VARCHAR(255),                  -- For admin/staff or non-SSO customers
  google_id VARCHAR(255),                      -- For Google SSO users (NULL if not SSO)
  role ENUM('admin','manager','staff','customer') NOT NULL DEFAULT 'customer',
  status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role, status) 
VALUES ('admin', 'admin@gmail.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', 'active');

-- Insert sample customer user (password: customer123)
INSERT INTO users (username, email, password_hash, role, status) 
VALUES ('customer', 'customer@gmail.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'customer', 'active');

-- Note: The password hashes above are for 'password' - change them in production!
-- To generate new password hashes, use: password_hash('your_password', PASSWORD_DEFAULT)
