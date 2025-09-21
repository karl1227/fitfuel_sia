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

-- ========================================
-- SHIPPING ADDRESSES
-- ========================================
CREATE TABLE shipping_addresses (
  address_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(20),
  address_line1 VARCHAR(255) NOT NULL,
  address_line2 VARCHAR(255),
  city VARCHAR(100),
  state VARCHAR(100),
  postal_code VARCHAR(20),
  country VARCHAR(100) DEFAULT 'Philippines',
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ========================================
-- CATEGORIES & SUBCATEGORIES
-- ========================================
CREATE TABLE categories (
  category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT
);

CREATE TABLE subcategories (
  subcategory_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
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

-- ========================================
-- ORDERS & ORDER ITEMS
-- ========================================
CREATE TABLE orders (
  order_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','processing','shipped','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  total_amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50),
  shipping_address_id BIGINT UNSIGNED,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (shipping_address_id) REFERENCES shipping_addresses(address_id)
);

CREATE TABLE order_items (
  order_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- ========================================
-- INVENTORY LOG
-- ========================================
CREATE TABLE inventory (
  inventory_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  change_type ENUM('stock_in','stock_out','adjustment') NOT NULL,
  quantity INT NOT NULL,
  reference_id BIGINT,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(product_id),
  FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- ========================================
-- ANALYTICS
-- ========================================
CREATE TABLE analytics (
  analytics_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  metric_type VARCHAR(50) NOT NULL,
  value DECIMAL(14,2) NOT NULL,
  related_id BIGINT,
  date_recorded DATE NOT NULL
);

-- ========================================
-- CONTENT MANAGEMENT (CMS)
-- ========================================
CREATE TABLE content (
  content_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('page','banner','promotion') NOT NULL,
  title VARCHAR(150),
  body TEXT,
  media_url VARCHAR(255),
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  created_by BIGINT UNSIGNED NOT NULL,
  published_at TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- ========================================
-- SETTINGS
-- ========================================
CREATE TABLE settings (
  setting_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(100) UNIQUE NOT NULL,
  value TEXT,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- NOTIFICATIONS
-- ========================================
CREATE TABLE notifications (
  notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('email','sms','in_app') NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  sent_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ========================================
-- AUDIT TRAIL
-- ========================================
CREATE TABLE audit_trail (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  action TEXT NOT NULL,
  target_table VARCHAR(100),
  target_id BIGINT,
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);
