-- Active: 1773826485163@@127.0.0.1@3306
CREATE TABLE IF NOT EXISTS vpn_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tg_id VARCHAR(50) NOT NULL DEFAULT '',
    tg_username VARCHAR(64) NOT NULL DEFAULT '',
    tg_first_name VARCHAR(50) NOT NULL DEFAULT '',
    tg_last_name VARCHAR(50) NOT NULL DEFAULT '',
    vpn_uuid VARCHAR(255) NOT NULL DEFAULT '',
    vpn_subscription VARCHAR(255) NOT NULL DEFAULT '',
    vpn_status VARCHAR(50) NOT NULL DEFAULT '',
    vpn_date_count VARCHAR(50) NOT NULL DEFAULT '',
    vpn_amount VARCHAR(50) NOT NULL DEFAULT '',
    vpn_divece_count VARCHAR(50) NOT NULL DEFAULT '',
    vpn_freekey VARCHAR(50) NOT NULL DEFAULT '',
    kassa_id VARCHAR(50) NOT NULL DEFAULT '',
    card_token VARCHAR(255) NOT NULL DEFAULT '',
    yk_autopay_active INTEGER NOT NULL DEFAULT 0,
    yk_autopay_id VARCHAR(255) DEFAULT '',
    yk_autopay_date VARCHAR(50) DEFAULT '',
    refer_link VARCHAR(50) NOT NULL DEFAULT '',
    my_refer_count INTEGER NOT NULL DEFAULT 0,
    my_refer_link VARCHAR(50) NOT NULL DEFAULT '',
    cid_ip VARCHAR(45) NOT NULL DEFAULT '',
    cid_user_agent VARCHAR(500) NOT NULL DEFAULT '',
    cid_device_type VARCHAR(50) NOT NULL DEFAULT '',
    cid_os VARCHAR(50) NOT NULL DEFAULT '',
    refer_discount INTEGER DEFAULT 0
);

-- Таблица для хранения pending реферальных кодов с привязкой к IP/CID данным
CREATE TABLE IF NOT EXISTS pending_refer_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    refer_code VARCHAR(50) NOT NULL,
    cid_ip VARCHAR(45) NOT NULL DEFAULT '',
    cid_user_agent VARCHAR(500) NOT NULL DEFAULT '',
    cid_device_type VARCHAR(50) NOT NULL DEFAULT '',
    cid_os VARCHAR(50) NOT NULL DEFAULT '',
    created_at INTEGER NOT NULL DEFAULT 0,
    expires_at INTEGER NOT NULL DEFAULT 0,
    activated INTEGER NOT NULL DEFAULT 0,
    activated_tg_id VARCHAR(50) DEFAULT ''
);

-- Таблица цен
CREATE TABLE IF NOT EXISTS vpn_price (
  basic INTEGER NOT NULL DEFAULT 150,
  plus INTEGER NOT NULL DEFAULT 280,
  pro INTEGER NOT NULL DEFAULT 380
);

-- Индексы для быстрого поиска
CREATE INDEX IF NOT EXISTS idx_pending_refer_ip ON pending_refer_codes(cid_ip);
CREATE INDEX IF NOT EXISTS idx_pending_refer_code ON pending_refer_codes(refer_code);
CREATE INDEX IF NOT EXISTS idx_pending_refer_expires ON pending_refer_codes(expires_at);
CREATE INDEX IF NOT EXISTS idx_pending_refer_activated ON pending_refer_codes(activated);

-- Таблица для хранения истории доходов партнеров по месяцам
CREATE TABLE IF NOT EXISTS partner_monthly_revenue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    partner_tg_id VARCHAR(50) NOT NULL,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL,
    revenue_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    referral_count INTEGER NOT NULL DEFAULT 0,
    created_at INTEGER NOT NULL DEFAULT 0,
    updated_at INTEGER NOT NULL DEFAULT 0,
    UNIQUE(partner_tg_id, year, month)
);

-- Индексы для таблицы доходов партнеров
CREATE INDEX IF NOT EXISTS idx_partner_monthly_partner ON partner_monthly_revenue(partner_tg_id);
CREATE INDEX IF NOT EXISTS idx_partner_monthly_period ON partner_monthly_revenue(year, month);
CREATE INDEX IF NOT EXISTS idx_partner_monthly_created ON partner_monthly_revenue(created_at);

--добавление цен по умолчанию
INSERT INTO vpn_price (basic, plus, pro) VALUES (150, 280, 380);
