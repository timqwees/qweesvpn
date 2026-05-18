-- Таблица пользователей
CREATE TABLE IF NOT EXISTS qwees_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL DEFAULT '',
    last_name TEXT NOT NULL DEFAULT '',
    uniID TEXT NOT NULL DEFAULT '',
    email TEXT NOT NULL DEFAULT '',
    myrefer TEXT DEFAULT NULL,
    refer TEXT DEFAULT NULL,
    refer_id INTEGER DEFAULT 0,
    refer_count INTEGER DEFAULT 0,
    discount_percent INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    bonus_percent INTEGER DEFAULT 0,
    UNIQUE (uniID),
    UNIQUE (email)
);

-- Таблица цен
CREATE TABLE IF NOT EXISTS qwees_price (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT DEFAULT NULL,
  `price` INTEGER DEFAULT NULL,
  UNIQUE (`name`)
);

-- Таблица рефералов
CREATE TABLE IF NOT EXISTS qwees_refer (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  uniID TEXT NOT NULL DEFAULT '',
  `refer` TEXT NOT NULL DEFAULT '',
  `me` TEXT NOT NULL DEFAULT '',
  `count` TEXT NOT NULL DEFAULT '',
  UNIQUE (uniID)
);

-- Таблица подписок
CREATE TABLE IF NOT EXISTS qwees_subscriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  uniID TEXT NOT NULL DEFAULT '',
  `status` TEXT NOT NULL DEFAULT 'off',
  subscription TEXT NOT NULL DEFAULT '',
  amount INTEGER DEFAULT NULL,
  count_days INTEGER DEFAULT NULL,
  count_devices INTEGER DEFAULT NULL,
  date_end TEXT NOT NULL DEFAULT '',
  payment_method_id TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (uniID),
  UNIQUE (status, date_end)
);

-- Данные по умолчанию
INSERT OR IGNORE INTO qwees_price (`name`, `price`) VALUES 
  ('basic', 150), 
  ('clasic', 180), 
  ('pro', 200)
;

INSERT INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, date_end, created_at, updated_at) VALUES
('qws6a077c4f1037c', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a077c4f1037c', 0, 60, 1, '2026-07-14', '2026-05-15 20:29:07', '2026-05-15 20:29:07'),
('qws6a07b1f2a1315', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07b1f2a1315', 0, 30, 1, '2026-06-15', '2026-05-15 23:57:06', '2026-05-16 05:05:39'),
('qws6a08c59a45f82', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a08c59a45f82', 0, 60, 1, '2026-07-15', '2026-05-16 19:30:55', '2026-05-16 19:30:55'),
('qws6a096266c4990', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a096266c4990', 0, 365, 1, '2027-05-17', '2026-05-17 06:40:54', '2026-05-17 06:40:54'),
('qws6a07799e2379b', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07799e2379b', 150.00, 30, 1, '2026-06-16', '2026-05-17 12:57:41', '2026-05-17 12:58:13');

INSERT INTO qwees_users (first_name, last_name, uniID, email) VALUES
('Tim', 'Qwees', 'qws6a077c4f1037c', 'timqwees@gmail.com')
;

