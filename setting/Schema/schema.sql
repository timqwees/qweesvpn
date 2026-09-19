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
    discount_uses INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_uniID (uniID),
    UNIQUE KEY unique_email (email),
    KEY idx_users_uniID (uniID),
    KEY idx_users_email (email)
);

-- Таблица рефералов (история: кто кого пригласил + какие бонусы выданы)
CREATE TABLE IF NOT EXISTS qwees_refer (
    id INT NOT NULL AUTO_INCREMENT,
    referrer_id INT NOT NULL DEFAULT 0,
    referrer_uniID VARCHAR(255) NOT NULL DEFAULT '',
    referral_id INT NOT NULL DEFAULT 0,
    referral_uniID VARCHAR(255) NOT NULL DEFAULT '',
    code VARCHAR(255) NOT NULL DEFAULT '',
    days_to_referral INT NOT NULL DEFAULT 0,
    days_to_referrer INT NOT NULL DEFAULT 0,
    discount_percent INT NOT NULL DEFAULT 0,
    takes_left INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_referral (referral_uniID),
    KEY idx_referrer (referrer_id)
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
    expiry BIGINT NOT NULL DEFAULT 0,
    payment_method_id VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_uniID (uniID),
    KEY idx_subscriptions_uniID (uniID),
    KEY idx_subscriptions_status (status),
    KEY idx_subscriptions_expiry (expiry)
);