-- Таблица пользователей
CREATE TABLE IF NOT EXISTS qwees_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL DEFAULT '',
    last_name TEXT NOT NULL DEFAULT '',
    uniID TEXT NOT NULL DEFAULT '',
    email TEXT NOT NULL DEFAULT '',
    myrefer TEXT DEFAULT NULL,
    refer TEXT DEFAULT NULL,
    refer_id INTEGER NOT NULL DEFAULT 0,
    refer_count INTEGER NOT NULL DEFAULT 0,
    discount_percent INTEGER NOT NULL DEFAULT 0,
    discount_uses INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (uniID),
    UNIQUE (email)
);

-- Таблица рефералов (история: кто кого пригласил + какие бонусы выданы)
CREATE TABLE IF NOT EXISTS qwees_refer (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    referrer_id INTEGER NOT NULL DEFAULT 0,
    referrer_uniID TEXT NOT NULL DEFAULT '',
    referral_id INTEGER NOT NULL DEFAULT 0,
    referral_uniID TEXT NOT NULL DEFAULT '',
    code TEXT NOT NULL DEFAULT '',
    days_to_referral INTEGER NOT NULL DEFAULT 0,
    days_to_referrer INTEGER NOT NULL DEFAULT 0,
    discount_percent INTEGER NOT NULL DEFAULT 0,
    takes_left INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (referral_uniID)
);

-- Таблица подписок
CREATE TABLE IF NOT EXISTS qwees_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uniID TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'off',
    subscription TEXT NOT NULL DEFAULT '',
    amount TEXT DEFAULT NULL,
    count_days INTEGER DEFAULT NULL,
    count_devices INTEGER DEFAULT NULL,
    expiry INTEGER NOT NULL DEFAULT 0,
    payment_method_id TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);