-- Таблица пользователей
CREATE TABLE IF NOT EXISTS qwees_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(50) NOT NULL DEFAULT '',
    last_name VARCHAR(50) NOT NULL DEFAULT '',
    uniID VARCHAR(50) NOT NULL DEFAULT '',
    email VARCHAR(50) NOT NULL DEFAULT '',
    "status" VARCHAR(50) NOT NULL DEFAULT 'off',
    subscription VARCHAR(255) NOT NULL DEFAULT '',
    amount VARCHAR(50) DEFAULT NULL,
    count_days VARCHAR(50) DEFAULT NULL,
    count_devices VARCHAR(50) DEFAULT NULL,
    date_end VARCHAR(50) NOT NULL DEFAULT '',
    myrefer VARCHAR(50) DEFAULT NULL,
    refer VARCHAR(50) DEFAULT NULL,
    refer_id INTEGER DEFAULT 0,
    refer_count INTEGER DEFAULT 0,
    discount_percent INTEGER DEFAULT 0,
    bonus_percent INTEGER DEFAULT 0
);

-- Таблица цен
CREATE TABLE IF NOT EXISTS qwees_price (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" VARCHAR(20) DEFAULT NULL,
  "price" INTEGER DEFAULT NULL
);

-- Таблица рефералов
CREATE TABLE IF NOT EXISTS qwees_refer (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  uniID VARCHAR(50) NOT NULL DEFAULT '',
  refer VARCHAR(50) NOT NULL DEFAULT '',
  me VARCHAR(50) NOT NULL DEFAULT '',
  count VARCHAR(255) NOT NULL DEFAULT ''
);

-- Таблица подписок (для примеров с timezone и датами)
CREATE TABLE IF NOT EXISTS qwees_subscriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL DEFAULT 0,
  price_id INTEGER NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES qwees_users(id),
  FOREIGN KEY (price_id) REFERENCES qwees_price(id)
);

--добавление цен по умолчанию
INSERT INTO qwees_price ("name", "price") VALUES ('basic', 100), ('clasic', 200), ('pro', 300);
--тестовый пользователь
INSERT INTO qwees_users (first_name, last_name, email, uniID, myrefer) VALUES ('tim', 'qwees', 'artemnersisyan777@gmail.com', 123, 'qwese');