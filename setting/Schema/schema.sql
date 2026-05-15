-- Таблица пользователей
CREATE TABLE IF NOT EXISTS qwees_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL DEFAULT '',
    last_name VARCHAR(50) NOT NULL DEFAULT '',
    uniID VARCHAR(50) NOT NULL DEFAULT '',
    email VARCHAR(50) NOT NULL DEFAULT '',
    myrefer VARCHAR(50) DEFAULT NULL,
    refer VARCHAR(50) DEFAULT NULL,
    refer_id INT DEFAULT 0,
    refer_count INT DEFAULT 0,
    discount_percent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bonus_percent INT DEFAULT 0,
    INDEX idx_users_uniID (uniID),
    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица цен
CREATE TABLE IF NOT EXISTS qwees_price (
  id INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(20) DEFAULT NULL,
  `price` INT DEFAULT NULL,
  INDEX idx_price_name (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица рефералов
CREATE TABLE IF NOT EXISTS qwees_refer (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uniID VARCHAR(50) NOT NULL DEFAULT '',
  `refer` VARCHAR(50) NOT NULL DEFAULT '',
  `me` VARCHAR(50) NOT NULL DEFAULT '',
  `count` VARCHAR(255) NOT NULL DEFAULT '',
  INDEX idx_refer_uniID (uniID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица подписок
CREATE TABLE IF NOT EXISTS qwees_subscriptions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uniID VARCHAR(50) NOT NULL DEFAULT '',
  `status` VARCHAR(50) NOT NULL DEFAULT 'off',
  subscription VARCHAR(255) NOT NULL DEFAULT '',
  amount VARCHAR(50) DEFAULT NULL,
  count_days VARCHAR(50) DEFAULT NULL,
  count_devices VARCHAR(50) DEFAULT NULL,
  date_end VARCHAR(50) NOT NULL DEFAULT '',
  payment_method_id VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_uniID (uniID),
  INDEX idx_subscriptions_uniID (uniID),
  INDEX idx_subscriptions_status (`status`),
  INDEX idx_subscriptions_date_end (date_end),
  INDEX idx_subscriptions_status_date (`status`, date_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Данные по умолчанию (безопасная вставка для MySQL)
INSERT INTO qwees_price (`name`, `price`) VALUES 
  ('basic', 150), 
  ('clasic', 180), 
  ('pro', 200)
ON DUPLICATE KEY UPDATE `price` = VALUES(`price`);