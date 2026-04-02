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
    date_end VARCHAR(50) NOT NULL DEFAULT ''
);

-- Таблица цен
CREATE TABLE IF NOT EXISTS qwees_price (
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

--добавление цен по умолчанию
INSERT INTO qwees_price ("name", "price") VALUES ('basic', 100), ('clasic', 200), ('pro', 300);
