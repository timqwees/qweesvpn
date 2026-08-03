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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    bonus_percent INTEGER NOT NULL DEFAULT 0,
    UNIQUE (uniID),
    UNIQUE (email)
);

-- Таблица рефералов
CREATE TABLE IF NOT EXISTS qwees_refer (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uniID TEXT NOT NULL DEFAULT '',
    refer TEXT NOT NULL DEFAULT '',
    me TEXT NOT NULL DEFAULT '',
    count TEXT NOT NULL DEFAULT '',
    UNIQUE (uniID)
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