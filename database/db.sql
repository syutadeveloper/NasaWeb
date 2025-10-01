CREATE DATABASE  nasaveb;

-- Таблица: subscriptions



CREATE TABLE IF NOT EXISTS subscriptions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(255) NOT NULL,
	category_weather TINYINT(1) NOT NULL DEFAULT 0,
	category_alerts TINYINT(1) NOT NULL DEFAULT 0,
	category_space TINYINT(1) NOT NULL DEFAULT 0,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица: feedback
CREATE TABLE IF NOT EXISTS feedback (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100),
	email VARCHAR(255),
	message TEXT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица: notifications_log
CREATE TABLE IF NOT EXISTS notifications_log (
	id INT AUTO_INCREMENT PRIMARY KEY,
	subscription_id INT NOT NULL,
	type VARCHAR(50) NOT NULL,
	status VARCHAR(50) NOT NULL,
	sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
);




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