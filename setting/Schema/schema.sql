-- Таблица пользователей
CREATE TABLE IF NOT EXISTS qwees_users (
    id INT NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL DEFAULT '',
    last_name VARCHAR(255) NOT NULL DEFAULT '',
    uniID VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    myrefer VARCHAR(255) DEFAULT NULL,
    refer VARCHAR(255) DEFAULT NULL,
    refer_id INT DEFAULT 0,
    refer_count INT DEFAULT 0,
    discount_percent INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    bonus_percent INT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY unique_uniID (uniID),
    UNIQUE KEY unique_email (email),
    KEY idx_users_uniID (uniID),
    KEY idx_users_email (email)
);

-- Таблица цен
CREATE TABLE IF NOT EXISTS qwees_price (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) DEFAULT NULL,
    price INT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_name (name),
    KEY idx_price_name (name)
);

-- Таблица рефералов
CREATE TABLE IF NOT EXISTS qwees_refer (
    id INT NOT NULL AUTO_INCREMENT,
    uniID VARCHAR(255) NOT NULL DEFAULT '',
    refer VARCHAR(255) NOT NULL DEFAULT '',
    me VARCHAR(255) NOT NULL DEFAULT '',
    count VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY unique_uniID (uniID),
    KEY idx_refer_uniID (uniID)
);

-- Таблица подписок
CREATE TABLE IF NOT EXISTS qwees_subscriptions (
    id INT NOT NULL AUTO_INCREMENT,
    uniID VARCHAR(255) NOT NULL DEFAULT '',
    status VARCHAR(255) NOT NULL DEFAULT 'off',
    subscription VARCHAR(255) NOT NULL DEFAULT '',
    amount VARCHAR(255) DEFAULT NULL,
    count_days INT DEFAULT NULL,
    count_devices INT DEFAULT NULL,
    date_end VARCHAR(255) NOT NULL DEFAULT '',
    payment_method_id VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_uniID (uniID),
    KEY idx_subscriptions_uniID (uniID),
    KEY idx_subscriptions_status (status),
    KEY idx_subscriptions_date_end (date_end),
    KEY idx_subscriptions_status_date (status, date_end)
);