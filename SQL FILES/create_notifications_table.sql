-- Notifications table for user alerts (order status, promotions, etc.)
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- e.g., order_status, promotion, system
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) DEFAULT NULL, -- optional deep link to details
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: basic config storage for notifications and emails
CREATE TABLE IF NOT EXISTS notification_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO notification_settings (setting_key, setting_value) VALUES
    ('email_order_confirmation_enabled', '1'),
    ('notif_order_status_enabled', '1'),
    ('notif_promotions_enabled', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);


