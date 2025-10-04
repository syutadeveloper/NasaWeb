CREATE DATABASE IF NOT EXISTS nasaveb;
USE nasaveb;

-- Таблица: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255),
    language CHAR(2) DEFAULT 'ru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица: user_devices (для push уведомлений)
CREATE TABLE IF NOT EXISTS user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    device_type ENUM('web','android','ios') NOT NULL,
    push_token TEXT,
    vapid_public_key TEXT,
    language CHAR(2) DEFAULT 'ru',
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица: alerts (оповещения о ЧС)
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source ENUM('usgs','firms','manual','weather','other') NOT NULL,
    type ENUM('earthquake','fire','flood','storm','air_quality','other') NOT NULL,
    magnitude DECIMAL(4,2) NULL,
    severity ENUM('info','advisory','warning','critical') NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_km DECIMAL(8,2) NULL,
    properties JSON,
    message_ru TEXT,
    message_en TEXT,
    message_kg TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    status ENUM('active','resolved','test') DEFAULT 'active'
);

-- Таблица: subscriptions (обновленная)
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    category_weather TINYINT(1) NOT NULL DEFAULT 0,
    category_alerts TINYINT(1) NOT NULL DEFAULT 0,
    category_space TINYINT(1) NOT NULL DEFAULT 0,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    radius_km DECIMAL(8,2) DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица: news_items (новости NASA)
CREATE TABLE IF NOT EXISTS news_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source ENUM('nasa','custom') NOT NULL,
    title_ru VARCHAR(500),
    title_en VARCHAR(500),
    title_kg VARCHAR(500),
    summary_ru TEXT,
    summary_en TEXT,
    summary_kg TEXT,
    body_ru TEXT,
    body_en TEXT,
    body_kg TEXT,
    media_url VARCHAR(1000),
    source_url VARCHAR(1000),
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица: audit_logs (логи рассылки)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NULL,
    recipients_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payload JSON,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE SET NULL
);

-- Таблица: feedback (обратная связь)
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица: notifications_log (история уведомлений)
CREATE TABLE IF NOT EXISTS notifications_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    alert_id INT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE SET NULL
);

-- Индексы для оптимизации
CREATE INDEX idx_alerts_location ON alerts(latitude, longitude);
CREATE INDEX idx_alerts_created ON alerts(created_at);
CREATE INDEX idx_alerts_type ON alerts(type);
CREATE INDEX idx_alerts_severity ON alerts(severity);
CREATE INDEX idx_user_devices_location ON user_devices(latitude, longitude);
CREATE INDEX idx_subscriptions_location ON subscriptions(latitude, longitude);
CREATE INDEX idx_news_published ON news_items(published_at);




-- Описание таблиц ниже
Таблица: subscriptions

Назначение: хранение e-mail подписок и выбранных категорий уведомлений.

Поле	Тип данных	Описание
id	INT, AUTO_INCREMENT, PRIMARY KEY	Уникальный идентификатор
email	VARCHAR(255)	e-mail пользователя
category_weather	TINYINT(1)	Подписка на погоду (0 = нет, 1 = да)
category_alerts	TINYINT(1)	Подписка на природные катастрофы (0 = нет, 1 = да)
category_space	TINYINT(1)	Подписка на новости NASA / космос (0 = нет, 1 = да)
created_at	TIMESTAMP DEFAULT CURRENT_TIMESTAMP	Дата подписки

Пример записи:

id	email	category_weather	category_alerts	category_space	created_at
1	ivan@mail.com
	1	1	1	2025-10-01 18:00
2️⃣ Таблица: feedback

Назначение: хранение обратной связи от пользователей.

Поле	Тип данных	Описание
id	INT, AUTO_INCREMENT, PRIMARY KEY	Уникальный идентификатор
name	VARCHAR(100)	Имя пользователя (опционально)
email	VARCHAR(255)	E-mail пользователя (опционально)
message	TEXT	Текст сообщения
created_at	TIMESTAMP DEFAULT CURRENT_TIMESTAMP	Дата и время отправки

Пример записи:

id	name	email	message	created_at
1	Ivan	ivan@mail.com
	Супер сайт, классно!	2025-10-01 18:05
3️⃣ (Опционально) Таблица: notifications_log

Назначение: хранение истории рассылки и уведомлений (для отладки или аналитики).

Поле	Тип данных	Описание
id	INT, AUTO_INCREMENT, PRIMARY KEY	Уникальный идентификатор
subscription_id	INT	Ссылка на пользователя (из subscriptions)
type	VARCHAR(50)	Тип уведомления: weather / alert / space
status	VARCHAR(50)	Статус: sent / failed
sent_at	TIMESTAMP DEFAULT CURRENT_TIMESTAMP